# Webhooks

Les webhooks sont là pour informer votre système des événements qui surviennent coté Cocolis pendant la vie de votre annonce. Ils vous permettent de les exploiter, par exemple pour informer votre acheteur et/ou votre vendeur de l'avancée de la livraison.

### Créer un Webhook

Vous pouvez créer un webhook (en respectant les paramètres requis [ici](https://doc.cocolis.fr/docs/cocolis-api/docs/apidoc/schema_swagger_json.json/paths/~1applications~1webhooks/post)) :

```php
$params = [
  'event' => 'ride_published', 
  'url' => 'https://www.test.com/ride_webhook', 
  'active' => true
];
$client->getWebhookClient()->create($params);
```

Les paramètres `$params` sont sous la forme d'un tableau, on utilise la même forme que la documentation de l'API.

### Mettre à jour un Webhook

Vous pouvez mettre à jour un webhook (en respectant les paramètres requis [ici](https://doc.cocolis.fr/docs/cocolis-api/docs/apidoc/schema_swagger_json.json/paths/~1applications~1webhooks~1%7Bid%7D/put)) :

```php
$params = [
  'event' => 'offer_accepted', 
  'url' => 'https://www.test.com/ride_webhook', 
  'active' => true
];
$webhook = $client->getWebhookClient()->update($params, $id);
```

Nous reprenons la même forme que lors de la création d'un Webhook, cependant on rajoute un paramètre celui du `$id` de notre Webhook.

### Récupérer tous les Webhooks

Vous pouvez récupérer la liste de tous les Webhooks sous la forme d'un tableau de `Cocolis\Api\Models\Webhook` avec `$id`, `$event`, etc ...

```php
$webhooks = $client->getWebhookClient()->getAll();
```

### Récupérer un Webhook

Il est possible de récupérer un Webhook précis sous la forme d'un objet en fournissant l'`$id` du Webhook en paramètre :

```php
$webhook = $client->getWebhookClient()->get($id);
```

### Supprimer un Webhook

En reprenant le même principe que pour **récupérer un Webhook**, vous pouvez le supprimer de cette façon :

```php
$client->getWebhookClient()->remove($id);
```