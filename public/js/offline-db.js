/**
 * offline-db.js — Gestion IndexedDB pour le mode hors-ligne SIGDRI
 * ================================================================
 * Base de données : "sigdri-offline" (version 1)
 * Magasin         : "declarations_en_attente"
 *
 * Permet de stocker les déclarations saisies sans connexion
 * et de les soumettre automatiquement à la reconnexion.
 *
 * API publique :
 *   SigdriOfflineDB.sauvegarder(donnees)       → Promise<id>
 *   SigdriOfflineDB.lireTous()                 → Promise<entrée[]>
 *   SigdriOfflineDB.supprimer(id)              → Promise<void>
 *   SigdriOfflineDB.compter()                  → Promise<number>
 *   SigdriOfflineDB.synchroniser()             → Promise<résultat[]>
 */

const SigdriOfflineDB = (function () {

  const NOM_DB     = 'sigdri-offline';
  const VERSION_DB = 1;
  const STORE      = 'declarations_en_attente';

  /* ── Ouverture/création de la base IndexedDB ── */
  function ouvrirDB() {
    return new Promise((resolve, reject) => {
      const req = indexedDB.open(NOM_DB, VERSION_DB);

      req.onerror = () => reject(new Error('IndexedDB inaccessible : ' + req.error?.message));

      req.onsuccess = () => resolve(req.result);

      req.onupgradeneeded = (event) => {
        const db    = event.target.result;
        const store = db.createObjectStore(STORE, {
          keyPath:       'id',
          autoIncrement: true,
        });
        // Index chronologique pour lire les entrées dans l'ordre d'insertion
        store.createIndex('timestamp', 'timestamp', { unique: false });
        // Index sur le statut pour filtrer les entrées "en_attente"
        store.createIndex('statut', 'statut', { unique: false });
      };
    });
  }

  /**
   * Sérialise un FormData en objet JS plat (compatible IndexedDB).
   * Les champs multi-valeurs (checkboxes, tableaux) sont stockés en tableau.
   */
  function serializerFormData(formData) {
    const obj = {};
    for (const [cle, valeur] of formData.entries()) {
      if (obj[cle] !== undefined) {
        // Deuxième valeur pour la même clé → convertir en tableau
        if (!Array.isArray(obj[cle])) obj[cle] = [obj[cle]];
        obj[cle].push(valeur);
      } else {
        obj[cle] = valeur;
      }
    }
    return obj;
  }

  /**
   * Reconstruit un FormData à partir de l'objet sérialisé.
   */
  function deserializerVersFormData(donnees) {
    const fd = new FormData();
    for (const [cle, valeur] of Object.entries(donnees)) {
      if (Array.isArray(valeur)) {
        valeur.forEach((v) => fd.append(cle, v));
      } else {
        fd.append(cle, valeur);
      }
    }
    return fd;
  }

  /* ── Sauvegarde d'une déclaration hors-ligne ── */
  async function sauvegarder(formDataOuObjet) {
    const db = await ouvrirDB();
    const donnees = formDataOuObjet instanceof FormData
      ? serializerFormData(formDataOuObjet)
      : formDataOuObjet;

    // Retirer le CSRF token — il sera récupéré frais lors de la synchronisation
    delete donnees['_token'];

    return new Promise((resolve, reject) => {
      const tx    = db.transaction(STORE, 'readwrite');
      const store = tx.objectStore(STORE);
      const entree = {
        donnees,
        timestamp:  Date.now(),
        statut:     'en_attente',   // en_attente | en_cours | echoue
        tentatives: 0,
      };
      const req      = store.add(entree);
      req.onsuccess  = () => resolve(req.result);  // id auto-généré
      req.onerror    = () => reject(req.error);
    });
  }

  /* ── Lecture de toutes les déclarations en attente ── */
  async function lireTous() {
    const db = await ouvrirDB();
    return new Promise((resolve, reject) => {
      const tx    = db.transaction(STORE, 'readonly');
      const store = tx.objectStore(STORE);
      const index = store.index('statut');
      const req   = index.getAll('en_attente');
      req.onsuccess = () => resolve(req.result);
      req.onerror   = () => reject(req.error);
    });
  }

  /* ── Suppression après synchronisation réussie ── */
  async function supprimer(id) {
    const db = await ouvrirDB();
    return new Promise((resolve, reject) => {
      const tx    = db.transaction(STORE, 'readwrite');
      const store = tx.objectStore(STORE);
      const req   = store.delete(id);
      req.onsuccess = () => resolve();
      req.onerror   = () => reject(req.error);
    });
  }

  /* ── Mise à jour du statut d'une entrée ── */
  async function majStatut(id, statut) {
    const db = await ouvrirDB();
    return new Promise((resolve, reject) => {
      const tx    = db.transaction(STORE, 'readwrite');
      const store = tx.objectStore(STORE);
      const get   = store.get(id);
      get.onsuccess = () => {
        const entree = get.result;
        if (!entree) return resolve();
        entree.statut = statut;
        entree.tentatives += 1;
        const put     = store.put(entree);
        put.onsuccess = () => resolve();
        put.onerror   = () => reject(put.error);
      };
      get.onerror = () => reject(get.error);
    });
  }

  /* ── Compte le nombre de déclarations en attente (pour le badge UI) ── */
  async function compter() {
    const db = await ouvrirDB();
    return new Promise((resolve, reject) => {
      const tx    = db.transaction(STORE, 'readonly');
      const store = tx.objectStore(STORE);
      const index = store.index('statut');
      const req   = index.count('en_attente');
      req.onsuccess = () => resolve(req.result);
      req.onerror   = () => reject(req.error);
    });
  }

  /**
   * Obtient un CSRF token frais depuis le serveur.
   * L'endpoint /csrf-token est déclaré dans routes/web.php.
   */
  async function obtenirCsrfToken() {
    try {
      const rep = await fetch('/csrf-token', {
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json' },
      });
      if (rep.ok) {
        const data = await rep.json();
        return data.token;
      }
    } catch { /* ignoré si hors-ligne */ }
    // Fallback : token depuis la méta-balise de la page courante
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
  }

  /**
   * Synchronise toutes les déclarations en attente vers le serveur.
   * Appelé automatiquement à la reconnexion via l'événement "online".
   * Retourne un tableau de résultats {id, statut}.
   */
  async function synchroniser() {
    const enAttente = await lireTous();
    if (enAttente.length === 0) return [];

    const token    = await obtenirCsrfToken();
    const resultats = [];

    for (const decl of enAttente) {
      try {
        await majStatut(decl.id, 'en_cours');

        // Reconstruction du FormData
        const fd = deserializerVersFormData(decl.donnees);
        if (token) fd.set('_token', token);

        const reponse = await fetch('/industriel/declarations', {
          method:      'POST',
          body:        fd,
          credentials: 'same-origin',
          redirect:    'follow',
        });

        // 200/302/303 = succès (Laravel redirige après store())
        if (reponse.ok || reponse.redirected || reponse.status < 400) {
          await supprimer(decl.id);
          resultats.push({ id: decl.id, statut: 'ok' });
        } else {
          // Erreur serveur (validation échouée, etc.)
          await majStatut(decl.id, 'echoue');
          resultats.push({ id: decl.id, statut: 'erreur_serveur', code: reponse.status });
        }
      } catch (err) {
        // Erreur réseau → remettre en attente pour la prochaine tentative
        await majStatut(decl.id, 'en_attente');
        resultats.push({ id: decl.id, statut: 'erreur_reseau', message: err.message });
      }
    }

    /* Notifie la page du résultat de la synchronisation.
       Les pages abonnées à "sigdri:sync-complete" peuvent afficher un bandeau
       ou mettre à jour leur UI sans avoir besoin d'appeler synchroniser() elles-mêmes. */
    if (typeof window !== 'undefined') {
      window.dispatchEvent(new CustomEvent('sigdri:sync-complete', {
        bubbles:    false,
        cancelable: false,
        detail:     { resultats },
      }));
    }

    return resultats;
  }

  /* ── Déclenchement automatique de la sync au retour de connexion ──────────
     Enregistré une seule fois au chargement du script. Fonctionne sur toutes
     les pages où offline-db.js est inclus, sans code supplémentaire par page. */
  if (typeof window !== 'undefined') {
    window.addEventListener('online', () => {
      synchroniser().catch(() => { /* erreur réseau : la prochaine reconnexion réessaiera */ });
    });
  }

  /* ── Interface publique ── */
  return { sauvegarder, lireTous, supprimer, compter, synchroniser };

})();
