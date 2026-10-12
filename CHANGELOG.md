# Changelog

All notable changes to this module are documented in this file.

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
