<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201218094720 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('bill_setting');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('supplier_id', 'integer', ['notnull'=>true]);
        $table->addColumn('status', 'integer', ['notnull'=>true]);
        $table->addColumn('name', 'string', ['notnull'=>true, 'default' => '']);
        $table->addColumn('description', 'json', ['notnull'=>false]);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('supplier', ['supplier_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'supplier_id_bill_setting_supplier_id_fk');
        $table->addOption('engine' , 'InnoDB');

        $table = $schema->createTable('idoc');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('supplier_id', 'integer', ['notnull'=>true]);
        $table->addColumn('doc_key', 'string', ['notnull'=>false, 'length'=>64]);
        $table->addColumn('status', 'integer', ['notnull'=>true]);
        $table->addColumn('name', 'string', ['notnull'=>true, 'default' => '']);
        $table->addColumn('description', 'text', ['notnull'=>false]);
        $table->addColumn('date_created', 'datetime', ['notnull'=>true]);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('supplier', ['supplier_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'supplier_id_idoc_supplier_id_fk');
        $table->addOption('engine' , 'InnoDB');

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('bill_setting');
        $schema->dropTable('idoc');
    }
}
