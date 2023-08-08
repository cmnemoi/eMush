<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230708203956 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE equipment_mechanic ALTER collect_scrap_number SET DEFAULT \'[]\'');
        $this->addSql('ALTER TABLE equipment_mechanic ALTER collect_scrap_player_damage SET DEFAULT \'[]\'');
        $this->addSql('ALTER TABLE equipment_mechanic ALTER collect_scrap_patrol_ship_damage SET DEFAULT \'[]\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE equipment_mechanic ALTER collect_scrap_number DROP DEFAULT');
        $this->addSql('ALTER TABLE equipment_mechanic ALTER collect_scrap_patrol_ship_damage DROP DEFAULT');
        $this->addSql('ALTER TABLE equipment_mechanic ALTER collect_scrap_player_damage DROP DEFAULT');
    }
}
