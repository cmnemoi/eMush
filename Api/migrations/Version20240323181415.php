<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240323181415 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE moderationAction_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE moderationAction (id INT NOT NULL, user_id INT DEFAULT NULL, moderation_action VARCHAR(255) NOT NULL, reason VARCHAR(255) NOT NULL, message VARCHAR(255) NOT NULL, is_visible_by_user BOOLEAN DEFAULT false NOT NULL, start_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, end_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_390282ACA76ED395 ON moderationAction (user_id)');
        $this->addSql('ALTER TABLE moderationAction ADD CONSTRAINT FK_390282ACA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE users DROP banned');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE moderationAction_id_seq CASCADE');
        $this->addSql('ALTER TABLE moderationAction DROP CONSTRAINT FK_390282ACA76ED395');
        $this->addSql('DROP TABLE moderationAction');
        $this->addSql('ALTER TABLE users ADD banned BOOLEAN DEFAULT false NOT NULL');
    }
}
