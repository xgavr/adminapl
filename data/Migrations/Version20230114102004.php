<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230114102004 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('bid');
        $table->addColumn('base_key', 'string', ['notnull' => false, 'length' => 48]);

        $table = $schema->getTable('st_good');
        $table->addColumn('base_key', 'string', ['notnull' => false, 'length' => 48]);

        $table = $schema->getTable('pt_good');
        $table->addColumn('base_key', 'string', ['notnull' => false, 'length' => 48]);
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('bid');
        $table->dropColumn('base_key');
        $table = $schema->getTable('st_good');
        $table->dropColumn('base_key');
        $table = $schema->getTable('pt_good');
        $table->dropColumn('base_key');
    }
}
