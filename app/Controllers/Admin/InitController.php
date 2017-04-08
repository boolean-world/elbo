<?php

namespace Elbo\Controllers\Admin;

use Symfony\Component\HttpFoundation\{Request, Response};
use Elbo\{Models\DomainPolicy, Models\User, Models\ShortURL, Library\Controller};

class InitController extends Controller {
	use \Elbo\Middlewares\Session;
	use \Elbo\Middlewares\NotFoundIfNotAdmin;

	protected $middlewares = [
		'manageSession',
		'notFoundIfNotAdmin'
	];

	public function run(Request $request, array &$data) {
		$twig = $this->container->get(\Twig_Environment::class);

		return new Response($twig->render('admin/init.html.twig', [
			'user_count' => User::count(),
			'policy_count' => DomainPolicy::count(),
			'shorturl_count' => ShortURL::count(),
			'login_email' => User::where('id', $this->session->get('userid'))->pluck('email')->first()
		]));
	}
}
