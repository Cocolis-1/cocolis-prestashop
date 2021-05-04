# Introduction

Bienvenue dans la documentation officielle du module PrestaShop de **Cocolis**.
Ce module vous permet d'installer facilement notre solution de livraison sur votre site et de la proposer à tous vos clients sans frais supplémentaires.

Vous pouvez signaler des bugs sur cette [page](https://github.com/Cocolis-1/cocolis-prestashop/issues).

# Installation

Il existe plusieurs moyens d'installer le module sur votre site.


**Pour des raisons techniques, il est impératif que le dossier du module se nomme "cocolis".**

Une fois le module dans le répertoire, il suffit de se connecter au back-office de votre PrestaShop et de vous rendre dans le Catalogue des modules.

Recherchez : **Cocolis** puis cliquer tout simplement sur **Install**.

![Capture d'écran Catalogue Modules](https://res.cloudinary.com/cocolis-prod/image/upload/v1605524040/Documentation/prestashop/install-module_cagxy9.png)

## Réinstallation

> En cas de réinstallation, veillez à ce que le mode de livraison avec assurance et sans assurance Cocolis dans le back-office ne soit pas en double, si cela est le cas, supprimez les doublons.

# Pour les développeurs

Avant toute chose, vous devez vous rendre dans le dossier "**modules**" de votre PrestaShop.

Le chemin est généralement celui-ci : **/var/www/modules/**

## Composer

```bash
composer require cocolis/prestashop
```

## Marketpace Prestashop

@TODO

## Git

```bash
git clone https://github.com/Cocolis-1/cocolis-prestashop cocolis
```

Pour ces deux commandes, vous devez avoir un accès SSH sur votre serveur Web. Si vous n'avez qu'un accès FTP, téléchargez depuis GitHub le projet et mettez le manuellement dans le dossier "modules" de PrestaShop.

## Documentation API

Le principe du module étant essentiellement basé sur la **documentation officielle de l'API et de la librairie PHP**, vous pouvez la retrouver sur **[https://doc.cocolis.fr/docs/cocolis-api](https://doc.cocolis.fr/docs/cocolis-api)**.

