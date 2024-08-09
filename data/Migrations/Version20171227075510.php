<?php

namespace Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171227075510 extends AbstractMigration
{
    /**
     * Returns the description of this migration.
     */
    public function getDescription(): string
    {
        $description = 'A migration which creates the `pricesettings` tables.';
        return $description;
    }
    
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        // Create 'price_settings' table
        $table = $schema->createTable('price_settings');
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
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'supplier_price_settings_supplier_id_fk');
        $table->addOption('engine' , 'InnoDB');
        
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('price_settings');
    }
}
