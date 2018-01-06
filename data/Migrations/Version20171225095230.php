<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171225095230 extends AbstractMigration
{
    /**
     * Returns the description of this migration.
     */
    public function getDescription()
    {
        $description = 'A migration which creates the `raw` and `rawprice` tables.';
        return $description;
    }
    
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        // Create 'raw' table
        $table = $schema->createTable('raw');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('supplier_id', 'integer', ['notnull'=>true]);        
        $table->addColumn('filename', 'string', ['notnull'=>true, 'length' => 512]);        
        $table->addColumn('status', 'integer', ['notnull'=>true]);
        $table->addColumn('date_created', 'datetime', ['notnull'=>true]);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('supplier', ['supplier_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'supplier_supplier_id_fk');
        $table->addOption('engine' , 'InnoDB');
        
        $table = $schema->createTable('unknown_producer');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('producer_id', 'integer', ['notnull'=>false]);                
        $table->addColumn('name', 'string', ['notnull'=>true, 'length' => 128]);        
        $table->addColumn('date_created', 'datetime', ['notnull'=>true]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['name'], 'name_idx');
        $table->addForeignKeyConstraint('producer', ['producer_id'], ['id'], 
                [], 'produser_id_uproduseer_produser_id_fk');
        $table->addOption('engine' , 'InnoDB');        
        
        $table = $schema->createTable('rawprice');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('raw_id', 'integer', ['notnull'=>true]);   
        $table->addColumn('good_id', 'integer', ['notnull'=>false, 'default' => 0]);  
        $table->addColumn('unknown_producer_id', 'integer', ['notnull'=>false, 'default' => 0]);                
        $table->addColumn('rawdata', 'string', ['notnull'=>true, 'length'=>4096]);
        $table->addColumn('article', 'string', ['length'=>64]);
        $table->addColumn('producer', 'string', ['length'=>128]);
        $table->addColumn('goodname', 'string', ['length'=>512]);
        $table->addColumn('price', 'float', ['precision'=>2]);
        $table->addColumn('rest', 'float', ['precision'=>512]);        
        $table->addColumn('date_created', 'datetime', ['notnull'=>true]);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('raw', ['raw_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'raw_raw_id_fk');
        $table->addForeignKeyConstraint('goods', ['good_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'goods_id_rawprice_good_id_fk');
        $table->addForeignKeyConstraint('unknown_producer', ['unknown_producer_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'unknownn_producer_id_rawprice_unknown_producer_id_fk');
        $table->addOption('engine' , 'InnoDB');
        
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('rawprice');
        $schema->dropTable('unknown_producer');
        $schema->dropTable('raw');
    }
}
