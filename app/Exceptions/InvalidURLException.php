<?php

namespace Elbo\Exceptions;

class InvalidURLException extends \Exception {
	const INVALID_HOSTNAME = 1;
	const INVALID_PORT = 2;
	const INVALID_PATH = 3;
	const UNSUPPORTED_PROTOCOL = 4;
}
