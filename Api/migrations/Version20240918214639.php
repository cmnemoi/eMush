<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240918214639 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE skin_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE skin_slot_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE skin_slot_config_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE unlock_condition_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE character_config_skin_slot_config (character_config_id INT NOT NULL, skin_slot_config_id INT NOT NULL, PRIMARY KEY(character_config_id, skin_slot_config_id))');
        $this->addSql('CREATE INDEX IDX_943ED33A38BA4B8 ON character_config_skin_slot_config (character_config_id)');
        $this->addSql('CREATE INDEX IDX_943ED3356BA3245 ON character_config_skin_slot_config (skin_slot_config_id)');
        $this->addSql('CREATE TABLE equipment_config_skin_slot_config (equipment_config_id INT NOT NULL, skin_slot_config_id INT NOT NULL, PRIMARY KEY(equipment_config_id, skin_slot_config_id))');
        $this->addSql('CREATE INDEX IDX_78A05730F6E640FA ON equipment_config_skin_slot_config (equipment_config_id)');
        $this->addSql('CREATE INDEX IDX_78A0573056BA3245 ON equipment_config_skin_slot_config (skin_slot_config_id)');
        $this->addSql('CREATE TABLE game_equipment_skin_slot (game_equipment_id INT NOT NULL, skin_slot_id INT NOT NULL, PRIMARY KEY(game_equipment_id, skin_slot_id))');
        $this->addSql('CREATE INDEX IDX_9075EA06BFAFDD90 ON game_equipment_skin_slot (game_equipment_id)');
        $this->addSql('CREATE INDEX IDX_9075EA06AA28DE13 ON game_equipment_skin_slot (skin_slot_id)');
        $this->addSql('CREATE TABLE place_config_skin_slot_config (place_config_id INT NOT NULL, skin_slot_config_id INT NOT NULL, PRIMARY KEY(place_config_id, skin_slot_config_id))');
        $this->addSql('CREATE INDEX IDX_D598D85150408FFE ON place_config_skin_slot_config (place_config_id)');
        $this->addSql('CREATE INDEX IDX_D598D85156BA3245 ON place_config_skin_slot_config (skin_slot_config_id)');
        $this->addSql('CREATE TABLE player_skin_slot (player_id INT NOT NULL, skin_slot_id INT NOT NULL, PRIMARY KEY(player_id, skin_slot_id))');
        $this->addSql('CREATE INDEX IDX_1973A21799E6F5DF ON player_skin_slot (player_id)');
        $this->addSql('CREATE INDEX IDX_1973A217AA28DE13 ON player_skin_slot (skin_slot_id)');
        $this->addSql('CREATE TABLE place_skin_slot (place_id INT NOT NULL, skin_slot_id INT NOT NULL, PRIMARY KEY(place_id, skin_slot_id))');
        $this->addSql('CREATE INDEX IDX_CDE41E53DA6A219 ON place_skin_slot (place_id)');
        $this->addSql('CREATE INDEX IDX_CDE41E53AA28DE13 ON place_skin_slot (skin_slot_id)');
        $this->addSql('CREATE TABLE skin (id INT NOT NULL, unlock_condition_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_279681E5E237E06 ON skin (name)');
        $this->addSql('CREATE INDEX IDX_279681E7E02C61B ON skin (unlock_condition_id)');
        $this->addSql('CREATE TABLE skin_slot (id INT NOT NULL, skin_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B97F972E5E237E06 ON skin_slot (name)');
        $this->addSql('CREATE INDEX IDX_B97F972EF404637F ON skin_slot (skin_id)');
        $this->addSql('CREATE TABLE skin_slot_config (id INT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A9A0DACB5E237E06 ON skin_slot_config (name)');
        $this->addSql('CREATE TABLE skin_slot_config_skin (skin_slot_config_id INT NOT NULL, skin_id INT NOT NULL, PRIMARY KEY(skin_slot_config_id, skin_id))');
        $this->addSql('CREATE INDEX IDX_2ECDC2F256BA3245 ON skin_slot_config_skin (skin_slot_config_id)');
        $this->addSql('CREATE INDEX IDX_2ECDC2F2F404637F ON skin_slot_config_skin (skin_id)');
        $this->addSql('CREATE TABLE unlock_condition (id INT NOT NULL, name VARCHAR(255) NOT NULL, strategy VARCHAR(255) NOT NULL, value VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE unlocked_skins (user_id INT NOT NULL, skin_id INT NOT NULL, PRIMARY KEY(user_id, skin_id))');
        $this->addSql('CREATE INDEX IDX_7CFB916AA76ED395 ON unlocked_skins (user_id)');
        $this->addSql('CREATE INDEX IDX_7CFB916AF404637F ON unlocked_skins (skin_id)');
        $this->addSql('ALTER TABLE character_config_skin_slot_config ADD CONSTRAINT FK_943ED33A38BA4B8 FOREIGN KEY (character_config_id) REFERENCES character_config (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE character_config_skin_slot_config ADD CONSTRAINT FK_943ED3356BA3245 FOREIGN KEY (skin_slot_config_id) REFERENCES skin_slot_config (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE equipment_config_skin_slot_config ADD CONSTRAINT FK_78A05730F6E640FA FOREIGN KEY (equipment_config_id) REFERENCES equipment_config (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE equipment_config_skin_slot_config ADD CONSTRAINT FK_78A0573056BA3245 FOREIGN KEY (skin_slot_config_id) REFERENCES skin_slot_config (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE game_equipment_skin_slot ADD CONSTRAINT FK_9075EA06BFAFDD90 FOREIGN KEY (game_equipment_id) REFERENCES game_equipment (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE game_equipment_skin_slot ADD CONSTRAINT FK_9075EA06AA28DE13 FOREIGN KEY (skin_slot_id) REFERENCES skin_slot (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE place_config_skin_slot_config ADD CONSTRAINT FK_D598D85150408FFE FOREIGN KEY (place_config_id) REFERENCES place_config (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE place_config_skin_slot_config ADD CONSTRAINT FK_D598D85156BA3245 FOREIGN KEY (skin_slot_config_id) REFERENCES skin_slot_config (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE player_skin_slot ADD CONSTRAINT FK_1973A21799E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE player_skin_slot ADD CONSTRAINT FK_1973A217AA28DE13 FOREIGN KEY (skin_slot_id) REFERENCES skin_slot (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE place_skin_slot ADD CONSTRAINT FK_CDE41E53DA6A219 FOREIGN KEY (place_id) REFERENCES room (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE place_skin_slot ADD CONSTRAINT FK_CDE41E53AA28DE13 FOREIGN KEY (skin_slot_id) REFERENCES skin_slot (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE skin ADD CONSTRAINT FK_279681E7E02C61B FOREIGN KEY (unlock_condition_id) REFERENCES unlock_condition (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE skin_slot ADD CONSTRAINT FK_B97F972EF404637F FOREIGN KEY (skin_id) REFERENCES skin (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE skin_slot_config_skin ADD CONSTRAINT FK_2ECDC2F256BA3245 FOREIGN KEY (skin_slot_config_id) REFERENCES skin_slot_config (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE skin_slot_config_skin ADD CONSTRAINT FK_2ECDC2F2F404637F FOREIGN KEY (skin_id) REFERENCES skin (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE unlocked_skins ADD CONSTRAINT FK_7CFB916AA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE unlocked_skins ADD CONSTRAINT FK_7CFB916AF404637F FOREIGN KEY (skin_id) REFERENCES skin (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE status ALTER content TYPE TEXT');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE skin_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE skin_slot_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE skin_slot_config_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE unlock_condition_id_seq CASCADE');
        $this->addSql('ALTER TABLE character_config_skin_slot_config DROP CONSTRAINT FK_943ED33A38BA4B8');
        $this->addSql('ALTER TABLE character_config_skin_slot_config DROP CONSTRAINT FK_943ED3356BA3245');
        $this->addSql('ALTER TABLE equipment_config_skin_slot_config DROP CONSTRAINT FK_78A05730F6E640FA');
        $this->addSql('ALTER TABLE equipment_config_skin_slot_config DROP CONSTRAINT FK_78A0573056BA3245');
        $this->addSql('ALTER TABLE game_equipment_skin_slot DROP CONSTRAINT FK_9075EA06BFAFDD90');
        $this->addSql('ALTER TABLE game_equipment_skin_slot DROP CONSTRAINT FK_9075EA06AA28DE13');
        $this->addSql('ALTER TABLE place_config_skin_slot_config DROP CONSTRAINT FK_D598D85150408FFE');
        $this->addSql('ALTER TABLE place_config_skin_slot_config DROP CONSTRAINT FK_D598D85156BA3245');
        $this->addSql('ALTER TABLE player_skin_slot DROP CONSTRAINT FK_1973A21799E6F5DF');
        $this->addSql('ALTER TABLE player_skin_slot DROP CONSTRAINT FK_1973A217AA28DE13');
        $this->addSql('ALTER TABLE place_skin_slot DROP CONSTRAINT FK_CDE41E53DA6A219');
        $this->addSql('ALTER TABLE place_skin_slot DROP CONSTRAINT FK_CDE41E53AA28DE13');
        $this->addSql('ALTER TABLE skin DROP CONSTRAINT FK_279681E7E02C61B');
        $this->addSql('ALTER TABLE skin_slot DROP CONSTRAINT FK_B97F972EF404637F');
        $this->addSql('ALTER TABLE skin_slot_config_skin DROP CONSTRAINT FK_2ECDC2F256BA3245');
        $this->addSql('ALTER TABLE skin_slot_config_skin DROP CONSTRAINT FK_2ECDC2F2F404637F');
        $this->addSql('ALTER TABLE unlocked_skins DROP CONSTRAINT FK_7CFB916AA76ED395');
        $this->addSql('ALTER TABLE unlocked_skins DROP CONSTRAINT FK_7CFB916AF404637F');
        $this->addSql('DROP TABLE character_config_skin_slot_config');
        $this->addSql('DROP TABLE equipment_config_skin_slot_config');
        $this->addSql('DROP TABLE game_equipment_skin_slot');
        $this->addSql('DROP TABLE place_config_skin_slot_config');
        $this->addSql('DROP TABLE player_skin_slot');
        $this->addSql('DROP TABLE place_skin_slot');
        $this->addSql('DROP TABLE skin');
        $this->addSql('DROP TABLE skin_slot');
        $this->addSql('DROP TABLE skin_slot_config');
        $this->addSql('DROP TABLE skin_slot_config_skin');
        $this->addSql('DROP TABLE unlock_condition');
        $this->addSql('DROP TABLE unlocked_skins');
        $this->addSql('ALTER TABLE status ALTER content TYPE VARCHAR(255)');
    }
}
