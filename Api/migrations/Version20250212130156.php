<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250212130156 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE neron_version_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE neron_version (id INT NOT NULL, daedalus_id INT DEFAULT NULL, major INT DEFAULT 1 NOT NULL, minor INT DEFAULT 0 NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F7E5885A74B5A52D ON neron_version (daedalus_id)');
        $this->addSql('ALTER TABLE neron_version ADD CONSTRAINT FK_F7E5885A74B5A52D FOREIGN KEY (daedalus_id) REFERENCES daedalus (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE neron_version_id_seq CASCADE');
        $this->addSql('ALTER TABLE neron_version DROP CONSTRAINT FK_F7E5885A74B5A52D');
        $this->addSql('DROP TABLE neron_version');
    }
}
