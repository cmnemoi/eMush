<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\Uid\Uuid;

final class Version20260704153332 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add unique uuid to closed_exploration (backfilling existing rows with collision-checked values) and startDay/startCycle to exploration and closed_exploration';
    }

    public function up(Schema $schema): void
    {
        $this->connection->executeStatement('ALTER TABLE closed_exploration ADD uuid VARCHAR(36) DEFAULT NULL');

        $ids = $this->connection->fetchFirstColumn('SELECT id FROM closed_exploration');
        foreach ($ids as $id) {
            do {
                $uuid = Uuid::v4()->toRfc4122();
            } while ($this->connection->fetchOne('SELECT 1 FROM closed_exploration WHERE uuid = ?', [$uuid]) !== false);

            $this->connection->executeStatement('UPDATE closed_exploration SET uuid = ? WHERE id = ?', [$uuid, $id]);
        }

        $this->connection->executeStatement('ALTER TABLE closed_exploration ALTER uuid SET NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_637290E6D17F50A6 ON closed_exploration (uuid)');

        $this->addSql('ALTER TABLE closed_exploration ADD start_day INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE closed_exploration ADD start_cycle INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE exploration ADD start_day INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE exploration ADD start_cycle INT DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE exploration DROP start_day');
        $this->addSql('ALTER TABLE exploration DROP start_cycle');
        $this->addSql('ALTER TABLE closed_exploration DROP start_day');
        $this->addSql('ALTER TABLE closed_exploration DROP start_cycle');

        $this->addSql('DROP INDEX UNIQ_637290E6D17F50A6');
        $this->addSql('ALTER TABLE closed_exploration DROP uuid');
    }
}
