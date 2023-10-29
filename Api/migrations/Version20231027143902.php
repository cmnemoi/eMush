<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231027143902 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE legacy_user_history_heroes_id_seq CASCADE');
        $this->addSql('CREATE SEQUENCE admin_secret_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE admin_secret (id INT NOT NULL, name VARCHAR(255) NOT NULL, value VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('DROP TABLE legacy_user_history_heroes');
        $this->addSql('ALTER TABLE legacy_user ADD history_heroes VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE legacy_user ADD history_ships VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE legacy_user_twinoid_profile ADD stats TEXT NOT NULL');
        $this->addSql('COMMENT ON COLUMN legacy_user_twinoid_profile.stats IS \'(DC2Type:array)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE admin_secret_id_seq CASCADE');
        $this->addSql('CREATE SEQUENCE legacy_user_history_heroes_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE legacy_user_history_heroes (id INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('DROP TABLE admin_secret');
        $this->addSql('ALTER TABLE legacy_user DROP history_heroes');
        $this->addSql('ALTER TABLE legacy_user DROP history_ships');
        $this->addSql('ALTER TABLE legacy_user_twinoid_profile DROP stats');
    }
}
