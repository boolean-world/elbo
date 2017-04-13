<?php

namespace Elbo\Controllers;

use Elbo\Library\Controller;
use Symfony\Component\HttpFoundation\{Request, Response};

class RegisterPageController extends Controller {
	use \Elbo\Middlewares\Session;
	use \Elbo\Middlewares\PersistLogin;
	use \Elbo\Middlewares\RedirectIfLoggedIn;

	protected $middlewares = [
		'manageSession',
		'persistLogin',
		'redirectIfLoggedIn'
	];

	public function run(Request $request, array &$data) {
		$twig = $this->container->get(\Twig_Environment::class);

		return new Response($twig->render('auth/register.html.twig'));
	}
}
