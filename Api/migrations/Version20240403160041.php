<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240403160041 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE moderationSanction_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE moderationSanction (id INT NOT NULL, user_id INT DEFAULT NULL, moderation_action VARCHAR(255) NOT NULL, reason VARCHAR(255) NOT NULL, message VARCHAR(255) DEFAULT NULL, is_visible_by_user BOOLEAN DEFAULT false NOT NULL, start_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, end_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_CB19D91BA76ED395 ON moderationSanction (user_id)');
        $this->addSql('ALTER TABLE moderationSanction ADD CONSTRAINT FK_CB19D91BA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE users DROP banned');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE moderationSanction_id_seq CASCADE');
        $this->addSql('ALTER TABLE moderationSanction DROP CONSTRAINT FK_CB19D91BA76ED395');
        $this->addSql('DROP TABLE moderationSanction');
        $this->addSql('ALTER TABLE users ADD banned BOOLEAN DEFAULT false NOT NULL');
    }
}
