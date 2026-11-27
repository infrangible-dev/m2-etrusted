# Infrangible eTrusted

Shows the rating and the reviews of your shop at Trusted Shops (eTrusted platform) on CMS pages and blocks. The
widgets are rendered by Magento itself from the eTrusted API, without external scripts of Trusted Shops.

## Widgets

Insert them with "Insert Widget" in the editor of CMS pages and blocks, or as widget instances (Content > Widgets).

- **eTrusted Aggregate Rating**: the average rating of the service reviews (for example "4,8 / 5,0") with the number
  of reviews, for a period: overall, or the last 7, 30, 90 or 365 days.
- **eTrusted Reviews**: a slider with the latest reviews, each with its stars, "Satisfied Customer" (the API gives no
  names), the date, the comment and the trust mark image of the configuration. Parameters: the number of reviews,
  and optionally the ratings (1 to 5), the status (approved, in moderation, rejected) and the type (service or
  product reviews).

A widget shows nothing if there are no reviews, also if eTrusted is not configured or not available. Errors of the API
are written to the log.

## Configuration

Stores > Configuration > Infrangible Solutions > eTrusted (per website and store view):

- **SSO**: the client ID and the secret of an API client of your eTrusted account. The secret is saved encrypted.
- **Channel**: the ID of the channel (shop) whose reviews are shown, for example `chl-xxxxxxxx-...`.
- **Display**: the trust mark image next to every review, for example the trust mark of Trusted Shops for members;
  without it, the reviews have no image.

The data of eTrusted is cached for one hour in the cache type "eTrusted" (System > Cache Management), separately for
every channel and every combination of widget parameters. Changed settings take effect immediately; refresh the
cache type to show new reviews before the hour is over. Requests to eTrusted time out after 10 seconds.

The slider uses slick, which Magento ships with Page Builder; it starts automatically and changes every 4 seconds.
The options can be changed with `data-mage-init` in an own template, for example
`{"infrangible/etrusted-reviews": {"autoplaySpeed": 6000}}`.

For Hyvä themes, the module `infrangible/m2-etrusted-hyva` renders the widgets.

## Requirements

- Magento Open Source or Adobe Commerce 2.4.6 or newer, with Page Builder
- PHP 8.1 or newer
- An eTrusted (Trusted Shops) account with API access

## Installation

```bash
composer require infrangible/m2-etrusted
bin/magento module:enable Infrangible_ETrusted
bin/magento setup:upgrade
```

In production mode, also run `bin/magento setup:di:compile` and `bin/magento setup:static-content:deploy`.

If the secret is set in `app/etc/env.php` or `config.php` instead of the admin, it must be encrypted (version 2
saved it as plain text; `setup:upgrade` encrypts the values in the database).

## License

Infrangible eTrusted is licensed under the MIT License - see the LICENSE file for details.
