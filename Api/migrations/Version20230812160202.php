<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230812160202 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE hunter_config_action (hunter_config_id INT NOT NULL, action_id INT NOT NULL, PRIMARY KEY(hunter_config_id, action_id))');
        $this->addSql('CREATE INDEX IDX_B09040DE324BCEC3 ON hunter_config_action (hunter_config_id)');
        $this->addSql('CREATE INDEX IDX_B09040DE9D32F035 ON hunter_config_action (action_id)');
        $this->addSql('ALTER TABLE hunter_config_action ADD CONSTRAINT FK_B09040DE324BCEC3 FOREIGN KEY (hunter_config_id) REFERENCES hunter_config (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE hunter_config_action ADD CONSTRAINT FK_B09040DE9D32F035 FOREIGN KEY (action_id) REFERENCES action (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE hunter_config_action DROP CONSTRAINT FK_B09040DE324BCEC3');
        $this->addSql('ALTER TABLE hunter_config_action DROP CONSTRAINT FK_B09040DE9D32F035');
        $this->addSql('DROP TABLE hunter_config_action');
    }
}
