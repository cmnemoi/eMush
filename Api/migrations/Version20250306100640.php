<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250306100640 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE trade_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE trade_asset_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE trade_option_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE trade (id INT NOT NULL, transport_id INT DEFAULT NULL, name VARCHAR(255) DEFAULT \'\' NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7E1A43669909C13F ON trade (transport_id)');
        $this->addSql('CREATE TABLE trade_asset (id INT NOT NULL, required_trade_option_id INT DEFAULT NULL, offered_trade_option_id INT DEFAULT NULL, type VARCHAR(255) DEFAULT \'\' NOT NULL, asset_name VARCHAR(255) DEFAULT \'\' NOT NULL, quantity INT DEFAULT 0 NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_28A28ABFD197FFA4 ON trade_asset (required_trade_option_id)');
        $this->addSql('CREATE INDEX IDX_28A28ABFBC61EF17 ON trade_asset (offered_trade_option_id)');
        $this->addSql('CREATE TABLE trade_option (id INT NOT NULL, trade_id INT DEFAULT NULL, required_skill VARCHAR(255) DEFAULT \'\' NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_63AFBEA2C2D9760 ON trade_option (trade_id)');
        $this->addSql('ALTER TABLE trade ADD CONSTRAINT FK_7E1A43669909C13F FOREIGN KEY (transport_id) REFERENCES hunter (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE trade_asset ADD CONSTRAINT FK_28A28ABFD197FFA4 FOREIGN KEY (required_trade_option_id) REFERENCES trade_option (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE trade_asset ADD CONSTRAINT FK_28A28ABFBC61EF17 FOREIGN KEY (offered_trade_option_id) REFERENCES trade_option (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE trade_option ADD CONSTRAINT FK_63AFBEA2C2D9760 FOREIGN KEY (trade_id) REFERENCES trade (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE hunter_config ALTER name SET DEFAULT \'\'');
        $this->addSql('ALTER TABLE hunter_config ALTER hunter_name SET DEFAULT \'\'');
        $this->addSql('ALTER TABLE hunter_config ALTER initial_health SET DEFAULT 0');
        $this->addSql('ALTER TABLE hunter_config ALTER damage_range SET DEFAULT \'a:0:{}\'');
        $this->addSql('ALTER TABLE hunter_config ALTER hit_chance SET DEFAULT 0');
        $this->addSql('ALTER TABLE hunter_config ALTER dodge_chance SET DEFAULT 0');
        $this->addSql('ALTER TABLE hunter_config ALTER draw_cost SET DEFAULT 0');
        $this->addSql('ALTER TABLE hunter_config ALTER draw_weight SET DEFAULT 0');
        $this->addSql('ALTER TABLE hunter_config ALTER spawn_difficulty SET DEFAULT 0');
        $this->addSql('ALTER TABLE hunter_config ALTER scrap_drop_table SET DEFAULT \'a:0:{}\'');
        $this->addSql('ALTER TABLE hunter_config ALTER number_of_dropped_scrap SET DEFAULT \'a:0:{}\'');
        $this->addSql('ALTER TABLE hunter_config ALTER target_probabilities SET DEFAULT \'a:0:{}\'');
        $this->addSql('ALTER TABLE hunter_config ALTER number_of_actions_per_cycle SET DEFAULT 0');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE trade_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE trade_asset_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE trade_option_id_seq CASCADE');
        $this->addSql('ALTER TABLE trade DROP CONSTRAINT FK_7E1A43669909C13F');
        $this->addSql('ALTER TABLE trade_asset DROP CONSTRAINT FK_28A28ABFD197FFA4');
        $this->addSql('ALTER TABLE trade_asset DROP CONSTRAINT FK_28A28ABFBC61EF17');
        $this->addSql('ALTER TABLE trade_option DROP CONSTRAINT FK_63AFBEA2C2D9760');
        $this->addSql('DROP TABLE trade');
        $this->addSql('DROP TABLE trade_asset');
        $this->addSql('DROP TABLE trade_option');
        $this->addSql('ALTER TABLE hunter_config ALTER name DROP DEFAULT');
        $this->addSql('ALTER TABLE hunter_config ALTER hunter_name DROP DEFAULT');
        $this->addSql('ALTER TABLE hunter_config ALTER initial_health DROP DEFAULT');
        $this->addSql('ALTER TABLE hunter_config ALTER damage_range SET DEFAULT \'[]\'');
        $this->addSql('ALTER TABLE hunter_config ALTER hit_chance DROP DEFAULT');
        $this->addSql('ALTER TABLE hunter_config ALTER dodge_chance DROP DEFAULT');
        $this->addSql('ALTER TABLE hunter_config ALTER draw_cost DROP DEFAULT');
        $this->addSql('ALTER TABLE hunter_config ALTER draw_weight DROP DEFAULT');
        $this->addSql('ALTER TABLE hunter_config ALTER spawn_difficulty DROP DEFAULT');
        $this->addSql('ALTER TABLE hunter_config ALTER scrap_drop_table SET DEFAULT \'[]\'');
        $this->addSql('ALTER TABLE hunter_config ALTER number_of_dropped_scrap SET DEFAULT \'[]\'');
        $this->addSql('ALTER TABLE hunter_config ALTER target_probabilities DROP DEFAULT');
        $this->addSql('ALTER TABLE hunter_config ALTER number_of_actions_per_cycle SET DEFAULT 1');
    }
}
