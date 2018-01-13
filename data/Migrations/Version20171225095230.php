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
        // 
        // 
        // Create 'raw' table
        $table = $schema->createTable('currency');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('name', 'string', ['notnull'=>true, 'length'=>256]);        
        $table->addColumn('description', 'string', ['notnull'=>true, 'length'=>1024]);        
        $table->setPrimaryKey(['id']);
        $table->addOption('engine' , 'InnoDB');
        
        $table = $schema->createTable('currency_rate');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('date_rate', 'string', ['notnull'=>true, 'length'=>256]);        
        $table->addColumn('currency_id', 'integer', ['notnull'=>false]);                
        $table->addColumn('rate', 'float', ['precision'=>2]);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('currency', ['currency_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'currency_id_currency_rate_currency_id_fk');
        $table->addOption('engine' , 'InnoDB');
        
        $table = $schema->createTable('tax');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('name', 'string', ['notnull'=>true, 'length'=>256]);        
        $table->addColumn('amount', 'float', ['precision'=>2]);
        $table->setPrimaryKey(['id']);
        $table->addOption('engine' , 'InnoDB');
        
        $table = $schema->createTable('goods');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('name', 'string', ['notnull'=>true, 'length'=>256]);        
        $table->addColumn('code', 'string', ['notnull'=>true, 'length'=>128]);        
        $table->addColumn('producer_id', 'integer', ['notnull'=>false]);                
        $table->addColumn('tax_id', 'integer', ['notnull'=>false]);                
        $table->addColumn('available', 'integer', ['notnull'=>true]);
        $table->addColumn('description', 'string', ['notnull'=>true, 'length'=>1024]);        
        $table->setPrimaryKey(['id']);
        $table->addOption('engine' , 'InnoDB');

        $table = $schema->createTable('images');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('good_id', 'integer', ['notnull'=>false, 'default' => 0]);  
        $table->addColumn('path', 'string', ['notnull'=>true, 'length'=>1024]);        
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('goods', ['good_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'goods_id_images_good_id_fk');
        $table->addOption('engine' , 'InnoDB');
        
        $table = $schema->createTable('country');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('name', 'string', ['notnull'=>true, 'length'=>128]);        
        $table->addColumn('fullname', 'string', ['notnull'=>true, 'length'=>512]);        
        $table->addColumn('code', 'string', ['notnull'=>true, 'length'=>128]);        
        $table->addColumn('alpha2', 'string', ['notnull'=>true, 'length'=>2]);        
        $table->addColumn('alpha3', 'string', ['notnull'=>true, 'length'=>3]);        
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['code'], 'code_idx');
        $table->addOption('engine' , 'InnoDB');
        
        
        $table = $schema->createTable('producer');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('name', 'string', ['notnull'=>true, 'length'=>128]);        
        $table->addColumn('country_id', 'integer', ['notnull'=>false]);
        $table->setPrimaryKey(['id']);
        $table->addOption('engine' , 'InnoDB');
        
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
        $table->addColumn('article', 'string', ['length'=>128]);
        $table->addColumn('producer', 'string', ['length'=>128]);
        $table->addColumn('goodname', 'string', ['length'=>256]);
        $table->addColumn('price', 'float', ['precision'=>2]);
        $table->addColumn('rest', 'float', ['precision'=>512]);        
        $table->addColumn('date_created', 'datetime', ['notnull'=>true]);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('raw', ['raw_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'raw_raw_id_fk');
        $table->addForeignKeyConstraint('goods', ['good_id'], ['id'], 
                [], 'goods_id_rawprice_good_id_fk');
        $table->addForeignKeyConstraint('unknown_producer', ['unknown_producer_id'], ['id'], 
                [], 'unknownn_producer_id_rawprice_unknown_producer_id_fk');
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
        $schema->dropTable('producer');
        $schema->dropTable('country');
        $schema->dropTable('tax');
        $schema->dropTable('goods');
        $schema->dropTable('currency');
        $schema->dropTable('currency_rate');
        $schema->dropTable('images');
    }
}
