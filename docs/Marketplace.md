---
tags: [override, marketplace]
---

# Marketplace

Il est possible que vous ayez besoin d'adapter le module Cocolis à un plugin marketplace déjà existant.

Face à la multitude de solutions proposés par Prestashop, **nous ne sommes pas en mesure de faire une version universelle.**

C'est pour cela que nous avons mis en place la possibilité d'**override** notre module pour changer l'adresse d'expédition des colis.

Pour ce faire, il est nécessaire que vous ayez **quelques notions de développement**. Vous pouvez demander l'adaptation du module à quelqu'un de spécialisé ou faire appel à nous si besoin.

## Override

Un exemple d'override est déjà présent dans notre module (commenté). Il se trouve dans `/override/modules/cocolis/cocolis.php`.

**Le nom de la société qui expédie :**

```php
  public static function getName()
  {
    return "CocolisOverride";
  }
```

**L'adresse :**

```php
  public static function getAddress()
  {
    return "12 rue de Lourmel";
  }
```

**Le code postal :**

```php
  public static function getZip()
  {
    return 75015;
  }
```
**La ville :**

```php
  public static function getCity()
  {
    return "Paris";
  }
```

**Le pays :**

```php
  public static function getCountry()
  {
    return "FR"; // Format ISO 3166-1 alpha-2
  }
```

Si vous avez besoin d'avoir des précisions sur les techniques d'override sous Prestashop vous pouvez consulter ce [lien](https://build.prestashop.com/howtos/module/how-to-override-modules/).