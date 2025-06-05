<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250602113140 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add UUID fields to collections, publications, and items tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE collections ADD uuid UUID NULL');
        $this->addSql('ALTER TABLE publications ADD uuid UUID NULL');
        $this->addSql('ALTER TABLE items ADD uuid UUID NULL');
        
        $this->addSql("UPDATE collections SET uuid = gen_random_uuid()");
        $this->addSql("UPDATE publications SET uuid = gen_random_uuid()");
        $this->addSql("UPDATE items SET uuid = gen_random_uuid()");
        
        $this->addSql('ALTER TABLE collections ALTER COLUMN uuid SET NOT NULL');
        $this->addSql('ALTER TABLE publications ALTER COLUMN uuid SET NOT NULL');
        $this->addSql('ALTER TABLE items ALTER COLUMN uuid SET NOT NULL');
        
        $this->addSql('CREATE UNIQUE INDEX UNIQ_collections_uuid ON collections (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_publications_uuid ON publications (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_items_uuid ON items (uuid)');
        
        $this->addSql("COMMENT ON COLUMN collections.uuid IS '(DC2Type:uuid)'");
        $this->addSql("COMMENT ON COLUMN publications.uuid IS '(DC2Type:uuid)'");
        $this->addSql("COMMENT ON COLUMN items.uuid IS '(DC2Type:uuid)'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("COMMENT ON COLUMN collections.uuid IS NULL");
        $this->addSql("COMMENT ON COLUMN publications.uuid IS NULL");
        $this->addSql("COMMENT ON COLUMN items.uuid IS NULL");
        
        $this->addSql('DROP INDEX UNIQ_collections_uuid');
        $this->addSql('DROP INDEX UNIQ_publications_uuid');
        $this->addSql('DROP INDEX UNIQ_items_uuid');
        
        $this->addSql('ALTER TABLE collections DROP COLUMN uuid');
        $this->addSql('ALTER TABLE publications DROP COLUMN uuid');
        $this->addSql('ALTER TABLE items DROP COLUMN uuid');
    }
}
