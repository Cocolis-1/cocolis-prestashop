# Changelog

All notable changes to this module are documented in this file.

## [1.3.0] - 2026-10-13

### Added

- Connection with an API key, created in the Cocolis account (*API keys* page). The API key
  replaces the application ID and password, which still work when no API key is saved.
- The configuration page checks the credentials when they are saved, and invites the shops still
  connected with the application ID and password to create an API key.

### Fixed

- PHP notices on the configuration page after clicking "Automatically configure webhooks" in
  debug mode.

## [1.2.0] - 2026-10-12

Compatible with PrestaShop 1.7 to 9 (tested on 1.7.5, 1.7.8 and 9.1.5), with PHP 7.1 or later.

### Added

- Cocolis tracking tab on the order page of PrestaShop 1.7.7 and later
  (`displayAdminOrderTabLink` / `displayAdminOrderTabContent`).
- The weight class and a photo of each product are sent with the ride.
- Clear messages on the configuration page when the credentials are missing or rejected, or when
  the Cocolis API is unavailable.

### Fixed

- The module could not be installed on PrestaShop 9 in debug mode: hooks without any matching
  method are no longer registered, and are removed during the upgrade.
- The shipping price could be computed with the stock quantity instead of the cart quantity,
  giving a much higher price.
- The shipping price and the goods value sent to Cocolis were truncated (12.50 € was sent as
  12 €).
- A product without image made the ride creation fail on PHP 8.
- A missing shop phone number stopped the payment of the order. The error is now logged in
  Advanced parameters > Logs, like any other ride creation error.
- The customer mobile phone is used when the phone field of the address is empty.
- The configuration form no longer erases the saved password when the password field is left
  empty, and the webhook button saves the credentials before configuring the webhooks.
- The carriers created by the module are disabled when it is uninstalled.

### Removed

- The terms and conditions of the former insurance carrier.
- The former insurance carrier ("Livraison Cocolis avec assurance"), still active on the shops
  installed with 1.0.10 or 1.1.0, is deleted during the upgrade. Past orders keep their carrier.

## [1.1.1] - 2026-10-12

### Security

- Security hardening of the webhook endpoint. Upgrading is strongly recommended for all shops.
  Thanks to [@ytokan](https://github.com/ytokan) for the report.

  Webhook calls are now authenticated with the `X-Cocolis-Signature` header. The signing secret
  is fetched automatically from the Cocolis API with the credentials of the module.

### Fixed

- Order statuses were never updated by the Cocolis webhooks: the module did not read the JSON
  payload sent by Cocolis.
- The `offer_cancelled` webhook URL contained a double slash (`//index.php`).
- Use `PrestaShopLogger` instead of the deprecated `Logger` alias.
- The order tracking block (customer order detail and back office) looked up the Cocolis history
  with the order carrier ID instead of the order ID, showing no data or another order's data.
- Webhook payloads longer than 255 characters were truncated, which broke the link to the Cocolis
  ride. The `webhook_params` column is now a `TEXT` column.
- The order tracking block loaded the ride with the offer ID for offer events, and could break the
  order page when the Cocolis API was unavailable.
- The link to the ride on the customer order detail page led to a page not found.
