<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240511083402 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE character_config_action_config (character_config_id INT NOT NULL, action_config_id INT NOT NULL, PRIMARY KEY(character_config_id, action_config_id))');
        $this->addSql('CREATE INDEX IDX_55422528A38BA4B8 ON character_config_action_config (character_config_id)');
        $this->addSql('CREATE INDEX IDX_5542252880DD159E ON character_config_action_config (action_config_id)');
        $this->addSql('CREATE TABLE equipment_config_action_config (equipment_config_id INT NOT NULL, action_config_id INT NOT NULL, PRIMARY KEY(equipment_config_id, action_config_id))');
        $this->addSql('CREATE INDEX IDX_5585B35CF6E640FA ON equipment_config_action_config (equipment_config_id)');
        $this->addSql('CREATE INDEX IDX_5585B35C80DD159E ON equipment_config_action_config (action_config_id)');
        $this->addSql('CREATE TABLE equipment_mechanic_action_config (equipment_mechanic_id INT NOT NULL, action_config_id INT NOT NULL, PRIMARY KEY(equipment_mechanic_id, action_config_id))');
        $this->addSql('CREATE INDEX IDX_ABED6341FB252F27 ON equipment_mechanic_action_config (equipment_mechanic_id)');
        $this->addSql('CREATE INDEX IDX_ABED634180DD159E ON equipment_mechanic_action_config (action_config_id)');
        $this->addSql('CREATE TABLE status_config_action_config (status_config_id INT NOT NULL, action_config_id INT NOT NULL, PRIMARY KEY(status_config_id, action_config_id))');
        $this->addSql('CREATE INDEX IDX_2917A819AC4E86C2 ON status_config_action_config (status_config_id)');
        $this->addSql('CREATE INDEX IDX_2917A81980DD159E ON status_config_action_config (action_config_id)');
        $this->addSql('ALTER TABLE character_config_action_config ADD CONSTRAINT FK_55422528A38BA4B8 FOREIGN KEY (character_config_id) REFERENCES character_config (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE character_config_action_config ADD CONSTRAINT FK_5542252880DD159E FOREIGN KEY (action_config_id) REFERENCES action (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE equipment_config_action_config ADD CONSTRAINT FK_5585B35CF6E640FA FOREIGN KEY (equipment_config_id) REFERENCES equipment_config (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE equipment_config_action_config ADD CONSTRAINT FK_5585B35C80DD159E FOREIGN KEY (action_config_id) REFERENCES action (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE equipment_mechanic_action_config ADD CONSTRAINT FK_ABED6341FB252F27 FOREIGN KEY (equipment_mechanic_id) REFERENCES equipment_mechanic (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE equipment_mechanic_action_config ADD CONSTRAINT FK_ABED634180DD159E FOREIGN KEY (action_config_id) REFERENCES action (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE status_config_action_config ADD CONSTRAINT FK_2917A819AC4E86C2 FOREIGN KEY (status_config_id) REFERENCES status_config (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE status_config_action_config ADD CONSTRAINT FK_2917A81980DD159E FOREIGN KEY (action_config_id) REFERENCES action (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE equipment_mechanic_action DROP CONSTRAINT fk_586b101bfb252f27');
        $this->addSql('ALTER TABLE equipment_mechanic_action DROP CONSTRAINT fk_586b101b9d32f035');
        $this->addSql('ALTER TABLE equipment_config_action DROP CONSTRAINT fk_51b80572f6e640fa');
        $this->addSql('ALTER TABLE equipment_config_action DROP CONSTRAINT fk_51b805729d32f035');
        $this->addSql('ALTER TABLE character_config_action DROP CONSTRAINT fk_51c5d29a38ba4b8');
        $this->addSql('ALTER TABLE character_config_action DROP CONSTRAINT fk_51c5d299d32f035');
        $this->addSql('DROP TABLE equipment_mechanic_action');
        $this->addSql('DROP TABLE equipment_config_action');
        $this->addSql('DROP TABLE character_config_action');
        $this->addSql('ALTER TABLE action ADD range VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE action DROP target');
        $this->addSql('ALTER TABLE action RENAME COLUMN scope TO display_holder');
        $this->addSql('ALTER TABLE game_modifier DROP CONSTRAINT fk_fb26dba7dc5c81');
        $this->addSql('ALTER TABLE game_modifier DROP CONSTRAINT fk_fb26db166d1f9c');
        $this->addSql('DROP INDEX idx_fb26db166d1f9c');
        $this->addSql('DROP INDEX idx_fb26dba7dc5c81');
        $this->addSql('ALTER TABLE game_modifier DROP hunter_id');
        $this->addSql('ALTER TABLE game_modifier DROP project_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE equipment_mechanic_action (equipment_mechanic_id INT NOT NULL, action_id INT NOT NULL, PRIMARY KEY(equipment_mechanic_id, action_id))');
        $this->addSql('CREATE INDEX idx_586b101b9d32f035 ON equipment_mechanic_action (action_id)');
        $this->addSql('CREATE INDEX idx_586b101bfb252f27 ON equipment_mechanic_action (equipment_mechanic_id)');
        $this->addSql('CREATE TABLE equipment_config_action (equipment_config_id INT NOT NULL, action_id INT NOT NULL, PRIMARY KEY(equipment_config_id, action_id))');
        $this->addSql('CREATE INDEX idx_51b805729d32f035 ON equipment_config_action (action_id)');
        $this->addSql('CREATE INDEX idx_51b80572f6e640fa ON equipment_config_action (equipment_config_id)');
        $this->addSql('CREATE TABLE character_config_action (character_config_id INT NOT NULL, action_id INT NOT NULL, PRIMARY KEY(character_config_id, action_id))');
        $this->addSql('CREATE INDEX idx_51c5d299d32f035 ON character_config_action (action_id)');
        $this->addSql('CREATE INDEX idx_51c5d29a38ba4b8 ON character_config_action (character_config_id)');
        $this->addSql('ALTER TABLE equipment_mechanic_action ADD CONSTRAINT fk_586b101bfb252f27 FOREIGN KEY (equipment_mechanic_id) REFERENCES equipment_mechanic (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE equipment_mechanic_action ADD CONSTRAINT fk_586b101b9d32f035 FOREIGN KEY (action_id) REFERENCES action (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE equipment_config_action ADD CONSTRAINT fk_51b80572f6e640fa FOREIGN KEY (equipment_config_id) REFERENCES equipment_config (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE equipment_config_action ADD CONSTRAINT fk_51b805729d32f035 FOREIGN KEY (action_id) REFERENCES action (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE character_config_action ADD CONSTRAINT fk_51c5d29a38ba4b8 FOREIGN KEY (character_config_id) REFERENCES character_config (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE character_config_action ADD CONSTRAINT fk_51c5d299d32f035 FOREIGN KEY (action_id) REFERENCES action (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE character_config_action_config DROP CONSTRAINT FK_55422528A38BA4B8');
        $this->addSql('ALTER TABLE character_config_action_config DROP CONSTRAINT FK_5542252880DD159E');
        $this->addSql('ALTER TABLE equipment_config_action_config DROP CONSTRAINT FK_5585B35CF6E640FA');
        $this->addSql('ALTER TABLE equipment_config_action_config DROP CONSTRAINT FK_5585B35C80DD159E');
        $this->addSql('ALTER TABLE equipment_mechanic_action_config DROP CONSTRAINT FK_ABED6341FB252F27');
        $this->addSql('ALTER TABLE equipment_mechanic_action_config DROP CONSTRAINT FK_ABED634180DD159E');
        $this->addSql('ALTER TABLE status_config_action_config DROP CONSTRAINT FK_2917A819AC4E86C2');
        $this->addSql('ALTER TABLE status_config_action_config DROP CONSTRAINT FK_2917A81980DD159E');
        $this->addSql('DROP TABLE character_config_action_config');
        $this->addSql('DROP TABLE equipment_config_action_config');
        $this->addSql('DROP TABLE equipment_mechanic_action_config');
        $this->addSql('DROP TABLE status_config_action_config');
        $this->addSql('ALTER TABLE action ADD target VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE action ADD scope VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE action DROP display_holder');
        $this->addSql('ALTER TABLE action DROP range');
        $this->addSql('ALTER TABLE game_modifier ADD hunter_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE game_modifier ADD project_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE game_modifier ADD CONSTRAINT fk_fb26dba7dc5c81 FOREIGN KEY (hunter_id) REFERENCES hunter (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE game_modifier ADD CONSTRAINT fk_fb26db166d1f9c FOREIGN KEY (project_id) REFERENCES project (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_fb26db166d1f9c ON game_modifier (project_id)');
        $this->addSql('CREATE INDEX idx_fb26dba7dc5c81 ON game_modifier (hunter_id)');
    }
}
