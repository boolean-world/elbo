<?php

namespace Elbo\Controllers\Admin;

use Symfony\Component\HttpFoundation\{Request, Response};
use Elbo\{Models\DomainPolicy, Models\User, Library\Controller};

class EditPolicyController extends Controller {
	use \Elbo\Middlewares\Session;
	use \Elbo\Middlewares\NotFoundIfNotAdmin;

	protected $middlewares = [
		'manageSession',
		'notFoundIfNotAdmin'
	];

	public function run(Request $request, array &$data) {
		$twig = $this->container->get(\Twig_Environment::class);

		$context = [
			'r_domain' => $request->query->get('r_domain'),
			'r_automated' => $request->query->get('r_automated'),
			'r_policy' => $request->query->get('r_policy'),
			'r_comment' => $request->query->get('r_comment'),
			'r_n' => $request->query->get('r_n'),
			'edit_mode' => 2,
			'login_email' => User::where('id', $this->session->get('userid'))->pluck('email')->first()
		];

		$entry = DomainPolicy::where('domain', $data['domain'])->first();

		if ($entry === null) {
			return new Response($twig->render('admin/policy_notfound.html.twig', $context), 404);
		}

		return new Response($twig->render('admin/edit_policy.html.twig', $context + [
			'entry' => $entry
		]));
	}
}
