<?php

namespace Elbo\Controllers\Admin;

use Symfony\Component\HttpFoundation\{Request, Response};
use Elbo\{Models\DomainPolicy, Models\User, Library\Controller};

class NewPolicyHandlerController extends Controller {
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
			'login_email' => User::where('id', $this->session->get('userid'))->pluck('email')->first(),
		];

		$automated = false;
		$domain = trim($request->request->get('domain'));
		$policy = (int)($request->request->get('policy'));
		$comment = trim($request->request->get('comment'));

		if (filter_var($domain, FILTER_VALIDATE_IP) === false) {
			$domain = idn_to_ascii(strtolower($domain), 0, INTL_IDNA_VARIANT_UTS46);

			if (!preg_match('/^(?:[a-z0-9][a-z0-9-]*[a-z0-9]?\.)+[a-z]{2,}$/', $domain) ||
				(filter_var($domain, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false)) {
				return new Response($twig->render('admin/add_policy.html.twig', $context + [
					'entry' => compact('domain', 'policy', 'automated', 'comment'),
					'error' => 1
				]));
			}
		}

		if (!in_array($policy, [
			DomainPolicy::POLICY_ALLOWED,
			DomainPolicy::POLICY_BLOCKED_SPAM,
			DomainPolicy::POLICY_BLOCKED_MALWARE,
			DomainPolicy::POLICY_BLOCKED_PHISHING,
			DomainPolicy::POLICY_BLOCKED_ILLEGAL,
			DomainPolicy::POLICY_BLOCKED_REDIRECTOR
		])) {
			return new Response($twig->render('admin/add_policy.html.twig', $context + [
				'entry' => compact('domain', 'policy', 'automated', 'comment'),
				'error' => 2
			]));
		}

		$count = DomainPolicy::where('domain', $domain)->count();
		if ($count > 0) {
			return new Response($twig->render('admin/add_policy.html.twig', $context + [
				'entry' => compact('domain', 'policy', 'automated', 'comment'),
				'error' => 3
			]));
		}

		if ($comment === '') {
			$comment = null;
		}

		DomainPolicy::create(compact('domain', 'policy', 'automated', 'comment'));

		return new Response($twig->render('admin/policy_added.html.twig', $context));
	}
}
