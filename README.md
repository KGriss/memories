# Projet de CSC4101 - Site web de souvenirs de voyages

## Contexte

Ce projet éducatif à pour but de création d'un site web permettant à ses utilisateurs de répertorier des souvenirs de leurs voyages passés.

Table de correspondance des termes (Guide de réalisation du projet) :
[objet] : *souvenir*
[inventaire] : *mémoire*
[galerie] : *voyage*
utilisateur : *utilisateur*

Tous le code sera écrit en anglais.
La convention de nommage suivra celle dominante en Symfony, le CamelCase.
On utilisera en priorité des *double_quotes*.

Les termes dans le code seront donc les suivants :
*souvenir* : `memory`
*mémoire* : `mind`
*voyage* : `travel`
*utilisateur* : `user`

Voici la spécification des classes :
`Memory` : name (string), picture (image optionnal), location (string optionnal), description (string)
`Mind` : memories (memory list)
`Travel` : name (string), frontPicture (image), memories (memory list)
`User` : _voir la suite du cours CSC4101_
