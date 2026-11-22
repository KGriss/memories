# Projet de CSC4101 - Site web de souvenirs de voyages

Lucas THURIES

Thème : site web de collections de souvenirs de voyages (Projet intitulé Memories).

## Contexte

Ce projet éducatif à pour but de création d'un site web permettant à ses utilisateurs de répertorier des souvenirs de leurs voyages passés.

Table de correspondance des termes (Guide de réalisation du projet) :\
[objet] : *souvenir*\
[inventaire] : *mémoire*\
[galerie] : *voyage*\
utilisateur : *utilisateur*

Tous le code sera écrit en anglais.\
La convention de nommage suivra celle dominante en Symfony, le CamelCase.\
On utilisera en priorité des *single_quotes*, comme le code généré par Symfony et Doctrine.

Les termes dans le code seront donc les suivants :\
*souvenir* : `memory`\
*mémoire* : `mind`\
*voyage* : `travel`\
*utilisateur* : `member`

Voici la spécification des classes :\
`Memory` : name (string), description (string), location (string optionnal), imageFile (image optionnal, VichUploader)\
`Mind` : description (string), memories (memory list)\
`Travel` : name (string), description (string), published (boolean), creator (Member, créateur), memories (memory list)\
`Member` (utilisateur) : email (string), name (string, nom de profil public), roles (list), password (string hashé), mind (Mind)

Associations :
- `Member` (1) — (1) `Mind` : OneToOne
- `Mind` (1) — (0..n) `Memory` : OneToMany
- `Member` (1) — (0..n) `Travel` : OneToMany (créateur du voyage)
- `Travel` (0..n) — (0..n) `Memory` : ManyToMany (un souvenir peut figurer dans plusieurs voyages)

## Avancement du projet

Voir la check-list (TODO.md).

## Pour tester le programme

### Installation

```
symfony composer install
```

La base de données SQLite (`var/data_dev.db`) est fournie avec les données de test. Pour la recréer et recharger les données de test (fixtures, avec les images d'exemple de `resources/images/`) :
```
symfony console doctrine:database:drop --force
symfony console doctrine:schema:create
symfony console doctrine:fixtures:load
```

### Lancement

```
symfony server:start
```
-> Se rendre ensuite sur l'URL affichée par la commande (par défaut http://127.0.0.1:8000/), qui redirige vers les voyages publics `/travel` (ou vers sa fiche membre si on est connecté, la liste des membres pour l'administrateur)

### Comptes de test et rôles

Comptes de test (chargés par les fixtures, `symfony console doctrine:fixtures:load`, voir `src/DataFixtures/UserFixtures.php`) :
- lucas@localhost / 123456 (membre "Lucas", ROLE_USER) : inventaire de 3 souvenirs, 2 voyages (dont 1 non publié)
- alice@localhost / 123456 (membre "Alice", ROLE_USER) : inventaire de 3 souvenirs, 2 voyages (dont 1 non publié)
- admin@localhost / 123456 ("Administrateur", ROLE_ADMIN) : pas d'inventaire, accès à toutes les données

Le menu "Login" (en haut à droite) mène au formulaire de connexion, "Logout" permet de se déconnecter.

### Parcours de test des éléments obligatoires

Les inventaires et les souvenirs sont privés : il faut être connecté (par exemple avec lucas@localhost) pour les consulter.

1. Consultation des souvenirs d'un inventaire (gabarits) : se connecter avec lucas@localhost -> on arrive sur sa fiche membre -> lien "mon inventaire" (`/mind/1`) : liste des souvenirs de l'inventaire -> clic sur un souvenir : fiche du souvenir (`/memory/1`), avec son image.
2. Mise en forme Bootstrap : toutes les pages (thème Start Bootstrap "Shop Homepage", menus, formulaires Bootstrap 5).
3. Entité voyage et association M-N avec les souvenirs : menu "Voyages" (`/travel`) -> fiche d'un voyage, avec la liste de ses souvenirs. Le souvenir "Week-end à Paris" apparaît dans les deux voyages de Lucas.
4. CRUD des voyages : depuis la fiche membre de Lucas, lien "Create new" (création), puis liens "edit" et bouton "Delete" depuis la fiche d'un voyage.
5. Création d'un souvenir dans le contexte de l'inventaire : dans l'inventaire de Lucas, lien "Ajouter un souvenir" (`/memory/new/1`) ; le champ "Mind" est pré-rempli et grisé.
6. Mise en ligne des images : champ "Image file" des formulaires de création et de modification d'un souvenir ; l'image s'affiche sur la fiche du souvenir.
7. Authentification : menus Login / Logout, comptes et rôles ci-dessus.

### Éléments optionnels réalisés

- Accès réservé aux membres connectés : `/member`, `/mind`, `/memory` redirigent vers la page de connexion si on n'est pas connecté. Les voyages publiés restent visibles par tous.
- Accès réservé aux propriétaires : connecté avec lucas@localhost, `/mind/2`, `/memory/4` ou `/travel/4` (voyage non publié d'Alice) renvoient une erreur 403. L'administrateur a accès à tout.
- Listes selon l'utilisateur connecté : un membre ne voit que ses propres données ; la liste des voyages affiche les voyages publiés et les siens (publiés ou non).
- Ajout des souvenirs dans un voyage : formulaire de création / modification d'un voyage (cases à cocher), limité aux souvenirs du créateur du voyage.
- Messages flash après chaque création, modification ou suppression.
- Favoris (marque-pages en session) : sur la page d'un souvenir consulté depuis un voyage (par exemple `/travel/1/memory/1`), lien "Ajouter aux favoris".

### Limites connues et choix

- La classe des utilisateurs s'appelle `Member` (comme dans le guide de réalisation) et non `User` (comme dans le cahier des charges).
- Les voyages publiés sont visibles par les visiteurs non connectés, comme l'indique le guide (§16.1).
- Pas de page d'accueil publique ni de back-office (EasyAdmin) : la racine `/` redirige vers les voyages publics.
- Les pages générées par les assistants (make:crud, make:security:form-login) sont restées en anglais.
- Un souvenir s'ajoute à un voyage uniquement depuis le formulaire du voyage.
- Les favoris s'affichent sur la page de chaque souvenir ; il n'y a pas de page listant tous les favoris.
- Les images téléversées sont stockées dans `public/images/memories/` ; chaque `doctrine:fixtures:load` y ajoute une nouvelle copie des images d'exemple.
