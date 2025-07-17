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
                is_fetching boolean DEFAULT false NOT NULL
            )
        SQL);


        $this->addSql("CREATE TYPE publication_status AS ENUM ('pending', 'completed', 'failed')");

        $this->addSql(<<<SQL
            CREATE TABLE publication_fetches (
                id bigserial NOT NULL PRIMARY KEY,
                uuid UUID NOT NULL DEFAULT gen_random_uuid() UNIQUE,
                created_at timestamptz DEFAULT CURRENT_TIMESTAMP NOT NULL,
                updated_at timestamptz DEFAULT CURRENT_TIMESTAMP NOT NULL,
                publication_id bigint NOT NULL REFERENCES publications(id) ON DELETE CASCADE,
                status publication_status NOT NULL,
                status_code integer NULL,
                error text NULL,
                error_private text NULL,
                new_items_count integer DEFAULT 0 NOT NULL,
                updated_items_count integer DEFAULT 0 NOT NULL,
                latency_ms integer NULL
            )
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS publication_fetches');
        $this->addSql('DROP TYPE IF EXISTS publication_status');
        $this->addSql('DROP TABLE IF EXISTS publications');
    }
}
