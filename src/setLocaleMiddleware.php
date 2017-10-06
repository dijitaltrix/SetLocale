<?php
/**
 * Sets the application locale based on:
 *	a) First url segment
 *	b) HTTP Accept-Language header
 *	c) the default
 */

namespace Dijix\Locale;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class setLocaleMiddleware {
	
	/**
	 * Contains the list of locales handled by your
	 * application. Any 5 char or 2 char forms are valid
	 * e.g. ["en-gb", "pt_PT", "de"]
	 *
	 * @var array
	 */
	protected $app_locales;

	/**
	 * Contains your applications default locale
	 * either the 5 char or 2 char forms are valid
	 * e.g. "pt_PT" or "pt-pt", or "pt"
	 *
	 * @var string
	 */
	protected $app_default;
	
	/**
	 * Flag to call the setlocale() function with the
	 * determined locale.
	 * Defaults to true
	 * @var boolean
	 */
	protected $set_locale;
	
	/**
	 * The override can be used to shortcut the locale detection
	 * Anything passed as override will be set as the locale
	 * if it exists in $app_locales
	 *
	 * @var string
	 */
	protected $override;
	
	/**
	 * Match the locales exactly if true or 'fuzzy' match if false
	 *
	 * @var boolean
	 */
	protected $strict_match;
	
	
	/**
	 * Pass your config array to override the defaults
	 *
	 * @param array $opts 
	 * @author Ian Grindley
	 */
	public function __construct($opts=[])
	{
		// merge defaults with user options
		$opts = array_merge([
			"app_locales" => ["en-gb"],
			"app_default" => ["en-gb"],
			"set_locale" => LC_ALL,
			"strict_match" => false,
			"override" => null,
		], $opts);

		// set options
		foreach ($opts as $k=>$v) {
			if (property_exists($this, $k)) {
				$this->$k = $v;
			}
		}

	}
	
	/**
	 * @param \Psr\Http\Message\ServerRequestInterface $request
	 * @param \Psr\Http\Message\ResponseInterface $response
	 * @param callable $next
	 * @return \Psr\Http\Message\ResponseInterface
	 * @throws \Exception
	 */
	public function __invoke (
		ServerRequestInterface $request,
		ResponseInterface $response,
		callable $next)
	{
		$locale = false;
		
		// if we're given an override and it matches then use it.
		if ($this->override) {
			$locale = $this->matchOverride($this->override);
		}
		// no match, check against URI
		if ( ! $locale) {
			$locale = $this->matchUri($request->getUri());
		}
		// no match check against headers
		if ( ! $locale) {
			$locale = $this->matchHeader($request->getHeader("Accept-language"));
		}
		// no match set default
		if ( ! $locale) {
			$locale = $this->app_default;
		}
		
		// do default actions, set locale attribute and content-language header
		$request = $request->withAttribute('locale', $locale);
		$response = $response->withHeader("Content-language", $locale);

		// try to set the users preferred locale, this will throw an exception if
		// the locale is not available on your system
		if ($this->set_locale === true) {
			if ( ! setlocale($this->set_locale, $locale)) {
				throw new \Exception("Cannot set locale to $locale");
			}
		}

		return $next($request, $response);

	}
	
	/**
	 * Matches the preferred locales list to your application locales
	 * Accepts an array formated as locale => weight
	 * e.g. ["en_GB" => 1.0, "en" => 0.8]
	 *
	 * Returns the matched locale string 
	 * or boolean false if no match found
	 *
	 * @param array $list
	 * @return mixed (string | boolean)
	 * @author Ian Grindley
	 */
	private function match($list)
	{
		// sort array by locale weight, highest first
		arsort($list);

		// compare preferred locales in turn against each
		// supported application locale
		foreach ($list as $l=>$w) {
			// normalise locale for str comparison
			$code = $this->normaliseCode($l);
			// compare each application locale against user pref
			foreach ($this->app_locales as $app_locale) {
				if ($this->strict_match) {
					if ($this->normaliseCode($app_locale) == $code) {
						return $app_locale;
					}
				} else {
					if (strstr($this->normaliseCode($app_locale), $code)) {
						return $app_locale;
					}
				}
			}
		}

		return false;

	}

	/**
	 * matches any of the the locale codes in the request headers
	 * returns best fit locale code if match found
	 * or false if no match found
	 *
	 * @param string $request 
	 * @return mixed
	 * @author Ian Grindley
	 */
	private function matchHeader($headers)
	{
		// implode may not be required if we're not passed multiple arrays
		$headers = explode(",", implode(",", $headers));

		// get accepted languages into array
		$prefs = [];
		foreach ($headers as $str) {
			// array_merge adds the missing weight 1 to preferred locale
			list($l, $w) = array_merge(explode(";q=", $str), ["1.0"]);
			$code = $this->sanitiseCode($l);
			if ($this->isValidCode($code)) {
				$prefs[$code] = (float) $w;
			}
		};

		// find first matching prefs locale that matches app locales
		return $this->match($prefs);

	}
	
	/**
	 * matches the locale code given in the override variable
	 * returns the matched $app_locale code if found
	 * or false if no match found
	 *
	 * @param string $override
	 * @return mixed
	 * @author Ian Grindley
	 */
	private function matchOverride($str=null)
	{
		$code = $this->sanitiseCode($str);
		
		if ($this->isValidCode($code)) {
			return $this->match([
				$code => 1
			]);
		}

		return false;

	}

	/**
	 * matches the language code in the first segment of the URI
	 * returns the matched $app_locale code if found
	 * or false if no match found
	 *
	 * @param string $uri 
	 * @return mixed
	 * @author Ian Grindley
	 */
	private function matchUri($uri)
	{
		// get the first segment from the URI path
		$segments = explode("/", ltrim($uri->getpath(), "/"));
		if (isset($segments[0]) && ! empty($segments[0])) {
			// sanitise the first segment which should contain the locale
			$code = $this->sanitiseCode($segments[0]);
			if ($this->isValidCode($code)) {
				return $this->match([
					$code => 1
				]);
			}
		}

		return false;

	}

	/**
	 * Sanitises a locale code string accepting a-z dash - and underscore _
	 *
	 * @param string $str 
	 * @return void
	 * @author Ian Grindley
	 */
	private function sanitiseCode($str)
	{
		return preg_replace("/^[a-zA-Z\_\-]+/", "", $str);
	}
	
	/**
	 * Normalises a locale code for str comparison,
	 * e.g. from en_GB to en-gb
	 *
	 * @param string $str 
	 * @return void
	 * @author Ian Grindley
	 */
	private function normaliseCode($str)
	{
		return strtolower(str_replace('_', '-', $str));
	}

	/**
	 * undocumented function
	 *
	 * @param string $str 
	 * @return boolean
	 * @author Ian Grindley
	 */
	private function isValidCode($str) 
	{
		if (1 === preg_match("/(^[a-z]{2}$|^[a-z]{2}(_|-)[a-z|A-Z]{2}$)/", $str)) {
			return true;
		}

		return false;

	}

}
