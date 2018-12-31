<?php

namespace Elbo\Middlewares;

use Symfony\Component\HttpFoundation\Request;

class RedirectParameterVerified {
	public function handle(Request $request, $next) {
		$redir = $request->query->get('redirect', '');

		if (substr($redir, 0, 2) !== '/~' || preg_match('#^/~(?:logout|admin/\w+/\w+/.+)(?:\?.*)?$#', $redir)) {
			$request->query->set('redirect', '/');
		}

		return $next();
	}
}
