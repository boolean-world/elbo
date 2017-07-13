<?php

namespace Elbo\Models;

use Illuminate\Database\Eloquent\Model;

class DomainPolicy extends Model {
	const POLICY_ALLOWED = 0;
	const POLICY_BLOCKED_SPAM = 1;
	const POLICY_BLOCKED_MALWARE = 2;
	const POLICY_BLOCKED_PHISHING = 3;
	const POLICY_BLOCKED_ILLEGAL_CONTENT = 4;
	const POLICY_BLOCKED_REDIRECTOR = 7;

	protected $table = 'domain_policy';
	protected $primaryKey = 'domain';
	public $incrementing = false;
	public $timestamps = false;

	protected $guarded = [];

	public static function getDomainComponents(string $domain) {
		yield $domain;

		if (($max = strrpos($domain, '.')) !== false) {
			for ($i = strpos($domain, '.'); $i < $max - 1; $i++) {
				if ($domain[$i] === '.') {
					yield substr($domain, $i + 1);
				}
			}
		}
	}

	public static function getPolicy(string $input) {
		if (filter_var($input, FILTER_VALIDATE_IP) !== false) {
			return self::where('domain', $input)->pluck('policy')->first();
		}

		foreach (self::getDomainComponents($input) as $component) {
			$policy = self::where('domain', $component)->pluck('policy')->first();

			if ($policy !== null) {
				return $policy;
			}
		}

		return null;
	}

	public static function isAllowed(string $input) {
		$res = self::getPolicy($input);

		return ($res === null || $res == self::POLICY_ALLOWED);
	}
}
