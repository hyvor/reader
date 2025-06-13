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
            CREATE TABLE publications (
                id bigserial NOT NULL PRIMARY KEY, 
                created_at timestamptz DEFAULT CURRENT_TIMESTAMP NOT NULL, 
                updated_at timestamptz DEFAULT CURRENT_TIMESTAMP NOT NULL, 
                uuid UUID NOT NULL DEFAULT gen_random_uuid(),
                url text NOT NULL UNIQUE, 
                title text DEFAULT NULL, 
                description text DEFAULT NULL, 
                interval INT DEFAULT 60 NOT NULL,
                last_fetched_at timestamptz DEFAULT NULL, 
                next_fetch_at timestamptz NOT NULL, 
                subscribers INT DEFAULT 0 NOT NULL,
                conditional_get_last_modified text DEFAULT NULL, 
                conditional_get_etag text DEFAULT NULL,
                collection_id INT NOT NULL,
                CONSTRAINT UNIQ_publications_uuid UNIQUE (uuid)
          )
        ');

        $this->addSql('
            CREATE TABLE publication_fetches (
                id bigserial NOT NULL PRIMARY KEY,
                uuid UUID NOT NULL DEFAULT gen_random_uuid() UNIQUE,
                created_at timestamptz DEFAULT CURRENT_TIMESTAMP NOT NULL,
                updated_at timestamptz DEFAULT CURRENT_TIMESTAMP NOT NULL,
                publication_id bigint NOT NULL,
                status varchar(20) NOT NULL CHECK (status IN (\'pending\', \'completed\', \'failed\')),
                status_code integer NULL,
                error varchar(255) NULL,
                error_private text NULL,
                new_items_count integer DEFAULT 0 NOT NULL,
                updated_items_count integer DEFAULT 0 NOT NULL,
                latency_ms integer NULL,
                FOREIGN KEY (publication_id) REFERENCES publications(id) ON DELETE CASCADE,
                CONSTRAINT UNIQ_publication_fetches_uuid UNIQUE (uuid)
            )
        ');

        $this->addSql('CREATE INDEX idx_publication_fetches_publication_id ON publication_fetches(publication_id)');
        $this->addSql('CREATE INDEX idx_publication_fetches_created_at ON publication_fetches(created_at)');
        $this->addSql('CREATE INDEX idx_publication_fetches_status ON publication_fetches(status)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE publication_fetches');
        $this->addSql('DROP TABLE publications');
    }
}
