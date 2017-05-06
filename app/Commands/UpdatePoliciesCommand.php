<?php

namespace Elbo\Commands;

use GuzzleHttp\Client;
use Elbo\Models\DomainPolicy;
use Illuminate\Database\Capsule\Manager as DB;
use Symfony\Component\Console\{Command\Command, Input\InputInterface, Output\OutputInterface};

class UpdatePoliciesCommand extends Command {
	const domain_lists = [
		DomainPolicy::POLICY_BLOCKED_MALWARE => [
			'https://s3.amazonaws.com/lists.disconnect.me/simple_malware.txt',
			'http://mirror2.malwaredomains.com/files/justdomains',
			'http://mirror2.malwaredomains.com/files/immortal_domains.txt',
			'https://ransomwaretracker.abuse.ch/downloads/RW_DOMBL.txt',
			'http://www.networksec.org/grabbho/block.txt',
			'https://www.threatcrowd.org/feeds/domains.txt',
			'https://zeustracker.abuse.ch/blocklist.php?download=domainblocklist'
		],
		DomainPolicy::POLICY_BLOCKED_REDIRECTOR => [
			'https://raw.githubusercontent.com/boolean-world/elbo/master/misc/blacklists/redirectors.txt'
		]
	];

	const url_lists = [
		DomainPolicy::POLICY_BLOCKED_MALWARE => [
			'https://cybercrime-tracker.net/all.php',
			'http://vxvault.net/URL_List.php'
		],
		DomainPolicy::POLICY_BLOCKED_PHISHING => [
			'https://openphish.com/feed.txt'
		]
	];

	const hosts_files = [
		DomainPolicy::POLICY_BLOCKED_SPAM => [
			'https://hosts-file.net/pha.txt'
		],
		DomainPolicy::POLICY_BLOCKED_MALWARE => [
			'https://www.malwaredomainlist.com/hostslist/hosts.txt',
			'https://hosts-file.net/pup.txt'
		]
	];

	const ip_lists = [
		DomainPolicy::POLICY_BLOCKED_MALWARE => [
			'http://rules.emergingthreats.net/blockrules/compromised-ips.txt',
			'https://zeustracker.abuse.ch/blocklist.php?download=badips',
			'https://ransomwaretracker.abuse.ch/downloads/RW_IPBL.txt',
			'https://www.threatcrowd.org/feeds/ips.txt'
		]
	];

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
				# Exclude test domains.
				if (!preg_match('/\.disconnect\.me$/', $line)) {
					$rule = preg_replace('/^www\.(.{4,}\..{2,})/', '\1', strtolower($line));
					$array[$rule] = $value;
				}
			}

			$line = strtok("\r\n");
		}
	}

	protected function processHostsFile(string $text, array &$array, int $value) {
		$line = strtok($text, "\r\n");

		while ($line !== false) {
			$line = preg_replace('/^\s*[0-9:.]+\s*|\s*#.*$/', '', $line);

			if (preg_match(self::domain_regex, $line)) {
				$rule = preg_replace('/^www\.(.{4,}\..{2,})/', '\1', strtolower($line));
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

	protected function execute(InputInterface $input, OutputInterface $output) {
		$client = new Client();
		$domains = [];

		$output->writeln('Starting policy update ('.strftime('%Y-%m-%d %H:%M:%S').')');

		foreach (self::domain_lists as $k => $v) {
			foreach ($v as $v1) {
				$output->writeln("Downloading and processing $v1...");
				$this->processDomainList($client->get($v1)->getBody(), $domains, $k, $v1);
			}
		}

		foreach (self::hosts_files as $k => $v) {
			foreach ($v as $v1) {
				$output->writeln("Downloading and processing $v1...");
				$this->processHostsFile($client->get($v1)->getBody(), $domains, $k, $v1);
			}
		}

		foreach (self::url_lists as $k => $v) {
			foreach ($v as $v1) {
				$output->writeln("Downloading and processing $v1...");
				$this->processURLList($client->get($v1)->getBody(), $domains, $k, $v1);
			}
		}

		foreach (self::ip_lists as $k => $v) {
			foreach ($v as $v1) {
				$output->writeln("Downloading and processing $v1...");
				$this->processIPList($client->get($v1)->getBody(), $domains, $k, $v1);
			}
		}

		$output->writeln('Beginning transaction...');

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
