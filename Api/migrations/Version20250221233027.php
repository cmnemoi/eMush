<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250221233027 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE rebel_base_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE rebel_base_config_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE game_config_rebel_base_config (game_config_id INT NOT NULL, rebel_base_config_id INT NOT NULL, PRIMARY KEY(game_config_id, rebel_base_config_id))');
        $this->addSql('CREATE INDEX IDX_327E6488F67DC781 ON game_config_rebel_base_config (game_config_id)');
        $this->addSql('CREATE INDEX IDX_327E648838B52EC7 ON game_config_rebel_base_config (rebel_base_config_id)');
        $this->addSql('CREATE TABLE rebel_base (id INT NOT NULL, rebel_base_config_id INT DEFAULT NULL, daedalus_id INT DEFAULT NULL, is_contacting BOOLEAN DEFAULT false NOT NULL, signal INT DEFAULT 0 NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_68078BFA38B52EC7 ON rebel_base (rebel_base_config_id)');
        $this->addSql('CREATE INDEX IDX_68078BFA74B5A52D ON rebel_base (daedalus_id)');
        $this->addSql('CREATE TABLE rebel_base_config (id INT NOT NULL, key VARCHAR(255) NOT NULL, name VARCHAR(255) DEFAULT \'\' NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE rebel_base_config_abstract_modifier_config (rebel_base_config_id INT NOT NULL, abstract_modifier_config_id INT NOT NULL, PRIMARY KEY(rebel_base_config_id, abstract_modifier_config_id))');
        $this->addSql('CREATE INDEX IDX_7AF021DE38B52EC7 ON rebel_base_config_abstract_modifier_config (rebel_base_config_id)');
        $this->addSql('CREATE INDEX IDX_7AF021DEBFA8DC8C ON rebel_base_config_abstract_modifier_config (abstract_modifier_config_id)');
        $this->addSql('ALTER TABLE game_config_rebel_base_config ADD CONSTRAINT FK_327E6488F67DC781 FOREIGN KEY (game_config_id) REFERENCES config_game (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE game_config_rebel_base_config ADD CONSTRAINT FK_327E648838B52EC7 FOREIGN KEY (rebel_base_config_id) REFERENCES rebel_base_config (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE rebel_base ADD CONSTRAINT FK_68078BFA38B52EC7 FOREIGN KEY (rebel_base_config_id) REFERENCES rebel_base_config (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE rebel_base ADD CONSTRAINT FK_68078BFA74B5A52D FOREIGN KEY (daedalus_id) REFERENCES daedalus (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE rebel_base_config_abstract_modifier_config ADD CONSTRAINT FK_7AF021DE38B52EC7 FOREIGN KEY (rebel_base_config_id) REFERENCES rebel_base_config (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE rebel_base_config_abstract_modifier_config ADD CONSTRAINT FK_7AF021DEBFA8DC8C FOREIGN KEY (abstract_modifier_config_id) REFERENCES abstract_modifier_config (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE modifier_provider ADD rebel_base_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE modifier_provider ADD CONSTRAINT FK_6709A1629801E77D FOREIGN KEY (rebel_base_id) REFERENCES rebel_base (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_6709A1629801E77D ON modifier_provider (rebel_base_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE modifier_provider DROP CONSTRAINT FK_6709A1629801E77D');
        $this->addSql('DROP SEQUENCE rebel_base_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE rebel_base_config_id_seq CASCADE');
        $this->addSql('ALTER TABLE game_config_rebel_base_config DROP CONSTRAINT FK_327E6488F67DC781');
        $this->addSql('ALTER TABLE game_config_rebel_base_config DROP CONSTRAINT FK_327E648838B52EC7');
        $this->addSql('ALTER TABLE rebel_base DROP CONSTRAINT FK_68078BFA38B52EC7');
        $this->addSql('ALTER TABLE rebel_base DROP CONSTRAINT FK_68078BFA74B5A52D');
        $this->addSql('ALTER TABLE rebel_base_config_abstract_modifier_config DROP CONSTRAINT FK_7AF021DE38B52EC7');
        $this->addSql('ALTER TABLE rebel_base_config_abstract_modifier_config DROP CONSTRAINT FK_7AF021DEBFA8DC8C');
        $this->addSql('DROP TABLE game_config_rebel_base_config');
        $this->addSql('DROP TABLE rebel_base');
        $this->addSql('DROP TABLE rebel_base_config');
        $this->addSql('DROP TABLE rebel_base_config_abstract_modifier_config');
        $this->addSql('DROP INDEX IDX_6709A1629801E77D');
        $this->addSql('ALTER TABLE modifier_provider DROP rebel_base_id');
    }
}
