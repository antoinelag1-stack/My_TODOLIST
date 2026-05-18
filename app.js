// Configuration du path absolu vers l'api.php pour éviter fautes de frappe car répétition
const API_URL = '/my_todolist/api.php';


// Etat de l'app
// Ces variables gardent en mémoire les filtres actifs et la page courante
let etat = {
    priorite : '',
    tri      : 'date_creation',
    ordre    : 'DESC',
    limite   : 10,
    page     : 1
};


// Initialisation : exécuté au chargement
document.addEventListener('DOMContentLoaded', () => {
    chargerTaches();
    ecouterFiltres();
    ecouterBoutons();
});


// Charger et afficher les tâches
async function chargerTaches() {

    // On construit l'URL avec les paramètres actuels de l'état de l'app sous forme 'etat.paramètre'
    const params = new URLSearchParams({
        action   : 'liste',
        priorite : etat.priorite,
        tri      : etat.tri,
        ordre    : etat.ordre,
        limite   : etat.limite,
        page     : etat.page
    });

    try {
        const reponse = await fetch(`${API_URL}?${params}`); //

        if (!reponse.ok) {
            throw new Error('Erreur serveur : ' + reponse.status);
        }

        const taches = await reponse.json();
        afficherTaches(taches);

    } catch (erreur) {
        afficherNotif('Impossible de charger les tâches. Vérifiez votre connexion.');
    }
}


// Afficher les 'taches' chargées juste au-dessus dans les colonnes
function afficherTaches(taches) {

    // On vide les 3 colonnes avant de les remplir
    const colonnes = {
        'à faire'  : document.getElementById('cards-afaire'),
        'en cours' : document.getElementById('cards-encours'),
        'terminée' : document.getElementById('cards-terminee')
    };

    // Compteurs pour les headers de colonnes
    const compteurs = { 'à faire': 0, 'en cours': 0, 'terminée': 0 };

    // On vide chaque colonne de son contenu
    Object.values(colonnes).forEach(col => col.innerHTML = '');

    // On distribue chaque tâche dans la bonne colonne
    taches.forEach(tache => {
        const statut = tache.statut.toLowerCase();
        if (colonnes[statut]) {
            colonnes[statut].appendChild(creerCard(tache));
            compteurs[statut]++;
        }
    });

    // On met à jour les compteurs dans les headers
    document.getElementById('count-afaire').textContent  = compteurs['à faire'];
    document.getElementById('count-encours').textContent = compteurs['en cours'];
    document.getElementById('count-terminee').textContent = compteurs['terminée'];
}


// Créer une card HTML pour une tâche en paramètre
function creerCard(tache) {

    // On vérifie si la date d'échéance est dépassée
    const aujourd_hui  = new Date();
          aujourd_hui.setHours(0, 0, 0, 0);
    const date_echeance = new Date(tache.date_echeance);
    const en_retard     = date_echeance < aujourd_hui;

    // On choisit la classe CSS selon la priorité pour l'affichage dans la card
    const classePrio = {
        'haute'   : 'prio-haute',
        'normale' : 'prio-normale',
        'basse'   : 'prio-basse'
    }[tache.priorite] || 'prio-normale';

    // On formate la date en français pour normaliser 
    const date_formatee = date_echeance.toLocaleDateString('fr-FR');

    // On crée l'élément HTML div de la card
    const card = document.createElement('div');
    card.className = 'card';
    card.dataset.id = tache.id; // On stocke l'id de la div dans un attribut data

    // On construit le HTML intérieur de la card
    card.innerHTML = `
        <div class="card-top">
            <span class="card-titre">${htmlEchapper(tache.titre)}</span>
            <span class="prio ${classePrio}">${htmlEchapper(tache.priorite)}</span>
        </div>
        ${tache.description //Si présente, sinon on met ''
            ? `<p class="card-desc">${htmlEchapper(tache.description)}</p>`
            : ''}
        <div class="card-date ${en_retard ? 'retard' : ''}">
            Échéance : ${date_formatee}${en_retard ? ' — en retard' : ''}
        </div>
        <div class="card-actions">
            <select class="card-select" data-id="${tache.id}">
                <option value="à faire"  ${tache.statut === 'à faire'  ? 'selected' : ''}>À faire</option>
                <option value="en cours" ${tache.statut === 'en cours' ? 'selected' : ''}>En cours</option>
                <option value="terminée" ${tache.statut === 'terminée' ? 'selected' : ''}>Terminée</option>
            </select>
            <button class="card-btn btn-edit" data-id="${tache.id}">Modifier</button>
            <button class="card-btn btn-del"  data-id="${tache.id}">Supprimer</button>
        </div>
    `;

    return card;
}


// SÉCURITÉ : on créé htmlEchapper() pour un équivalent JS de htmlspecialchars()
// pour empêcher l'injection de HTML dans les cards
function htmlEchapper(str) {
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}


// Notifications visuelles succès/echec
function afficherNotif(message, type = 'erreur') {
    const notif = document.getElementById('notif');
    notif.textContent  = message;
    notif.className    = `notif notif-${type}`;
    notif.style.display = 'block';

    // Disparaît automatiquement après 5 secondes
    setTimeout(() => {
        notif.style.display = 'none';
    }, 5000);
}


// Ecouter les filtres saisis
// Détecte les changements sur les selects de la barre de filtres et relance le chargement
function ecouterFiltres() {

    document.getElementById('filtre-priorite').addEventListener('change', (e) => {
        etat.priorite = e.target.value;
        etat.page = 1; // On revient à la page 1 quand on change un filtre
        chargerTaches();
    });

    document.getElementById('filtre-tri').addEventListener('change', (e) => {
        etat.tri = e.target.value;
        etat.page = 1;
        chargerTaches();
    });

    document.getElementById('filtre-limite').addEventListener('change', (e) => {
        etat.limite = e.target.value;
        etat.page = 1;
        chargerTaches();
    });

    document.getElementById('filtre-ordre').addEventListener('change', (e) => {
    etat.ordre = e.target.value;
    etat.page  = 1;
    chargerTaches();
});
}


// Ecouter les filtes
// Gère tous les clics de la page en appellant les fonctions créées
function ecouterBoutons() {

    // Bouton "Nouvelle tâche"
    document.getElementById('btn-nouvelle-tache').addEventListener('click', () => {
        ouvrirModaleCreation();
    });

    // Bouton "Annuler" dans la modale
    document.getElementById('btn-annuler').addEventListener('click', () => {
        fermerModale();
    });

    // Clic sur le fond sombre de la modale = fermer
    document.getElementById('modal-overlay').addEventListener('click', (e) => {
        if (e.target === document.getElementById('modal-overlay')) {
            fermerModale();
        }
    });

    // Délégation d'événements sur le kanban entier
    // On écoute les clics sur le kanban plutôt que sur chaque bouton grâce aux fonctions créés juste après
    // car les cards sont créées dynamiquement par le JS, elles n'existent pas encore au chargement de la page
    document.getElementById('cards-afaire').addEventListener('click',  gererClicCard);
    document.getElementById('cards-encours').addEventListener('click', gererClicCard);
    document.getElementById('cards-terminee').addEventListener('click', gererClicCard);

    // Délégation pour les selects de statut dans les cards
    document.getElementById('cards-afaire').addEventListener('change',  gererChangeStatut);
    document.getElementById('cards-encours').addEventListener('change', gererChangeStatut);
    document.getElementById('cards-terminee').addEventListener('change', gererChangeStatut);

    // Soumission du formulaire de la modale
    document.getElementById('form-tache').addEventListener('submit', (e) => {
        e.preventDefault(); // Empêche le rechargement de page
        soumettreFormulaire();
    });
}


// Gérer les clics sur les cards
// Détecte si c'est "Modifier" ou "Supprimer"
function gererClicCard(e) {
    const btn = e.target.closest('.card-btn');
    if (!btn) return; // Clic ailleurs sur la card, on ignore

    const id = btn.dataset.id;

    if (btn.classList.contains('btn-edit')) {
        ouvrirModaleModification(id);
    }

    if (btn.classList.contains('btn-del')) {
        supprimerTache(id);
    }
}


// Gérer le changement de statut
// Déclenché quand on change le select d'une card
function gererChangeStatut(e) {
    const select = e.target.closest('.card-select');
    if (!select) return;

    const id     = select.dataset.id;
    const statut = select.value;

    changerStatut(id, statut);
}


// Modale : Ouverture et fermeture
function ouvrirModaleCreation() {

    // On remet le formulaire à zéro
    document.getElementById('form-tache').reset();
    document.getElementById('tache-id').value = ''; // id vide = création

    // On adapte les textes de la modale
    document.getElementById('modal-eyerow').textContent      = 'Nouvelle tâche';
    document.getElementById('modal-titre-label').textContent = 'Créer une tâche';
    document.getElementById('btn-submit-tache').textContent  = 'Créer';

    document.getElementById('modal-overlay').classList.add('active');
}

async function ouvrirModaleModification(id) {
    // On récupère les données actuelles de la tâche depuis l'API
    const params = new URLSearchParams({ action: 'liste' });
    const reponse = await fetch(`${API_URL}?${params}`);
    const taches  = await reponse.json();
    const tache   = taches.find(t => t.id == id);

    if (!tache) return;

    // On pré-remplit le formulaire avec les données existantes
    document.getElementById('tache-id').value          = tache.id;
    document.getElementById('tache-titre').value       = tache.titre;
    document.getElementById('tache-description').value = tache.description ?? '';
    document.getElementById('tache-date').value        = tache.date_echeance;
    document.getElementById('tache-priorite').value    = tache.priorite;

    // On adapte les textes de la modale
    document.getElementById('modal-eyerow').textContent      = 'Modifier la tâche';
    document.getElementById('modal-titre-label').textContent = 'Modifier une tâche';
    document.getElementById('btn-submit-tache').textContent  = 'Enregistrer';

    document.getElementById('modal-overlay').classList.add('active');
}

function fermerModale() {
    document.getElementById('modal-overlay').classList.remove('active');
    document.getElementById('form-tache').reset();
}


// Soumettre le formulaire
// Création ou modification selon si tache-id est rempli
async function soumettreFormulaire() {

    const id = document.getElementById('tache-id').value;

    if (id) {
        await modifierTache(id);
    } else {
        await creerTache();
    }
}


// Créer une tâche
async function creerTache() {

    // On récupère les valeurs du formulaire
    const donnees = new FormData();
    donnees.append('titre',         document.getElementById('tache-titre').value);
    donnees.append('description',   document.getElementById('tache-description').value);
    donnees.append('date_echeance', document.getElementById('tache-date').value);
    donnees.append('priorite',      document.getElementById('tache-priorite').value);

    try {
        const reponse = await fetch(`${API_URL}?action=creer`, {
            method : 'POST',
            body   : donnees
        });

        if (!reponse.ok) {
            const erreur = await reponse.json();
            alert(erreur.erreur);
            return;
        }

        fermerModale();
        afficherNotif('Tâche créée avec succès.', 'succes');
        chargerTaches(); // On recharge les tâches pour afficher la nouvelle

    } catch (erreur) {
        afficherNotif('Impossible de créer la tâche. Vérifiez votre connexion.');
    }
}


// Modifier une tâche
async function modifierTache(id) {

    // PUT envoie les données en JSON dans le corps de la requête
    const donnees = {
        id            : parseInt(id),
        titre         : document.getElementById('tache-titre').value,
        description   : document.getElementById('tache-description').value,
        date_echeance : document.getElementById('tache-date').value,
        priorite      : document.getElementById('tache-priorite').value
    };

    try {
        const reponse = await fetch(`${API_URL}?action=modifier`, {
            method  : 'PUT',
            headers : { 'Content-Type': 'application/json' },
            body    : JSON.stringify(donnees)
        });

        if (!reponse.ok) {
            const erreur = await reponse.json();
            alert(erreur.erreur);
            return;
        }

        fermerModale();
        afficherNotif('Tâche modifiée avec succès.', 'succes');
        chargerTaches();

    } catch (erreur) {
        afficherNotif('Impossible de modifier la tâche. Vérifiez votre connexion.');
    }
}


// Changer le statut d'une tâche
async function changerStatut(id, statut) {

    try {
        const reponse = await fetch(`${API_URL}?action=statut`, {
            method  : 'PATCH',
            headers : { 'Content-Type': 'application/json' },
            body    : JSON.stringify({ id: parseInt(id), statut: statut })
        });

        if (!reponse.ok) {
            const erreur = await reponse.json();
            console.error('Erreur statut :', erreur);
            return;
        }

        chargerTaches(); // On recharge pour déplacer la card dans la bonne colonne

    } catch (erreur) {
        afficherNotif('Changement de statut impossible. Vérifiez votre connexion.');
    }
}


// Supprimer une tâche
async function supprimerTache(id) {

    // On demande confirmation avant de supprimer
    if (!confirm('Supprimer cette tâche ?')) return;

    try {
        const reponse = await fetch(`${API_URL}?action=supprimer`, {
            method  : 'DELETE',
            headers : { 'Content-Type': 'application/json' },
            body    : JSON.stringify({ id: parseInt(id) })
        });

        if (!reponse.ok) {
            const erreur = await reponse.json();
            console.error('Erreur suppression :', erreur);
            return;
        }

        afficherNotif('Tâche supprimée.', 'succes');
        chargerTaches();

    } catch (erreur) {
        afficherNotif('Impossible de supprimer la tâche. Vérifiez votre connexion.');
    }
}