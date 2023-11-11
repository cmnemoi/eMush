<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231111150300 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE equipment_mechanic ADD number_of_exploration_steps INT DEFAULT 0');
        $this->addSql('ALTER TABLE exploration DROP cycle_started_at');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE equipment_mechanic DROP number_of_exploration_steps');
        $this->addSql('ALTER TABLE exploration ADD cycle_started_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
    }
}
