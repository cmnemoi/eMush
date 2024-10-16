<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241013193113 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE project_config_project_requirement (project_config_id INT NOT NULL, project_requirement_id INT NOT NULL, PRIMARY KEY(project_config_id, project_requirement_id))');
        $this->addSql('CREATE INDEX IDX_DFDF8B1B35E74354 ON project_config_project_requirement (project_config_id)');
        $this->addSql('CREATE INDEX IDX_DFDF8B1B92E05BD5 ON project_config_project_requirement (project_requirement_id)');
        $this->addSql('ALTER TABLE project_config_project_requirement ADD CONSTRAINT FK_DFDF8B1B35E74354 FOREIGN KEY (project_config_id) REFERENCES project_config (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE project_config_project_requirement ADD CONSTRAINT FK_DFDF8B1B92E05BD5 FOREIGN KEY (project_requirement_id) REFERENCES project_requirement (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE project_config_project_requirement DROP CONSTRAINT FK_DFDF8B1B35E74354');
        $this->addSql('ALTER TABLE project_config_project_requirement DROP CONSTRAINT FK_DFDF8B1B92E05BD5');
        $this->addSql('DROP TABLE project_config_project_requirement');
    }
}
