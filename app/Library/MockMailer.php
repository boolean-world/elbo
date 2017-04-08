<?php

namespace Elbo\Library;

use Nette\Mail\{IMailer, Message};

class MockMailer implements IMailer {
	function send(Message $m) {
		$date = strftime('%Y%m%d%H%M%S');

		$fp = file_put_contents(__DIR__."/../../data/tmp/mail_$date.txt", var_export($m, true));
	}
}
