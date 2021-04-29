<?php
// Application middleware

use Slim\Http\Request;
use Slim\Http\Response;

$app->add(function (Request $request, Response $response, callable $next){
	$uri = $request->getUri();
	$path = $uri->getPath();
	if ($path!=='/' && substr($path, -1)==='/'){
		// permanently redirect paths with a trailing slash
		// to their non-trailing counterpart
		$cleaned_uri = $uri->withPath(substr($path, 0, -1));

		if ($request->getMethod()==='GET'){
			return $response->withRedirect((string)$uri, 301);
		}

		return $next($request->withUri($uri), $response);
	}

	return $next($request, $response);
});

$app->add(function ($req, $res, $next){
	$response = $next($req, $res);

	return $response
		->withHeader('Access-Control-Allow-Origin', $this->get('settings')['cors'])
		->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
		->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
});
