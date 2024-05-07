<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240507062323 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE project_config_abstract_event_config (project_config_id INT NOT NULL, abstract_event_config_id INT NOT NULL, PRIMARY KEY(project_config_id, abstract_event_config_id))');
        $this->addSql('CREATE INDEX IDX_D30F1BE435E74354 ON project_config_abstract_event_config (project_config_id)');
        $this->addSql('CREATE INDEX IDX_D30F1BE468801AAB ON project_config_abstract_event_config (abstract_event_config_id)');
        $this->addSql('ALTER TABLE project_config_abstract_event_config ADD CONSTRAINT FK_D30F1BE435E74354 FOREIGN KEY (project_config_id) REFERENCES project_config (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE project_config_abstract_event_config ADD CONSTRAINT FK_D30F1BE468801AAB FOREIGN KEY (abstract_event_config_id) REFERENCES event_config (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event_config ADD equipment_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE event_config ADD room_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE event_config ADD replaced_equipment VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE project_config_abstract_event_config DROP CONSTRAINT FK_D30F1BE435E74354');
        $this->addSql('ALTER TABLE project_config_abstract_event_config DROP CONSTRAINT FK_D30F1BE468801AAB');
        $this->addSql('DROP TABLE project_config_abstract_event_config');
        $this->addSql('ALTER TABLE event_config DROP equipment_name');
        $this->addSql('ALTER TABLE event_config DROP room_name');
        $this->addSql('ALTER TABLE event_config DROP replaced_equipment');
    }
}
