<?php

namespace Elbo\Controllers;

use Elbo\{Library\Controller, Models\User};
use Symfony\Component\HttpFoundation\{Request, Response};

class HomeController extends Controller {
	use \Elbo\Middlewares\Session;
	use \Elbo\Middlewares\PersistLogin;
	use \Elbo\Middlewares\RedirectIfLoggedOut;

	protected $middlewares = [
		'manageSession',
		'persistLogin',
		'redirectIfLoggedOut'
	];

	public function run(Request $request, array &$data) {
		$userid = $this->session->get('userid');
		$twig = $this->container->get(\Twig_Environment::class);

		$login_email = User::where('id', $userid)->pluck('email')->first();

		return new Response($twig->render('home.html.twig', [
			'login_email' => $login_email,
			'time' => time()
		]));
	}
}
