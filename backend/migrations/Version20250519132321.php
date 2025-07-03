<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250519132321 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE collections (
                id BIGSERIAL NOT NULL PRIMARY KEY,
                name TEXT NOT NULL,
                uuid UUID NOT NULL DEFAULT gen_random_uuid() UNIQUE,
                slug TEXT NOT NULL UNIQUE,
                is_public BOOLEAN NOT NULL DEFAULT FALSE,
                owner_id INT NOT NULL
            )
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE collection_users (
                id BIGSERIAL NOT NULL PRIMARY KEY,
                collection_id BIGINT NOT NULL REFERENCES collections (id) ON DELETE CASCADE,
                user_id INT NOT NULL,
                access VARCHAR(5) NOT NULL CHECK (access IN ('read', 'write')),
                UNIQUE (collection_id, user_id)
            )
        SQL);

        $this->addSql('ALTER TABLE publications ADD CONSTRAINT FK_32783AF4514956FD FOREIGN KEY (collection_id) REFERENCES collections (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE publications DROP CONSTRAINT IF EXISTS FK_32783AF4514956FD');
        $this->addSql('DROP TABLE IF EXISTS collection_users');
        $this->addSql('DROP TABLE collections');
    }
}
