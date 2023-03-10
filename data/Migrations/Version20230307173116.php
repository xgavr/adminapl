<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use ApiMarketPlace\Entity\MarketplaceOrder;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230307173116 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('marketplace_order');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('marketplace_order_id', 'integer', ['notnull'=>false]);
        $table->addColumn('marketplace_order_number', 'string', ['notnull'=>false, 'length' => 128]);
        $table->addColumn('marketplace_posting_number', 'string', ['notnull'=>false, 'length' => 128]);
        $table->addColumn('date_created', 'datetime', ['notnull'=>true]);
        $table->addColumn('status', 'integer', ['notnull'=>true, 'default' => MarketplaceOrder::STATUS_ACTIVE]);
        $table->addColumn('marketplace_id', 'integer', ['notnull'=>true]);
        $table->addColumn('order_id', 'integer', ['notnull'=>false]);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('marketplace', ['marketplace_id'], ['id'], 
                ['onUpdate'=>'CASCADE', 'onDelete'=>'CASCADE'], 'mp_id_mp_order_map_id_fk');
        $table->addForeignKeyConstraint('orders', ['order_id'], ['id'], 
                ['onUpdate'=>'CASCADE', 'onDelete'=>'CASCADE'], 'order_id_mp_order_order_id_fk');
        $table->addOption('engine' , 'InnoDB');    
        
        $table = $schema->getTable('marketplace_update');
        $table->removeForeignKey('mpu_order_id_orders_id_fk');
        $table->dropColumn('order_id');
        $table->addColumn('marketplace_order_id', 'integer', ['notnull'=>false]);
        $table->addForeignKeyConstraint('marketplace_order', ['marketplace_order_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'mpu_mp_order_id_mpu_order_id_fk');

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

        $table = $schema->getTable('marketplace_update');
        $table->removeForeignKey('mpu_mp_order_id_mpu_order_id_fk');
        $table->dropColumn('marketplace_order_id');
        $table->addColumn('order_id', 'integer', ['notnull'=>false]);
//        $table->addForeignKeyConstraint('orders', ['order_id'], ['id'], 
//                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'mpu_order_id_orders_id_fk');

        $schema->dropTable('marketplace_order');
    }
}
