<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250528195142 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE closed_player ADD triumph INT NOT NULL');
        $this->addSql('ALTER TABLE equipment_mechanic DROP critical_success_rate');
        $this->addSql('ALTER TABLE equipment_mechanic DROP critical_fail_rate');
        $this->addSql('ALTER TABLE equipment_mechanic DROP one_shot_rate');
        $this->addSql('ALTER TABLE triumph_config ALTER quantity SET DEFAULT 0');
        $this->addSql('ALTER TABLE triumph_config ALTER key SET DEFAULT \'\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE closed_player DROP triumph');
        $this->addSql('ALTER TABLE equipment_mechanic ADD critical_success_rate INT DEFAULT NULL');
        $this->addSql('ALTER TABLE equipment_mechanic ADD critical_fail_rate INT DEFAULT NULL');
        $this->addSql('ALTER TABLE equipment_mechanic ADD one_shot_rate INT DEFAULT NULL');
        $this->addSql('ALTER TABLE triumph_config ALTER key DROP DEFAULT');
        $this->addSql('ALTER TABLE triumph_config ALTER quantity DROP DEFAULT');
    }
}
