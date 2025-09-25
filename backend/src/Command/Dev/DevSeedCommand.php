<?php 

namespace App\Command\Dev;

use App\Factory\CollectionFactory;
use App\Factory\ItemFactory;
use App\Factory\PublicationFactory;
use App\Entity\CollectionUser;
use App\InternalFake;
use Doctrine\ORM\EntityManagerInterface;
use Hyvor\Internal\Auth\AuthFake;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use App\Service\Collection\CollectionService;

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
        private EntityManagerInterface $em,
        private CollectionService $collectionService,
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

        $this->collectionService->ensureUserHasDefaultCollection(AuthFake::generateUser());

        $createdCollections = CollectionFactory::createMany(3, function () {
            $publications = PublicationFactory::createMany(rand(2, 5));
            foreach ($publications as $publication) {
                ItemFactory::createMany(5, [
                    'publication' => $publication,
                ]);
            }

            return [
                'publications' => $publications,
            ];
        });

        foreach ($createdCollections as $collectionProxy) {
            /** @var \App\Entity\Collection $collection */
            $collection = $collectionProxy->_real(false);

            $cu = new CollectionUser();
            $cu->setCollection($collection);
            $cu->setHyvorUserId($collection->getHyvorUserId());
            $cu->setWriteAccess(true);

            $this->em->persist($cu);
        }

        $this->em->flush();

        $output->writeln('<info>Database seeded with test data.</info>');

        return Command::SUCCESS;
    }
}
