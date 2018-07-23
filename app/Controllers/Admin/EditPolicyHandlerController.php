<?php

namespace Elbo\Controllers\Admin;

use Symfony\Component\HttpFoundation\{Request, Response};
use Elbo\{Models\DomainPolicy, Models\User, Library\Controller};

class EditPolicyHandlerController extends Controller {
	use \Elbo\Middlewares\Session;
	use \Elbo\Middlewares\PersistLogin;
	use \Elbo\Middlewares\CSRFProtected;
	use \Elbo\Middlewares\RedirectIfNotAdmin;

	protected $middlewares = [
		'manageSession',
		'persistLogin',
		'redirectIfNotAdmin',
		'csrfProtected'
	];

	public function run(Request $request, array &$data) {
		$twig = $this->container->get(\Twig_Environment::class);

		$context = [
			'r_domain' => $request->query->get('r_domain'),
			'r_automated' => $request->query->get('r_automated'),
			'r_policy' => $request->query->get('r_policy'),
			'r_comment' => $request->query->get('r_comment'),
			'r_n' => $request->query->get('r_n'),
			'login_email' => User::where('id', $this->session->get('userid'))->pluck('email')->first()
		];

		$entry = DomainPolicy::where('domain', $data['domain'])->first();

		if ($entry === null) {
			return new Response($twig->render('admin/policy_notfound.html.twig', $context), 404);
		}

		$domain = $data['domain'];
		$policy = (int)($request->request->get('policy'));
		$comment = trim($request->request->get('comment'));

		if (!in_array($policy, [
			DomainPolicy::POLICY_ALLOWED,
			DomainPolicy::POLICY_BLOCKED_SPAM,
			DomainPolicy::POLICY_BLOCKED_MALWARE,
			DomainPolicy::POLICY_BLOCKED_PHISHING,
			DomainPolicy::POLICY_BLOCKED_ILLEGAL,
			DomainPolicy::POLICY_BLOCKED_REDIRECTOR
		])) {
			return new Response($twig->render('admin/edit_policy.html.twig', $context + [
				'entry' => compact('domain', 'policy', 'automated', 'comment'),
				'error' => 2
			]));
		}

		if ($comment === '') {
			$comment = null;
		}

		$entry->policy = $policy;
		$entry->automated = false;
		$entry->comment = $comment;
		$entry->save();

		return new Response($twig->render('admin/policy_updated.html.twig', $context));
	}
}
