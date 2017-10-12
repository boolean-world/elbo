<?php

namespace Elbo\Commands;

use GuzzleHttp\Client;
use Elbo\Library\Configuration;
use Symfony\Component\Console\{Command\Command, Input\InputInterface, Output\OutputInterface};

class UpdateDispEmailCommand extends Command {
	protected function configure() {
		$this->setName('update:dispemail')
		     ->setDescription('Update blacklist of disposable email providers')
		     ->setHelp('Update blacklist of disposable email providers');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$output->writeln('Starting blacklist update ('.strftime('%Y-%m-%d %H:%M:%S').')');

		$config = new Configuration();
		$lists = $config->get('email_policies');

		if (!is_array($lists)) {
			throw new \Exception('"email_policies" is configured incorrectly.');
		}

		$client = new Client();
		$domains = [];

		foreach ($lists as $list) {
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
