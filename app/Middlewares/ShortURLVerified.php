<?php

namespace Elbo\Middlewares;

use Twig_Environment;
use Elbo\Library\URL;
use Elbo\Models\User;
use Elbo\Library\Session;
use Elbo\Models\ShortURL;
use Elbo\Models\DomainPolicy;
use Elbo\Library\Configuration;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ShortURLVerified {
	protected $twig;
	protected $session;
	protected $config;

	public function __construct(Twig_Environment $twig, Session $session, Configuration $config) {
		$this->twig = $twig;
		$this->session = $session;
		$this->config = $config;
	}

	public function handle(Request $request, $next) {
		$res = ShortURL::where('shorturl', $this->data['shorturl'])->select('shorturl', 'url', 'disabled')->first();
		$email = User::where('id', $this->session->get('userid'))->pluck('email')->first();

		if ($res === null) {
			return new Response($this->twig->render('errors/notfound.html.twig', [
				'login_email' => $email
			]), 404);
		}

		if ($res->disabled) {
			return new Response($this->twig->render($this->warningPage ?? 'disabled.html.twig', [
				'login_email' => $email
			]), 403);
		}

		$deny_regex = $this->config->get('url_policies.deny_urls', null);

		if ($deny_regex !== null && $deny_regex !== "") {
			$deny_regex = '/'.str_replace('/', '\/', $deny_regex).'/i';

			if (preg_match($deny_regex, $res->url)) {
				$twig = $this->container->get(\Twig_Environment::class);

				return new Response($this->twig->render($this->warningPage ?? 'disabled.html.twig', [
					'login_email' => $email
				]), 403);
			}
		}

		$policy = DomainPolicy::getPolicy(URL::determineHostName($res->url));

		if ($policy !== null && $policy != DomainPolicy::POLICY_ALLOWED) {
			$twig = $this->container->get(\Twig_Environment::class);

			if ($policy === DomainPolicy::POLICY_BLOCKED_ILLEGAL) {
				# Redirect to the generic "disabled" page to prevent giving out the URL.
				return new Response($this->twig->render($this->warningPage ?? 'disabled.html.twig', [
					'login_email' => $email
				]), 403);
			}

			return new Response($this->twig->render($this->warningPage ?? 'warning.html.twig', [
				'type' => $policy,
				'url' => $res->url,
				'shorturl' => $res->shorturl,
				'login_email' => $email
			]), 403);
		}

		return $next();
	}
}
