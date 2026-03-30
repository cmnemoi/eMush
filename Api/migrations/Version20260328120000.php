<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260328120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename metalworker skill to scrapper';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE skill_config SET name = 'scrapper' WHERE name = 'metalworker'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("UPDATE skill_config SET name = 'metalworker' WHERE name = 'scrapper'");
    }
}
