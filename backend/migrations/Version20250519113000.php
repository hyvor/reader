<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250519113000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE feed (id SERIAL NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, url VARCHAR(255) NOT NULL, title VARCHAR(255) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, interval INT DEFAULT 60 NOT NULL, last_fetched_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, next_fetch_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, subscribers INT DEFAULT 0 NOT NULL, conditional_get_last_modified VARCHAR(255) DEFAULT NULL, conditional_get_etag VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_234044ABF47645AE ON feed (url)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE item (id SERIAL NOT NULL, url VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, content_html VARCHAR(10000) DEFAULT NULL, content_text VARCHAR(10000) DEFAULT NULL, summary VARCHAR(1000) DEFAULT NULL, image VARCHAR(255) DEFAULT NULL, published_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, authors TEXT NOT NULL, tags TEXT NOT NULL, language VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN item.published_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN item.updated_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN item.authors IS '(DC2Type:array)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN item.tags IS '(DC2Type:array)'
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE feed
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE item
        SQL);
    }
}
