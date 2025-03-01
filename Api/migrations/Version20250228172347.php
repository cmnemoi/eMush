<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250228172347 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE xyloph_entry_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE xyloph_config_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE game_config_xyloph_config (game_config_id INT NOT NULL, xyloph_config_id INT NOT NULL, PRIMARY KEY(game_config_id, xyloph_config_id))');
        $this->addSql('CREATE INDEX IDX_BD1FD031F67DC781 ON game_config_xyloph_config (game_config_id)');
        $this->addSql('CREATE INDEX IDX_BD1FD031FBDB82CE ON game_config_xyloph_config (xyloph_config_id)');
        $this->addSql('CREATE TABLE xyloph_entry (id INT NOT NULL, xyloph_config_id INT DEFAULT NULL, daedalus_id INT DEFAULT NULL, is_decoded BOOLEAN DEFAULT false NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_FC5CF88BFBDB82CE ON xyloph_entry (xyloph_config_id)');
        $this->addSql('CREATE INDEX IDX_FC5CF88B74B5A52D ON xyloph_entry (daedalus_id)');
        $this->addSql('CREATE TABLE xyloph_config (id INT NOT NULL, key VARCHAR(255) NOT NULL, name VARCHAR(255) DEFAULT \'\' NOT NULL, weight INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE xyloph_config_abstract_modifier_config (xyloph_config_id INT NOT NULL, abstract_modifier_config_id INT NOT NULL, PRIMARY KEY(xyloph_config_id, abstract_modifier_config_id))');
        $this->addSql('CREATE INDEX IDX_458DF03FBDB82CE ON xyloph_config_abstract_modifier_config (xyloph_config_id)');
        $this->addSql('CREATE INDEX IDX_458DF03BFA8DC8C ON xyloph_config_abstract_modifier_config (abstract_modifier_config_id)');
        $this->addSql('ALTER TABLE game_config_xyloph_config ADD CONSTRAINT FK_BD1FD031F67DC781 FOREIGN KEY (game_config_id) REFERENCES config_game (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE game_config_xyloph_config ADD CONSTRAINT FK_BD1FD031FBDB82CE FOREIGN KEY (xyloph_config_id) REFERENCES xyloph_config (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE xyloph_entry ADD CONSTRAINT FK_FC5CF88BFBDB82CE FOREIGN KEY (xyloph_config_id) REFERENCES xyloph_config (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE xyloph_entry ADD CONSTRAINT FK_FC5CF88B74B5A52D FOREIGN KEY (daedalus_id) REFERENCES daedalus (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE xyloph_config_abstract_modifier_config ADD CONSTRAINT FK_458DF03FBDB82CE FOREIGN KEY (xyloph_config_id) REFERENCES xyloph_config (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE xyloph_config_abstract_modifier_config ADD CONSTRAINT FK_458DF03BFA8DC8C FOREIGN KEY (abstract_modifier_config_id) REFERENCES abstract_modifier_config (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE xyloph_entry_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE xyloph_config_id_seq CASCADE');
        $this->addSql('ALTER TABLE game_config_xyloph_config DROP CONSTRAINT FK_BD1FD031F67DC781');
        $this->addSql('ALTER TABLE game_config_xyloph_config DROP CONSTRAINT FK_BD1FD031FBDB82CE');
        $this->addSql('ALTER TABLE xyloph_entry DROP CONSTRAINT FK_FC5CF88BFBDB82CE');
        $this->addSql('ALTER TABLE xyloph_entry DROP CONSTRAINT FK_FC5CF88B74B5A52D');
        $this->addSql('ALTER TABLE xyloph_config_abstract_modifier_config DROP CONSTRAINT FK_458DF03FBDB82CE');
        $this->addSql('ALTER TABLE xyloph_config_abstract_modifier_config DROP CONSTRAINT FK_458DF03BFA8DC8C');
        $this->addSql('DROP TABLE game_config_xyloph_config');
        $this->addSql('DROP TABLE xyloph_entry');
        $this->addSql('DROP TABLE xyloph_config');
        $this->addSql('DROP TABLE xyloph_config_abstract_modifier_config');
    }
}
