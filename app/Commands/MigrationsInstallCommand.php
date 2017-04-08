<?php

namespace Elbo\Commands;

use Elbo\Library\Configuration;
use Symfony\Component\Console\{Command\Command, Input\InputInterface, Output\OutputInterface, Helper\FormatterHelper};

class MigrationsInstallCommand extends Command {
	protected function configure() {
		$this->setName('migrations:install')
		     ->setDescription('Initialize the database with the necessary tables')
		     ->setHelp('Initialize the database with the necessary tables');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$migration_files = glob('app/Migrations/*');
		$count = count($migration_files);

		if ($count === 0) {
			$output->writeln("No migrations found.");
			return 0;
		}

		$config = new Configuration();

		if ($config->get('database.driver') === 'sqlite') {
			$database = $config->get('database.database', 'data/db.sqlite');

			if (!file_exists($database) && !@touch($database)) {
				throw new \RuntimeException("Failed to create database at $database");
			}
		}

		$output->writeln("Installing migrations...");

		foreach ($migration_files as $migration_file) {
			$migration_class = '\\Elbo\\Migrations\\'.preg_replace('#.*/(.*)\.php#', '$1', $migration_file);
			$migration = new $migration_class;
			$migration->create();
		}

		$output->writeln("All migrations were run successfully.");
	}
}
