<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250614054126 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('car');
        $table->addColumn('details', 'json', ['notnull' => false, 'comment' => 'Характеристики']);
        $table->addColumn('year_from', 'integer', ['notnull' => true, 'default' => 0, 'comment' => 'Год начала']);
        $table->addColumn('year_to', 'integer', ['notnull' => true, 'default' => 0, 'comment' => 'Год окончания']);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('car');
        $table->dropColumn('details');
        $table->dropColumn('year_from');
        $table->dropColumn('year_to');
    }
}
