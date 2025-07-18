<?php 

namespace App\Command\Dev;

use App\Factory\CollectionFactory;
use App\Factory\ItemFactory;
use App\Factory\PublicationFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @codeCoverageIgnore
 */
#[AsCommand(
    name: 'app:dev:seed',
    description: 'Seeds the database with test data for development purposes.'
)]
class DevSeedCommand extends Command
{
    public function __construct(
        private KernelInterface $kernel,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $env = $this->kernel->getEnvironment();
        if ($env !== 'dev' && $env !== 'test') {
            $output->writeln('<error>This command can only be run in the dev and test environments.</error>');
            return Command::FAILURE;
        }

        $collection = CollectionFactory::createOne();

        $publications = PublicationFactory::createMany(2, [
            'collection' => $collection
        ]);

        ItemFactory::createMany(5, function() {
            return [
                'publication' => PublicationFactory::random()
            ];
        });

        $output->writeln('<info>Database seeded with test data.</info>');

        return Command::SUCCESS;
    }
}
