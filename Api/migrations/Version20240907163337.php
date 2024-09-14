<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240907163337 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE spawn_equipment_config ALTER name SET DEFAULT \'\'');
        $this->addSql('ALTER TABLE spawn_equipment_config ALTER equipment_name SET DEFAULT \'\'');
        $this->addSql('ALTER TABLE spawn_equipment_config ALTER place_name SET DEFAULT \'\'');
        $this->addSql('ALTER TABLE spawn_equipment_config ALTER quantity SET DEFAULT 0');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE spawn_equipment_config ALTER name DROP DEFAULT');
        $this->addSql('ALTER TABLE spawn_equipment_config ALTER equipment_name DROP DEFAULT');
        $this->addSql('ALTER TABLE spawn_equipment_config ALTER place_name DROP DEFAULT');
        $this->addSql('ALTER TABLE spawn_equipment_config ALTER quantity DROP DEFAULT');
    }
}
