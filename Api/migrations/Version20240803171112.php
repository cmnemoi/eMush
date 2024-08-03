<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240803171112 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE status_config_skill_config (status_config_id INT NOT NULL, skill_config_id INT NOT NULL, PRIMARY KEY(status_config_id, skill_config_id))');
        $this->addSql('CREATE INDEX IDX_E370A8CEAC4E86C2 ON status_config_skill_config (status_config_id)');
        $this->addSql('CREATE INDEX IDX_E370A8CE772A3DDE ON status_config_skill_config (skill_config_id)');
        $this->addSql('ALTER TABLE status_config_skill_config ADD CONSTRAINT FK_E370A8CEAC4E86C2 FOREIGN KEY (status_config_id) REFERENCES status_config (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE status_config_skill_config ADD CONSTRAINT FK_E370A8CE772A3DDE FOREIGN KEY (skill_config_id) REFERENCES skill_config (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER INDEX idx_fbc07b68d7e86f28 RENAME TO IDX_FBC07B68B9E73F9C');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE status_config_skill_config DROP CONSTRAINT FK_E370A8CEAC4E86C2');
        $this->addSql('ALTER TABLE status_config_skill_config DROP CONSTRAINT FK_E370A8CE772A3DDE');
        $this->addSql('DROP TABLE status_config_skill_config');
        $this->addSql('ALTER INDEX idx_fbc07b68b9e73f9c RENAME TO idx_fbc07b68d7e86f28');
    }
}
