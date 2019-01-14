<?php

namespace Elbo\Controllers;

use Elbo\Library\Controller;
use Symfony\Component\HttpFoundation\{Request, JsonResponse};

class ShortenController extends Controller {
	protected $middlewares = [];

	public function run(Request $request, array &$data) {
		return new JsonResponse([
			'status' => false
		], 400);
	}
}
