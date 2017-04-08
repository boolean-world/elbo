<?php

namespace Elbo\RateLimiters;

class AnonShortenRateLimiter extends IPRateLimiter {
	protected $requests = 10;
	protected $timeframe = 10;
}
