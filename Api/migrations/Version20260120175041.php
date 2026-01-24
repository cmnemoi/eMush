<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260120175041 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE player_disease_abstract_modifier_config (player_disease_id INT NOT NULL, abstract_modifier_config_id INT NOT NULL, PRIMARY KEY(player_disease_id, abstract_modifier_config_id))');
        $this->addSql('CREATE INDEX IDX_E926DDC98790D77C ON player_disease_abstract_modifier_config (player_disease_id)');
        $this->addSql('CREATE INDEX IDX_E926DDC9BFA8DC8C ON player_disease_abstract_modifier_config (abstract_modifier_config_id)');
        $this->addSql('ALTER TABLE player_disease_abstract_modifier_config ADD CONSTRAINT FK_E926DDC98790D77C FOREIGN KEY (player_disease_id) REFERENCES disease_player (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE player_disease_abstract_modifier_config ADD CONSTRAINT FK_E926DDC9BFA8DC8C FOREIGN KEY (abstract_modifier_config_id) REFERENCES abstract_modifier_config (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE disease_config_abstract_modifier_config DROP CONSTRAINT fk_65a5eefe1998f6f9');
        $this->addSql('ALTER TABLE disease_config_abstract_modifier_config DROP CONSTRAINT fk_65a5eefebfa8dc8c');
        $this->addSql('DROP TABLE disease_config_abstract_modifier_config');
        $this->addSql('ALTER TABLE disease_config ADD can_heal_naturally BOOLEAN DEFAULT true NOT NULL');
        $this->addSql('ALTER TABLE disease_config ADD duration TEXT DEFAULT \'a:0:{}\' NOT NULL');
        $this->addSql('ALTER TABLE disease_config ADD heal_action_resistance INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE disease_config ADD mush_can_have BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE disease_config ADD remove_lower TEXT DEFAULT \'a:0:{}\' NOT NULL');
        $this->addSql('ALTER TABLE disease_config ADD event_when_appeared VARCHAR(255) DEFAULT \'none\' NOT NULL');
        $this->addSql('ALTER TABLE disease_config DROP resistance');
        $this->addSql('ALTER TABLE disease_config DROP delay_min');
        $this->addSql('ALTER TABLE disease_config DROP delay_length');
        $this->addSql('ALTER TABLE disease_config DROP disease_point_min');
        $this->addSql('ALTER TABLE disease_config DROP disease_point_length');
        $this->addSql('ALTER TABLE disease_config ALTER type SET DEFAULT \'disease\'');
        $this->addSql('ALTER TABLE disease_config RENAME COLUMN override TO modifier_configs');
        $this->addSql('COMMENT ON COLUMN disease_config.duration IS \'(DC2Type:array)\'');
        $this->addSql('COMMENT ON COLUMN disease_config.remove_lower IS \'(DC2Type:array)\'');
        $this->addSql('ALTER TABLE disease_player ADD duration INT NOT NULL');
        $this->addSql('ALTER TABLE disease_player ADD heal_action_resistance INT NOT NULL');
        $this->addSql('ALTER TABLE disease_player DROP disease_point');
        $this->addSql('ALTER TABLE disease_player DROP resistance_point');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE disease_config_abstract_modifier_config (disease_config_id INT NOT NULL, abstract_modifier_config_id INT NOT NULL, PRIMARY KEY(disease_config_id, abstract_modifier_config_id))');
        $this->addSql('CREATE INDEX idx_65a5eefe1998f6f9 ON disease_config_abstract_modifier_config (disease_config_id)');
        $this->addSql('CREATE INDEX idx_65a5eefebfa8dc8c ON disease_config_abstract_modifier_config (abstract_modifier_config_id)');
        $this->addSql('ALTER TABLE disease_config_abstract_modifier_config ADD CONSTRAINT fk_65a5eefe1998f6f9 FOREIGN KEY (disease_config_id) REFERENCES disease_config (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE disease_config_abstract_modifier_config ADD CONSTRAINT fk_65a5eefebfa8dc8c FOREIGN KEY (abstract_modifier_config_id) REFERENCES abstract_modifier_config (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE player_disease_abstract_modifier_config DROP CONSTRAINT FK_E926DDC98790D77C');
        $this->addSql('ALTER TABLE player_disease_abstract_modifier_config DROP CONSTRAINT FK_E926DDC9BFA8DC8C');
        $this->addSql('DROP TABLE player_disease_abstract_modifier_config');
        $this->addSql('ALTER TABLE disease_config ADD resistance INT NOT NULL');
        $this->addSql('ALTER TABLE disease_config ADD delay_min INT NOT NULL');
        $this->addSql('ALTER TABLE disease_config ADD delay_length INT NOT NULL');
        $this->addSql('ALTER TABLE disease_config ADD disease_point_min INT NOT NULL');
        $this->addSql('ALTER TABLE disease_config ADD disease_point_length INT NOT NULL');
        $this->addSql('ALTER TABLE disease_config DROP can_heal_naturally');
        $this->addSql('ALTER TABLE disease_config DROP duration');
        $this->addSql('ALTER TABLE disease_config DROP heal_action_resistance');
        $this->addSql('ALTER TABLE disease_config DROP mush_can_have');
        $this->addSql('ALTER TABLE disease_config DROP remove_lower');
        $this->addSql('ALTER TABLE disease_config DROP event_when_appeared');
        $this->addSql('ALTER TABLE disease_config ALTER type DROP DEFAULT');
        $this->addSql('ALTER TABLE disease_config RENAME COLUMN modifier_configs TO override');
        $this->addSql('ALTER TABLE disease_player ADD disease_point INT NOT NULL');
        $this->addSql('ALTER TABLE disease_player ADD resistance_point INT NOT NULL');
        $this->addSql('ALTER TABLE disease_player DROP duration');
        $this->addSql('ALTER TABLE disease_player DROP heal_action_resistance');
    }
}
