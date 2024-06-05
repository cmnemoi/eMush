<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240605183415 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE project ADD progress_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE project DROP progress');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EE43DB87C9 FOREIGN KEY (progress_id) REFERENCES game_variable_collection (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2FB3D0EE43DB87C9 ON project (progress_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE project DROP CONSTRAINT FK_2FB3D0EE43DB87C9');
        $this->addSql('DROP INDEX UNIQ_2FB3D0EE43DB87C9');
        $this->addSql('ALTER TABLE project ADD progress INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE project DROP progress_id');
    }
}
