<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250831164437 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE UNIQUE INDEX unique_modifier_config_holder_provider ON game_modifier (modifier_config_id, modifier_holder_id, modifier_provider_id)');
        $this->addSql('ALTER TABLE modifier_holder DROP CONSTRAINT FK_17FE980174B5A52D');
        $this->addSql('ALTER TABLE modifier_holder DROP CONSTRAINT FK_17FE980199E6F5DF');
        $this->addSql('ALTER TABLE modifier_holder DROP CONSTRAINT FK_17FE9801BFAFDD90');
        $this->addSql('ALTER TABLE modifier_holder DROP CONSTRAINT FK_17FE9801DA6A219');
        $this->addSql('ALTER TABLE modifier_holder ADD CONSTRAINT FK_17FE980174B5A52D FOREIGN KEY (daedalus_id) REFERENCES daedalus (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE modifier_holder ADD CONSTRAINT FK_17FE980199E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE modifier_holder ADD CONSTRAINT FK_17FE9801BFAFDD90 FOREIGN KEY (game_equipment_id) REFERENCES game_equipment (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE modifier_holder ADD CONSTRAINT FK_17FE9801DA6A219 FOREIGN KEY (place_id) REFERENCES room (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE modifier_provider DROP CONSTRAINT FK_6709A162166D1F9C');
        $this->addSql('ALTER TABLE modifier_provider DROP CONSTRAINT FK_6709A1626BF700BD');
        $this->addSql('ALTER TABLE modifier_provider DROP CONSTRAINT FK_6709A1628790D77C');
        $this->addSql('ALTER TABLE modifier_provider DROP CONSTRAINT FK_6709A1629801E77D');
        $this->addSql('ALTER TABLE modifier_provider DROP CONSTRAINT FK_6709A16299E6F5DF');
        $this->addSql('ALTER TABLE modifier_provider DROP CONSTRAINT FK_6709A162BFAFDD90');
        $this->addSql('ALTER TABLE modifier_provider DROP CONSTRAINT FK_6709A162DA5DC7EE');
        $this->addSql('ALTER TABLE modifier_provider ADD CONSTRAINT FK_6709A162166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE modifier_provider ADD CONSTRAINT FK_6709A1626BF700BD FOREIGN KEY (status_id) REFERENCES status (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE modifier_provider ADD CONSTRAINT FK_6709A1628790D77C FOREIGN KEY (player_disease_id) REFERENCES disease_player (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE modifier_provider ADD CONSTRAINT FK_6709A1629801E77D FOREIGN KEY (rebel_base_id) REFERENCES rebel_base (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE modifier_provider ADD CONSTRAINT FK_6709A16299E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE modifier_provider ADD CONSTRAINT FK_6709A162BFAFDD90 FOREIGN KEY (game_equipment_id) REFERENCES game_equipment (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE modifier_provider ADD CONSTRAINT FK_6709A162DA5DC7EE FOREIGN KEY (xyloph_entry_id) REFERENCES xyloph_entry (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX unique_modifier_config_holder_provider');
        $this->addSql('ALTER TABLE modifier_provider DROP CONSTRAINT fk_6709a16299e6f5df');
        $this->addSql('ALTER TABLE modifier_provider DROP CONSTRAINT fk_6709a162bfafdd90');
        $this->addSql('ALTER TABLE modifier_provider DROP CONSTRAINT fk_6709a162166d1f9c');
        $this->addSql('ALTER TABLE modifier_provider DROP CONSTRAINT fk_6709a1626bf700bd');
        $this->addSql('ALTER TABLE modifier_provider DROP CONSTRAINT fk_6709a1629801e77d');
        $this->addSql('ALTER TABLE modifier_provider DROP CONSTRAINT fk_6709a162da5dc7ee');
        $this->addSql('ALTER TABLE modifier_provider DROP CONSTRAINT fk_6709a1628790d77c');
        $this->addSql('ALTER TABLE modifier_provider ADD CONSTRAINT fk_6709a16299e6f5df FOREIGN KEY (player_id) REFERENCES player (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE modifier_provider ADD CONSTRAINT fk_6709a162bfafdd90 FOREIGN KEY (game_equipment_id) REFERENCES game_equipment (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE modifier_provider ADD CONSTRAINT fk_6709a162166d1f9c FOREIGN KEY (project_id) REFERENCES project (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE modifier_provider ADD CONSTRAINT fk_6709a1626bf700bd FOREIGN KEY (status_id) REFERENCES status (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE modifier_provider ADD CONSTRAINT fk_6709a1629801e77d FOREIGN KEY (rebel_base_id) REFERENCES rebel_base (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE modifier_provider ADD CONSTRAINT fk_6709a162da5dc7ee FOREIGN KEY (xyloph_entry_id) REFERENCES xyloph_entry (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE modifier_provider ADD CONSTRAINT fk_6709a1628790d77c FOREIGN KEY (player_disease_id) REFERENCES disease_player (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE modifier_holder DROP CONSTRAINT fk_17fe980199e6f5df');
        $this->addSql('ALTER TABLE modifier_holder DROP CONSTRAINT fk_17fe9801da6a219');
        $this->addSql('ALTER TABLE modifier_holder DROP CONSTRAINT fk_17fe9801bfafdd90');
        $this->addSql('ALTER TABLE modifier_holder DROP CONSTRAINT fk_17fe980174b5a52d');
        $this->addSql('ALTER TABLE modifier_holder ADD CONSTRAINT fk_17fe980199e6f5df FOREIGN KEY (player_id) REFERENCES player (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE modifier_holder ADD CONSTRAINT fk_17fe9801da6a219 FOREIGN KEY (place_id) REFERENCES room (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE modifier_holder ADD CONSTRAINT fk_17fe9801bfafdd90 FOREIGN KEY (game_equipment_id) REFERENCES game_equipment (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE modifier_holder ADD CONSTRAINT fk_17fe980174b5a52d FOREIGN KEY (daedalus_id) REFERENCES daedalus (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
