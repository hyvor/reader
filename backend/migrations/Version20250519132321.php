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
                uuid UUID NOT NULL DEFAULT gen_random_uuid(),
                CONSTRAINT UNIQ_collections_uuid UNIQUE (uuid)
            )
        SQL);
        $this->addSql('ALTER TABLE publications ADD CONSTRAINT FK_32783AF4514956FD FOREIGN KEY (collection_id) REFERENCES collections (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_32783AF4514956FD ON publications (collection_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE publications DROP CONSTRAINT IF EXISTS FK_32783AF4514956FD');
        $this->addSql('DROP INDEX IF EXISTS IDX_32783AF4514956FD');
        $this->addSql(<<<'SQL'
            DROP TABLE collections
        SQL);
    }
}
