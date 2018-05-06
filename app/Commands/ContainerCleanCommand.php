<?php

namespace Elbo\Commands;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\{Command\Command, Input\InputInterface, Output\OutputInterface};

class ContainerCleanCommand extends Command {
	protected function configure() {
		$this->setName('clean:container')
		     ->setDescription('Remove compiled container file')
		     ->setHelp('Remove compiled container file built in production mode');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$fs = new Filesystem();
		$path = 'data/cache/CompiledContainer.php';

		if ($fs->exists($path)) {
			$fs->remove($path);
		}

		$output->writeln('Compiled container removed.');
	}
}
