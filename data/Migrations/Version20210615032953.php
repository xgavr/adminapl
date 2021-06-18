<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Application\Entity\Client;
use Application\Entity\Order;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210615032953 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('selection');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('comment', 'string', ['notnull'=>false, 'length'=>128]);
        $table->addColumn('oem_id', 'integer', ['notnull'=>true]);
        $table->addColumn('order_id', 'integer', ['notnull'=>true]);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('oem', ['oem_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'oem_id_selection_oem_id_fk');
        $table->addForeignKeyConstraint('orders', ['order_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'order_id_selection_order_id_fk');
        $table->addOption('engine' , 'InnoDB');
                
        $table = $schema->createTable('comment');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('apl_id', 'integer', ['notnull'=>false]);
        $table->addColumn('comment', 'text', ['notnull'=>false]);
        $table->addColumn('order_id', 'integer', ['notnull'=>true]);
        $table->addColumn('user_id', 'integer', ['notnull'=>true]);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('user', ['user_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'user_id_comment_user_id_fk');
        $table->addForeignKeyConstraint('orders', ['order_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'order_id_comment_order_id_fk');
        $table->addOption('engine' , 'InnoDB');
                

        $table = $schema->getTable('bid');
        $table->addColumn('oem_id', 'integer', ['notnull'=>false]);
        $table->addColumn('opts', 'json', ['notnull'=>false]);
        $table->addColumn('display_name', 'string', ['notnull'=>false, 'length' => 128]);
        $table->addForeignKeyConstraint('oem', ['oem_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'oem_id_bid_oem_id_fk');
        
        $table = $schema->getTable('client');
        $table->addColumn('sales_total', 'float', ['notnull'=>true, 'default' => 0]);
        $table->addColumn('sales_order', 'integer', ['notnull'=>true, 'default' => 0]);
        $table->addColumn('sales_good', 'integer', ['notnull'=>true, 'default' => 0]);
        $table->addColumn('pricecol', 'integer', ['notnull'=>true, 'default' => Client::PRICE_0]);

        $table = $schema->getTable('orders');
        $table->dropColumn('comment');
        $table->addColumn('apl_id', 'integer', ['notnull'=>false]);
        $table->addColumn('geo', 'string', ['notnull'=>false, 'length' => 128]);
        $table->addColumn('invoice_info', 'text', ['notnull'=>false]);
        $table->addColumn('info', 'text', ['notnull'=>false]);
        $table->addColumn('address', 'text', ['notnull'=>false]);
        $table->addColumn('shipment_rate', 'float', ['notnull'=>true, 'default' => 0]);
        $table->addColumn('shipment_distance', 'float', ['notnull'=>true, 'default' => 0]);
        $table->addColumn('shipment_add_rate', 'float', ['notnull'=>true, 'default' => 0]);
        $table->addColumn('shipment_total', 'float', ['notnull'=>true, 'default' => 0]);
        $table->addColumn('track_number', 'string', ['notnull'=>false, 'length' => 128]);
        $table->addColumn('date_oper', 'datetime', ['notnull'=>false]);
        $table->addColumn('date_shipment', 'datetime', ['notnull'=>false]);
        $table->addColumn('date_mod', 'datetime', ['notnull'=>true]);
        $table->addColumn('mode', 'integer', ['notnull'=>true, 'default' => Order::MODE_MAN]);
        $table->addColumn('legal_id', 'integer', ['notnull'=>false]);
        $table->addColumn('recipient_id', 'integer', ['notnull'=>false]);
        $table->addColumn('company_id', 'integer', ['notnull'=>false]);
        $table->addColumn('skiper_id', 'integer', ['notnull'=>false]);
        $table->addColumn('office_id', 'integer', ['notnull'=>false]);
        $table->addForeignKeyConstraint('legal', ['legal_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'legal_id_orders_legal_id_fk');
        $table->addForeignKeyConstraint('legal', ['recipient_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'legal_id_orders_recipient_id_fk');
        $table->addForeignKeyConstraint('legal', ['company_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'legal_id_orders_company_id_fk');
        $table->addForeignKeyConstraint('user', ['skiper_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'user_id_orders_skiper_id_fk');
        $table->addForeignKeyConstraint('office', ['office_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'office_id_orders_office_id_fk');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('orders');
        $table->removeForeignKey('legal_id_orders_legal_id_fk');
        $table->removeForeignKey('legal_id_orders_recipient_id_fk');
        $table->removeForeignKey('legal_id_orders_company_id_fk');
        $table->removeForeignKey('user_id_orders_skiper_id_fk');
        $table->removeForeignKey('office_id_orders_office_id_fk');
        $table->dropColumn('office_id');
        $table->dropColumn('skiper_id');
        $table->dropColumn('company_id');
        $table->dropColumn('recipient_id');
        $table->dropColumn('legal_id');
        $table->dropColumn('mode');
        $table->dropColumn('date_mod');
        $table->dropColumn('date_shipment');
        $table->dropColumn('date_oper');
        $table->dropColumn('track_number');
        $table->dropColumn('shipment_total');
        $table->dropColumn('shipment_add_rate');
        $table->dropColumn('shipment_distance');
        $table->dropColumn('shipment_rate');
        $table->dropColumn('address');
        $table->dropColumn('info');
        $table->dropColumn('invoice_info');
        $table->dropColumn('geo');
        $table->dropColumn('apl_id');
        $table->addColumn('comment', 'string', ['notnull'=>false]);

        $table = $schema->getTable('client');
        $table->dropColumn('sales_total');
        $table->dropColumn('sales_order');
        $table->dropColumn('sales_good');
        $table->dropColumn('pricecol');

        $table = $schema->getTable('bid');
        $table->removeForeignKey('oem_id_bid_oem_id_fk');
        $table->dropColumn('oem_id');
        $table->dropColumn('opts');
        $table->dropColumn('display_name');
        
        $schema->dropTable('comment');
        $schema->dropTable('selection');
    }
}
