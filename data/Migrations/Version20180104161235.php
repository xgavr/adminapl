<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180104161235 extends AbstractMigration
{
    /**
     * Returns the description of this migration.
     */
    public function getDescription()
    {
        $description = 'A migration which creates the `cart` tables.';
        return $description;
    }
    
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        // Create 'price_settings' table
        $table = $schema->createTable('cart');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('price', 'float', ['notnull'=>true]);        
        $table->addColumn('num', 'float', ['notnull'=>true]);        
        $table->addColumn('date_created', 'datetime', ['notnull'=>true]);
        $table->addColumn('good_id', 'integer', ['notnull'=>true]);        
        $table->addColumn('client_id', 'integer', ['notnull'=>true]);        
        $table->addColumn('user_id', 'integer', ['notnull'=>true]);        
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('goods', ['good_id'], ['id'], 
                ['onUpdate'=>'CASCADE'], 'goods_id_cart_good_id_fk');
        $table->addForeignKeyConstraint('client', ['client_id'], ['id'], 
                ['onUpdate'=>'CASCADE'], 'client_id_cart_client_id_fk');
        $table->addForeignKeyConstraint('user', ['user_id'], ['id'], 
                ['onUpdate'=>'CASCADE'], 'user_id_cart_user_id_fk');
        $table->addOption('engine' , 'InnoDB');
        
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('cart');
    }
}
