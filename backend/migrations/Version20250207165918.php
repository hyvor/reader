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
        $this->addSql('
            CREATE TABLE feed (
                id bigserial NOT NULL PRIMARY KEY, 
                created_at timestamp DEFAULT CURRENT_TIMESTAMP NOT NULL, 
                updated_at timestamp DEFAULT CURRENT_TIMESTAMP NOT NULL, 
                url text NOT NULL UNIQUE, 
                title text DEFAULT NULL, 
                description text DEFAULT NULL, 
                interval INT DEFAULT 60 NOT NULL,
                last_fetched_at timestamp DEFAULT NULL, 
                next_fetch_at timestamp NOT NULL, 
                subscribers INT DEFAULT 0 NOT NULL,
                conditional_get_last_modified text DEFAULT NULL, 
                conditional_get_etag text DEFAULT NULL
          )
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE feed');
    }
}
