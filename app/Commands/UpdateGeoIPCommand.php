<?php

namespace Elbo\Commands;

use GuzzleHttp\Client;
use Symfony\Component\FileSystem\FileSystem;
use Symfony\Component\Console\{Command\Command, Input\InputInterface, Output\OutputInterface};

class UpdateGeoIPCommand extends Command {
	const maxmind_geoipdb_url = 'http://geolite.maxmind.com/download/geoip/database/GeoLite2-Country.mmdb.gz';

	protected function configure() {
		$this->setName('update:geoip')
		     ->setDescription('Update MaxMind GeoIP database')
		     ->setHelp('Update MaxMind GeoIP database');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$output->writeln('Starting GeoIP database update ('.strftime('%Y-%m-%d %H:%M:%S').')');

		$client = new Client();
		$fs = new FileSystem();

		$db_gzip = $client->get(self::maxmind_geoipdb_url)->getBody();
		$fs->dumpFile('data/GeoLite2-Country.mmdb', gzdecode($db_gzip));

		$output->writeln('GeoIP database updated successfully.');
	}
}
