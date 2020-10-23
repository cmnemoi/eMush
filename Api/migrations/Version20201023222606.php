<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201023222606 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE item ADD game_fruit_id INT DEFAULT NULL, ADD game_plant_id INT DEFAULT NULL, ADD charge INT DEFAULT NULL');
        $this->addSql('ALTER TABLE item ADD CONSTRAINT FK_1F1B251E1185EAD6 FOREIGN KEY (game_fruit_id) REFERENCES game_fruit (id)');
        $this->addSql('ALTER TABLE item ADD CONSTRAINT FK_1F1B251EB6D7A974 FOREIGN KEY (game_plant_id) REFERENCES game_plant (id)');
        $this->addSql('CREATE INDEX IDX_1F1B251E1185EAD6 ON item (game_fruit_id)');
        $this->addSql('CREATE INDEX IDX_1F1B251EB6D7A974 ON item (game_plant_id)');
        $this->addSql('ALTER TABLE room_log ADD item_id INT DEFAULT NULL, CHANGE id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE room_log ADD CONSTRAINT FK_8DB9D5D8126F525E FOREIGN KEY (item_id) REFERENCES item (id)');
        $this->addSql('CREATE INDEX IDX_8DB9D5D8126F525E ON room_log (item_id)');
        $this->addSql('ALTER TABLE user ADD current_game_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6494E825C80 FOREIGN KEY (current_game_id) REFERENCES player (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D6494E825C80 ON user (current_game_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE item DROP FOREIGN KEY FK_1F1B251E1185EAD6');
        $this->addSql('ALTER TABLE item DROP FOREIGN KEY FK_1F1B251EB6D7A974');
        $this->addSql('DROP INDEX IDX_1F1B251E1185EAD6 ON item');
        $this->addSql('DROP INDEX IDX_1F1B251EB6D7A974 ON item');
        $this->addSql('ALTER TABLE item DROP game_fruit_id, DROP game_plant_id, DROP charge');
        $this->addSql('ALTER TABLE room_log DROP FOREIGN KEY FK_8DB9D5D8126F525E');
        $this->addSql('DROP INDEX IDX_8DB9D5D8126F525E ON room_log');
        $this->addSql('ALTER TABLE room_log DROP item_id, CHANGE id id INT NOT NULL');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6494E825C80');
        $this->addSql('DROP INDEX UNIQ_8D93D6494E825C80 ON user');
        $this->addSql('ALTER TABLE user DROP current_game_id');
    }
}
