<?php

namespace Elbo\Library;

use Symfony\Component\HttpFoundation\Request;

abstract class Controller {
	abstract public function run(Request $request, array &$data) {}
}
