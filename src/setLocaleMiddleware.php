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

		// try set locale, will throw exception if the locale is not available on your system
		if ( ! setlocale($this->set_locale, $locale)) {
			throw new \Exception("Cannot set locale to $locale");
		}

		return $next($request, $response);

	}
	
	private function match($list)
	{
		// sort array by locale weight
		arsort($list);

		// run through lists and return first matching pair
		foreach ($list as $l=>$w) {
			// normalise locale for str comparison
			$l = $this->normalise($l);
			foreach ($this->app_locales as $app_locale) {

				if ($this->strict_match) {
					if ($this->normalise($app_locale) == $l) {
						return $app_locale;
					}
				} else {
					if (strstr($this->normalise($app_locale), $l)) {
						return $app_locale;
					}
				}

			}

		}

		return false;

	}

	/**
	 * matches the language in the request headers
	 * returns locale code if match found
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
			if ( ! empty($l)) {
				$prefs[$l] = $w;
			}
		};

		// find first matching prefs locale that matches app locales
		return $this->match($prefs);

	}
	
	/**
	 * matches the langugae code given in teh override variable
	 * returns the matched $app_locale code if found
	 * or false if no match found
	 *
	 * @param string $override
	 * @return mixed
	 * @author Ian Grindley
	 */
	private function matchOverride($str=null)
	{
		if ($str) {
			return $this->match([
				$str => 1
			]);
		}

		return false;

	}

	/**
	 * matches the langugae code in the first segment of the URI
	 * returns the matched $app_locale code if found
	 * or false if no match found
	 *
	 * @param string $uri 
	 * @return mixed
	 * @author Ian Grindley
	 */
	private function matchUri($uri)
	{
		$segments = explode("/", ltrim($uri->getpath(), "/"));

		if (isset($segments[0]) && ! empty($segments[0])) {
			return $this->match([
				$segments[0] => 1
			]);
		}

		return false;

	}

	/**
	 * Normalises a locale code for str comparison,
	 * e.g. from en_GB to en-gb
	 *
	 * @param string $str 
	 * @return void
	 * @author Ian Grindley
	 */
	private function normalise($str)
	{
		return strtolower(str_replace('_', '-', $str));
	}

}
