<?php

namespace Elbo\Commands;

use GuzzleHttp\Client;
use Illuminate\Database\Capsule\Manager as DB;
use Symfony\Component\Console\{Command\Command, Input\InputInterface, Output\OutputInterface};
use Elbo\{Library\URL, Exceptions\InvalidURLException, Models\DomainPolicy, Library\Configuration};

class UpdatePoliciesCommand extends Command {
	protected function configure() {
		$this->setName('update:policies')
		     ->setDescription('Update domain policies from publicly available blacklists')
		     ->setHelp('Update domain policies from publicly available blacklists');
	}

	protected function stripWWW(string $domain) {
		return preg_replace('/^www\.(.{4,}\.{2,})$/', '\1', $domain);
	}

	protected function processTextRules(string $filename, array &$array, int $value) {
		$fp = fopen($filename, 'r');

		while (($line = fgets($fp)) !== false) {
			$line = trim(preg_replace('/#.*/', '', $line));

			if (preg_match('/^\d+.\d+.\d+.\d+\s+(.*)/', $line, $matches)) {
				$domain = $matches[1];
			}
			else if (strpos($line, '/') !== false) {
				try {
					$domain = URL::determineHostName($line);
				}
				catch (InvalidURLException $e) {
					continue;
				}
			}
			else if (preg_match('/[:.]/', $line) && !preg_match('/\s/', $line)) {
				$domain = $line;
			}
			else {
				continue;
			}

			$domain = idn_to_ascii(strtolower($domain), 0, INTL_IDNA_VARIANT_UTS46);
			$domain = $this->stripWWW($domain);
			$array[$domain] = $value;
		}
	}

	protected function processJsonGzipRules(string $filename, array &$array, int $value) {
		$fp = gzopen($filename, 'r');
		$state = 0;
		$url = '';

		// Simple scanner that looks for the string '"url":"..."' patterns, allowing
		// loading of very big .json.gz files.
		while (($str = gzgets($fp, 4096)) !== false) {
			for ($i = 0; $i < strlen($str); $i++) {
				if ($state === 0 && $str[$i] === '"') {
					$state = 1;
				}
				else if ($state === 1 && $str[$i] === 'u') {
					$state = 2;
				}
				else if ($state === 2 && $str[$i] === 'r') {
					$state = 3;
				}
				else if ($state === 3 && $str[$i] === 'l') {
					$state = 4;
				}
				else if ($state === 4 && $str[$i] === '"') {
					$state = 5;
				}
				else if ($state === 5) {
					if ($str[$i] === '"') {
						$state = 6;
						$url = '';
					}
					else if (strpos(" :\t\r\n", $str[$i]) === false) {
						$state = 0;
					}
				}
				else if ($state === 6) {
					if ($str[$i] === '\\') {
						$state = 7;
					}
					else if ($str[$i] === '"') {
						try {
							$domain = URL::determineHostName($url);
							$domain = $this->stripWWW($domain);
							$array[$domain] = $value;
						}
						catch (InvalidURLException $e) {
							// ignore
						}

						$state = 0;
					}
					else {
						$url .= $str[$i];
					}
				}
				else if ($state === 7) {
					$url .= $str[$i];
					$state = 6;
				}
				else {
					$state = 0;
				}
			}
		}

		gzclose($fp);
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$output->writeln('Starting policy update ('.strftime('%Y-%m-%d %H:%M:%S').')');

		$config = new Configuration();
		$client = new Client();
		$domains = [];
		$tmpfile = 'data/tmp/.policy.tmp';

		foreach ([
			'malware' => DomainPolicy::POLICY_BLOCKED_MALWARE,
			'phishing' => DomainPolicy::POLICY_BLOCKED_PHISHING,
			'illegal' => DomainPolicy::POLICY_BLOCKED_ILLEGAL,
			'redirector' => DomainPolicy::POLICY_BLOCKED_REDIRECTOR,
			'spam' => DomainPolicy::POLICY_BLOCKED_SPAM,
			'allowed' => DomainPolicy::POLICY_ALLOWED
		] as $section => $policy) {
			foreach ($config->get("url_policies.sources.${section}", []) as $entry) {
				$output->writeln("Download: ${entry}");
				$data = $client->get($entry, ['sink' => $tmpfile]);

				$output->writeln("Process : ${entry}");
				if (preg_match('/\.json\.gz$/', $entry)) {
					$this->processJsonGzipRules($tmpfile, $domains, $policy);
				}
				else {
					$this->processTextRules($tmpfile, $domains, $policy);
				}
			}
		}

		@unlink($tmpfile);

		$output->writeln('Beginning transaction...');

		if (isset($domains[''])) {
			unset($domains['']);
		}

		DB::transaction(function() use ($output, $domains) {
			$output->writeln('Removing previous automatic rules...');
			DomainPolicy::where('automated', true)->delete();

			$output->writeln('Adding new rules...');
			foreach ($domains as $domain => $policy) {
				$count = DomainPolicy::where('domain', $domain)->count();

				if ($count === 0) {
					DomainPolicy::create([
						'domain' => $domain,
						'automated' => true,
						'policy' => $policy
					]);
				}
			}

			$output->writeln('Policies updated.');
		});
	}
}
