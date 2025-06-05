<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250603100040 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add foreign key relationships between collections, publications, and items';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE publications ADD collection_id INT NOT NULL');
        $this->addSql('ALTER TABLE publications ADD CONSTRAINT FK_32783AF4514956FD FOREIGN KEY (collection_id) REFERENCES collections (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_32783AF4514956FD ON publications (collection_id)');
        
        $this->addSql('ALTER TABLE items ADD publication_id INT NOT NULL');
        $this->addSql('ALTER TABLE items ADD CONSTRAINT FK_E11EE94D38B217A7 FOREIGN KEY (publication_id) REFERENCES publications (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_E11EE94D38B217A7 ON items (publication_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE items DROP CONSTRAINT IF EXISTS FK_E11EE94D38B217A7');
        $this->addSql('DROP INDEX IF EXISTS IDX_E11EE94D38B217A7');
        $this->addSql('ALTER TABLE items DROP COLUMN IF EXISTS publication_id');
        
        $this->addSql('ALTER TABLE publications DROP CONSTRAINT IF EXISTS FK_32783AF4514956FD');
        $this->addSql('DROP INDEX IF EXISTS IDX_32783AF4514956FD');
        $this->addSql('ALTER TABLE publications DROP COLUMN IF EXISTS collection_id');
    }
}
