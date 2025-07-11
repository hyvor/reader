<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250207165918 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE publications (
                id bigserial NOT NULL PRIMARY KEY, 
                created_at timestamptz DEFAULT CURRENT_TIMESTAMP NOT NULL, 
                updated_at timestamptz DEFAULT CURRENT_TIMESTAMP NOT NULL, 
                uuid UUID NOT NULL DEFAULT gen_random_uuid() UNIQUE,
                slug VARCHAR(255) NOT NULL UNIQUE,
                url text NOT NULL UNIQUE, 
                title text DEFAULT NULL, 
                description text DEFAULT NULL, 
                interval INT DEFAULT 60 NOT NULL,
                last_fetched_at timestamptz DEFAULT NULL, 
                next_fetch_at timestamptz NOT NULL, 
                subscribers INT DEFAULT 0 NOT NULL,
                conditional_get_last_modified text DEFAULT NULL, 
                conditional_get_etag text DEFAULT NULL,
                collection_id INT NOT NULL
            )
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE publications');
    }
}
