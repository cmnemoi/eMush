<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240427100337 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE project_config_abstract_modifier_config (project_config_id INT NOT NULL, abstract_modifier_config_id INT NOT NULL, PRIMARY KEY(project_config_id, abstract_modifier_config_id))');
        $this->addSql('CREATE INDEX IDX_46B18CEA35E74354 ON project_config_abstract_modifier_config (project_config_id)');
        $this->addSql('CREATE INDEX IDX_46B18CEABFA8DC8C ON project_config_abstract_modifier_config (abstract_modifier_config_id)');
        $this->addSql('ALTER TABLE project_config_abstract_modifier_config ADD CONSTRAINT FK_46B18CEA35E74354 FOREIGN KEY (project_config_id) REFERENCES project_config (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE project_config_abstract_modifier_config ADD CONSTRAINT FK_46B18CEABFA8DC8C FOREIGN KEY (abstract_modifier_config_id) REFERENCES abstract_modifier_config (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE project_config_abstract_modifier_config DROP CONSTRAINT FK_46B18CEA35E74354');
        $this->addSql('ALTER TABLE project_config_abstract_modifier_config DROP CONSTRAINT FK_46B18CEABFA8DC8C');
        $this->addSql('DROP TABLE project_config_abstract_modifier_config');
    }
}
