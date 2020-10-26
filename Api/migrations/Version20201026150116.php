<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201026150116 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE game_plant DROP FOREIGN KEY FK_993074FD1185EAD6');
        $this->addSql('ALTER TABLE item DROP FOREIGN KEY FK_1F1B251E1185EAD6');
        $this->addSql('ALTER TABLE game_fruit DROP FOREIGN KEY FK_9238AB18B6D7A974');
        $this->addSql('ALTER TABLE item DROP FOREIGN KEY FK_1F1B251EB6D7A974');
        $this->addSql('CREATE TABLE character_config (id INT AUTO_INCREMENT NOT NULL, game_config_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, statuses LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', skills LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', INDEX IDX_77C8ECEFF67DC781 (game_config_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE daedalus_config (id INT AUTO_INCREMENT NOT NULL, game_config_id INT DEFAULT NULL, random_item_place_id INT DEFAULT NULL, init_oxygen INT NOT NULL, init_fuel INT NOT NULL, init_hull INT NOT NULL, init_shield INT NOT NULL, UNIQUE INDEX UNIQ_4474D96CF67DC781 (game_config_id), UNIQUE INDEX UNIQ_4474D96C5E67882C (random_item_place_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE fruit (id INT AUTO_INCREMENT NOT NULL, daedalus_id INT DEFAULT NULL, plant_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, action_point INT NOT NULL, health_point INT NOT NULL, moral_point VARCHAR(255) NOT NULL, satiety INT NOT NULL, cures LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', diseases LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', INDEX IDX_A00BD29774B5A52D (daedalus_id), UNIQUE INDEX UNIQ_A00BD2971D935652 (plant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE game_config (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, max_player INT NOT NULL, nb_mush INT NOT NULL, cycle_length INT NOT NULL, time_zone VARCHAR(255) NOT NULL, language VARCHAR(255) NOT NULL, init_health_point INT NOT NULL, max_health_point INT NOT NULL, init_moral_point INT NOT NULL, max_moral_point INT NOT NULL, init_satiety INT NOT NULL, init_action_point INT NOT NULL, max_action_point INT NOT NULL, init_movement_point INT NOT NULL, max_movement_point INT NOT NULL, max_item_in_inventory INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE game_item (id INT AUTO_INCREMENT NOT NULL, room_id INT DEFAULT NULL, player_id INT DEFAULT NULL, item_id INT DEFAULT NULL, plant_id INT DEFAULT NULL, statuses LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, type VARCHAR(255) NOT NULL, charge INT DEFAULT NULL, INDEX IDX_F40E493254177093 (room_id), INDEX IDX_F40E493299E6F5DF (player_id), INDEX IDX_F40E4932126F525E (item_id), INDEX IDX_F40E49321D935652 (plant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE plant (id INT AUTO_INCREMENT NOT NULL, fruit_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, maturation_time INT NOT NULL, oxygen INT NOT NULL, UNIQUE INDEX UNIQ_AB030D72BAC115F0 (fruit_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE random_item_places (id INT AUTO_INCREMENT NOT NULL, places LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', items LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE room_config (id INT AUTO_INCREMENT NOT NULL, daedalus_config_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, doors LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', items LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', INDEX IDX_95C4E9BD201E3C43 (daedalus_config_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE character_config ADD CONSTRAINT FK_77C8ECEFF67DC781 FOREIGN KEY (game_config_id) REFERENCES game_config (id)');
        $this->addSql('ALTER TABLE daedalus_config ADD CONSTRAINT FK_4474D96CF67DC781 FOREIGN KEY (game_config_id) REFERENCES game_config (id)');
        $this->addSql('ALTER TABLE daedalus_config ADD CONSTRAINT FK_4474D96C5E67882C FOREIGN KEY (random_item_place_id) REFERENCES random_item_places (id)');
        $this->addSql('ALTER TABLE fruit ADD CONSTRAINT FK_A00BD29774B5A52D FOREIGN KEY (daedalus_id) REFERENCES daedalus (id)');
        $this->addSql('ALTER TABLE fruit ADD CONSTRAINT FK_A00BD2971D935652 FOREIGN KEY (plant_id) REFERENCES plant (id)');
        $this->addSql('ALTER TABLE game_item ADD CONSTRAINT FK_F40E493254177093 FOREIGN KEY (room_id) REFERENCES room (id)');
        $this->addSql('ALTER TABLE game_item ADD CONSTRAINT FK_F40E493299E6F5DF FOREIGN KEY (player_id) REFERENCES player (id)');
        $this->addSql('ALTER TABLE game_item ADD CONSTRAINT FK_F40E4932126F525E FOREIGN KEY (item_id) REFERENCES item (id)');
        $this->addSql('ALTER TABLE game_item ADD CONSTRAINT FK_F40E49321D935652 FOREIGN KEY (plant_id) REFERENCES plant (id)');
        $this->addSql('ALTER TABLE plant ADD CONSTRAINT FK_AB030D72BAC115F0 FOREIGN KEY (fruit_id) REFERENCES fruit (id)');
        $this->addSql('ALTER TABLE room_config ADD CONSTRAINT FK_95C4E9BD201E3C43 FOREIGN KEY (daedalus_config_id) REFERENCES daedalus_config (id)');
        $this->addSql('DROP TABLE game_fruit');
        $this->addSql('DROP TABLE game_plant');
        $this->addSql('ALTER TABLE daedalus ADD game_config_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE daedalus ADD CONSTRAINT FK_71DA760AF67DC781 FOREIGN KEY (game_config_id) REFERENCES game_config (id)');
        $this->addSql('CREATE INDEX IDX_71DA760AF67DC781 ON daedalus (game_config_id)');
        $this->addSql('ALTER TABLE item DROP FOREIGN KEY FK_1F1B251E54177093');
        $this->addSql('ALTER TABLE item DROP FOREIGN KEY FK_1F1B251E99E6F5DF');
        $this->addSql('DROP INDEX IDX_1F1B251E1185EAD6 ON item');
        $this->addSql('DROP INDEX IDX_1F1B251E54177093 ON item');
        $this->addSql('DROP INDEX IDX_1F1B251E99E6F5DF ON item');
        $this->addSql('DROP INDEX IDX_1F1B251EB6D7A974 ON item');
        $this->addSql('ALTER TABLE item ADD game_config_id INT DEFAULT NULL, ADD is_dropable TINYINT(1) NOT NULL, DROP room_id, DROP player_id, DROP game_fruit_id, DROP game_plant_id, DROP statuses, DROP created_at, DROP updated_at, DROP charge');
        $this->addSql('ALTER TABLE item ADD CONSTRAINT FK_1F1B251EF67DC781 FOREIGN KEY (game_config_id) REFERENCES game_config (id)');
        $this->addSql('CREATE INDEX IDX_1F1B251EF67DC781 ON item (game_config_id)');
        $this->addSql('ALTER TABLE room_log DROP FOREIGN KEY FK_8DB9D5D8126F525E');
        $this->addSql('ALTER TABLE room_log ADD CONSTRAINT FK_8DB9D5D8126F525E FOREIGN KEY (item_id) REFERENCES game_item (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE room_config DROP FOREIGN KEY FK_95C4E9BD201E3C43');
        $this->addSql('ALTER TABLE plant DROP FOREIGN KEY FK_AB030D72BAC115F0');
        $this->addSql('ALTER TABLE character_config DROP FOREIGN KEY FK_77C8ECEFF67DC781');
        $this->addSql('ALTER TABLE daedalus DROP FOREIGN KEY FK_71DA760AF67DC781');
        $this->addSql('ALTER TABLE daedalus_config DROP FOREIGN KEY FK_4474D96CF67DC781');
        $this->addSql('ALTER TABLE item DROP FOREIGN KEY FK_1F1B251EF67DC781');
        $this->addSql('ALTER TABLE room_log DROP FOREIGN KEY FK_8DB9D5D8126F525E');
        $this->addSql('ALTER TABLE fruit DROP FOREIGN KEY FK_A00BD2971D935652');
        $this->addSql('ALTER TABLE game_item DROP FOREIGN KEY FK_F40E49321D935652');
        $this->addSql('ALTER TABLE daedalus_config DROP FOREIGN KEY FK_4474D96C5E67882C');
        $this->addSql('CREATE TABLE game_fruit (id INT AUTO_INCREMENT NOT NULL, daedalus_id INT DEFAULT NULL, game_plant_id INT DEFAULT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, action_point INT NOT NULL, health_point INT NOT NULL, moral_point VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, satiety INT NOT NULL, cures LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:array)\', diseases LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:array)\', INDEX IDX_9238AB1874B5A52D (daedalus_id), UNIQUE INDEX UNIQ_9238AB18B6D7A974 (game_plant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE game_plant (id INT AUTO_INCREMENT NOT NULL, game_fruit_id INT DEFAULT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, maturation_time INT NOT NULL, oxygen INT NOT NULL, UNIQUE INDEX UNIQ_993074FD1185EAD6 (game_fruit_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE game_fruit ADD CONSTRAINT FK_9238AB1874B5A52D FOREIGN KEY (daedalus_id) REFERENCES daedalus (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE game_fruit ADD CONSTRAINT FK_9238AB18B6D7A974 FOREIGN KEY (game_plant_id) REFERENCES game_plant (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE game_plant ADD CONSTRAINT FK_993074FD1185EAD6 FOREIGN KEY (game_fruit_id) REFERENCES game_fruit (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('DROP TABLE character_config');
        $this->addSql('DROP TABLE daedalus_config');
        $this->addSql('DROP TABLE fruit');
        $this->addSql('DROP TABLE game_config');
        $this->addSql('DROP TABLE game_item');
        $this->addSql('DROP TABLE plant');
        $this->addSql('DROP TABLE random_item_places');
        $this->addSql('DROP TABLE room_config');
        $this->addSql('DROP INDEX IDX_71DA760AF67DC781 ON daedalus');
        $this->addSql('ALTER TABLE daedalus DROP game_config_id');
        $this->addSql('DROP INDEX IDX_1F1B251EF67DC781 ON item');
        $this->addSql('ALTER TABLE item ADD player_id INT DEFAULT NULL, ADD game_fruit_id INT DEFAULT NULL, ADD game_plant_id INT DEFAULT NULL, ADD statuses LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:array)\', ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL, ADD charge INT DEFAULT NULL, DROP is_dropable, CHANGE game_config_id room_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE item ADD CONSTRAINT FK_1F1B251E1185EAD6 FOREIGN KEY (game_fruit_id) REFERENCES game_fruit (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE item ADD CONSTRAINT FK_1F1B251E54177093 FOREIGN KEY (room_id) REFERENCES room (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE item ADD CONSTRAINT FK_1F1B251E99E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE item ADD CONSTRAINT FK_1F1B251EB6D7A974 FOREIGN KEY (game_plant_id) REFERENCES game_plant (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_1F1B251E1185EAD6 ON item (game_fruit_id)');
        $this->addSql('CREATE INDEX IDX_1F1B251E54177093 ON item (room_id)');
        $this->addSql('CREATE INDEX IDX_1F1B251E99E6F5DF ON item (player_id)');
        $this->addSql('CREATE INDEX IDX_1F1B251EB6D7A974 ON item (game_plant_id)');
        $this->addSql('ALTER TABLE room_log DROP FOREIGN KEY FK_8DB9D5D8126F525E');
        $this->addSql('ALTER TABLE room_log ADD CONSTRAINT FK_8DB9D5D8126F525E FOREIGN KEY (item_id) REFERENCES item (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
