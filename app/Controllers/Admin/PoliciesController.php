<?php

namespace Elbo\Controllers\Admin;

use Symfony\Component\HttpFoundation\{Request, Response};
use Elbo\{Models\DomainPolicy, Models\User, Library\Controller};

class PoliciesController extends Controller {
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

		$pagenum = (int)($request->query->get('n'));
		$skip = 30 * $pagenum;
		$domain = trim($request->query->get('domain'));
		$automated = (int)($request->query->get('automated'));
		$policy = (int)($request->query->get('policy'));
		$comment = trim($request->query->get('comment'));

		$query = DomainPolicy::skip($skip)->take(31);

		if ($domain !== '') {
			$query->where('domain', 'like', $domain);
		}

		if ($automated === 1 || $automated === 2) {
			$query->where('automated', $automated === 1);
		}

		if (in_array($policy - 1, [
			DomainPolicy::POLICY_ALLOWED,
			DomainPolicy::POLICY_BLOCKED_SPAM,
			DomainPolicy::POLICY_BLOCKED_MALWARE,
			DomainPolicy::POLICY_BLOCKED_PHISHING,
			DomainPolicy::POLICY_BLOCKED_ILLEGAL,
			DomainPolicy::POLICY_BLOCKED_REDIRECTOR
		])) {
			$query->where('policy', $policy - 1);
		}

		if ($comment !== '') {
			$query->where('comment', 'like', $comment);
		}

		$entries = $query->get();

		if ($entries->count() > 30) {
			$has_more = true;
		}
		else {
			$has_more = false;
		}

		return new Response($twig->render('admin/policies.html.twig', [
			'entries' => $entries->slice(0, 30),
			'has_more' => $has_more,
			'form' => compact('domain', 'automated', 'policy', 'comment'),
			'pagenum' => $pagenum,
			'login_email' => User::where('id', $this->session->get('userid'))->pluck('email')->first()
		]));
	}
}
