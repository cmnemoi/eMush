<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250529103754 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
<<<<<<< HEAD:Api/migrations/Version20250528195142.php
        $this->addSql('ALTER TABLE closed_player ADD triumph INT NOT NULL DEFAULT 0');
=======
        $this->addSql('ALTER TABLE closed_player ADD triumph INT DEFAULT NULL');
>>>>>>> dd656981c (fix: Pages loading ClosedPlayer from the older version will not throw an error):Api/migrations/Version20250529103754.php
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE closed_player DROP triumph');
    }
}
