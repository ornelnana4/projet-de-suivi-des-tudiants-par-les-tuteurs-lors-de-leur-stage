# Visites Tuteurs – Projet Symfony

Application de gestion des visites de tuteurs auprès des étudiants, réalisée avec Symfony, Doctrine, Twig et Bootstrap.  
Elle permet à un tuteur connecté de gérer ses étudiants, planifier des visites, remplir des comptes‑rendus et exporter ces derniers en PDF.

## 1. Fonctionnalités

### Gestion des tuteurs
- Authentification du tuteur (login / logout).
- Affichage du nom du tuteur dans la barre de navigation.
- Accès aux étudiants et visites uniquement après connexion.

### Gestion des étudiants
- Liste des étudiants associés au tuteur connecté.
- Création d’un étudiant.
- Modification d’un étudiant.
- Navigation rapide :
  - Bouton **Voir visites** pour accéder aux visites d’un étudiant.
  - Bouton **Ajouter visite** pour créer une visite directement pour cet étudiant.

### Gestion des visites
- Liste des visites d’un étudiant sélectionné (`/etudiants/{id}/visites`).
- Création d’une visite pour un étudiant (`/etudiants/{id}/visites/new`) avec préremplissage :
  - Étudiant concerné.
  - Tuteur connecté.
  - Statut initial : `prévue`.
- Modification d’une visite (`/visites/{id}/edit`).
- Suppression d’une visite (`/visites/delete/{id}`).

### Statut de visite et filtres
- Champ **statut** sur l’entité `Visite` avec valeurs possibles :
  - `prévue`
  - `réalisée`
  - `annulée`
- Filtre par statut dans la liste des visites :
  - Formulaire GET avec un `<select>` : `Toutes`, `Prévue`, `Réalisée`, `Annulée`.
- Tri des visites par date :
  - Liens **Trier ↑** (ascendant) et **Trier ↓** (descendant).
  - Implémenté via une méthode personnalisée dans `VisiteRepository` (requête Doctrine avec `orderBy`).

### Compte‑rendu de visite
- Page de compte‑rendu pour une visite (`/visites/{id}/compte-rendu`) avec :
  - Affichage :
    - Étudiant concerné (nom, prénom, formation).
    - Date de la visite.
    - Commentaire initial.
    - Statut actuel.
  - Formulaire pour saisir / modifier le champ `compteRendu`.
- Export du compte‑rendu en **PDF** :
  - Route dédiée (`/visites/{id}/compte-rendu/pdf`).
  - Génération du PDF à partir d’un template Twig HTML.

### Mise en forme (Bootstrap)
- Navbar Bootstrap sur toutes les pages importantes :
  - Titre de l’application.
  - Nom du tuteur.
  - Liens vers le dashboard, la liste des étudiants, et bouton **Déconnexion**.
- Mise en page Bootstrap :
  - Tables `table table-hover` pour les listes (étudiants, visites).
  - Cartes (`card`) pour les formulaires et les sections de détail.
  - Boutons cohérents (actions principales en vert/bleu, actions secondaires en outline).

## 2. Stack technique

- **Backend** : PHP, Symfony
- **Base de données** : MySQL / MariaDB (Doctrine ORM)
- **Templates** : Twig
- **Frontend** : Bootstrap 5 (CSS + composants)
- **PDF** : Librairie PHP pour transformer un template HTML en PDF (type Dompdf)

## 3. Installation

**1. Cloner le dépôt :

```bash
##git clone <url-du-repo>
cd <nom-du-dossier>
Installer les dépendances PHP :

bash
composer install
Configurer l’accès à la base de données dans .env :

text
DATABASE_URL="mysql://user:password@127.0.0.1:3306/visites_tuteurs?serverVersion=8.0"
Créer la base et appliquer le schéma :

bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
Lancer le serveur de développement :

bash
symfony server:start
# ou
php -S 127.0.0.1:8000 -t public
Puis ouvrir http://127.0.0.1:8000 dans le navigateur.

4. Structure principale
src/Entity/Etudiant.php : entité étudiant (nom, prénom, formation, etc.).

src/Entity/Tuteur.php : entité tuteur (utilisée pour la connexion).

src/Entity/Visite.php : entité visite (date, commentaire, compteRendu, statut, relations vers Étudiant et Tuteur).

src/Controller/EtudiantController.php : CRUD des étudiants.

src/Controller/VisiteController.php :

Liste, création, modification, suppression des visites.

Compte‑rendu et export PDF.

src/Repository/VisiteRepository.php :

Méthode personnalisée findByEtudiantWithFilters() pour filtrer et trier les visites.

templates/etudiant/*.html.twig : pages liées aux étudiants.

templates/visite/*.html.twig : pages liées aux visites (liste, formulaire, compte‑rendu, PDF).

5. Utilisation
Se connecter en tant que tuteur.

Gérer les étudiants (ajout, modification).

Depuis la liste des étudiants :

utiliser Voir visites pour accéder aux visites d’un étudiant,

utiliser Ajouter visite pour planifier une nouvelle visite.

Dans la liste des visites :

filtrer par statut,

trier par date,

modifier / supprimer une visite,

ouvrir la page Compte‑rendu.

Dans la page compte‑rendu :

saisir le texte,

enregistrer,

exporter en PDF si nécessaire.

6. Évolutions possibles
Gestion des droits plus fine (un tuteur ne voit que ses propres étudiants / visites).
Envoi du compte‑rendu par email au tuteur ou à l’étudiant.

