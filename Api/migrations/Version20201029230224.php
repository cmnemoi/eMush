<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201029230224 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE room_config DROP FOREIGN KEY FK_95C4E9BD201E3C43');
        $this->addSql('ALTER TABLE character_config DROP FOREIGN KEY FK_77C8ECEFF67DC781');
        $this->addSql('ALTER TABLE daedalus DROP FOREIGN KEY FK_71DA760AF67DC781');
        $this->addSql('ALTER TABLE daedalus_config DROP FOREIGN KEY FK_4474D96CF67DC781');
        $this->addSql('ALTER TABLE item DROP FOREIGN KEY FK_1F1B251EF67DC781');
        $this->addSql('ALTER TABLE daedalus_config DROP FOREIGN KEY FK_4474D96C5E67882C');
        $this->addSql('CREATE TABLE config_daedalus (id INT AUTO_INCREMENT NOT NULL, game_config_id INT DEFAULT NULL, random_item_place_id INT DEFAULT NULL, init_oxygen INT NOT NULL, init_fuel INT NOT NULL, init_hull INT NOT NULL, init_shield INT NOT NULL, UNIQUE INDEX UNIQ_CC1298D4F67DC781 (game_config_id), UNIQUE INDEX UNIQ_CC1298D45E67882C (random_item_place_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE config_game (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, max_player INT NOT NULL, nb_mush INT NOT NULL, cycle_length INT NOT NULL, time_zone VARCHAR(255) NOT NULL, language VARCHAR(255) NOT NULL, init_health_point INT NOT NULL, max_health_point INT NOT NULL, init_moral_point INT NOT NULL, max_moral_point INT NOT NULL, init_satiety INT NOT NULL, init_action_point INT NOT NULL, max_action_point INT NOT NULL, init_movement_point INT NOT NULL, max_movement_point INT NOT NULL, max_item_in_inventory INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE config_random_item_place (id INT AUTO_INCREMENT NOT NULL, places LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', items LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE item_item_type (item_id INT NOT NULL, item_type_id INT NOT NULL, INDEX IDX_48340522126F525E (item_id), INDEX IDX_48340522CE11AAC7 (item_type_id), PRIMARY KEY(item_id, item_type_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE item_type (id INT AUTO_INCREMENT NOT NULL, fruit_id INT DEFAULT NULL, actions LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', type VARCHAR(255) NOT NULL, max_maturation_time INT DEFAULT NULL, min_maturation_time INT DEFAULT NULL, min_oxygen INT DEFAULT NULL, max_oxygen INT DEFAULT NULL, min_action_point INT DEFAULT NULL, max_action_point INT DEFAULT NULL, min_movement_point INT DEFAULT NULL, max_movement_point INT DEFAULT NULL, min_health_point INT DEFAULT NULL, max_health_point INT DEFAULT NULL, min_moral_point VARCHAR(255) DEFAULT NULL, max_moral_point VARCHAR(255) DEFAULT NULL, satiety INT DEFAULT NULL, cures LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', diseases LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', INDEX IDX_44EE13D2BAC115F0 (fruit_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE status_effect (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, duration INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE config_daedalus ADD CONSTRAINT FK_CC1298D4F67DC781 FOREIGN KEY (game_config_id) REFERENCES config_game (id)');
        $this->addSql('ALTER TABLE config_daedalus ADD CONSTRAINT FK_CC1298D45E67882C FOREIGN KEY (random_item_place_id) REFERENCES config_random_item_place (id)');
        $this->addSql('ALTER TABLE item_item_type ADD CONSTRAINT FK_48340522126F525E FOREIGN KEY (item_id) REFERENCES item (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE item_item_type ADD CONSTRAINT FK_48340522CE11AAC7 FOREIGN KEY (item_type_id) REFERENCES item_type (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE item_type ADD CONSTRAINT FK_44EE13D2BAC115F0 FOREIGN KEY (fruit_id) REFERENCES item (id)');
        $this->addSql('DROP TABLE daedalus_config');
        $this->addSql('DROP TABLE game_config');
        $this->addSql('DROP TABLE random_item_places');
        $this->addSql('ALTER TABLE character_config DROP FOREIGN KEY FK_77C8ECEFF67DC781');
        $this->addSql('ALTER TABLE character_config ADD CONSTRAINT FK_77C8ECEFF67DC781 FOREIGN KEY (game_config_id) REFERENCES config_game (id)');
        $this->addSql('ALTER TABLE consumable_effect DROP FOREIGN KEY FK_C27596264A5A89FC');
        $this->addSql('ALTER TABLE consumable_effect ADD CONSTRAINT FK_C27596264A5A89FC FOREIGN KEY (ration_id) REFERENCES item_type (id)');
        $this->addSql('ALTER TABLE daedalus DROP FOREIGN KEY FK_71DA760AF67DC781');
        $this->addSql('ALTER TABLE daedalus ADD CONSTRAINT FK_71DA760AF67DC781 FOREIGN KEY (game_config_id) REFERENCES config_game (id)');
        $this->addSql('ALTER TABLE item DROP FOREIGN KEY FK_1F1B251EBAC115F0');
        $this->addSql('ALTER TABLE item DROP FOREIGN KEY FK_1F1B251EF67DC781');
        $this->addSql('DROP INDEX IDX_1F1B251EBAC115F0 ON item');
        $this->addSql('ALTER TABLE item DROP fruit_id, DROP type, DROP max_maturation_time, DROP min_maturation_time, DROP min_oxygen, DROP max_oxygen, DROP min_action_point, DROP max_action_point, DROP min_movement_point, DROP max_movement_point, DROP min_health_point, DROP max_health_point, DROP min_moral_point, DROP max_moral_point, DROP satiety, DROP cures, DROP diseases');
        $this->addSql('ALTER TABLE item ADD CONSTRAINT FK_1F1B251EF67DC781 FOREIGN KEY (game_config_id) REFERENCES config_game (id)');
        $this->addSql('ALTER TABLE plant_effect DROP FOREIGN KEY FK_55F040791D935652');
        $this->addSql('ALTER TABLE plant_effect ADD CONSTRAINT FK_55F040791D935652 FOREIGN KEY (plant_id) REFERENCES item_type (id)');
        $this->addSql('ALTER TABLE room_config DROP FOREIGN KEY FK_95C4E9BD201E3C43');
        $this->addSql('ALTER TABLE room_config ADD CONSTRAINT FK_95C4E9BD201E3C43 FOREIGN KEY (daedalus_config_id) REFERENCES config_daedalus (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE room_config DROP FOREIGN KEY FK_95C4E9BD201E3C43');
        $this->addSql('ALTER TABLE character_config DROP FOREIGN KEY FK_77C8ECEFF67DC781');
        $this->addSql('ALTER TABLE config_daedalus DROP FOREIGN KEY FK_CC1298D4F67DC781');
        $this->addSql('ALTER TABLE daedalus DROP FOREIGN KEY FK_71DA760AF67DC781');
        $this->addSql('ALTER TABLE item DROP FOREIGN KEY FK_1F1B251EF67DC781');
        $this->addSql('ALTER TABLE config_daedalus DROP FOREIGN KEY FK_CC1298D45E67882C');
        $this->addSql('ALTER TABLE consumable_effect DROP FOREIGN KEY FK_C27596264A5A89FC');
        $this->addSql('ALTER TABLE item_item_type DROP FOREIGN KEY FK_48340522CE11AAC7');
        $this->addSql('ALTER TABLE plant_effect DROP FOREIGN KEY FK_55F040791D935652');
        $this->addSql('CREATE TABLE daedalus_config (id INT AUTO_INCREMENT NOT NULL, game_config_id INT DEFAULT NULL, random_item_place_id INT DEFAULT NULL, init_oxygen INT NOT NULL, init_fuel INT NOT NULL, init_hull INT NOT NULL, init_shield INT NOT NULL, UNIQUE INDEX UNIQ_4474D96C5E67882C (random_item_place_id), UNIQUE INDEX UNIQ_4474D96CF67DC781 (game_config_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE game_config (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, max_player INT NOT NULL, nb_mush INT NOT NULL, cycle_length INT NOT NULL, time_zone VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, language VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, init_health_point INT NOT NULL, max_health_point INT NOT NULL, init_moral_point INT NOT NULL, max_moral_point INT NOT NULL, init_satiety INT NOT NULL, init_action_point INT NOT NULL, max_action_point INT NOT NULL, init_movement_point INT NOT NULL, max_movement_point INT NOT NULL, max_item_in_inventory INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE random_item_places (id INT AUTO_INCREMENT NOT NULL, places LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:array)\', items LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:array)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE daedalus_config ADD CONSTRAINT FK_4474D96C5E67882C FOREIGN KEY (random_item_place_id) REFERENCES random_item_places (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE daedalus_config ADD CONSTRAINT FK_4474D96CF67DC781 FOREIGN KEY (game_config_id) REFERENCES game_config (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('DROP TABLE config_daedalus');
        $this->addSql('DROP TABLE config_game');
        $this->addSql('DROP TABLE config_random_item_place');
        $this->addSql('DROP TABLE item_item_type');
        $this->addSql('DROP TABLE item_type');
        $this->addSql('DROP TABLE status_effect');
        $this->addSql('ALTER TABLE character_config DROP FOREIGN KEY FK_77C8ECEFF67DC781');
        $this->addSql('ALTER TABLE character_config ADD CONSTRAINT FK_77C8ECEFF67DC781 FOREIGN KEY (game_config_id) REFERENCES game_config (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE consumable_effect DROP FOREIGN KEY FK_C27596264A5A89FC');
        $this->addSql('ALTER TABLE consumable_effect ADD CONSTRAINT FK_C27596264A5A89FC FOREIGN KEY (ration_id) REFERENCES item (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE daedalus DROP FOREIGN KEY FK_71DA760AF67DC781');
        $this->addSql('ALTER TABLE daedalus ADD CONSTRAINT FK_71DA760AF67DC781 FOREIGN KEY (game_config_id) REFERENCES game_config (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE item DROP FOREIGN KEY FK_1F1B251EF67DC781');
        $this->addSql('ALTER TABLE item ADD fruit_id INT DEFAULT NULL, ADD type VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, ADD max_maturation_time INT DEFAULT NULL, ADD min_maturation_time INT DEFAULT NULL, ADD min_oxygen INT DEFAULT NULL, ADD max_oxygen INT DEFAULT NULL, ADD min_action_point INT DEFAULT NULL, ADD max_action_point INT DEFAULT NULL, ADD min_movement_point INT DEFAULT NULL, ADD max_movement_point INT DEFAULT NULL, ADD min_health_point INT DEFAULT NULL, ADD max_health_point INT DEFAULT NULL, ADD min_moral_point VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD max_moral_point VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD satiety INT DEFAULT NULL, ADD cures LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:array)\', ADD diseases LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:array)\'');
        $this->addSql('ALTER TABLE item ADD CONSTRAINT FK_1F1B251EBAC115F0 FOREIGN KEY (fruit_id) REFERENCES item (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE item ADD CONSTRAINT FK_1F1B251EF67DC781 FOREIGN KEY (game_config_id) REFERENCES game_config (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_1F1B251EBAC115F0 ON item (fruit_id)');
        $this->addSql('ALTER TABLE plant_effect DROP FOREIGN KEY FK_55F040791D935652');
        $this->addSql('ALTER TABLE plant_effect ADD CONSTRAINT FK_55F040791D935652 FOREIGN KEY (plant_id) REFERENCES item (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE room_config DROP FOREIGN KEY FK_95C4E9BD201E3C43');
        $this->addSql('ALTER TABLE room_config ADD CONSTRAINT FK_95C4E9BD201E3C43 FOREIGN KEY (daedalus_config_id) REFERENCES daedalus_config (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
