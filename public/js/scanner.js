/**
 * scanner.js — Scan de codes-barres SIGDRI (sans CDN, compatible iOS Safari)
 * ===========================================================================
 * Trois modes selon le navigateur, choisis automatiquement :
 *
 *  iOS (tous)           → Mode C direct (saisie manuelle, pas de tentative caméra)
 *
 *  Mode A — BarcodeDetector natif  (Android Chrome 83+, Edge 83+)
 *    → getUserMedia + BarcodeDetector + détection auto toutes les 250 ms
 *
 *  Mode B — Caméra + saisie manuelle  (Android sans BarcodeDetector, Firefox…)
 *    → getUserMedia fonctionne mais BarcodeDetector absent
 *    → L'utilisateur voit son flux caméra et tape le code dans un champ overlay
 *
 *  Mode C — Saisie seule  (iOS, caméra refusée / indisponible)
 *    → Carte blanche centrée avec un champ texte
 *
 * Interface publique :
 *   SigdriScanner.lancerScan(inputDesignation, inputUniteMesure)
 *   SigdriScanner.fermer()
 */

const SigdriScanner = (function () {

  /* Flux caméra actif (MediaStream) */
  let streamActif  = null;
  /* Identifiant du setInterval de détection BarcodeDetector */
  let intervalScan = null;

  /* ── Détection iOS ──────────────────────────────────────────────────────────
     Sur iOS (Safari), getUserMedia + BarcodeDetector est peu fiable avant iOS 17.
     On bascule donc directement en saisie manuelle sans tenter la caméra.    */
  const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;

  /* ── Détection de la disponibilité de BarcodeDetector ── */
  function barcodeDetectorDispo() {
    return typeof BarcodeDetector !== 'undefined';
  }

  /* ── Stoppe le flux caméra et l'intervalle de détection ── */
  function libererCamera() {
    if (intervalScan) { clearInterval(intervalScan); intervalScan = null; }
    if (streamActif)  { streamActif.getTracks().forEach((t) => t.stop()); streamActif = null; }
    const video = document.getElementById('scanner-video');
    if (video) { video.srcObject = null; video.style.display = 'none'; }
  }

  /* ── Ferme le modal et remet toutes les sections à leur état initial ── */
  function fermer() {
    libererCamera();

    /* Cache le modal via style.display — même mécanisme qu'ouvrirModal() */
    const modal = document.getElementById('modal-scanner');
    if (modal) modal.style.display = 'none';

    /* Cache chaque section interne */
    ['scanner-viseur', 'scanner-overlay-saisie',
     'scanner-statut-bar', 'scanner-sec-solo'].forEach((id) => {
      const el = document.getElementById(id);
      if (el) el.style.display = 'none';
    });

    /* Vide les champs de saisie */
    ['scanner-code-video', 'scanner-code-solo'].forEach((id) => {
      const el = document.getElementById(id);
      if (el) el.value = '';
    });
  }

  /* ── Rend le modal visible — s'appuie sur style.display, pas sur Tailwind ── */
  function ouvrirModal() {
    const modal = document.getElementById('modal-scanner');
    if (!modal) return;
    modal.style.display = 'block';
  }

  /* ── Affiche la barre de statut inférieure (Mode A et phase de recherche) ── */
  function afficherStatutBar() {
    const el = document.getElementById('scanner-statut-bar');
    if (el) el.style.display = 'block';
  }

  /* ── Met à jour le texte de statut ── */
  function setStatut(message, estErreur = false) {
    const statusEl = document.getElementById('scanner-status');
    const errorEl  = document.getElementById('scanner-error');
    if (statusEl) statusEl.textContent = message;
    if (errorEl) {
      if (estErreur) { errorEl.textContent = message; errorEl.style.display = 'block'; }
      else errorEl.style.display = 'none';
    }
  }

  /* ────────────────────────────────────────────────────────────────────────
     MODE A : BarcodeDetector natif (Chrome, Edge, Safari iOS 17+)
     Détection automatique — aucune saisie requise de l'utilisateur.
  ──────────────────────────────────────────────────────────────────────── */
  async function modeAuto(callback) {
    ouvrirModal();
    afficherStatutBar();
    setStatut('Initialisation de la caméra…');

    try {
      /* Formats courants : le navigateur peut en ignorer certains */
      const detecteur = new BarcodeDetector({
        formats: ['ean_13', 'ean_8', 'qr_code', 'code_128', 'code_39', 'upc_a', 'upc_e'],
      });

      /* Préférence caméra arrière sur mobile */
      streamActif = await navigator.mediaDevices.getUserMedia({
        video: { facingMode: { ideal: 'environment' }, width: { ideal: 1280 }, height: { ideal: 720 } },
      });

      const video = document.getElementById('scanner-video');
      video.srcObject = streamActif;
      video.style.display = 'block';
      await video.play();

      /* Affiche le cadre de visée orange */
      const viseur = document.getElementById('scanner-viseur');
      if (viseur) viseur.style.display = 'flex';

      setStatut('Pointez la caméra vers le code-barres…');

      /* Boucle de détection toutes les 250 ms */
      intervalScan = setInterval(async () => {
        if (!video || video.readyState < video.HAVE_ENOUGH_DATA) return;
        try {
          const codes = await detecteur.detect(video);
          if (codes.length > 0) {
            fermer();
            callback(codes[0].rawValue);
          }
        } catch {
          /* Frame illisible : normal, on continue */
        }
      }, 250);

    } catch (err) {
      setStatut(err.message || 'Impossible d\'accéder à la caméra.', true);
    }
  }

  /* ────────────────────────────────────────────────────────────────────────
     MODE B : caméra disponible mais BarcodeDetector absent (iOS 14-16)
     L'utilisateur voit le flux vidéo et tape le code manuellement.
  ──────────────────────────────────────────────────────────────────────── */
  async function modeCameraManuel(callback) {
    ouvrirModal();
    afficherStatutBar();
    setStatut('Initialisation de la caméra…');

    try {
      /* Qualité réduite suffisante pour un viewfinder — économise la batterie */
      streamActif = await navigator.mediaDevices.getUserMedia({
        video: { facingMode: { ideal: 'environment' } },
      });

      const video = document.getElementById('scanner-video');
      video.srcObject = streamActif;
      video.style.display = 'block';
      await video.play();

      /* Masque la barre de statut, révèle l'overlay de saisie */
      const statusBar = document.getElementById('scanner-statut-bar');
      const overlay   = document.getElementById('scanner-overlay-saisie');
      if (statusBar) statusBar.style.display = 'none';
      if (overlay)   overlay.style.display   = 'block';

      /* Attache les handlers de validation */
      const inputCode = document.getElementById('scanner-code-video');
      const btnOK     = document.getElementById('scanner-btn-video');
      if (inputCode) setTimeout(() => inputCode.focus(), 200);
      bindSaisie(inputCode, btnOK, callback);

    } catch {
      /* Caméra refusée ou absente → bascule en saisie manuelle avec titre explicatif */
      libererCamera();
      modeSolo(callback, 'Caméra indisponible', 'Saisissez le code du produit manuellement.');
    }
  }

  /* ────────────────────────────────────────────────────────────────────────
     MODE C : saisie manuelle — carte blanche centrée
     Utilisé dans 2 cas :
       • iOS — chemin direct, on n'essaie jamais la caméra
       • Fallback — caméra refusée ou absente sur Android/desktop
     titre    (optionnel) : surcharge le titre affiché dans la carte
     sousTitre (optionnel) : surcharge le sous-titre
  ──────────────────────────────────────────────────────────────────────── */
  function modeSolo(callback, titre, sousTitre) {
    ouvrirModal();
    /* Cache la barre de statut si elle était visible (bascule depuis mode B) */
    const statusBar = document.getElementById('scanner-statut-bar');
    if (statusBar) statusBar.style.display = 'none';

    /* Met à jour dynamiquement le titre et le sous-titre si fournis */
    const titreEl     = document.getElementById('scanner-solo-titre');
    const sousTitreEl = document.getElementById('scanner-solo-sous');
    if (titreEl     && titre)     titreEl.textContent     = titre;
    if (sousTitreEl && sousTitre) sousTitreEl.textContent = sousTitre;

    const secSolo = document.getElementById('scanner-sec-solo');
    if (secSolo) secSolo.style.display = 'flex';

    const input = document.getElementById('scanner-code-solo');
    if (input) setTimeout(() => input.focus(), 80);

    bindSaisie(
      document.getElementById('scanner-code-solo'),
      document.getElementById('scanner-btn-solo'),
      callback
    );
  }

  /* ── Attache les handlers Entrée + clic sur un couple input/bouton ── */
  function bindSaisie(input, btn, callback) {
    if (!input) return;
    const valider = () => {
      const code = input.value.trim();
      if (!code) return;
      fermer();
      callback(code);
    };
    input.onkeydown = (e) => { if (e.key === 'Enter') valider(); };
    if (btn) btn.onclick = valider;
  }

  /* ── Interroge GET /api/produits/scan/{code} ── */
  async function rechercherProduit(code, onTrouve, onAbsent) {
    try {
      const rep = await fetch(`/api/produits/scan/${encodeURIComponent(code)}`, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin',
      });
      if (!rep.ok) throw new Error(`HTTP ${rep.status}`);
      const data = await rep.json();
      data.trouve ? onTrouve(data) : onAbsent(code, false);
    } catch {
      onAbsent(code, true);   /* hors-ligne ou erreur réseau */
    }
  }

  /* ────────────────────────────────────────────────────────────────────────
     POINT D'ENTRÉE PRINCIPAL
     Choisit le mode selon les capacités du navigateur, puis traite le code.
  ──────────────────────────────────────────────────────────────────────── */
  function lancerScan(inputDesignation, inputUniteMesure) {

    /* Callback partagé entre les 3 modes */
    const traiterCode = async (codeScanne) => {

      /* Ré-affichage du modal pendant la recherche API */
      ouvrirModal();
      afficherStatutBar();
      setStatut(`Code « ${codeScanne} » — Recherche du produit…`);

      await rechercherProduit(
        codeScanne,

        /* ── Produit trouvé ─────────────────────────────────── */
        (data) => {
          fermer();
          inputDesignation.value = data.nom;
          inputDesignation.dispatchEvent(new Event('input', { bubbles: true }));
          if (inputUniteMesure && data.unite) inputUniteMesure.value = data.unite;

          /* Flash vert 2 secondes pour indiquer le succès */
          inputDesignation.classList.add('!border-green-500', '!bg-green-50');
          setTimeout(() => inputDesignation.classList.remove('!border-green-500', '!bg-green-50'), 2000);
        },

        /* ── Code absent ou hors-ligne ──────────────────────── */
        (code, horsLigne) => {
          fermer();
          if (horsLigne) {
            inputDesignation.value = code;
          } else {
            inputDesignation.value = '';
            inputDesignation.placeholder = `Code « ${code} » inconnu — saisissez le nom`;
          }
          inputDesignation.focus();
        }
      );
    };

    /* ── Sélection du mode selon le navigateur ───────────────────────────────
       1. iOS (Safari)    → saisie manuelle directe, pas de tentative caméra
       2. Android Chrome  → BarcodeDetector auto si dispo, sinon caméra+manuel
       3. Autres          → même logique Android, ou saisie seule en dernier recours */
    if (isIOS) {
      /* iOS — saisie manuelle directe */
      modeSolo(traiterCode);
    } else if (barcodeDetectorDispo()) {
      /* Android Chrome / Edge / Safari iOS 17+ — scan auto */
      modeAuto(traiterCode);
    } else if (navigator.mediaDevices?.getUserMedia) {
      /* Android sans BarcodeDetector, Firefox — caméra + saisie overlay */
      modeCameraManuel(traiterCode);
    } else {
      /* Aucune caméra disponible */
      modeSolo(traiterCode, 'Caméra indisponible', 'Saisissez le code manuellement.');
    }
  }

  return { lancerScan, fermer };

})();
