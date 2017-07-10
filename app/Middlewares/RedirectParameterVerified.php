<?php

namespace Elbo\Middlewares;

use Symfony\Component\HttpFoundation\{Request, Response};

trait RedirectParameterVerified {
	protected function redirectParameterVerified(Request $request) {
		$redir = $request->query->get('redirect', '');

		if ($redir === '' || $redir[0] !== '/' || substr($redir, 0, 2) === '//') {
			$request->query->set('redirect', '/');
		}

		return $this->next();
	}
}
