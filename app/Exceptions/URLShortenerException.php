<?php

namespace Elbo\Exceptions;

class URLShortenerException extends \Exception {
	const SHORTURL_INVALID = 1;
	const SHORTURL_CREATION_FAILED = 2;
	const SHORTURL_TAKEN = 3;
	const PROHIBITED_URL = 4;
}
