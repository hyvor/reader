<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250716050115 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create collection_publications table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE collection_publications (
                id BIGSERIAL NOT NULL PRIMARY KEY,
                collection_id BIGINT NOT NULL REFERENCES collections(id) ON DELETE CASCADE,
                publication_id BIGINT NOT NULL REFERENCES publications(id) ON DELETE CASCADE,
                created_at timestamptz DEFAULT CURRENT_TIMESTAMP NOT NULL,
                updated_at timestamptz DEFAULT CURRENT_TIMESTAMP NOT NULL,
                UNIQUE (collection_id, publication_id)
            )
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE collection_publications');
    }
} 