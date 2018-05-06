<?php

namespace Elbo\Library;

class EmailValidator {
	private $validation_closure = null;

	public function normalize($email) {
		if (!preg_match('/^(.*)@(.*\.(.*))$/', $email, $matches)) {
			throw new \InvalidArgumentException('Invalid email address');
		}

		$domain = idn_to_ascii($matches[2], 0, INTL_IDNA_VARIANT_UTS46);
		if ($domain === false) {
			throw new \InvalidArgumentException('Invalid email address');
		}

		$domain = strtolower($domain);
		$account = strtolower($matches[1]);

		if (!isset(URL::tld_list[$matches[3]])) {
			throw new \InvalidArgumentException('Invalid TLD.');
		}

		// Yahoo domains use the '-' character as a tag.
		if (preg_match('/^yahoo(?:\.[a-z0-9-]+){1,2}$/', $domain)) {
			$account = strtok($account, '-');
		}
		else {
			// Fastmail accounts of the form whatever@username.fastmail.com are actually username@fastmail.com
			if (preg_match('/^([a-z0-9-]+)\.fastmail\.(?:co|f)m$/', $domain, $fastmail_matches)) {
				$account = $fastmail_matches[1];
				$domain = 'fastmail.com';
			}

			// Remove alternative spellings such as john.doe@example.com to johndoe@example.com. Also, remove
			// + and = tags.
			$account = preg_replace('/(?:[+=].*|\.)/', '', $account);
		}

		$rv = filter_var($account.'@'.$domain, FILTER_VALIDATE_EMAIL);
		if ($rv === false) {
			throw new \InvalidArgumentException('Invalid email address');
		}

		return $rv;
	}

	public function isAllowed($email) {
		if (strlen($email) > 255) {
			return false;
		}

		if ($this->validation_closure === null) {
			$rgx_file = __DIR__.'/../../data/disposable-email-domains';

			if (file_exists($rgx_file)) {
				$this->validation_closure = require $rgx_file;
			}
			else {
				$this->validation_closure = function() {
					return true;
				};
			}
		}

		$domain = strrchr($email, '@');

		if ($domain === false) {
			return false;
		}

		$domain = substr($domain, 1);

		return ($this->validation_closure)($domain);
	}
}
