<?php

namespace Elbo\Library;

use Elbo\Exceptions\InvalidURLException;

class URL {
	const hostname_chars = 'abcdefghijklmnopqrstuvwxyz0123456789-.';
	const path_chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789/-._~!$&\'()*+,;=?:%';
	const query_chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-._=&%+';
	const query_param_chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-._+';

	# This regex was generated automatically using bin/tld_regex_generator.plx. Do not manually edit.
	const tld_regex = '/^(?:x(?:n--(?:m(?:gb(?:a(?:(?:7c0bbn0|yh7gp)a|3a(?:4f16a|3ejt)|am7a8h|b2bd)|c(?:0a9azcg|a7dzdo)|b(?:9fbpob|h1a71e)|t(?:3dhd|x2b)|erp4a5d4ar|x4cd0ab|9awbf|pl2fh)|k1bu44c|ix891f|xtq1m)|f(?:iq(?:(?:228c5h|s8|z9)s|64b)|z(?:ys8d69uvgm|c2c9e2c)|pcrj9c3d|ct429k|jq720a|lw351e|hbei)|c(?:zr(?:694b|s0t|u2d)|lchc0ea0b2g2a9gcd|(?:2br7|1av)g|ck2b3b|g4bki)|3(?:oq18vl8pn36a|e0b707e|bst00m|ds443g|0rr7y|pxu8k)|5(?:(?:su34j936bgs|tzm5)g|5q(?:w42g|x5d)|4b7fta0cc)|n(?:gb(?:c5azd|e9e0a)|qv7f(?:s00ema)?|yqy26a|ode)|v(?:(?:ermgensberat(?:ung-pw|er-ct)|uq861)b|hquv)|k(?:p(?:r(?:w13|y57)d|u(?:716f|t3i))|crx77d1x4a)|w(?:4r(?:85el8fhu5dnra|s40l)|gb(?:h1c|l6a))|8(?:0a(?:s(?:ehdb|wg)|dxhks|o21a)|y0a063a)|9(?:(?:krt00|dbq2)a|0a(?:3ac|is|e)|et52u)|j(?:1a(?:ef|mh)|lq61u9w7b|6w193g|vr189m)|x(?:kc2(?:dl3a5ee0h|al3hye2a)|hq521b)|g(?:(?:2xx48|ecrj9)c|ckr3f0f|k3at1e)|p(?:1a(?:cf|i)|bt977c|gbs0dh|ssy2u)|4(?:5(?:brj9|q11)c|2c2d9a|gbrim)|e(?:ckvdtc9d|fvy88h|stv75g|1a4c)|i(?:1b6b1a6a2e|mr513n|o0a7i)|y(?:fro4i67o|gbi2ammx|9a3aq)|b(?:ck1b9a5dre4c|4w605ferd)|q(?:(?:cka1pm|9jyb4)c|xam)|1(?:1b4c3d|ck2e1b|qqw23a)|6(?:qq986b3xl|frz82g)|l(?:gbbat1ad8j|1acc)|h(?:2brj9c|xt814e)|o(?:gbpf8fl|3cw4h)|r(?:hqv96g|ovu88b)|s(?:9brj9c|es554g)|t(?:60b56a|ckwe)|d1a(?:cj3b|lf)|zfr164b|unup4y)|(?:(?:er|b)o|x)x|i(?:hua)?n|finity|peria|yz)|s(?:[dgjvxz]|t(?:a(?:t(?:e(?:bank|farm)|oil)|r(?:hub)?|ples|da)|o(?:r(?:ag)?e|ckholm)|c(?:group)?|ud(?:io|y)|ream|yle)?|a(?:n(?:dvik(?:coromant)?|ofi)|ms(?:club|ung)|fe(?:ty)?|l(?:on|e)|arland|kura|po?|rl|ve|xo|s)?|h(?:o(?:p(?:ping)?|w(?:time)?|uji|es)|a(?:ngrila|rp|w)|i(?:ksh)?a|riram|ell)?|c(?:[ab]|h(?:o(?:larships|ol)|aeffler|midt|warz|ule)|johnson|ience|o[rt])?|o(?:ft(?:bank|ware)|l(?:utions|ar)|c(?:cer|ial)|n[gy]|hu|y)?|e(?:cur(?:ity|e)|(?:rvice)?s|(?:lec|a)t|ner|ven|xy?|ek|w)?|u(?:pp(?:l(?:ies|y)|ort)|r(?:gery|f)|zuki|cks)?|p(?:readbetting|iegel|ace|ot)|w(?:i(?:ftcover|ss)|atch)|i(?:n(?:gles|a)|lk|te)?|y(?:mantec|stems|dney)?|k(?:y(?:pe)?|in?)?|m(?:art|ile)?|l(?:ing)?|n(?:cf)?|b[is]?|r[lt]?|fr)|c(?:[dgkmnvwxz]|o(?:m(?:p(?:a(?:ny|re)|uter)|m(?:unity|bank)|cast|sec)?|n(?:s(?:truction|ulting)|t(?:ractors|act)|dos)|o(?:[lp]|king(?:channel)?)|(?:l(?:leg|ogn)|ffe)e|u(?:pons?|ntry|rses)|rsica|ach|des)?|a(?:r(?:e(?:ers?)?|avan|tier|d?s)?|p(?:ital(?:one)?|etown)|s(?:[ah]|e(?:ih)?|ino)|n(?:cerresearch|on)|l(?:vinklein|l)?|m(?:era|p)?|t(?:ering)?|fe|b)?|h(?:r(?:istmas|ysler|ome)|a(?:n?nel|se|t)|intai|urch|eap|loe)?|l(?:i(?:ni(?:que|c)|ck)|o(?:thing|ud)|ub(?:med)?|eaning|aims)?|i(?:t(?:y(?:eats)?|adel|ic?)|priani|rcle|sco)?|r(?:edit(?:union|card)?|uises?|icket|own|s)?|e(?:[bo]|nter|rn)|y(?:(?:mr|o)u)?|u(?:isinella)?|b(?:[ans]|re)|f[ad]?|s?c)|a(?:l(?:l(?:finanz|state|y)|i(?:baba|pay)|s(?:ace|tom)|faromeo)?|m(?:e(?:rican(?:express|family)|x)|(?:sterd|f)am|ica)?|c(?:c(?:ountants?|enture)|t(?:ive|or)|ademy|o)?|u(?:di(?:ble|o)?|t(?:hor|os?)|ction|spost)?|b(?:b(?:ott|vie)?|udhabi|ogado|arth|le|c)|i(?:r(?:force|bus|tel)|go?)?|n(?:alytics|droid|quan|z)|p(?:artments|p(?:le)?)|r(?:amco|chi|te?|my)?|f(?:amilycompany|l)?|s(?:sociates|[di]a)?|t(?:torney|hleta)?|g(?:akhan|ency)?|d(?:ult|ac|s)?|e(?:tna|ro|g)?|q(?:uarelle)?|a(?:rp|a)|z(?:ure)?|vianca|kdn|ol?|ws?|xa?)|b(?:[dfgjstvwy]|a(?:r(?:c(?:lay(?:card|s)|elona)|efoot|gains)?|n(?:[dk]|a(?:narepublic|mex))|s(?:ket|e)ball|uhaus|yern|idu|by)?|o(?:[mtx]|o(?:k(?:ing)?|ts)?|s(?:tik|ch)|ehringer|utique|ats|fa|nd)?|l(?:o(?:(?:omber)?g|ckbuster)|a(?:ck(?:friday)?|nco)|ue)|r(?:o(?:(?:th|k)er|adway)|idgestone|adesco|ussels)?|e(?:a(?:uty|ts)|st(?:buy)?|ntley|rlin|er|t)?|u(?:ild(?:ers)?|dapest|siness|gatti|zz|y)|i(?:[doz]|(?:bl|k)e|ngo?)?|n(?:pparibas|l)?|b(?:[ct]|va)?|h(?:arti)?|m[sw]?|c[gn]|zh?)|m(?:[dghknpqrvwxyz]|o(?:[eim]|n(?:tblanc|ster|ash|ey)|v(?:i(?:star|e))?|r(?:tgage|mon)|to(?:rcycles)?|bi(?:ly)?|scow|par|da)?|a(?:r(?:ket(?:ing|s)?|shalls|riott)|n(?:agement|go)?|i(?:son|f)|serati|drid|keup|ttel|cys)?|e(?:(?:lbourn|tlif)e|m(?:orial|e)|d(?:ia)?|nu?|et|o)?|i(?:t(?:subishi)?|crosoft|n[it]|ami|l)|c(?:d(?:onalds)?|kinsey)?|u(?:tu(?:elle|al)|seum)?|t(?:[nr]|pc)?|l[bs]?|ma?|sd?|ba)|t(?:[fglntwz]|r(?:a(?:vel(?:ers(?:insurance)?|channel)?|d(?:ing|e)|ining)|ust|v)?|e(?:l(?:e(?:fonica|city))?|ch(?:nology)?|masek|nnis|am|va)|o(?:(?:ol|ur)s|y(?:ota|s)|[dr]ay|shiba|kyo|tal|wn|p)?|a(?:t(?:a(?:motors|r)|too)|ipei|obao|rget|xi?|lk|b)|i(?:(?:cket|p)s|(?:end|a)a|r(?:es|ol)|ffany)|h(?:eat(?:er|re)|d)?|u(?:nes|shu|be|i)|j(?:(?:max)?x)?|k(?:maxx)?|m(?:all)?|ci?|dk?|vs?)|f(?:[jkm]|i(?:r(?:(?:eston)?|mdal)e|na(?:nc(?:ial|e)|l)|d(?:elity|o)|sh(?:ing)?|t(?:ness)?|at|lm)?|o(?:o(?:d(?:network)?|tball)?|r(?:sale|ex|um|d)|undation|x)?|a(?:i(?:rwinds|th|l)|s(?:hion|t)|rm(?:ers)?|mily|ns?|ge)|r(?:o(?:nt(?:doo|ie)r|gans)|e(?:senius|e)|l)?|l(?:i(?:(?:ck)?r|ghts)|o(?:rist|wers)|y)|u(?:ji(?:xerox|tsu)|rniture|tbol|nd)|e(?:rr(?:ari|ero)|edback|dex)|tr|yi)|l(?:[bckrvy]|i(?:(?:f(?:e(?:insuranc|styl))?|k)e|n(?:coln|de|k)|m(?:ited|o)|(?:ll|ps)y|v(?:ing|e)|(?:xi|d)l|ghting|aison)?|a(?:n(?:c(?:aster|ome|ia)|d(?:rover)?|xess)|m(?:borghini|er)|t(?:robe|ino)?|w(?:yer)?|dbrokes|caixa|salle)?|o(?:c(?:ker|us)|tt[eo]|ans?|ndon|ft|ve|l)|e(?:g(?:al|o)|clerc|frak|ase|xus)|u(?:x(?:ury|e)|ndbeck|pin)?|p(?:lfinancia)?l|t(?:da?)?|d?s|gbt)|p(?:[gkmsty]|r(?:o(?:d(?:uctions)?|pert(?:ies|y)|gressive|tection|mo|f)?|a(?:merica|xi)|u(?:dential)?|ess|ime)?|a(?:r(?:t(?:(?:ner)?s|y)|i?s)|n(?:asonic|erai)|mperedchef|ssagens|ge|y)?|i(?:c(?:t(?:ures|et)|s)|n[gk]?|oneer|aget|zza|d)|h(?:oto(?:graphy|s)?|armacy|ilips|ysio)?|l(?:a(?:y(?:station)?|ce)|u(?:mbing|s))?|o(?:litie|ker|hl|rn|st)|f(?:izer)?|[nw]c?|ccw|et?|ub)|g(?:[fhnpqstwy]|o(?:[ptv]|o(?:d(?:hands|year)|g(?:le)?)?|l(?:d(?:point)?|f)|daddy)|r(?:a(?:(?:phic|ti)s|inger)|een|ipe|oup)?|a(?:l(?:l(?:ery|up|o))?|mes?|rden|p)?|u(?:i(?:tars|de)|ardian|cci|ge|ru)?|l(?:a(?:de|ss)|ob(?:al|o)|e)?|e(?:nt(?:ing)?|orge|a)?|i(?:v(?:ing|es)|fts?)?|m(?:[ox]|ail|bh)?|b(?:iz)?|g(?:ee)?|dn?)|d(?:[jmz]|e(?:l(?:ivery|oitte|ta|l)|nt(?:ist|al)|al(?:er|s)?|si(?:gn)?|mocrat|gree|v)?|i(?:s(?:co(?:unt|ver)|h)|rect(?:ory)?|amonds|gital|et|y)|o(?:[gt]|c(?:tor|s)|wnload|mains|dge|ha)?|a(?:[dy]|t(?:ing|sun|e)|bur|nce)|u(?:n(?:lop|s)|pont|rban|bai|ck)|v(?:ag|r)|(?:cl)?k|rive|ds|hl|np|tv)|h(?:[mnr]|o(?:me(?:s(?:ense)?|depot|goods)|l(?:dings|iday)|t(?:eles|mail)?|n(?:eywell|da)|st(?:ing)?|[ru]se|ckey|w)|e(?:alth(?:care)?|l(?:sinki|p)|r(?:mes|e))|i(?:samitsu|tachi|phop|v)|a(?:mburg|ngout|us)|y(?:undai|att)|dfc(?:bank)?|u(?:ghes)?|gtv|kt?|sbc|tc?|bo)|r(?:e(?:a(?:l(?:t(?:or|y)|estate)|d)|d(?:umbrella|stone)?|p(?:ublican|air|ort)|n(?:t(?:als)?)?|s(?:tauran)?t|i(?:sen?|t)|views?|cipes|xroth|hab)?|i(?:[op]|c(?:h(?:ardli)?|oh)|ghtathome)|o(?:c(?:her|ks)|gers|deo|om)?|a(?:cing|dio|id)|u(?:hr|n)?|s(?:vp)?|yukyu|we?)|i(?:[dlq]|n(?:[gk]|t(?:e(?:rnationa)?l|uit)?|s(?:ur(?:anc)?|titut)e|(?:vestment|dustrie)s|f(?:initi|o))?|s(?:t(?:anbul)?|elect|maili)?|m(?:mo(?:bilien)?|amat|db)?|(?:kan|vec)?o|c(?:[eu]|bc)|t(?:au|v)?|r(?:ish)?|(?:ee)?e|piranga|[bf]m|inet|wc)|e(?:[eg]|x(?:(?:traspac|chang)e|p(?:osed|ress|ert))|n(?:gineer(?:ing)?|terprises|ergy)|s(?:(?:uranc|tat)e|q)?|d(?:u(?:cation)?|eka)|u(?:rovision|s)?|r(?:icsson|ni)?|ve(?:rbank|nts)|(?:quipmen)?t|m(?:erck|ail)|p(?:ost|son)|a(?:rth|t)|co?)|n(?:[lpuz]|e(?:t(?:(?:ban|wor)k|flix)?|x(?:(?:tdirec)?t|us)|w(?:holland|s)?|ustar|c)?|o(?:rt(?:hwesternmutual|on)|w(?:ruz|tv)?|kia)?|a(?:t(?:ionwide|ura)|goya|dex|me|vy|b)?|i(?:k(?:on|e)|ssa[ny]|nja|co)?|r[aw]?|fl?|go?|y?c|ba|hk|tt)|v(?:[cg]|i(?:s(?:ta(?:print)?|ion|a)|(?:aje|lla)s|(?:kin)?g|(?:rgi)?n|v[ao]|deo|p)?|o(?:l(?:kswagen|vo)|t(?:[eo]|ing)|yage|dka)|e(?:r(?:sicherung|isign)|(?:nture|ga)s|t)?|a(?:n(?:guard|a)|cations)?|(?:laandere)?n|u(?:elos)?)|w(?:[fs]|e(?:ather(?:channel)?|b(?:site|cam|er)|d(?:ding)?|i(?:bo|r))|a(?:l(?:mart|ter|es)|ng(?:gou)?|tch(?:es)?|rman)|i(?:n(?:(?:dow|ner)s|e)?|lliamhill|en|ki)|o(?:lterskluwer|r(?:ks?|ld)|odside|w)|hoswho|t[cf]|me)|o(?:r(?:i(?:entexpres|gin)s|a(?:cl|ng)e|g(?:anic)?)|n(?:(?:yoursid)?e|l(?:ine)?|g)|l(?:ayan(?:group)?|dnavy|lo)|(?:kinaw|sak)a|b(?:server|i)|t(?:suka|t)|ff(?:ice)?|m(?:ega)?|pen|oo|vh)|k(?:[gmwz]|e(?:rry(?:propertie|logistic|hotel)s)?|i(?:[am]|nd(?:er|le)|tchen|wi)?|o(?:matsu|sher|eln)|(?:aufe)?n|p(?:mg|n)?|r(?:e?d)?|y(?:oto)?|uokgroup|ddi|f?h)|j(?:o(?:[ty]|b(?:urg|s))?|e(?:welry|tzt|ep)?|p(?:morgan|rs)?|u(?:niper|egos)|a(?:guar|va)|c[bp]|l[cl]|mp?|nj)|y(?:[et]|o(?:(?:koham|g)a|u(?:tube)?|dobashi)|a(?:maxun|chts|ndex|hoo)|un)|u(?:[agkyz]|n(?:i(?:versity|com)|o)|b(?:ank|s)|connect|p?s|ol)|z(?:[mw]|a(?:ppos|ra)?|ip(?:po)?|uerich|ero|one)|q(?:ue(?:bec|st)|pon|vc|a))$/';

	private $protocol;
	private $hostname;
	private $port;
	private $path;

	public static function determineProtocol($url) {
		$protocol = preg_replace('/^([a-z]+):\/\/.*/i', '$1', $url);
		return ($protocol === $url) ? '' : strtolower($protocol);
	}

	public static function determineHostName($url) {
		# Get the hostname+port, then remove the port segment, to be able to differentiate
		# invalid cases such as 'https:/foo/'
		$hostname = preg_replace('/^(?:[a-z]+:\/\/)?([^\/]+).*/i', '$1', $url);
		$hostname = preg_replace('/:[0-9]+$/', '', $hostname);

		if (strlen($hostname) === 0) {
			throw new InvalidURLException('Invalid hostname', InvalidURLException::INVALID_HOSTNAME);
		}

		# IPv6 addresses
		if ($hostname[0] === '[' && $hostname[strlen($hostname) - 1] === ']') {
			if (filter_var(substr($hostname, 1, -1), FILTER_VALIDATE_IP,
				FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
				throw new InvalidURLException('Invalid hostname', InvalidURLException::INVALID_HOSTNAME);
			}

			return $hostname;
		}

		# IPv4 addresses
		if (preg_match('/^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$/', $hostname)) {
			if (filter_var($hostname, FILTER_VALIDATE_IP,
				FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
				throw new InvalidURLException('Invalid hostname', InvalidURLException::INVALID_HOSTNAME);
			}

			if (ip2long($hostname) >> 24 === 127) {
				throw new InvalidURLException('Invalid hostname', InvalidURLException::INVALID_HOSTNAME);
			}

			return $hostname;
		}

		# Domain names
		$hostname = idn_to_ascii($hostname, 0, INTL_IDNA_VARIANT_UTS46);

		if ($hostname === false) {
			throw new InvalidURLException('Invalid hostname', InvalidURLException::INVALID_HOSTNAME);
		}

		# A dot at the end is perfectly valid!
		$hostname = preg_replace('/\.$/', '', $hostname);

		if (strlen($hostname) > 253) {
			throw new InvalidURLException('Invalid hostname', InvalidURLException::INVALID_HOSTNAME);
		}

		$hostname = strtolower($hostname);
		$segments = explode('.', $hostname);
		$segments_count = 0;

		foreach ($segments as $segment) {
			$segments_count++;
			$len = strlen($segment);

			for ($i = 0; $i < $len; $i++) {
				if (strpos(self::hostname_chars, $segment[$i]) !== false) {
					if ($segment[$i] === '-' && ($i === 0 || $i === $len - 1)) {
						throw new InvalidURLException('Invalid hostname', InvalidURLException::INVALID_HOSTNAME);
					}
				}
				else {
					throw new InvalidURLException('Invalid hostname', InvalidURLException::INVALID_HOSTNAME);
				}
			}
		}

		if ($segments_count < 2 || $segments_count > 63) {
			throw new InvalidURLException('Invalid hostname', InvalidURLException::INVALID_HOSTNAME);
		}

		if (!preg_match(self::tld_regex, $segments[$segments_count - 1])) {
			throw new InvalidURLException('Invalid hostname', InvalidURLException::INVALID_HOSTNAME);
		}

		return $hostname;
	}

	public static function determinePort($url) {
		# Get the hostname+port, then remove everything except the port.
		$url = preg_replace('/^(?:[a-z]+:\/\/)?([^\/]+).*/i', '$1', $url);
		$port = preg_replace('/.*:([0-9]+)$/', '$1', $url);

		if ($port === $url) {
			return 0;
		}

		$port = (int)$port;
		if ($port >= 65535) {
			throw new InvalidURLException('Invalid port number', InvalidURLException::INVALID_PORT);
		}
		return (int)$port;
	}

	public static function determinePath($url) {
		$path = preg_replace('/^(?:[a-z]+:\/\/)?(?:[^\/]+)(.*)/i', '$1', $url);
		return ($path === $url || $path === '') ? '/' : $path;
	}

	public static function urlEncodePath($path) {
		$len = strlen($path);
		$query_string = false;
		$query_param = false;
		$page_anchor = false;
		$ret = '';

		for ($i = 0; $i < $len; $i++) {
			if ($path[$i] === '#') {
				$page_anchor = true;
			}

			if ($page_anchor || (!$query_string && strpos(self::path_chars, $path[$i]) !== false) ||
			    ($query_string && strpos(self::query_chars, $path[$i]) !== false)) {
				if ($path[$i] === '?') {
					$query_string = true;
				}
				else if ($query_string && $path[$i] === '=') {
					$query_param = true;
				}
			
				$ret .= $path[$i];
			}
			else {
				# PHP's urlencode function encodes things as if they were query strings!
				if ($path[$i] === ' ' && !$query_string) {
					$ret .= '%20';
				}
				else {
					$ret .= urlencode($path[$i]);
				}
			}
		}

		return $ret;
	}

	public static function removeRedundantPercentEncoding($path) {
		$ret = '';
		$i = 0;
		$len = strlen($path);
		$query_string = false;
		$query_param = false;

		while ($i < $len) {
			if ($path[$i] === '#') {
				$ret .= substr($path, $i);
				break;
			}
			else if ($path[$i] === '%') {
				$pct_encoded = substr($path, $i, 3);
				$pct_decoded = urldecode($pct_encoded);
				if ($pct_decoded === $pct_encoded) {
					# Invalid %xx.
					throw new InvalidURLException('Invalid path', InvalidURLException::INVALID_PATH);
				}
				else if ((!$query_string && strpos(self::path_chars, $pct_decoded) !== false) ||
				         ($query_string && !$query_param && strpos(self::query_chars, $pct_decoded) !== false) ||
				         ($query_string && $query_param && strpos(self::query_param_chars, $pct_decoded) !== false)) {
					$ret .= $pct_decoded;
					$i += 2;
				}
				else {
					# Keep things just as they are, by preserving the urlencoded string,
					# but convert it to uppercase, to keep things consistent.
					$ret .= strtoupper($pct_encoded);
					$i += 2;
				}
			}
			else if (($query_string && strpos(self::query_chars, $path[$i]) !== false) ||
			         (!$query_string && strpos(self::path_chars, $path[$i]) !== false)) {
				if ($path[$i] === '?') {
					$query_string = true;
				}
				else if ($query_string && $path[$i] === '=') {
					$query_param = true;
				}
				else if ($query_string && $path[$i] === '&') {
					$query_param = false;
				}

				$ret .= $path[$i];
			}
			$i++;
		}

		return $ret;
	}

	public function getProtocol() {
		return $this->protocol;
	}

	public function getHost() {
		return $this->hostname;
	}

	public function getPort() {
		return $this->port;
	}

	public function getPath() {
		return $this->path;
	}

	public function getURL() {
		$ret = $this->protocol.'://'.$this->hostname;

		if (($this->protocol === 'http' && $this->port !== 80) || ($this->protocol === 'https' && $this->port !== 443)) {
			$ret .= ':'.$this->port;
		}

		$ret .= $this->path;
		return $ret;
	}

	public function __toString() {
		return $this->getURL();
	}

	public function __construct($url) {
		$url = trim($url);

		$protocol = $this->determineProtocol($url);
		$port = $this->determinePort($url);

		if ($protocol === '') {
			$protocol = 'http';
		}

		if ($protocol === 'http') {
			if ($port === 0) {
				$port = 80;
			}
		}
		else if ($protocol == 'https') {
			if ($port === 0) {
				$port = 443;
			}
		}
		else {
			throw new InvalidURLException('Unsupported protocol', InvalidURLException::UNSUPPORTED_PROTOCOL);
		}

		$hostname = $this->determineHostName($url);
		$path = $this->determinePath($url);
		$path = $this->urlEncodePath($path);
		$path = $this->removeRedundantPercentEncoding($path);

		$this->protocol = $protocol;
		$this->hostname = $hostname;
		$this->port = $port;
		$this->path = $path;
	}
};
