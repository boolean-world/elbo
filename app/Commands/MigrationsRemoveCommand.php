<?php

namespace Elbo\Commands;

use Elbo\Library\ProgressBar;
use Symfony\Component\Console\{Command\Command, Input\InputInterface, Output\OutputInterface, Helper\FormatterHelper};

class MigrationsRemoveCommand extends Command {
	protected function configure() {
		$this->setName('migrations:remove')
		     ->setDescription('Remove tables from the database')
		     ->setHelp('Remove tables from the database');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$migration_files = glob('app/Migrations/*');
		$count = count($migration_files);

		if ($count === 0) {
			$output->writeln("No migrations found.");
			return 0;
		}

		$output->writeln("Removing migrations...");

		foreach ($migration_files as $migration_file) {
			$migration_class = '\\Elbo\\Migrations\\'.preg_replace('#.*/(.*)\.php#', '$1', $migration_file);
			$migration = new $migration_class;
			$migration->delete();
		}

		$output->writeln("All migrations were run successfully.");
	}
}
