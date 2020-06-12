# Introduction

Cette librairie a été conçue pour aider les développeurs à intégrer les fonctionnalités de **Cocolis** dans leur application sans gérer les appels vers l'API.

Elle inclut toutes les fonctionnalités globales tel que **l'authentification**, la gestion des **Rides** et des **Webhooks**.

Vous pouvez signaler des bugs sur cette [page](https://github.com/Cocolis-1/cocolis-php/issues).

# Installation

Installation en utilisant composer : 

```bash
composer require cocolis/php
```

# Principe général

La librairie est essentiellement constituée de classes dont l'une des plus importante, **Cocolis\Api\Client**. Celle-ci permet d'instancier l'authentification et de récupérer les tokens nécessaires pour chaque appel API.

Il existe deux autres classes, **Cocolis\Api\Clients\RideClient** et **Cocolis\Api\Clients\WebhookClient**. Celles-ci permettent d'effectuer tous les appels pour créer une Ride, vérifier la compatibilité d'un trajet, etc ... 

Le principe général de cette librairie est : Un Cocolis Client par model (endpoint REST)

Leur utilisatation sera détaillée dans la suite de la documentation.

# Documentation API

Le principe de la librairie étant essentiellement basé sur la **documentation officielle de l'API**, vous pouvez la retrouver sur **[https://doc.cocolis.fr/docs/cocolis-api](https://doc.cocolis.fr/docs/cocolis-api)**.

## Authentification

> Avant toute chose, vous devez avoir un compte développeur, vous trouverez plus d'information ici : 
> [Demander un compte développeur](https://doc.cocolis.fr/docs/cocolis-api/docs/Tutoriel-impl%C3%A9mentation/Getting-Started.md#2-demander-un-compte-d%C3%A9veloppeur)

Avec la librairie, vous pouvez vous authentifier facilement de cette façon et **une seule fois** :

```php
$client = Client::create(array(
    'app_id' => 'mon_appid',
    'password' => 'mon_mot_de_passe',
    'live' => false // Permet de choisir l'environnement
  ));
$client->signIn(); // Cet appel fait l'authentification
```

Vous n'avez plus qu'à utiliser l'objet `$client` pour effectuer un appel.

Par exemple, pour **vérifier la disponibilité** d'une Ride :

```php
$result = $client->getRideClient()->canMatch(75000, 31400, 10); // Code postal de départ, Code postal d'arrivé, Volume en m3 de l'objet à transporter
```

Une fois authentifié, vous pouvez effectuer des **requêtes annexes** à l'API de cette façon :

```php
$client->callAuthentificated('app_auth/validate_token', 'GET', $params);
```

Dans cet exemple, `app_auth/validate_token` est équivalent à faire un appel vers : 
`{cocolis_domain}/api/v1/app_auth/validate_token` de type `GET` avec les `$params` fournis sous la forme d'un **array**.

## Validation du token

A chaque début de communication avec l'API, vous pouvez vérifier si vos tokens sauvés et précedemment générés lors du `$client->signIn()` sont toujours valides. Pour cela, vous devez faire l'appel suivant :

```php
$authinfo = ["uid" => "e0611906", "access-token" => "thisisnotavalidtoken", "client" => "HLSmEW1TIDqsSMiwuKjnQg", "expiry" => "1590748027"]
$client->validateToken($authinfo);
```

Le `$authinfo` n'est pas un paramètre obligatoire, il permet de tester la validité d'autres paramètres d'authentification.

Si `$authinfo` n'est pas spécifié, ça utilisera ceux du dernier appel de `signIn()`

L'appel renvoie une réponse trouvable dans le `body` avec `"success": boolean` ou bien un code HTTP 200 qui permet de déterminer la validité des informations d'authentification.

Si le token n'est plus valide, il suffit de refaire un `$client->signIn()`

## Environnements

Il existe **deux environnements**, l'environnement de test (**sandbox**) et l'environnement de **production**, vous pouvez en savoir plus [ici](https://doc.cocolis.fr/docs/cocolis-api/docs/Installation-et-utilisation/01-Environnements.md).

