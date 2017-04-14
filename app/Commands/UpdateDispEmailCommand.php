<?php

namespace Elbo\Commands;

use GuzzleHttp\Client;
use Symfony\Component\Console\{Command\Command, Input\InputInterface, Output\OutputInterface};

class UpdateDispEmailCommand extends Command {
	const lists = [
		'https://raw.githubusercontent.com/wesbos/burner-email-providers/master/emails.txt',
		'https://raw.githubusercontent.com/martenson/disposable-email-domains/master/disposable_email_blacklist.conf'
	];

	protected function configure() {
		$this->setName('update:dispemail')
		     ->setDescription('Update blacklist of disposable email providers')
		     ->setHelp('Update blacklist of disposable email providers');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$output->writeln('Starting blacklist update ('.strftime('%Y-%m-%d %H:%M:%S').')');

		$client = new Client();
		$domains = [];

		foreach (self::lists as $list) {
			$content = $client->get($list)->getBody();

			$line = strtok($content, "\r\n");

			while ($line !== false) {
				$domains[$line] = true;
				$line = strtok("\r\n");
			}
		}

		file_put_contents('data/disposable-email-domains',
			'<?php return function($x) { $y = '.var_export($domains, true).'; return !isset($y[$x]);};'
		);

		$output->writeln('Blacklist updated successfully.');
	}
}
