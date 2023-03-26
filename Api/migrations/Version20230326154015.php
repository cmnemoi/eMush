<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230326154015 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE hunter_config_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE hunter_wave_config_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE hunter_config (id INT NOT NULL, name VARCHAR(255) NOT NULL, hunter_name VARCHAR(255) NOT NULL, initial_health INT NOT NULL, initial_charge INT NOT NULL, initial_armor INT NOT NULL, min_damage INT NOT NULL, max_damage INT NOT NULL, hit_chance INT NOT NULL, dodge_chance INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5239640E5E237E06 ON hunter_config (name)');
        $this->addSql('CREATE TABLE hunter_wave_config (id INT NOT NULL, name VARCHAR(255) NOT NULL, wave_config_name VARCHAR(255) NOT NULL, hunter_pool_costs TEXT NOT NULL, max_hunter_per_wave TEXT NOT NULL, hunter_draw_chances TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4C1A89C45E237E06 ON hunter_wave_config (name)');
        $this->addSql('COMMENT ON COLUMN hunter_wave_config.hunter_pool_costs IS \'(DC2Type:array)\'');
        $this->addSql('COMMENT ON COLUMN hunter_wave_config.max_hunter_per_wave IS \'(DC2Type:array)\'');
        $this->addSql('COMMENT ON COLUMN hunter_wave_config.hunter_draw_chances IS \'(DC2Type:array)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE hunter_config_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE hunter_wave_config_id_seq CASCADE');
        $this->addSql('DROP TABLE hunter_config');
        $this->addSql('DROP TABLE hunter_wave_config');
    }
}
