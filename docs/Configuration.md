# Configuration

Une fois le module installé, il est nécessaire d'effectuer quelques réglages.

![Capture d'écran de la page de configuration](https://res.cloudinary.com/cocolis-prod/image/upload/v1605524040/Documentation/prestashop/config-module_u3nv4c.png)

## Authentification

> Avant toute chose, vous devez avoir un compte développeur, vous trouverez plus d'information ici :
> [Demander un compte développeur](https://doc.cocolis.fr/docs/cocolis-api/docs/Tutoriel-impl%C3%A9mentation/Getting-Started.md#2-demander-un-compte-d%C3%A9veloppeur)

Renseignez par la suite les **champs d'authentification** qui se trouvent en bas de page.

## Environnements

Il existe **deux environnements**, l'environnement de test (**sandbox**) et l'environnement de **production**, vous pouvez en savoir plus [ici](https://doc.cocolis.fr/docs/cocolis-api/docs/Installation-et-utilisation/01-Environnements.md).

Choisissez en fonction de votre utilisation le mode désiré.

## Valeurs par défaut

PrestaShop ne nous permettant pas de définir des valeurs par défaut pour tous les produits mis en ligne, certaines valeurs sont à renseigner.

Vos fiches produits doivent en temps normal comporter : 
- Le poids
- La largeur
- La longueur 
- La hauteur 

Si certains produits sont absents de ces valeurs, le module ira chercher **les valeurs par défaut** définies dans la page de configuration du module, à appliquer pour les frais de livraison.

## Expédition

Pour calculer les frais de livraison, le module doit se baser sur l'adresse de votre entrepôt.

**Nous ne prenons pas l'adresse configurée dans PrestaShop**, car celle-ci peut ne pas être actualisée ou être celle de facturation.

L'adresse postale utilisée par le module se trouve dans la configuration du module, **vous devez obligatoirement la configurer**.

![Capture d'écran Expedition](https://res.cloudinary.com/cocolis-prod/image/upload/v1605524040/Documentation/prestashop/from-config-module_klm3ky.png)

## Numéro de téléphone

Le numéro de téléphone du client et du vendeur est **obligatoire** pour le bon déroulé des livraisons.

Vous devez au préalable fournir un numéro de téléphone dans les paramètres de votre boutique, le procédé est le suivant :

- Paramètres de la boutique > Contact > Magasins > Coordonnées

Dans la section **Coordonnées**, en bas de page, renseignez un numéro de téléphone.

Vous devez aussi rendre obligatoire la saisie du numéro de téléphone pour tous vos clients, pour se faire allez dans :

- Clients > Adresses >  Définir les champs requis pour cette section

Activez l'option "**phone**" puis validez.

C'est terminé pour la configuration dans le PrestaShop !

![Téléphone](https://res.cloudinary.com/cocolis-prod/image/upload/v1606291408/Documentation/prestashop/image_2_ls5ufw.png)

## Configuration automatique des Webhooks

Après avoir effectué toute la configuration, vous pouvez sur votre site de production, configurer automatiquement les webhooks en cliquant sur le bouton **"Configurer automatiquement les Webhooks"**.

Cela veut dire qu'à chaque nouvelle étape de livraison, l'API de Cocolis vous enverra des "notifications" sous la forme de webhooks.