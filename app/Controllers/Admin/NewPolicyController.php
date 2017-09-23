<?php

namespace Elbo\Controllers\Admin;

use Elbo\{Models\User, Library\Controller};
use Symfony\Component\HttpFoundation\{Request, Response};

class NewPolicyController extends Controller {
	use \Elbo\Middlewares\Session;
	use \Elbo\Middlewares\PersistLogin;
	use \Elbo\Middlewares\RedirectIfNotAdmin;

	protected $middlewares = [
		'manageSession',
		'persistLogin',
		'redirectIfNotAdmin'
	];

	public function run(Request $request, array &$data) {
		$twig = $this->container->get(\Twig_Environment::class);

		return new Response($twig->render('admin/add_policy.html.twig', [
			'r_domain' => $request->query->get('r_domain'),
			'r_automated' => $request->query->get('r_automated'),
			'r_policy' => $request->query->get('r_policy'),
			'r_comment' => $request->query->get('r_comment'),
			'r_n' => $request->query->get('r_n'),
			'login_email' => User::where('id', $this->session->get('userid'))->pluck('email')->first()
		]));
	}
}
