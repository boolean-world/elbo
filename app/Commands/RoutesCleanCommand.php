<?php

namespace Elbo\Commands;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\{Command\Command, Input\InputInterface, Output\OutputInterface};

class RoutesCleanCommand extends Command {
	protected function configure() {
		$this->setName('clean:routes')
		     ->setDescription('Remove compiled routes file')
		     ->setHelp('Remove compiled routes file (that is built in production mode)');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$fs = new Filesystem();
		$path = 'data/cache/routes';

		if ($fs->exists($path)) {
			$fs->remove($path);
		}

		$output->writeln('Compiled routes file removed.');
	}
}
