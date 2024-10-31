<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241031210931 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE replace_equipment_config ADD place_name VARCHAR(255) DEFAULT \'\' NOT NULL');
        $this->addSql('ALTER TABLE replace_equipment_config ALTER name SET DEFAULT \'\'');
        $this->addSql('ALTER TABLE replace_equipment_config ALTER equipment_name SET DEFAULT \'\'');
        $this->addSql('ALTER TABLE replace_equipment_config ALTER replaced_equipment_name SET DEFAULT \'\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE replace_equipment_config DROP place_name');
        $this->addSql('ALTER TABLE replace_equipment_config ALTER name DROP DEFAULT');
        $this->addSql('ALTER TABLE replace_equipment_config ALTER equipment_name DROP DEFAULT');
        $this->addSql('ALTER TABLE replace_equipment_config ALTER replaced_equipment_name DROP DEFAULT');
    }
}
