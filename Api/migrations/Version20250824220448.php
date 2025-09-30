<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250824220448 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE achievement_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE achievement_config_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE achievement (id INT NOT NULL, config_id INT DEFAULT NULL, statistic_id INT DEFAULT NULL, version INT DEFAULT 1 NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_96737FF124DB0683 ON achievement (config_id)');
        $this->addSql('CREATE INDEX IDX_96737FF153B6268F ON achievement (statistic_id)');
        $this->addSql('CREATE UNIQUE INDEX unique_achievement_per_user ON achievement (config_id, statistic_id)');
        $this->addSql('CREATE TABLE achievement_config (id INT NOT NULL, statistic_config_id INT DEFAULT NULL, version INT DEFAULT 1 NOT NULL, name VARCHAR(255) DEFAULT \'\' NOT NULL, points INT DEFAULT 0 NOT NULL, unlock_threshold INT DEFAULT 0 NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_339D62E1124B7525 ON achievement_config (statistic_config_id)');
        $this->addSql('ALTER TABLE achievement ADD CONSTRAINT FK_96737FF124DB0683 FOREIGN KEY (config_id) REFERENCES achievement_config (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE achievement ADD CONSTRAINT FK_96737FF153B6268F FOREIGN KEY (statistic_id) REFERENCES statistic (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE achievement_config ADD CONSTRAINT FK_339D62E1124B7525 FOREIGN KEY (statistic_config_id) REFERENCES statistic_config (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE statistic_config ADD version INT DEFAULT 1 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE achievement_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE achievement_config_id_seq CASCADE');
        $this->addSql('ALTER TABLE achievement DROP CONSTRAINT FK_96737FF124DB0683');
        $this->addSql('ALTER TABLE achievement DROP CONSTRAINT FK_96737FF153B6268F');
        $this->addSql('ALTER TABLE achievement_config DROP CONSTRAINT FK_339D62E1124B7525');
        $this->addSql('DROP TABLE achievement');
        $this->addSql('DROP TABLE achievement_config');
        $this->addSql('ALTER TABLE statistic_config DROP version');
    }
}
