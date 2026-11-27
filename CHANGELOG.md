# Changelog

## 3.0.0

### Breaking changes

- Requires Magento 2.4.6 or newer with Page Builder, and PHP 8.1 or newer. Older versions of Magento and PHP 7.1 to 8.0
  are no longer supported; use version 2.x for these.
- Requires `infrangible/m2-core` 5.0, `infrangible/m2-foundation` 3.0 and `fewedev/base` 2.4.3.
- The models request eTrusted with Magento's HTTP client instead of Guzzle and have new methods:
  `AggregateRating::getAggregateRating()` and `Reviews::getReviews($count, $ratings, $status, $types)` replace `get()`
  and the setters of the filters. The classes have native types and use constructor property promotion.
- The trust mark next to the reviews is an image of the configuration (Display > Trust Mark) instead of an image of
  the website of Trusted Shops.

### Fixed

- Review comments were output as HTML: a comment with HTML code ran it in the shop (cross-site scripting). They are
  escaped now, like all texts of the templates.
- A reviews widget without filters showed the reviews of another reviews widget with filters on the same page.
- The trust mark next to every review was a broken image: the image on the website of Trusted Shops no longer
  exists.
- The date of the reviews was followed by the German word for "verified" in every language; it is translated now.
- The German translation of the review type "Product Review" was missing.

### Changed

- The widgets show nothing if eTrusted has no data (for example with a wrong channel ID or credentials), instead of a
  rating of "0.0 / 5.0" and an empty slider.
- The secret of the API client is saved encrypted; `setup:upgrade` encrypts the saved secrets.
- The data is cached per request, so stores with other channels and changed settings get their own data.
- Requests to eTrusted time out after 10 seconds; without credentials or channel nothing is requested.
- The slider script is loaded per widget with `data-mage-init` and accepts slick options.

### Added

- Translations into 24 languages.
- Unit tests.
