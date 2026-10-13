# Module validation procedure

Run this procedure before each release, on the oldest and on the newest supported PrestaShop
versions (currently 1.7.5 and 9.x). It takes about one hour per version with the Cocolis sandbox.

Write the results in the release pull request: PrestaShop and PHP versions, order and ride IDs,
and any step that failed or was skipped.

## 1. Prerequisites

- Docker, [ngrok](https://ngrok.com/) and Composer.
- A Cocolis **sandbox** application (App ID and password) and a sandbox account able to make an
  offer on a ride.
- The module built for production. The development dependencies (php-cs-fixer) need PHP 8 and
  make the module crash on older PHP versions:

  ```sh
  composer install --no-dev
  ```

  Run `composer install` again after the tests to get the development dependencies back.

## 2. Test shops

### PrestaShop 1.7

`docker-compose up` starts PrestaShop 1.7.5 (PHP 7.2) with the repository mounted as
`modules/cocolis`:

- Front office: http://localhost:8085
- Back office: http://localhost:8085/adminPS (`demo@prestashop.com` / `prestashop_demo`)
- Emails: MailHog on http://localhost:8025

Other versions are available, commented out, in `docker-compose.yml`.

### PrestaShop 9

Use a separate compose file with MySQL 8 and the `prestashop/prestashop:9.x` image (for example
port 8091, `PS_FOLDER_ADMIN=admin-dev`, `PS_LANGUAGE=fr`, `PS_COUNTRY=FR`).

Install PrestaShop **before** mounting the module in `modules/cocolis`: the automatic installer
fails when it finds the module folder.

### Expose the shop

The Cocolis webhooks must reach the shop:

1. Run `ngrok http <port>`.
2. Set the ngrok domain as the shop domain (*Shop parameters > Traffic & SEO*, or the `shop_url`
   table and `PS_SHOP_DOMAIN` / `PS_SHOP_DOMAIN_SSL`), and enable SSL on all pages.
3. ngrok forwards HTTP to the container: make Apache trust the forwarded protocol, otherwise the
   back office refuses the login ("SSL is enabled"):

   ```sh
   docker exec <container> sh -c 'echo "SetEnvIf X-Forwarded-Proto \"^https\$\" HTTPS=on" > /etc/apache2/conf-enabled/forwarded-https.conf && apache2ctl graceful'
   ```

## 3. Installation

| # | Check | Expected |
|---|-------|----------|
| 3.1 | Install the module from *Modules > Module manager*. | Installed without error. |
| 3.2 | Same on PrestaShop 9 with the debug mode enabled. | Installed without error. |
| 3.3 | *Shipping > Carriers*. | A "Livraison Cocolis" carrier, linked to the module. |
| 3.4 | *Design > Positions*, filter on the module. | Only the hooks listed in `Cocolis::$hooks`. |

## 4. Configuration

| # | Check | Expected |
|---|-------|----------|
| 4.1 | Click "Automatically configure webhooks" with empty credentials. | "Please fill in your API key…" message. |
| 4.2 | Save a wrong API key. | "Cocolis rejected your API key…" message. |
| 4.3 | Create an API key in the sandbox account (*API keys* page), fill it in with sandbox mode, the pickup address and the default dimensions, and save. | Saved, no error message. |
| 4.4 | Save again with the API key field left empty. | The saved key is kept (4.5 still works). |
| 4.5 | Click "Automatically configure webhooks". | "Webhooks updated !" message. In the sandbox, the webhooks point to `https://<ngrok domain>/index.php?fc=module&module=cocolis&controller=webhooks&event=...`. |
| 4.6 | Click it again. | "Webhooks already updated !" message. |
| 4.7 | Set the shop phone number (*Shop parameters > Contact > Stores*) and make the phone field required in the addresses (*Customers > Addresses*). | Both checks are green on the configuration page. |
| 4.8 | Former connection: no API key saved, sandbox App ID and password filled in. | The page invites to create an API key. Sections 5 to 8 pass with these credentials too. |

## 5. Checkout

Log in as a customer with a delivery address in France.

| # | Check | Expected |
|---|-------|----------|
| 5.1 | Cart with one product, delivery to Paris. | "Livraison Cocolis" is offered with the price returned by the Cocolis API. |
| 5.2 | Same product with a quantity of 2 or 3. | Consistent price (the volume uses the cart quantity, never the stock). |
| 5.3 | Address the sandbox cannot deliver (for example postal code 15000, or an overseas address). | "Livraison Cocolis" is not offered. An overseas address writes a Cocolis error in *Advanced parameters > Logs*. |

## 6. Payment and ride creation

| # | Check | Expected |
|---|-------|----------|
| 6.1 | Place an order with "Livraison Cocolis" and a payment method that confirms the payment (for example *Check*, then set the order to "Payment accepted"). | The order is paid. |
| 6.2 | In the sandbox, open the ride created for the order. | The `external_id` is the order ID. Pickup and delivery addresses, phones, email, price and goods value are correct (no truncated cents), the objects have a weight class and a photo, and there are at most 5 photos. |
| 6.3 | Webhook `ride_published`. | Received by the shop (ngrok inspector: HTTP 200). The order history contains it. |
| 6.4 | Stop ngrok or use wrong credentials, then pay another order. | The payment still succeeds. The error is in *Advanced parameters > Logs* and no ride is created. |

## 7. Order tracking

| # | Check | Expected |
|---|-------|----------|
| 7.1 | Back office, order page. | A "Cocolis Status" tab (PrestaShop 1.7.7 and later: next to the shipping tab) with the status, the link to the public ride page and the seller tracking link. |
| 7.2 | Front office, *My account > Order history*, order detail. | Cocolis block with the link to the ride and the buyer tracking link. |
| 7.3 | Links. | The ride page and the tracking pages open in the sandbox. |

## 8. Delivery workflow

| # | Check | Expected |
|---|-------|----------|
| 8.1 | With the sandbox transporter account, make an offer on the ride. | No webhook yet (the order stays "Payment accepted"). |
| 8.2 | Confirm the pickup slot (seller tracking link) and the delivery slot (buyer tracking link). | Webhook `offer_accepted`: the order becomes "Shipped". |
| 8.3 | Confirm the delivery. | Webhook `offer_completed`: the order becomes "Delivered". |
| 8.4 | Order history (back office tab). | `ride_published`, `offer_accepted`, `offer_completed`. |
| 8.5 | Customer emails (MailHog on 1.7). | The status emails are sent. |

## 9. Webhook endpoint

Send requests with curl to
`https://<domain>/index.php?fc=module&module=cocolis&controller=webhooks`.

| # | Check | Expected |
|---|-------|----------|
| 9.1 | Without `X-Cocolis-Signature` header. | HTTP 401. Order unchanged. |
| 9.2 | With a wrong signature, or a timestamp older than 5 minutes. | HTTP 401. |
| 9.3 | Correctly signed payload with the `external_id` of an order shipped with another carrier, or of an unknown order. | HTTP 404. |

A valid signature is `t=<timestamp>,v1=<hex HMAC-SHA256 of "<timestamp>.<raw body>">`, with the
secret stored in the `COCOLIS_WEBHOOK_SECRET` configuration value.

## 10. Uninstallation

| # | Check | Expected |
|---|-------|----------|
| 10.1 | Uninstall the module. | No error. The Cocolis carriers are deleted from the carrier list, and the past orders still show their carrier. |
| 10.2 | Install it again and configure it. | Sections 3 and 4 pass again. |

## 11. Upgrade (optional)

Install the previous release zip, place an order with it, then install the new zip over it.

- The module upgrades without error and the new version is displayed.
- The past order still shows its Cocolis tracking.
- A new order goes through sections 6 and 8.

## 12. Cleanup

- In the sandbox, delete the webhooks pointing to the ngrok domain.
- Stop ngrok and the containers (`docker compose down -v` to delete the test data).
- Run `composer install` to get the development dependencies back.

## Known limitations

- When the ride creation fails at payment (Cocolis API unavailable), it is not retried: the error
  is only logged.
- Postal code 15000 makes the sandbox answer that the delivery is impossible only when the
  `FAKE_CAN_MATCH` simulation works on the sandbox.
