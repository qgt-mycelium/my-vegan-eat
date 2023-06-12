# 🍃 MyVeganEat

## 🎯 Objectifs
- Retrouver des recettes et conseils afin de végétaliser son alimentation
- Découvrir des lieux qui servent des plats végans 
- Ajouter une partie budget/lifestyle

## 📑 Règles
- Symfony 6 et PHP8
- Interdiction d'utiliser le composant **Maker**
- PHPStan réglé au niveau max
- Maximum 3 requêtes SQL par requête HTTP
- Utiliser Tailwind CSS
- Test-Driven Development (TDD)

## 🐘 Fonctionnalités à concevoir
- Espace d'administration
    - Ajout d'un article *`(recette/plat/lieu/budget/lifestyle)`*
    - Modifier un article
    - Suprimmer un article
    - Lister,paginer, trier et filtrer les articles
    - Gestion des commentaires
- Espace client
    - Connexion ✔️
    - Inscription ✔️
    - Liker un article
    - Commenter un article
    - Modifier son avatar
    - Modifier ses informations
    - Supprimer un commentaire
    - Pouvoir repondre à un commentaire
    - Like un commentaire
    - Mettre des articles en favoris

## 🎒 Règles métier
- Un utilisateur possède
    - Une adresse mail ✔️ 
    - Un pseudo ✔️
    - Un mot de passe ✔️
    - Un avatar ✖️
- Un article posséde
    - Un titre
    - Un contenu
    - Une date de publication
    - Des likes
    - Des tags
    - Une liste de commentaires
    - SEO
        - Un title
        - Une meta description
- Un commentaire
    - A un auteur
    - Est rattché à un article
    - A une date de publication
    - Peut avoir des likes
    - Peut avoir des commentaires

## 🌿 Comment tester
```sh
composer ci # Lance l'analyse PHPStan et les tests
```