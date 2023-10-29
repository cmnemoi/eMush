<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231027103107 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE legacy_user_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE legacy_user_history_heroes_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE legacy_user_twinoid_profile_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE legacy_user (id INT NOT NULL, twinoid_profile_id INT DEFAULT NULL, character_levels TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A2236D5116C70FC6 ON legacy_user (twinoid_profile_id)');
        $this->addSql('COMMENT ON COLUMN legacy_user.character_levels IS \'(DC2Type:array)\'');
        $this->addSql('CREATE TABLE legacy_user_history_heroes (id INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE legacy_user_twinoid_profile (id INT NOT NULL, legacy_user_id INT DEFAULT NULL, twinoid_id INT NOT NULL, twinoid_username VARCHAR(255) NOT NULL, achievements TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_84AFCCA6790BE58D ON legacy_user_twinoid_profile (legacy_user_id)');
        $this->addSql('COMMENT ON COLUMN legacy_user_twinoid_profile.achievements IS \'(DC2Type:array)\'');
        $this->addSql('ALTER TABLE legacy_user ADD CONSTRAINT FK_A2236D5116C70FC6 FOREIGN KEY (twinoid_profile_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE legacy_user_twinoid_profile ADD CONSTRAINT FK_84AFCCA6790BE58D FOREIGN KEY (legacy_user_id) REFERENCES legacy_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE users ADD legacy_user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E9790BE58D FOREIGN KEY (legacy_user_id) REFERENCES legacy_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9790BE58D ON users (legacy_user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE users DROP CONSTRAINT FK_1483A5E9790BE58D');
        $this->addSql('DROP SEQUENCE legacy_user_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE legacy_user_history_heroes_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE legacy_user_twinoid_profile_id_seq CASCADE');
        $this->addSql('ALTER TABLE legacy_user DROP CONSTRAINT FK_A2236D5116C70FC6');
        $this->addSql('ALTER TABLE legacy_user_twinoid_profile DROP CONSTRAINT FK_84AFCCA6790BE58D');
        $this->addSql('DROP TABLE legacy_user');
        $this->addSql('DROP TABLE legacy_user_history_heroes');
        $this->addSql('DROP TABLE legacy_user_twinoid_profile');
        $this->addSql('DROP INDEX UNIQ_1483A5E9790BE58D');
        $this->addSql('ALTER TABLE users DROP legacy_user_id');
    }
}
