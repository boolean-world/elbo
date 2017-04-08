<?php

namespace Elbo\Commands;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\{Command\Command, Input\InputInterface, Output\OutputInterface};

class TemplatesCleanCommand extends Command {
	protected function configure() {
		$this->setName('clean:templates')
		     ->setDescription('Remove compiled template files')
		     ->setHelp('Remove compiled template files (that are built in production mode)');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$fs = new Filesystem();
		$path = 'data/cache/twig';

		if ($fs->exists($path)) {
			$fs->remove(glob("$path/*"));
		}

		$output->writeln('Compiled template files removed.');
	}
}
