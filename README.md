![CI](https://github.com/Cocolis-1/cocolis-prestashop/workflows/CI/badge.svg)

# Cocolis prestashop

This is the repository for our Prestashop module to be able to add [Cocolis.fr](https://www.cocolis.fr/) carrier as a delivery method.

## Compatibility

| PrestaShop version | Module version |
|--------------------|----------------|
| 9.x                | 1.2.0 and later |
| 8.x                | 1.2.0 and later (not tested yet) |
| 1.7.x              | 1.2.0 and later (1.0.10 and earlier also work, but are no longer maintained) |
| 1.6.x and earlier  | Not supported |

PHP 5.6 or later, within the PHP versions supported by your PrestaShop version.

Version 1.2.0 was tested on PrestaShop 1.7.5, 1.7.8 and 9.1.5. Always upgrade to the latest
version: older versions no longer receive fixes, including security fixes, and cannot be installed
on PrestaShop 9. Shops installed with an older version are upgraded automatically when the new zip is
uploaded in *Modules > Module manager*.

# Documentation

You can find our documentation on [doc.cocolis.fr](https://doc.cocolis.fr/)

## Test it

You can use docker to test it :

`docker-compose up`

The validation procedure to run before each release is described in [docs/TESTING.md](docs/TESTING.md).
