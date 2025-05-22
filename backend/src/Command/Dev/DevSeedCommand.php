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

        $items = ItemFactory::createMany(5, [
            ['url' => 'https://example.com/article1',
             'title' => 'Understanding Modern Web Development',
             'content_html' => '<p>A deep dive into modern web development practices and tools.</p>',
             'content_text' => 'A deep dive into modern web development practices and tools.',
             'summary' => 'Learn about the latest web development trends',
             'image' => 'https://picsum.photos/id/237/800/400',
             'published_at' => new \DateTimeImmutable('2024-03-15'),
             'authors' => ['John Doe'],
             'tags' => ['web development', 'programming'],
             'language' => 'en'],
            ['url' => 'https://example.com/article2',
             'title' => 'The Future of AI in Software',
             'content_html' => '<p>Exploring how AI is transforming software development.</p>',
             'content_text' => 'Exploring how AI is transforming software development.',
             'summary' => 'AI\'s impact on software development',
             'image' => 'https://picsum.photos/id/24/800/400',
             'published_at' => new \DateTimeImmutable('2024-03-14'),
             'authors' => ['Jane Smith'],
             'tags' => ['AI', 'software development'],
             'language' => 'en'],
            ['url' => 'https://example.com/article3',
             'title' => 'Getting Started with Docker',
             'content_html' => '<p>A beginner\'s guide to containerization with Docker.</p>',
             'content_text' => 'A beginner\'s guide to containerization with Docker.',
             'summary' => 'Learn Docker basics',
             'image' => 'https://picsum.photos/id/180/800/400',
             'published_at' => new \DateTimeImmutable('2024-03-13'),
             'authors' => ['Mike Johnson'],
             'tags' => ['docker', 'devops'],
             'language' => 'en'],
            ['url' => 'https://example.com/article4',
             'title' => 'Python Best Practices 2024',
             'content_html' => '<p>Updated best practices for Python development in 2024.</p>',
             'content_text' => 'Updated best practices for Python development in 2024.',
             'summary' => 'Modern Python development guidelines',
             'image' => 'https://picsum.photos/id/96/800/400',
             'published_at' => new \DateTimeImmutable('2024-03-12'),
             'authors' => ['Sarah Wilson'],
             'tags' => ['python', 'programming'],
             'language' => 'en'],
            ['url' => 'https://example.com/article5',
             'title' => 'Introduction to GraphQL',
             'content_html' => '<p>Understanding GraphQL and its advantages over REST.</p>',
             'content_text' => 'Understanding GraphQL and its advantages over REST.',
             'summary' => 'GraphQL basics and benefits',
             'image' => 'https://picsum.photos/id/42/800/400',
             'published_at' => new \DateTimeImmutable('2024-03-11'),
             'authors' => ['Alex Brown'],
             'tags' => ['graphql', 'api'],
             'language' => 'en']
        ]);

        $publications = PublicationFactory::createMany(2, [
            ['url' => 'https://techblog.example.com',
             'title' => 'Tech Blog Daily',
             'description' => 'Daily updates on technology and programming',
             'interval' => 60,
             'createdAt' => new \DateTime('2024-01-01'),
             'updatedAt' => new \DateTime('2024-03-15'),
             'lastFetchedAt' => new \DateTime('2024-03-15'),
             'nextFetchAt' => new \DateTime('2024-03-15 01:00:00'),
             'subscribers' => 1000],
            ['url' => 'https://devnews.example.com',
             'title' => 'Developer News Network',
             'description' => 'Latest news in software development',
             'interval' => 120,
             'createdAt' => new \DateTime('2024-02-01'),
             'updatedAt' => new \DateTime('2024-03-15'),
             'lastFetchedAt' => new \DateTime('2024-03-15'),
             'nextFetchAt' => new \DateTime('2024-03-15 02:00:00'),
             'subscribers' => 500]
        ]);

        $collection = CollectionFactory::createOne([
            'name' => 'Tech Articles 2024'
        ]);

        $output->writeln('<info>Database seeded with test data.</info>');

        return Command::SUCCESS;
    }
}
