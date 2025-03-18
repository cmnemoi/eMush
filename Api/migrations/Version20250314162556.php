<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250314162556 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE daedalus_character_config (daedalus_id INT NOT NULL, character_config_id INT NOT NULL, PRIMARY KEY(daedalus_id, character_config_id))');
        $this->addSql('CREATE INDEX IDX_B0A3A75374B5A52D ON daedalus_character_config (daedalus_id)');
        $this->addSql('CREATE INDEX IDX_B0A3A753A38BA4B8 ON daedalus_character_config (character_config_id)');
        $this->addSql('ALTER TABLE daedalus_character_config ADD CONSTRAINT FK_B0A3A75374B5A52D FOREIGN KEY (daedalus_id) REFERENCES daedalus (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE daedalus_character_config ADD CONSTRAINT FK_B0A3A753A38BA4B8 FOREIGN KEY (character_config_id) REFERENCES character_config (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE daedalus_character_config DROP CONSTRAINT FK_B0A3A75374B5A52D');
        $this->addSql('ALTER TABLE daedalus_character_config DROP CONSTRAINT FK_B0A3A753A38BA4B8');
        $this->addSql('DROP TABLE daedalus_character_config');
    }
}
