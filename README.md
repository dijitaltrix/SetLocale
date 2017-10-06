# SetLocale-Middleware
[![Build Status](https://travis-ci.org/dijitaltrix/PSR7-SetLocale.svg?branch=master)](https://travis-ci.org/dijitaltrix/PSR7-SetLocale.svg?branch=master)

## Description

This PSR7 compatible middleware sets your applications locale to the best match between your supported locales and your visitors preferred locales.

It's designed to be used with the Slim Framework but is PSR7 compatible so should work elsewhere too.

The visitors locale is determined in this order:

* An override e.g. from a cookie you have set
* The first segment of the URI eg: example.com/en/welcome or example.com/en-gb/welcome.
* The users browser accept-language header, it selects the best match
* A default passed locale eg: en_GB

When a match has been found the middleware sets:

* The request attribute 'locale'
* The response 'Content-language' header
* It will (optionally) call setlocale(LC_ALL) on the matched locale - this can be toggled with the set_locale flag.

## Usage

```php
// In Slim PHP framework 3

// pass your settings as an array to the constructor.
$app->add(new Dijix\Locale\setLocaleMiddleware([

	// set the locales supported by the application
	"app_locales" => ["de_DE", "en_GB", "fr_FR", "pt_PT"],
	
	// set a default locale to fallback on if no match is found
	"app_default" => "en_GB",
	
	// call PHP setlocale() function with LC_ALL
	"set_locale" => true,
	
	// strict or fuzzy matching of the locale codes
	"strict_match" => false,
	
	// override uri/headers with this locale useful when fetched from a cookie or user session
	"override" => "pt_PT"

]));
```

The locale middleware will set a request attribute which can be accessed as follows:

```php
$locale = $request->getAttribute('locale');		// sets $locale to "en_GB"
```
