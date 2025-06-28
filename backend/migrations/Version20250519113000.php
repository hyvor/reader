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
            CREATE TABLE items (
                id BIGSERIAL NOT NULL PRIMARY KEY,
                guid TEXT NOT NULL,
                url TEXT NOT NULL,
                title TEXT NOT NULL,
                content_html TEXT DEFAULT NULL,
                content_text TEXT DEFAULT NULL,
                summary TEXT DEFAULT NULL,
                image TEXT DEFAULT NULL,
                published_at TIMESTAMPTZ DEFAULT NULL,
                updated_at TIMESTAMPTZ DEFAULT NULL,
                authors TEXT NOT NULL,
                tags TEXT NOT NULL,
                language TEXT DEFAULT NULL,
                uuid UUID NOT NULL DEFAULT gen_random_uuid() UNIQUE,
                publication_id INT NOT NULL REFERENCES publications (id),
                CONSTRAINT UNIQ_items_publication_guid UNIQUE (publication_id, guid)
            )
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            DROP TABLE items
        SQL);
    }
}
