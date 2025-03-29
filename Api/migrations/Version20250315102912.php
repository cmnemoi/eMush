<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250315102912 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE trade_asset_config_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE trade_config_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE trade_option_config_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE game_config_trade_config (game_config_id INT NOT NULL, trade_config_id INT NOT NULL, PRIMARY KEY(game_config_id, trade_config_id))');
        $this->addSql('CREATE INDEX IDX_C5900B48F67DC781 ON game_config_trade_config (game_config_id)');
        $this->addSql('CREATE INDEX IDX_C5900B4838D6AFE ON game_config_trade_config (trade_config_id)');
        $this->addSql('CREATE TABLE trade_asset_config (id INT NOT NULL, trade_option_config_required_id INT DEFAULT NULL, trade_option_config_offered_id INT DEFAULT NULL, type VARCHAR(255) DEFAULT \'\' NOT NULL, min_quantity INT NOT NULL, max_quantity INT NOT NULL, asset_name VARCHAR(255) DEFAULT \'\' NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_2BCE8B2FE1087CB1 ON trade_asset_config (trade_option_config_required_id)');
        $this->addSql('CREATE INDEX IDX_2BCE8B2F2F0D727E ON trade_asset_config (trade_option_config_offered_id)');
        $this->addSql('CREATE TABLE trade_config (id INT NOT NULL, key VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EDA3916E8A90ABA9 ON trade_config (key)');
        $this->addSql('CREATE TABLE trade_option_config (id INT NOT NULL, trade_config_id INT DEFAULT NULL, required_skill VARCHAR(255) DEFAULT \'\' NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_6A9281738D6AFE ON trade_option_config (trade_config_id)');
        $this->addSql('ALTER TABLE game_config_trade_config ADD CONSTRAINT FK_C5900B48F67DC781 FOREIGN KEY (game_config_id) REFERENCES config_game (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE game_config_trade_config ADD CONSTRAINT FK_C5900B4838D6AFE FOREIGN KEY (trade_config_id) REFERENCES trade_config (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE trade_asset_config ADD CONSTRAINT FK_2BCE8B2FE1087CB1 FOREIGN KEY (trade_option_config_required_id) REFERENCES trade_option_config (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE trade_asset_config ADD CONSTRAINT FK_2BCE8B2F2F0D727E FOREIGN KEY (trade_option_config_offered_id) REFERENCES trade_option_config (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE trade_option_config ADD CONSTRAINT FK_6A9281738D6AFE FOREIGN KEY (trade_config_id) REFERENCES trade_config (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE trade_asset_config_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE trade_config_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE trade_option_config_id_seq CASCADE');
        $this->addSql('ALTER TABLE game_config_trade_config DROP CONSTRAINT FK_C5900B48F67DC781');
        $this->addSql('ALTER TABLE game_config_trade_config DROP CONSTRAINT FK_C5900B4838D6AFE');
        $this->addSql('ALTER TABLE trade_asset_config DROP CONSTRAINT FK_2BCE8B2FE1087CB1');
        $this->addSql('ALTER TABLE trade_asset_config DROP CONSTRAINT FK_2BCE8B2F2F0D727E');
        $this->addSql('ALTER TABLE trade_option_config DROP CONSTRAINT FK_6A9281738D6AFE');
        $this->addSql('DROP TABLE game_config_trade_config');
        $this->addSql('DROP TABLE trade_asset_config');
        $this->addSql('DROP TABLE trade_config');
        $this->addSql('DROP TABLE trade_option_config');
    }
}
