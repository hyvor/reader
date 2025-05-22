<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250519113000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE item (
                id BIGSERIAL NOT NULL PRIMARY KEY,
                feed_id INT NOT NULL REFERENCES feed (id),
                url TEXT NOT NULL,
                title TEXT NOT NULL,
                content_html TEXT DEFAULT NULL,
                content_text TEXT DEFAULT NULL,
                summary TEXT DEFAULT NULL,
                image TEXT DEFAULT NULL,
                published_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                authors TEXT NOT NULL,
                tags TEXT NOT NULL,
                language TEXT DEFAULT NULL,
            )
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            DROP TABLE item
        SQL);
    }
}
