<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251125132625 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE pending_statistic_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE pending_statistic (id INT NOT NULL, user_id INT NOT NULL, daedalus_info_id INT NOT NULL, config_id INT DEFAULT NULL, version INT DEFAULT 1 NOT NULL, count INT DEFAULT 0 NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_2F18511724DB0683 ON pending_statistic (config_id)');
        $this->addSql('CREATE INDEX IDX_2F185117A76ED395 ON pending_statistic (user_id)');
        $this->addSql('CREATE INDEX IDX_2F18511740AC2F6A ON pending_statistic (daedalus_info_id)');
        $this->addSql('CREATE UNIQUE INDEX unique_pending_statistic_per_user_and_daedalus ON pending_statistic (config_id, user_id, daedalus_info_id)');
        $this->addSql('ALTER TABLE pending_statistic ADD CONSTRAINT FK_2F18511724DB0683 FOREIGN KEY (config_id) REFERENCES statistic_config (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE pending_statistic ADD CONSTRAINT FK_2F185117A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE pending_statistic ADD CONSTRAINT FK_2F18511740AC2F6A FOREIGN KEY (daedalus_info_id) REFERENCES daedalus_info (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE statistic_config ADD strategy VARCHAR(255) DEFAULT \'\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE pending_statistic_id_seq CASCADE');
        $this->addSql('ALTER TABLE pending_statistic DROP CONSTRAINT FK_2F18511724DB0683');
        $this->addSql('ALTER TABLE pending_statistic DROP CONSTRAINT FK_2F185117A76ED395');
        $this->addSql('ALTER TABLE pending_statistic DROP CONSTRAINT FK_2F18511740AC2F6A');
        $this->addSql('DROP TABLE pending_statistic');
        $this->addSql('ALTER TABLE statistic_config DROP strategy');
    }
}
