# PSR7-SetLocale Middleware for Slim PHP

## Description

This Middleware sets the request attribute 'locale' and the response 'Content-language'
header with a locale set by:

* An override e.g. from a cookie you have set
* The first segment of the URI eg: example.com/en/welcome or example.com/en-gb/welcome.
* The users browser accept-language header
* A default passed locale eg: en_GB

## Usage

```php

// In Slim 3

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

Once the middleware is loaded your application locale can be set by either setting the 
Accept-Language header - this can be mocked or will be set by your visitors os/browser
Or by setting the first segment of your URL like so
http://www.example.com/en/your-page.html
http://www.example.com/de/your-page.html
or
http://www.example.com/en-gb/your-page.html
http://www.example.com/de-de/your-page.html

The locale middleware will set the request attribute which can be accessed as follows:
```php
	$locale = $request->getAttribute('locale');
```


Note:

The locale middleware sets the response Content-language header with the matching locale from your specifed app locales
