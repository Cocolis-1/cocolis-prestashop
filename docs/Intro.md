# Introduction

Bienvenue dans la documentation officielle du module PrestaShop de **Cocolis**. 
Ce module vous permet d'installer facilement notre solution de livraison sur votre site et de la proposer à tous vos clients sans frais supplémentaires.

Vous pouvez signaler des bugs sur cette [page](https://github.com/Cocolis-1/cocolis-prestashop/issues).

# Installation

Il existe plusieurs moyens d'installer le module sur votre site.

Avant toute chose, vous devez vous rendre dans le dossier "**modules**" de votre PrestaShop.

Le chemin est généralement celui-ci : **/var/www/modules/**

Installation en utilisant **composer** :

```bash
composer require cocolis/prestashop
```

Installation en utilisant **git** :

```bash
git clone https://github.com/Cocolis-1/cocolis-prestashop
```

Pour ces deux commandes, vous devez avoir un accès SSH sur votre serveur Web. Si vous n'avez qu'un accès FTP, téléchargez depuis GitHub le projet et mettez le manuellement dans le dossier "modules" de PrestaShop.

**Pour des raisons techniques, il est nécessaire de renommer le dossier du module par "cocolis".**

Vous pouvez le faire manuellement via un client FTP tel que FileZilla ou en ligne de commande SSH par la suivante :

`mv cocolis-prestashop cocolis`

Une fois le module dans le répertoir, il suffit de se connecter au back-office de votre PrestaShop et de vous rendre dans le Catalogue des modules.

Recherchez : **Cocolis** puis cliquer tout simplement sur **Installer**.

[Capture d'écran Catalogue Modules]

## Documentation API

> Il est déconseillé d'effectuer des modifications du code source, cela pourrait avoir des répercussions sur l'ensemble de vos annonces.


Le principe du module étant essentiellement basé sur la **documentation officielle de l'API et de la librairie PHP**, vous pouvez la retrouver sur **[https://doc.cocolis.fr/docs/cocolis-api](https://doc.cocolis.fr/docs/cocolis-api)**.

## Réinstallation

> En cas de réinstallation, veillez à ce que le mode de livraison avec assurance et sans assurance Cocolis dans le back-office ne soit pas en double, si cela est le cas, supprimez les doublons.

