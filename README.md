CurrencyConverter
=================

TODO
----

* security/robustness improvements:
** if used as is, needs error handling & logging
** should sanitize values coming from the exchange rate service
* better code organization so it can be integrated into a larger project:
** separate configuration from code
** PSR-0 classnames, autoloading
** move boostrapping and markup into separate files
** proper solution for i18n
* better code structure
** CurrencyConverter should not depend on ExchangeRatesService
** rounding logic should go into a helper class
* better tests
** ExchangeRatesServiceTest should not use the real service for tests
* double-check business logic
** is it safe to assume same rate for JPY->USD and USD->JPY?
** needs better rounding logic
