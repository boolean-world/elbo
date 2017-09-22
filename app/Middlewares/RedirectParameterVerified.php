<?php

namespace Elbo\Middlewares;

use Symfony\Component\HttpFoundation\Request;

trait RedirectParameterVerified {
	protected function redirectParameterVerified(Request $request) {
		$redir = $request->query->get('redirect', '');

		if (substr($redir, 0, 2) !== '/~' || preg_match('#^/~(?:logout|admin/\w+/\w+.*)(?:\?.*)?$#', $redir)) {
			$request->query->set('redirect', '/');
		}

		return $this->next();
	}
}
