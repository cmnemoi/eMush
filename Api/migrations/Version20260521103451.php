<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260521103451 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE planet_sector ALTER created_at DROP DEFAULT');
        $this->addSql('ALTER TABLE planet_sector ALTER updated_at DROP DEFAULT');
        $this->addSql('ALTER TABLE skill_config DROP CONSTRAINT fk_fbc07b68d7e86f28');
        $this->addSql('DROP INDEX idx_fbc07b68b9e73f9c');
        $this->addSql('ALTER TABLE skill_config DROP skill_points_config_id');
        $this->addSql('DROP INDEX idx_75ea56e016ba31db');
        $this->addSql('DROP INDEX idx_75ea56e0e3bd61ce');
        $this->addSql('DROP INDEX idx_75ea56e0fb7336f0');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 ON messenger_messages (queue_name, available_at, delivered_at, id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE skill_config ADD skill_points_config_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE skill_config ADD CONSTRAINT fk_fbc07b68d7e86f28 FOREIGN KEY (skill_points_config_id) REFERENCES status_config (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_fbc07b68b9e73f9c ON skill_config (skill_points_config_id)');
        $this->addSql('ALTER TABLE planet_sector ALTER created_at SET DEFAULT \'CURRENT_TIMESTAMP(0)\'');
        $this->addSql('ALTER TABLE planet_sector ALTER updated_at SET DEFAULT \'CURRENT_TIMESTAMP(0)\'');
        $this->addSql('DROP INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750');
        $this->addSql('CREATE INDEX idx_75ea56e016ba31db ON messenger_messages (delivered_at)');
        $this->addSql('CREATE INDEX idx_75ea56e0e3bd61ce ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX idx_75ea56e0fb7336f0 ON messenger_messages (queue_name)');
    }
}
