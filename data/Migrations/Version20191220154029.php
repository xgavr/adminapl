<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191220154029 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('rate_type');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('name', 'string', ['notnull'=>true, 'length'=>128]);
        $table->addColumn('status', 'integer', ['notnull'=>true]);
        $table->addColumn('mode', 'integer', ['notnull'=>true]);
        $table->addColumn('office_id', 'integer', ['notnull'=>false]);
        $table->addColumn('supplier_id', 'integer', ['notnull'=>false]);
        $table->addColumn('producer_id', 'integer', ['notnull'=>false]);
        $table->addColumn('generic_group_id', 'integer', ['notnull'=>false]);
        $table->setPrimaryKey(['id']);
        $table->addOption('engine' , 'InnoDB');

        $table = $schema->createTable('rate');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('threshold', 'float', ['notnull'=>true, 'default' => 0]);
        $table->addColumn('rounding', 'float', ['notnull'=>true, 'default' => -1]);
        $table->addColumn('mode', 'integer', ['notnull'=>true]);
        $table->addColumn('office_id', 'integer', ['notnull'=>false]);
        $table->addColumn('supplier_id', 'integer', ['notnull'=>false]);
        $table->addColumn('producer_id', 'integer', ['notnull'=>false]);
        $table->addColumn('generic_group_id', 'integer', ['notnull'=>false]);
        $table->setPrimaryKey(['id']);
        $table->addOption('engine' , 'InnoDB');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('rate_type');
    }
}
