<?php

namespace Elbo\Library;

class Random {
	public static function string(int $len_min, int $len_max) {
		$len = random_int($len_min, $len_max);
		$str = random_bytes($len);
		$map = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

		for ($i = 0; $i < $len; $i++) {
			$str[$i] = $map[ord($str[$i]) % 62];
		}

		return $str;
	}
}
