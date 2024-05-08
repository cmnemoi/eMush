<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240507074756 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE spawn_equipment_config_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE project_config_spawn_equipment_config (project_config_id INT NOT NULL, spawn_equipment_config_id INT NOT NULL, PRIMARY KEY(project_config_id, spawn_equipment_config_id))');
        $this->addSql('CREATE INDEX IDX_ED2C2E8435E74354 ON project_config_spawn_equipment_config (project_config_id)');
        $this->addSql('CREATE INDEX IDX_ED2C2E84F5D9D02 ON project_config_spawn_equipment_config (spawn_equipment_config_id)');
        $this->addSql('CREATE TABLE spawn_equipment_config (id INT NOT NULL, name VARCHAR(255) NOT NULL, equipment_name VARCHAR(255) NOT NULL, place_name VARCHAR(255) NOT NULL, quantity INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE project_config_spawn_equipment_config ADD CONSTRAINT FK_ED2C2E8435E74354 FOREIGN KEY (project_config_id) REFERENCES project_config (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE project_config_spawn_equipment_config ADD CONSTRAINT FK_ED2C2E84F5D9D02 FOREIGN KEY (spawn_equipment_config_id) REFERENCES spawn_equipment_config (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE spawn_equipment_config_id_seq CASCADE');
        $this->addSql('ALTER TABLE project_config_spawn_equipment_config DROP CONSTRAINT FK_ED2C2E8435E74354');
        $this->addSql('ALTER TABLE project_config_spawn_equipment_config DROP CONSTRAINT FK_ED2C2E84F5D9D02');
        $this->addSql('DROP TABLE project_config_spawn_equipment_config');
        $this->addSql('DROP TABLE spawn_equipment_config');
    }
}
