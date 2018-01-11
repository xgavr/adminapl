<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180105180008 extends AbstractMigration
{
    /**
     * Returns the description of this migration.
     */
    public function getDescription()
    {
        $description = 'A migration which creates the `order` table.';
        return $description;
    }
    
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        // Create 'price_settings' table
        $table = $schema->createTable('orders');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('date_created', 'datetime', ['notnull'=>true]);
        $table->addColumn('total', 'float', ['notnull'=>true]);        
        $table->addColumn('comment', 'string', ['notnull'=>false, 'length'=>1024]);
        $table->addColumn('status', 'integer', ['notnull'=>true]);
        $table->addColumn('client_id', 'integer', ['notnull'=>true]);        
        $table->addColumn('user_id', 'integer', ['notnull'=>true]);        
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('client', ['client_id'], ['id'], 
                ['onUpdate'=>'CASCADE'], 'client_id_orders_client_id_fk');
        $table->addForeignKeyConstraint('user', ['user_id'], ['id'], 
                ['onUpdate'=>'CASCADE'], 'user_id_orders_user_id_fk');
        $table->addOption('engine' , 'InnoDB');
                
        $table = $schema->createTable('bid');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('date_created', 'datetime', ['notnull'=>true]);
        $table->addColumn('price', 'float', ['notnull'=>true]);        
        $table->addColumn('num', 'float', ['notnull'=>true]);        
        $table->addColumn('good_id', 'integer', ['notnull'=>true]);        
        $table->addColumn('order_id', 'integer', ['notnull'=>true]);        
        $table->addColumn('user_id', 'integer', ['notnull'=>true]);        
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('orders', ['order_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'orders_id_bid_orders_id_fk');
        $table->addForeignKeyConstraint('user', ['user_id'], ['id'], 
                ['onUpdate'=>'CASCADE'], 'user_id_bid_user_id_fk');
        $table->addForeignKeyConstraint('goods', ['good_id'], ['id'], 
                ['onUpdate'=>'CASCADE'], 'goods_id_bid_good_id_fk');
        $table->addOption('engine' , 'InnoDB');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('bid');
        $schema->dropTable('orders');
    }
}
