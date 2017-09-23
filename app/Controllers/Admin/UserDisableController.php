<?php

namespace Elbo\Controllers\Admin;

use Elbo\Library\Controller;
use Elbo\Models\{User, RememberToken};
use Symfony\Component\HttpFoundation\{Request, Response};

class UserDisableController extends Controller {
	use \Elbo\Middlewares\Session;
	use \Elbo\Middlewares\PersistLogin;
	use \Elbo\Middlewares\RedirectIfNotAdmin;
	use \Elbo\Middlewares\CSRFProtected;

	protected $middlewares = [
		'manageSession',
		'persistLogin',
		'redirectIfNotAdmin',
		'csrfProtected'
	];

	public function run(Request $request, array &$data) {
		$twig = $this->container->get(\Twig_Environment::class);

		$context = [
			'r_email' => $request->query->get('r_email'),
			'r_disabled' => $request->query->get('r_disabled'),
			'r_created_ip' => $request->query->get('r_created_ip'),
			'r_last_login_ip' => $request->query->get('r_last_login_ip'),
			'r_n' => $request->query->get('r_n'),
			'login_email' => User::where('id', $this->session->get('userid'))->pluck('email')->first()
		];

		$entry = User::where('id', $data['userid'])->first();

		if ($entry === null) {
			return new Response($twig->render('admin/user_notfound.html.twig', $context), 404);
		}

		$entry->disabled = true;
		$entry->save();

		RememberToken::where('userid', $data['userid'])->delete();

		return new Response($twig->render('admin/user_disabled.html.twig', $context));
	}
}
