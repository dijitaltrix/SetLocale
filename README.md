# SetLocale
[![Build Status](https://travis-ci.org/dijitaltrix/PSR7-SetLocale.svg?branch=master)](https://travis-ci.org/dijitaltrix/PSR7-SetLocale.svg?branch=master)

## Description

Slim Framework middleware to set your applications locale

This middleware sets your applications locale to the best match between your applications supported locales and your visitors preferred locales.

It's designed to be used with the Slim Framework but is PSR7 compatible so should work elsewhere too.

The visitors locale is determined in this order:

* An override passed to the middleware constructor e.g. from an existing cookie/session
* The first segment of the URI eg: example.com/en/welcome or example.com/en-gb/welcome.
* The users browser accept-language header, where it selects the best match
* A default passed locale eg: en_GB, used as a fallback if none of the above match

When a match has been found the middleware sets:

* The request attribute 'locale'
* The response 'Content-language' header
* It will set the environment by calling setlocale(LC_ALL) on the matched locale - this can be toggled with the set_locale flag.

## Installation

Install via composer
```bash
$ composer require dijix/setlocale
````

## Usage

```php
// In Slim PHP framework 3

// add the middleware to your app, often in the middleware.php or dependencies.php file

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
	
	// override uri/headers locale, useful when setting locale from a cookie or user session
	"override" => "pt_PT"

]));
```

The locale middleware will set a request attribute which can be accessed as follows:

```php
$locale = $request->getAttribute('locale');		// sets $locale to "en_GB"
```
