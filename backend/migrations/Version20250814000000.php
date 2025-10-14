<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250814000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create OIDC tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE oidc_users (
                id SERIAL PRIMARY KEY,
                created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
                updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
                iss TEXT NOT NULL,
                sub TEXT NOT NULL,
                email TEXT NOT NULL,
                name TEXT NOT NULL,
                picture_url TEXT,
                website_url TEXT,
                UNIQUE (iss, sub)
            )
        SQL);

        $this->addSql('CREATE INDEX idx_oidc_users_email ON oidc_users (email)');

        $this->addSql(<<<'SQL'
            CREATE TABLE oidc_sessions (
                sess_id VARCHAR(128) NOT NULL PRIMARY KEY,
                sess_data BYTEA NOT NULL,
                sess_lifetime INTEGER NOT NULL,
                sess_time INTEGER NOT NULL
            )
        SQL);

        $this->addSql('CREATE INDEX idx_oidc_sessions_sess_lifetime ON oidc_sessions (sess_lifetime)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS oidc_sessions');
        $this->addSql('DROP INDEX IF EXISTS idx_oidc_sessions_sess_lifetime');
        $this->addSql('DROP TABLE IF EXISTS oidc_users');
        $this->addSql('DROP INDEX IF EXISTS idx_oidc_users_email');
    }
}


