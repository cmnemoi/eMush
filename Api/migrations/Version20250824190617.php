<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250824190617 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE statistic_config_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE statistic_config (id INT NOT NULL, name VARCHAR(255) DEFAULT \'\' NOT NULL, is_rare BOOLEAN DEFAULT false NOT NULL, PRIMARY KEY(id))');
        $this->addSql('DROP INDEX unique_statistic_per_user');
        $this->addSql('ALTER TABLE statistic ADD config_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE statistic DROP name');
        $this->addSql('ALTER TABLE statistic ADD CONSTRAINT FK_649B469C24DB0683 FOREIGN KEY (config_id) REFERENCES statistic_config (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_649B469C24DB0683 ON statistic (config_id)');
        $this->addSql('CREATE UNIQUE INDEX unique_statistic_per_user ON statistic (config_id, user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE statistic DROP CONSTRAINT FK_649B469C24DB0683');
        $this->addSql('DROP SEQUENCE statistic_config_id_seq CASCADE');
        $this->addSql('DROP TABLE statistic_config');
        $this->addSql('DROP INDEX IDX_649B469C24DB0683');
        $this->addSql('DROP INDEX unique_statistic_per_user');
        $this->addSql('ALTER TABLE statistic ADD name VARCHAR(255) DEFAULT \'\' NOT NULL');
        $this->addSql('ALTER TABLE statistic DROP config_id');
        $this->addSql('CREATE UNIQUE INDEX unique_statistic_per_user ON statistic (name, user_id)');
    }
}
