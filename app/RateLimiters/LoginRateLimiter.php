<?php

namespace Elbo\RateLimiters;

class LoginRateLimiter extends IPRateLimiter {
	protected $requests = 5;
	protected $timeframe = 10;
}
