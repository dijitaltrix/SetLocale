# psr7-locale-middleware

## Description

This Middleware sets the request attribute 'locale' with a locale set by:
* The first segment of the URI eg: example.com/en/welcome or example.com/en-gb/welcome.
* The users browser accept-langugae header
* A default passed locale eg: en_GB

## Usage

```php

// In Slim 3

// set the locales supported by the application
$locales = ["de_DE", "en_GB", "pt_BR", "pt_PT"];
// set a default locale to fallback on if no match is found in $locales
$default = "en_GB";

$app->add(new Dijix\Locale\setLocaleMiddleware($locales, $default));

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
