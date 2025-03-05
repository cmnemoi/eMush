<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250305211253 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE rebel_base_config_status_config (rebel_base_config_id INT NOT NULL, status_config_id INT NOT NULL, PRIMARY KEY(rebel_base_config_id, status_config_id))');
        $this->addSql('CREATE INDEX IDX_22DC301438B52EC7 ON rebel_base_config_status_config (rebel_base_config_id)');
        $this->addSql('CREATE INDEX IDX_22DC3014AC4E86C2 ON rebel_base_config_status_config (status_config_id)');
        $this->addSql('ALTER TABLE rebel_base_config_status_config ADD CONSTRAINT FK_22DC301438B52EC7 FOREIGN KEY (rebel_base_config_id) REFERENCES rebel_base_config (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE rebel_base_config_status_config ADD CONSTRAINT FK_22DC3014AC4E86C2 FOREIGN KEY (status_config_id) REFERENCES status_config (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE rebel_base_config_status_config DROP CONSTRAINT FK_22DC301438B52EC7');
        $this->addSql('ALTER TABLE rebel_base_config_status_config DROP CONSTRAINT FK_22DC3014AC4E86C2');
        $this->addSql('DROP TABLE rebel_base_config_status_config');
    }
}
