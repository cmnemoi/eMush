<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250308192829 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE rebel_base_config ADD status_config_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE rebel_base_config ADD CONSTRAINT FK_D5724A8FAC4E86C2 FOREIGN KEY (status_config_id) REFERENCES status_config (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_D5724A8FAC4E86C2 ON rebel_base_config (status_config_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE rebel_base_config DROP CONSTRAINT FK_D5724A8FAC4E86C2');
        $this->addSql('DROP INDEX IDX_D5724A8FAC4E86C2');
        $this->addSql('ALTER TABLE rebel_base_config DROP status_config_id');
    }
}
