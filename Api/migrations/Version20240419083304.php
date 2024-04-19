<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240419083304 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE project_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE project_config_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE project (id INT NOT NULL, config_id INT DEFAULT NULL, daedalus_id INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_2FB3D0EE24DB0683 ON project (config_id)');
        $this->addSql('CREATE INDEX IDX_2FB3D0EE74B5A52D ON project (daedalus_id)');
        $this->addSql('CREATE TABLE project_config (id INT NOT NULL, name VARCHAR(255) DEFAULT \'\' NOT NULL, type VARCHAR(255) DEFAULT \'\' NOT NULL, efficiency INT DEFAULT 0 NOT NULL, bonus_skills TEXT DEFAULT \'a:0:{}\' NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN project_config.bonus_skills IS \'(DC2Type:array)\'');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EE24DB0683 FOREIGN KEY (config_id) REFERENCES project_config (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EE74B5A52D FOREIGN KEY (daedalus_id) REFERENCES daedalus (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE project_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE project_config_id_seq CASCADE');
        $this->addSql('ALTER TABLE project DROP CONSTRAINT FK_2FB3D0EE24DB0683');
        $this->addSql('ALTER TABLE project DROP CONSTRAINT FK_2FB3D0EE74B5A52D');
        $this->addSql('DROP TABLE project');
        $this->addSql('DROP TABLE project_config');
    }
}
