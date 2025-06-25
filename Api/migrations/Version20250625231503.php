<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250625231503 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE player DROP CONSTRAINT fk_98197a651486c35f');
        $this->addSql('DROP INDEX uniq_98197a651486c35f');
        $this->addSql('ALTER TABLE player DROP available_skills_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE player ADD available_skills_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE player ADD CONSTRAINT fk_98197a651486c35f FOREIGN KEY (available_skills_id) REFERENCES skill_config (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_98197a651486c35f ON player (available_skills_id)');
    }
}
