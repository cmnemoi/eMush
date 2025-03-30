<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250330202432 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE trade_option_required_assets (trade_option_id INT NOT NULL, trade_asset_id INT NOT NULL, PRIMARY KEY(trade_option_id, trade_asset_id))');
        $this->addSql('CREATE INDEX IDX_595280FF80927112 ON trade_option_required_assets (trade_option_id)');
        $this->addSql('CREATE INDEX IDX_595280FF51F7CE30 ON trade_option_required_assets (trade_asset_id)');
        $this->addSql('CREATE TABLE trade_option_offered_assets (trade_option_id INT NOT NULL, trade_asset_id INT NOT NULL, PRIMARY KEY(trade_option_id, trade_asset_id))');
        $this->addSql('CREATE INDEX IDX_D44417B780927112 ON trade_option_offered_assets (trade_option_id)');
        $this->addSql('CREATE INDEX IDX_D44417B751F7CE30 ON trade_option_offered_assets (trade_asset_id)');
        $this->addSql('ALTER TABLE trade_option_required_assets ADD CONSTRAINT FK_595280FF80927112 FOREIGN KEY (trade_option_id) REFERENCES trade_option_config (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE trade_option_required_assets ADD CONSTRAINT FK_595280FF51F7CE30 FOREIGN KEY (trade_asset_id) REFERENCES trade_asset_config (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE trade_option_offered_assets ADD CONSTRAINT FK_D44417B780927112 FOREIGN KEY (trade_option_id) REFERENCES trade_option_config (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE trade_option_offered_assets ADD CONSTRAINT FK_D44417B751F7CE30 FOREIGN KEY (trade_asset_id) REFERENCES trade_asset_config (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE trade_asset_config DROP CONSTRAINT fk_2bce8b2fe1087cb1');
        $this->addSql('ALTER TABLE trade_asset_config DROP CONSTRAINT fk_2bce8b2f2f0d727e');
        $this->addSql('DROP INDEX idx_2bce8b2f2f0d727e');
        $this->addSql('DROP INDEX idx_2bce8b2fe1087cb1');
        $this->addSql('ALTER TABLE trade_asset_config DROP trade_option_config_required_id');
        $this->addSql('ALTER TABLE trade_asset_config DROP trade_option_config_offered_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE trade_option_required_assets DROP CONSTRAINT FK_595280FF80927112');
        $this->addSql('ALTER TABLE trade_option_required_assets DROP CONSTRAINT FK_595280FF51F7CE30');
        $this->addSql('ALTER TABLE trade_option_offered_assets DROP CONSTRAINT FK_D44417B780927112');
        $this->addSql('ALTER TABLE trade_option_offered_assets DROP CONSTRAINT FK_D44417B751F7CE30');
        $this->addSql('DROP TABLE trade_option_required_assets');
        $this->addSql('DROP TABLE trade_option_offered_assets');
        $this->addSql('ALTER TABLE trade_asset_config ADD trade_option_config_required_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE trade_asset_config ADD trade_option_config_offered_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE trade_asset_config ADD CONSTRAINT fk_2bce8b2fe1087cb1 FOREIGN KEY (trade_option_config_required_id) REFERENCES trade_option_config (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE trade_asset_config ADD CONSTRAINT fk_2bce8b2f2f0d727e FOREIGN KEY (trade_option_config_offered_id) REFERENCES trade_option_config (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_2bce8b2f2f0d727e ON trade_asset_config (trade_option_config_offered_id)');
        $this->addSql('CREATE INDEX idx_2bce8b2fe1087cb1 ON trade_asset_config (trade_option_config_required_id)');
    }
}
