<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230913071427 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE hunter_config ADD bonus_after_failed_shot INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE hunter_config ALTER target_probabilities TYPE TEXT');
        $this->addSql('COMMENT ON COLUMN hunter_config.target_probabilities IS \'(DC2Type:array)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE hunter_config DROP bonus_after_failed_shot');
        $this->addSql('ALTER TABLE hunter_config ALTER target_probabilities TYPE TEXT');
        $this->addSql('COMMENT ON COLUMN hunter_config.target_probabilities IS NULL');
    }
}
