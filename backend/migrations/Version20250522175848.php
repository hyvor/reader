<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250522175848 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE feed ADD feed_list_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE feed ADD CONSTRAINT FK_234044AB87BC02FE FOREIGN KEY (feed_list_id) REFERENCES feed_list (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE item ADD feed_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE item ADD CONSTRAINT FK_1F1B251E51A5BC03 FOREIGN KEY (feed_id) REFERENCES feed (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE item DROP CONSTRAINT FK_1F1B251E51A5BC03
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE item DROP feed_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE feed DROP CONSTRAINT FK_234044AB87BC02FE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE feed DROP feed_list_id
        SQL);
    }
}
