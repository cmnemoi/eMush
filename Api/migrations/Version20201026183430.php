<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201026183430 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE character_config (id INT AUTO_INCREMENT NOT NULL, game_config_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, statuses LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', skills LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', INDEX IDX_77C8ECEFF67DC781 (game_config_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE daedalus (id INT AUTO_INCREMENT NOT NULL, game_config_id INT DEFAULT NULL, oxygen INT NOT NULL, fuel INT NOT NULL, hull INT NOT NULL, cycle INT NOT NULL, shield INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_71DA760AF67DC781 (game_config_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE daedalus_config (id INT AUTO_INCREMENT NOT NULL, game_config_id INT DEFAULT NULL, random_item_place_id INT DEFAULT NULL, init_oxygen INT NOT NULL, init_fuel INT NOT NULL, init_hull INT NOT NULL, init_shield INT NOT NULL, UNIQUE INDEX UNIQ_4474D96CF67DC781 (game_config_id), UNIQUE INDEX UNIQ_4474D96C5E67882C (random_item_place_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE door (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, statuses LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE door_room (door_id INT NOT NULL, room_id INT NOT NULL, INDEX IDX_3CC1304A58639EAE (door_id), INDEX IDX_3CC1304A54177093 (room_id), PRIMARY KEY(door_id, room_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE game_config (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, max_player INT NOT NULL, nb_mush INT NOT NULL, cycle_length INT NOT NULL, time_zone VARCHAR(255) NOT NULL, language VARCHAR(255) NOT NULL, init_health_point INT NOT NULL, max_health_point INT NOT NULL, init_moral_point INT NOT NULL, max_moral_point INT NOT NULL, init_satiety INT NOT NULL, init_action_point INT NOT NULL, max_action_point INT NOT NULL, init_movement_point INT NOT NULL, max_movement_point INT NOT NULL, max_item_in_inventory INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE game_item (id INT AUTO_INCREMENT NOT NULL, room_id INT DEFAULT NULL, player_id INT DEFAULT NULL, item_id INT DEFAULT NULL, statuses LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', name VARCHAR(255) NOT NULL, charge INT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_F40E493254177093 (room_id), INDEX IDX_F40E493299E6F5DF (player_id), INDEX IDX_F40E4932126F525E (item_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE item (id INT AUTO_INCREMENT NOT NULL, game_config_id INT DEFAULT NULL, fruit_id INT DEFAULT NULL, daedalus_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, is_heavy TINYINT(1) NOT NULL, is_dropable TINYINT(1) NOT NULL, is_dismantable TINYINT(1) NOT NULL, is_stackable TINYINT(1) NOT NULL, is_hideable TINYINT(1) NOT NULL, is_movable TINYINT(1) NOT NULL, is_fire_destroyable TINYINT(1) NOT NULL, is_fire_breakable TINYINT(1) NOT NULL, type VARCHAR(255) NOT NULL, maturation_time INT DEFAULT NULL, oxygen INT DEFAULT NULL, action_point INT DEFAULT NULL, health_point INT DEFAULT NULL, moral_point VARCHAR(255) DEFAULT NULL, satiety INT DEFAULT NULL, cures LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', diseases LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', INDEX IDX_1F1B251EF67DC781 (game_config_id), INDEX IDX_1F1B251EBAC115F0 (fruit_id), INDEX IDX_1F1B251E74B5A52D (daedalus_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE player (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, daedalus_id INT DEFAULT NULL, room_id INT DEFAULT NULL, game_status VARCHAR(255) NOT NULL, person VARCHAR(255) NOT NULL, statuses LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', skills LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', health_point INT NOT NULL, moral_point INT NOT NULL, action_point INT NOT NULL, movement_point INT NOT NULL, satiety INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_98197A65A76ED395 (user_id), INDEX IDX_98197A6574B5A52D (daedalus_id), INDEX IDX_98197A6554177093 (room_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE random_item_places (id INT AUTO_INCREMENT NOT NULL, places LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', items LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE room (id INT AUTO_INCREMENT NOT NULL, daedalus_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, statuses LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_729F519B74B5A52D (daedalus_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE room_door (room_id INT NOT NULL, door_id INT NOT NULL, INDEX IDX_E9E1BE4954177093 (room_id), INDEX IDX_E9E1BE4958639EAE (door_id), PRIMARY KEY(room_id, door_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE room_config (id INT AUTO_INCREMENT NOT NULL, daedalus_config_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, doors LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', items LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', INDEX IDX_95C4E9BD201E3C43 (daedalus_config_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE room_log (id INT AUTO_INCREMENT NOT NULL, room_id INT DEFAULT NULL, player_id INT DEFAULT NULL, item_id INT DEFAULT NULL, visibility VARCHAR(255) NOT NULL, log VARCHAR(255) NOT NULL, date DATETIME NOT NULL, params TINYTEXT NOT NULL COMMENT \'(DC2Type:array)\', created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_8DB9D5D854177093 (room_id), INDEX IDX_8DB9D5D899E6F5DF (player_id), INDEX IDX_8DB9D5D8126F525E (item_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, current_game_id INT DEFAULT NULL, user_id VARCHAR(255) NOT NULL, username VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_8D93D6494E825C80 (current_game_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE character_config ADD CONSTRAINT FK_77C8ECEFF67DC781 FOREIGN KEY (game_config_id) REFERENCES game_config (id)');
        $this->addSql('ALTER TABLE daedalus ADD CONSTRAINT FK_71DA760AF67DC781 FOREIGN KEY (game_config_id) REFERENCES game_config (id)');
        $this->addSql('ALTER TABLE daedalus_config ADD CONSTRAINT FK_4474D96CF67DC781 FOREIGN KEY (game_config_id) REFERENCES game_config (id)');
        $this->addSql('ALTER TABLE daedalus_config ADD CONSTRAINT FK_4474D96C5E67882C FOREIGN KEY (random_item_place_id) REFERENCES random_item_places (id)');
        $this->addSql('ALTER TABLE door_room ADD CONSTRAINT FK_3CC1304A58639EAE FOREIGN KEY (door_id) REFERENCES door (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE door_room ADD CONSTRAINT FK_3CC1304A54177093 FOREIGN KEY (room_id) REFERENCES room (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE game_item ADD CONSTRAINT FK_F40E493254177093 FOREIGN KEY (room_id) REFERENCES room (id)');
        $this->addSql('ALTER TABLE game_item ADD CONSTRAINT FK_F40E493299E6F5DF FOREIGN KEY (player_id) REFERENCES player (id)');
        $this->addSql('ALTER TABLE game_item ADD CONSTRAINT FK_F40E4932126F525E FOREIGN KEY (item_id) REFERENCES item (id)');
        $this->addSql('ALTER TABLE item ADD CONSTRAINT FK_1F1B251EF67DC781 FOREIGN KEY (game_config_id) REFERENCES game_config (id)');
        $this->addSql('ALTER TABLE item ADD CONSTRAINT FK_1F1B251EBAC115F0 FOREIGN KEY (fruit_id) REFERENCES item (id)');
        $this->addSql('ALTER TABLE item ADD CONSTRAINT FK_1F1B251E74B5A52D FOREIGN KEY (daedalus_id) REFERENCES daedalus (id)');
        $this->addSql('ALTER TABLE player ADD CONSTRAINT FK_98197A65A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE player ADD CONSTRAINT FK_98197A6574B5A52D FOREIGN KEY (daedalus_id) REFERENCES daedalus (id)');
        $this->addSql('ALTER TABLE player ADD CONSTRAINT FK_98197A6554177093 FOREIGN KEY (room_id) REFERENCES room (id)');
        $this->addSql('ALTER TABLE room ADD CONSTRAINT FK_729F519B74B5A52D FOREIGN KEY (daedalus_id) REFERENCES daedalus (id)');
        $this->addSql('ALTER TABLE room_door ADD CONSTRAINT FK_E9E1BE4954177093 FOREIGN KEY (room_id) REFERENCES room (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE room_door ADD CONSTRAINT FK_E9E1BE4958639EAE FOREIGN KEY (door_id) REFERENCES door (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE room_config ADD CONSTRAINT FK_95C4E9BD201E3C43 FOREIGN KEY (daedalus_config_id) REFERENCES daedalus_config (id)');
        $this->addSql('ALTER TABLE room_log ADD CONSTRAINT FK_8DB9D5D854177093 FOREIGN KEY (room_id) REFERENCES room (id)');
        $this->addSql('ALTER TABLE room_log ADD CONSTRAINT FK_8DB9D5D899E6F5DF FOREIGN KEY (player_id) REFERENCES player (id)');
        $this->addSql('ALTER TABLE room_log ADD CONSTRAINT FK_8DB9D5D8126F525E FOREIGN KEY (item_id) REFERENCES game_item (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6494E825C80 FOREIGN KEY (current_game_id) REFERENCES player (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE item DROP FOREIGN KEY FK_1F1B251E74B5A52D');
        $this->addSql('ALTER TABLE player DROP FOREIGN KEY FK_98197A6574B5A52D');
        $this->addSql('ALTER TABLE room DROP FOREIGN KEY FK_729F519B74B5A52D');
        $this->addSql('ALTER TABLE room_config DROP FOREIGN KEY FK_95C4E9BD201E3C43');
        $this->addSql('ALTER TABLE door_room DROP FOREIGN KEY FK_3CC1304A58639EAE');
        $this->addSql('ALTER TABLE room_door DROP FOREIGN KEY FK_E9E1BE4958639EAE');
        $this->addSql('ALTER TABLE character_config DROP FOREIGN KEY FK_77C8ECEFF67DC781');
        $this->addSql('ALTER TABLE daedalus DROP FOREIGN KEY FK_71DA760AF67DC781');
        $this->addSql('ALTER TABLE daedalus_config DROP FOREIGN KEY FK_4474D96CF67DC781');
        $this->addSql('ALTER TABLE item DROP FOREIGN KEY FK_1F1B251EF67DC781');
        $this->addSql('ALTER TABLE room_log DROP FOREIGN KEY FK_8DB9D5D8126F525E');
        $this->addSql('ALTER TABLE game_item DROP FOREIGN KEY FK_F40E4932126F525E');
        $this->addSql('ALTER TABLE item DROP FOREIGN KEY FK_1F1B251EBAC115F0');
        $this->addSql('ALTER TABLE game_item DROP FOREIGN KEY FK_F40E493299E6F5DF');
        $this->addSql('ALTER TABLE room_log DROP FOREIGN KEY FK_8DB9D5D899E6F5DF');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6494E825C80');
        $this->addSql('ALTER TABLE daedalus_config DROP FOREIGN KEY FK_4474D96C5E67882C');
        $this->addSql('ALTER TABLE door_room DROP FOREIGN KEY FK_3CC1304A54177093');
        $this->addSql('ALTER TABLE game_item DROP FOREIGN KEY FK_F40E493254177093');
        $this->addSql('ALTER TABLE player DROP FOREIGN KEY FK_98197A6554177093');
        $this->addSql('ALTER TABLE room_door DROP FOREIGN KEY FK_E9E1BE4954177093');
        $this->addSql('ALTER TABLE room_log DROP FOREIGN KEY FK_8DB9D5D854177093');
        $this->addSql('ALTER TABLE player DROP FOREIGN KEY FK_98197A65A76ED395');
        $this->addSql('DROP TABLE character_config');
        $this->addSql('DROP TABLE daedalus');
        $this->addSql('DROP TABLE daedalus_config');
        $this->addSql('DROP TABLE door');
        $this->addSql('DROP TABLE door_room');
        $this->addSql('DROP TABLE game_config');
        $this->addSql('DROP TABLE game_item');
        $this->addSql('DROP TABLE item');
        $this->addSql('DROP TABLE player');
        $this->addSql('DROP TABLE random_item_places');
        $this->addSql('DROP TABLE room');
        $this->addSql('DROP TABLE room_door');
        $this->addSql('DROP TABLE room_config');
        $this->addSql('DROP TABLE room_log');
        $this->addSql('DROP TABLE user');
    }
}
