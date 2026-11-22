# Projet de CSC4101 - Site web de souvenirs de voyages

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
*utilisateur* : `user`

Voici la spécification des classes :\
`Memory` : name (string), description (string), location (string optionnal), picture (image optionnal)\
`Mind` : description (string), memories (memory list)\
`Travel` : name (string), description (string), published (boolean), frontPicture (image optionnal), member (Member, créateur), memories (memory list)\
`Member` (utilisateur) : email (string), roles (list), password (string hashé), mind (Mind)

Associations :
- `Member` (1) — (1) `Mind` : OneToOne
- `Mind` (1) — (0..n) `Memory` : OneToMany
- `Member` (1) — (0..n) `Travel` : OneToMany (créateur du voyage)
- `Travel` (0..n) — (0..n) `Memory` : ManyToMany (un souvenir peut figurer dans plusieurs voyages)

## Prise de note (partielle)

Guide de réalisation du projet [https://www-inf.telecom-sudparis.eu/COURS/CSC4101/projet/](https://www-inf.telecom-sudparis.eu/COURS/CSC4101/projet/)

Pour créer une entité (ou ajouter une propriété à une entité déjà existente) :
```symfony console make:entity```

Pour mettre à jour de force la base de donnée (n'utilise PAS les migration) (SQLite) :
```
symfony console doctrine:database:drop
symfony console doctrine:schema:create
```

Sinon, pour mettre à jour classiquement :
```
symfony console doctrine:schema:update
```

## Avancement du projet

Prochaine étape : 8

## Pour tester le programme

```
symfony server:start
```
-> Se rendre ensuite sur l'URL affichée par la commande (par défaut http://127.0.0.1:8000/), qui redirige vers la liste des inventaires `/mind`

Comptes de test (chargés par les fixtures, `symfony console doctrine:fixtures:load`) :
- lucas@localhost / 123456
- alice@localhost / 123456