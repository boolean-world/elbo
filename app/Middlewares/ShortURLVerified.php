<?php

namespace Elbo\Middlewares;

use Elbo\Library\URL;
use Elbo\Models\{User, ShortURL, DomainPolicy};
use Symfony\Component\HttpFoundation\{Request, Response};

trait ShortURLVerified {
	protected function shortURLVerified(Request $request) {
		$res = ShortURL::where('shorturl', $this->data['shorturl'])->select('shorturl', 'url', 'disabled')->first();
		$email = User::where('id', $this->session->get('userid'))->pluck('email')->first();

		if ($res === null) {
			$twig = $this->container->get(\Twig_Environment::class);

			return new Response($twig->render('errors/notfound.html.twig', [
				'login_email' => $email
			]), 404);
		}

		if ($res->disabled) {
			$twig = $this->container->get(\Twig_Environment::class);

			return new Response($twig->render($this->warningPage ?? 'disabled.html.twig', [
				'login_email' => $email
			]), 403);
		}

		$policy = DomainPolicy::getPolicy(URL::determineHostName($res->url));

		if ($policy !== null && $policy != DomainPolicy::POLICY_ALLOWED) {
			$twig = $this->container->get(\Twig_Environment::class);

			if ($policy === DomainPolicy::POLICY_BLOCKED_ILLEGAL) {
				# Redirect to the generic "disabled" page to prevent giving out the URL.
				return new Response($twig->render($this->warningPage ?? 'disabled.html.twig', [
					'login_email' => $email
				]), 403);
			}

			return new Response($twig->render($this->warningPage ?? 'warning.html.twig', [
				'type' => $policy,
				'url' => $res->url,
				'shorturl' => $res->shorturl,
				'login_email' => $email
			]), 403);
		}

		return $this->next();
	}
}
