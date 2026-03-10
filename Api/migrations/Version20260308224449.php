<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260308224449 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE skill_config_status_config (skill_config_id INT NOT NULL, status_config_id INT NOT NULL, PRIMARY KEY(skill_config_id, status_config_id))');
        $this->addSql('CREATE INDEX IDX_DE6FAD1F772A3DDE ON skill_config_status_config (skill_config_id)');
        $this->addSql('CREATE INDEX IDX_DE6FAD1FAC4E86C2 ON skill_config_status_config (status_config_id)');
        $this->addSql('ALTER TABLE skill_config_status_config ADD CONSTRAINT FK_DE6FAD1F772A3DDE FOREIGN KEY (skill_config_id) REFERENCES skill_config (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE skill_config_status_config ADD CONSTRAINT FK_DE6FAD1FAC4E86C2 FOREIGN KEY (status_config_id) REFERENCES status_config (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE skill_config_status_config DROP CONSTRAINT FK_DE6FAD1F772A3DDE');
        $this->addSql('ALTER TABLE skill_config_status_config DROP CONSTRAINT FK_DE6FAD1FAC4E86C2');
        $this->addSql('DROP TABLE skill_config_status_config');
    }
}
