<?php

namespace Elbo\Library;

class Base62 {
	public static function encode($data) {
		$outstring = '';
		$l = strlen($data);

		for ($i = 0; $i < $l; $i += 8) {
			$chunk = substr($data, $i, 8);
			$outlen = ceil((strlen($chunk) * 8)/6); //8bit/char in, 6bits/char out, round up
			$x = bin2hex($chunk);  //gmp won't convert from binary, so go via hex
			$w = gmp_strval(gmp_init(ltrim($x, '0'), 16), 62); //gmp doesn't like leading 0s
			$pad = str_pad($w, $outlen, '0', STR_PAD_LEFT);
			$outstring .= $pad;
		}

		return $outstring;
	}

	public static function decode($data) {
		$outstring = '';
		$l = strlen($data);

		for ($i = 0; $i < $l; $i += 11) {
			$chunk = substr($data, $i, 11);
			$outlen = floor((strlen($chunk) * 6)/8); //6bit/char in, 8bits/char out, round down
			$y = gmp_strval(gmp_init(ltrim($chunk, '0'), 62), 16); //gmp doesn't like leading 0s
			$pad = str_pad($y, $outlen * 2, '0', STR_PAD_LEFT); //double output length as as we're going via hex (4bits/char)
			$outstring .= pack('H*', $pad); //same as hex2bin
		}

		return $outstring;
	}
}
