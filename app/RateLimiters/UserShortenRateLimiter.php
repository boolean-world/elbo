<?php

namespace Elbo\RateLimiters;

class UserShortenRateLimiter extends IPRateLimiter {
	protected $requests = 20;
	protected $timeframe = 30;
}
