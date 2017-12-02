<?php

namespace Elbo\Commands;

use GuzzleHttp\Client;
use Illuminate\Database\Capsule\Manager as DB;
use Elbo\{Models\DomainPolicy, Library\Configuration};
use Symfony\Component\Console\{Command\Command, Input\InputInterface, Output\OutputInterface};

class UpdatePoliciesCommand extends Command {
	const domain_regex = '/^(?:[a-z0-9][a-z0-9-]*[a-z0-9]?\.)+(?:[a-z]{2,}(?:[a-z0-9-]*[a-z0-9])?)$/i';
	const extract_domain_regex = '~^(?:https?://)?([^/]+)/.*$~i';

	protected function configure() {
		$this->setName('update:policies')
		     ->setDescription('Update domain policies from publicly available blacklists')
		     ->setHelp('Update domain policies from publicly available blacklists');
	}

	protected function processDomainList(string $text, array &$array, int $value) {
		$line = strtok($text, "\r\n");

		while ($line !== false) {
			if (preg_match(self::domain_regex, $line)) {
				$rule = preg_replace('/^www\.(.{4,}\..{2,})/', '\1', strtolower($line));
				$array[$rule] = $value;
			}

			$line = strtok("\r\n");
		}
	}

	protected function processHostsFile(string $text, array &$array, int $value) {
		$line = strtok($text, "\r\n");

		while ($line !== false) {
			$line = preg_replace('/^\s*[0-9:.]+\s*|\s*#.*$/', '', $line);

			if (preg_match(self::domain_regex, $line)) {
				$rule = preg_replace('/^(?:www|\d{3,}[a-z\d]{4,})\.(.{4,}\..{2,})/', '\1', strtolower($line));
				$array[$rule] = $value;
			}

			$line = strtok("\r\n");
		}
	}

	protected function processURLList(string $text, array &$array, int $value) {
		$line = strtok($text, "\r\n");

		while ($line !== false) {
			if (preg_match(self::extract_domain_regex, $line, $matches)) {
				$rule = strtolower(preg_replace('/\:[0-9]+$/', '', $matches[1]));
				$rule = preg_replace('/^www\.(.{4,}\..{2,})/', '\1', $rule);

				$array[$rule] = $value;
			}

			$line = strtok("\r\n");
		}
	}

	protected function processIPList(string $text, array &$array, int $value) {
		$line = strtok($text, "\r\n");

		while ($line !== false) {
			if (filter_var($line, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
				$array[$line] = $value;
			}

			$line = strtok("\r\n");
		}
	}

	protected function processSerializedPhpGzip(string $data, array &$array, int $value) {
		foreach (unserialize(gzdecode($data)) as $item) {
			if (preg_match(self::extract_domain_regex, $item['url'], $matches)) {
				$rule = strtolower(preg_replace('/\:[0-9]+$/', '', $matches[1]));
				$rule = preg_replace('/^www\.(.{4,}\..{2,})/', '\1', $rule);
				$array[$rule] = $value;
			}
		}
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$output->writeln('Starting policy update ('.strftime('%Y-%m-%d %H:%M:%S').')');

		$config = new Configuration();
		$client = new Client();
		$domains = [];

		foreach ([
			'malware' => DomainPolicy::POLICY_BLOCKED_MALWARE,
			'phishing' => DomainPolicy::POLICY_BLOCKED_PHISHING,
			'illegal' => DomainPolicy::POLICY_BLOCKED_ILLEGAL,
			'redirector' => DomainPolicy::POLICY_BLOCKED_REDIRECTOR,
			'spam' => DomainPolicy::POLICY_BLOCKED_SPAM
		] as $key => $value) {
			foreach ([
				'hosts_files' => 'processHostsFile',
				'domain_lists' => 'processDomainList',
				'url_lists' => 'processURLList',
				'ip_lists' => 'processIPList',
				'serialized_php_gz' => 'processSerializedPhpGzip'
			] as $type => $processor) {
				foreach ($config->get("url_policies.sources.${key}.${type}", []) as $entry) {
					$output->writeln("Download: ${entry}");
					$data = $client->get($entry)->getBody();

					$output->writeln("Process : ${entry}");
					$this->$processor($data, $domains, $value);
				}
			}
		}

		$filterRegex = $config->get('url_policies.allow', null);

		if ($filterRegex !== null) {
			$filterRegex = '/'.str_replace('/', '\/', $filterRegex).'/i';
		}

		$output->writeln('Beginning transaction...');

		DB::transaction(function() use ($output, $domains, $filterRegex) {
			$output->writeln('Removing previous automatic rules...');
			DomainPolicy::where('automated', true)->delete();

			$output->writeln('Adding new rules...');
			foreach ($domains as $domain => $policy) {
				$count = DomainPolicy::where('domain', $domain)->count();

				if ($count === 0 && ($filterRegex === null || !preg_match($filterRegex, $domain))) {
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
