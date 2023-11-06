# Introduction

Bienvenue dans la documentation officielle du module PrestaShop de **Cocolis**.
Ce module vous permet d'installer facilement notre solution de livraison sur votre site et de la proposer à tous vos clients sans frais supplémentaires.

Vous pouvez signaler des bugs sur cette [page](https://github.com/Cocolis-1/cocolis-prestashop/issues).

# Installation

Téléchargez la dernière version du module Cocolis (le **cocolis.zip**) : [ici](https://github.com/Cocolis-1/cocolis-prestashop/releases) 

![Capture écran Git](https://res.cloudinary.com/cocolis-prod/image/upload/v1631736838/Documentation/prestashop/GitHub%20Presta.png)

Rendez-vous ensuite dans **Catalogue de modules** :

![Catalogue de module](https://res.cloudinary.com/cocolis-prod/image/upload/v1631736982/Documentation/prestashop/Catalogue%20modules%20presta.png)

Cliquez ensuite sur **Installer un module** :

![Installer](https://res.cloudinary.com/cocolis-prod/image/upload/v1631737152/Documentation/prestashop/Installer%20un%20module%20Presta.png)

Glissez le zip dans l'installateur et vous êtes bon ! 😉

Rendez vous ensuite dans la partie [Configuration](Configuration.md) 

**Pour des raisons techniques, il est impératif que le fichier du module se nomme cocolis.zip.**

# Comment notre module fonctionne ?
![Workflow](https://res.cloudinary.com/cocolis-prod/image/upload/v1644931549/Documentation/prestashop/workflow_c46eaa.png)

## Réinstallation

> En cas de réinstallation, veillez à ce que le mode de livraison avec assurance et sans assurance Cocolis dans le back-office ne soit pas en double, si cela est le cas, supprimez les doublons.

# Alternative pour installer le module (les développeurs)

Avant toute chose, vous devez vous rendre dans le dossier "**modules**" de votre PrestaShop.

Le chemin est généralement celui-ci : **/var/www/modules/**

## Composer

```bash
composer require cocolis/prestashop
```

## Git

```bash
git clone https://github.com/Cocolis-1/cocolis-prestashop cocolis
```

Pour ces deux commandes, vous devez avoir un accès SSH sur votre serveur Web. Si vous n'avez qu'un accès FTP, téléchargez depuis GitHub le projet et mettez le manuellement dans le dossier "modules" de PrestaShop.

## Documentation API

Le principe du module étant essentiellement basé sur la **documentation officielle de l'API et de la librairie PHP**, vous pouvez la retrouver **[ici](https://doc.cocolis.fr/docs/cocolis-api)**.

