<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180628075902 extends AbstractMigration
{
    public function getDescription()
    {
        $description = 'A migration which creates the `price_description` tables.';
        return $description;
    }
    
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        // Create 'price_description' table
        $table = $schema->createTable('price_description');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('supplier_id', 'integer', ['notnull'=>true]);        
        $table->addColumn('name', 'string', ['notnull'=>true, 'length' => 128]);        
        $table->addColumn('article', 'integer', ['notnull'=>false]);        
        $table->addColumn('iid', 'integer', ['notnull'=>false]);        
        $table->addColumn('producer', 'integer', ['notnull'=>false]);        
        $table->addColumn('title', 'integer', ['notnull'=>false]);        
        $table->addColumn('price', 'integer', ['notnull'=>false]);        
        $table->addColumn('rest', 'integer', ['notnull'=>false]);        
        $table->addColumn('status', 'integer', ['notnull'=>true]);
        $table->addColumn('date_created', 'datetime', ['notnull'=>true]);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('supplier', ['supplier_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'supplier_price_description_supplier_id_fk');
        $table->addOption('engine' , 'InnoDB');
        
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('price_description');
    }
}
