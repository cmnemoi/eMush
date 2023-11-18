<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231022155002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE closed_exploration_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE closed_exploration (id INT NOT NULL, daedalus_info_id INT DEFAULT NULL, planet_name TEXT NOT NULL, explorator_names TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_637290E640AC2F6A ON closed_exploration (daedalus_info_id)');
        $this->addSql('COMMENT ON COLUMN closed_exploration.planet_name IS \'(DC2Type:array)\'');
        $this->addSql('COMMENT ON COLUMN closed_exploration.explorator_names IS \'(DC2Type:array)\'');
        $this->addSql('ALTER TABLE closed_exploration ADD CONSTRAINT FK_637290E640AC2F6A FOREIGN KEY (daedalus_info_id) REFERENCES daedalus_info (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE exploration ADD closed_exploration_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE exploration ADD CONSTRAINT FK_AC0F0AB3464F34B FOREIGN KEY (closed_exploration_id) REFERENCES closed_exploration (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_AC0F0AB3464F34B ON exploration (closed_exploration_id)');
        $this->addSql('ALTER TABLE exploration_log DROP CONSTRAINT fk_98fce691cb0970ce');
        $this->addSql('DROP INDEX idx_98fce691cb0970ce');
        $this->addSql('ALTER TABLE exploration_log RENAME COLUMN exploration_id TO closed_exploration_id');
        $this->addSql('ALTER TABLE exploration_log ADD CONSTRAINT FK_98FCE691464F34B FOREIGN KEY (closed_exploration_id) REFERENCES closed_exploration (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_98FCE691464F34B ON exploration_log (closed_exploration_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE exploration DROP CONSTRAINT FK_AC0F0AB3464F34B');
        $this->addSql('ALTER TABLE exploration_log DROP CONSTRAINT FK_98FCE691464F34B');
        $this->addSql('DROP SEQUENCE closed_exploration_id_seq CASCADE');
        $this->addSql('ALTER TABLE closed_exploration DROP CONSTRAINT FK_637290E640AC2F6A');
        $this->addSql('DROP TABLE closed_exploration');
        $this->addSql('DROP INDEX IDX_98FCE691464F34B');
        $this->addSql('ALTER TABLE exploration_log RENAME COLUMN closed_exploration_id TO exploration_id');
        $this->addSql('ALTER TABLE exploration_log ADD CONSTRAINT fk_98fce691cb0970ce FOREIGN KEY (exploration_id) REFERENCES exploration (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_98fce691cb0970ce ON exploration_log (exploration_id)');
        $this->addSql('DROP INDEX UNIQ_AC0F0AB3464F34B');
        $this->addSql('ALTER TABLE exploration DROP closed_exploration_id');
    }
}
