<?php

use Slim\Http\Environment;
use Slim\Http\Headers;
use Slim\Http\Request;
use Slim\Http\RequestBody;
use Slim\Http\Response;
use Slim\Http\UploadedFile;
use Slim\Http\Uri;
use Psr\Http\Message\ServerRequestInterface;
use Dijix\Locale\setLocaleMiddleware;
use PHPUnit\Framework\TestCase;


final class setLocaleMiddlewareTest extends TestCase
{
	/**
	 * create a mock request
	 *
	 * @param string $uri 
	 * @param string $queryString 
	 * @return void
	 * @author Ian Grindley
	 */
	public function mockRequest($lang='', $headers='')
	{
		$uri = Uri::createFromString('http://www.example.com/'.$lang);
		$env = Environment::mock();
		$env['REQUEST_URI'] = $uri;
		$env['HTTP_ACCEPT_LANGUAGE'] = $headers;
		$headers = Headers::createFromEnvironment($env);
		$cookies = [];
		$serverParams = $env->all();
		$body = new RequestBody();
		$request = new Request(
			'GET',
			$uri,
			$headers,
			$cookies,
			$serverParams,
			$body
		);

		return $request;

	}

	
	public function testInvalidLocale()
	{
		// setup middleware with application locales and default locale
		$mw = new setLocaleMiddleware([
			"app_locales" => ["xx_XX"],
			"app_default" => "xx_XX"
		]);

		// test setting locale from URI
		$request = $this->mockRequest(null, "en_GB,en;q=0.8");

		$this->setExpectedException('Exception');

		$response = $mw($request, new Response(),
			function (ServerRequestInterface $req, $res) {
				return $res;
			}
		);

	}

	public function testEmptyUri()
	{
		// setup middleware with application locales and default locale
		$mw = new setLocaleMiddleware([
			"app_locales" => ["en_GB", "pt_PT"],
			"app_default" => "en_GB"
		]);

		// test setting locale from URI
		$request = $this->mockRequest();

		$response = $mw($request, new Response(),
			function (ServerRequestInterface $req, $res) {
				return $res;
			}
		);

		$this->assertInstanceOf(Response::class, $response);
		// $this->assertNotEmpty($response->getAttribute("locale"));
		$this->assertNotEmpty($response->getHeader("Content-language"));
		$cl = $response->getHeader("Content-language");
		$this->assertEquals("en_GB", $cl[0]);

	}
	
	public function testEmptyNonEnUri()
	{
		// setup middleware with application locales and default locale
		$mw = new setLocaleMiddleware([
			"app_locales" => ["en_GB", "fr_FR", "pt_PT"],
			"app_default" => "fr_FR"
		]);

		// test setting locale from URI
		$request = $this->mockRequest();

		$response = $mw($request, new Response(),
			function (ServerRequestInterface $req, $res) {
				return $res;
			}
		);

		$this->assertInstanceOf(Response::class, $response);
		// $this->assertNotEmpty($response->getAttribute("locale"));
		$this->assertNotEmpty($response->getHeader("Content-language"));
		$cl = $response->getHeader("Content-language");
		$this->assertEquals("fr_FR", $cl[0]);

	}

	public function testDeUri()
	{
		// setup middleware with application locales and default locale
		$mw = new setLocaleMiddleware([
			"app_locales" => ["en_GB", "pt_PT"],
			"app_default" => "en_GB"
		]);

		// test setting locale from URI
		$request = $this->mockRequest("de/wilkommen");

		$response = $mw($request, new Response(),
			function (ServerRequestInterface $req, $res) {
				return $res;
			}
		);

		$this->assertInstanceOf(Response::class, $response);
		// $this->assertNotEmpty($response->getAttribute("locale"));
		$this->assertNotEmpty($response->getHeader("Content-language"));
		$cl = $response->getHeader("Content-language");
		$this->assertEquals("en_GB", $cl[0]);

	}

	public function testEnUri()
	{
		// setup middleware with application locales and default locale
		$mw = new setLocaleMiddleware([
			"app_locales" => ["en_GB", "pt_PT"],
			"app_default" => "en_GB"
		]);

		// test setting locale from URI
		$request = $this->mockRequest("en/welcome");

		$response = $mw($request, new Response(),
			function (ServerRequestInterface $req, $res) {
				return $res;
			}
		);

		$this->assertInstanceOf(Response::class, $response);
		// $this->assertNotEmpty($response->getAttribute("locale"));
		$this->assertNotEmpty($response->getHeader("Content-language"));
		$cl = $response->getHeader("Content-language");
		$this->assertEquals("en_GB", $cl[0]);

	}
	
	public function testEnUsUri()
	{
		// setup middleware with application locales and default locale
		$mw = new setLocaleMiddleware([
			"app_locales" => ["en_GB", "pt_PT"],
			"app_default" => "en_GB"
		]);

		// test setting locale from URI
		$request = $this->mockRequest("en-us/home");

		$response = $mw($request, new Response(),
			function (ServerRequestInterface $req, $res) {
				return $res;
			}
		);

		$this->assertInstanceOf(Response::class, $response);
		// $this->assertNotEmpty($response->getAttribute("locale"));
		$this->assertNotEmpty($response->getHeader("Content-language"));
		$cl = $response->getHeader("Content-language");
		$this->assertEquals("en_GB", $cl[0]);

	}

	public function testPtUri()
	{
		// setup middleware with application locales and default locale
		$mw = new setLocaleMiddleware([
			"app_locales" => ["en_GB", "pt_PT"],
			"app_default" => "en_GB"
		]);

		// test setting locale from URI
		$request = $this->mockRequest("pt/initial");

		$response = $mw($request, new Response(),
			function (ServerRequestInterface $req, $res) {
				return $res;
			}
		);

		$this->assertInstanceOf(Response::class, $response);
		// $this->assertNotEmpty($response->getAttribute("locale"));
		$this->assertNotEmpty($response->getHeader("Content-language"));
		$cl = $response->getHeader("Content-language");
		$this->assertEquals("pt_PT", $cl[0]);

	}

	public function testDeHeaders()
	{
		// setup middleware with application locales and default locale
		$mw = new setLocaleMiddleware([
			"app_locales" => ["en_GB", "pt_PT"],
			"app_default" => "en_GB"
		]);

		// test setting locale from URI
		$request = $this->mockRequest(null, "de_DE,de;q=0.8,fr;q=0.1");

		$response = $mw($request, new Response(),
			function (ServerRequestInterface $req, $res) {
				return $res;
			}
		);

		$this->assertInstanceOf(Response::class, $response);
		// $this->assertNotEmpty($response->getAttribute("locale"));
		$this->assertNotEmpty($response->getHeader("Content-language"));
		$cl = $response->getHeader("Content-language");
		$this->assertEquals("en_GB", $cl[0]);

	}

	public function testEnGBHeaders()
	{
		// setup middleware with application locales and default locale
		$mw = new setLocaleMiddleware([
			"app_locales" => ["en_GB", "pt_PT"],
			"app_default" => "en_GB"
		]);

		// test setting locale from URI
		$request = $this->mockRequest(null, "en_GB,en;q=0.8");

		$response = $mw($request, new Response(),
			function (ServerRequestInterface $req, $res) {
				return $res;
			}
		);

		$this->assertInstanceOf(Response::class, $response);
		// $this->assertNotEmpty($response->getAttribute("locale"));
		$this->assertNotEmpty($response->getHeader("Content-language"));
		$cl = $response->getHeader("Content-language");
		$this->assertEquals("en_GB", $cl[0]);

	}
	
	public function testEnUSHeaders()
	{
		// setup middleware with application locales and default locale
		$mw = new setLocaleMiddleware([
			"app_locales" => ["en_GB", "pt_PT"],
			"app_default" => "en_GB"
		]);

		// test setting locale from URI
		$request = $this->mockRequest(null, "en_US,en;q=0.8");

		$response = $mw($request, new Response(),
			function (ServerRequestInterface $req, $res) {
				return $res;
			}
		);

		$this->assertInstanceOf(Response::class, $response);
		// $this->assertNotEmpty($response->getAttribute("locale"));
		$this->assertNotEmpty($response->getHeader("Content-language"));
		$cl = $response->getHeader("Content-language");
		$this->assertEquals("en_GB", $cl[0]);

	}

	public function testPtHeaders()
	{
		// setup middleware with application locales and default locale
		$mw = new setLocaleMiddleware([
			"app_locales" => ["en_GB", "pt_PT"],
			"app_default" => "en_GB"
		]);

		// test setting locale from URI
		$request = $this->mockRequest(null, "pt_BR,pt;q=0.8,pt_PT;q=0.6,en;q=0.4");

		$response = $mw($request, new Response(),
			function (ServerRequestInterface $req, $res) {
				return $res;
			}
		);

		$this->assertInstanceOf(Response::class, $response);
		// $this->assertNotEmpty($response->getAttribute("locale"));
		$this->assertNotEmpty($response->getHeader("Content-language"));
		$cl = $response->getHeader("Content-language");
		$this->assertEquals("pt_PT", $cl[0]);

	}

}