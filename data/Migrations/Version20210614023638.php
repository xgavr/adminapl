<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Application\Entity\Courier;
use Application\Entity\Shipping;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210614023638 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('courier');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('apl_id', 'integer', ['notnull'=>false]);
        $table->addColumn('name', 'string', ['notnull'=>true, 'length'=>128]);
        $table->addColumn('comment', 'string', ['notnull'=>false, 'length'=>512]);
        $table->addColumn('status', 'integer', ['notnull'=>true, 'default'=> Courier::STATUS_ACTIVE]);
        $table->addColumn('site', 'string', ['notnull'=>false, 'length'=>256]);
        $table->addColumn('track', 'string', ['notnull'=>false, 'length'=>256]);
        $table->addColumn('calculator', 'string', ['notnull'=>false, 'length'=>256]);
        $table->setPrimaryKey(['id']);
        $table->addOption('engine' , 'InnoDB');

        $table = $schema->createTable('shipping');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('apl_id', 'integer', ['notnull'=>false]);
        $table->addColumn('name', 'string', ['notnull'=>true, 'length'=>128]);
        $table->addColumn('comment', 'string', ['notnull'=>false, 'length'=>512]);
        $table->addColumn('status', 'integer', ['notnull'=>true, 'default'=> Shipping::STATUS_ACTIVE]);
        $table->addColumn('rate', 'integer', ['notnull'=>true, 'default'=> Shipping::RATE_TRIP]);
        $table->addColumn('rate_trip', 'float', ['notnull'=>false, 'default' => 0]);
        $table->addColumn('rate_distance', 'float', ['notnull'=>false, 'default' => 0]);
        $table->addColumn('sorting', 'integer', ['notnull'=>true, 'default' => 0]);
        $table->addColumn('office_id', 'integer', ['notnull'=>true]);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('office', ['office_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'office_id_shipping_office_id_fk');
        $table->addOption('engine' , 'InnoDB');

        $table = $schema->getTable('orders');
        $table->addColumn('contact_car_id', 'integer', ['notnull'=>false]);
        $table->addColumn('courier_id', 'integer', ['notnull'=>false]);
        $table->addColumn('shipping_id', 'integer', ['notnull'=>true]);
        $table->addForeignKeyConstraint('contact_car', ['contact_car_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'contact_car_id_orders_contact_car_id_fk');
        $table->addForeignKeyConstraint('courier', ['courier_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'courier_id_orders_courier_id_fk');
        $table->addForeignKeyConstraint('shipping', ['shipping_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'shipping_id_orders_shipping_id_fk');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('orders');
        $table->removeForeignKey('contact_car_id_orders_contact_car_id_fk');
        $table->removeForeignKey('courier_id_orders_courier_id_fk');
        $table->removeForeignKey('shipping_id_orders_shipping_id_fk');
        $table->dropColumn('contact_car_id');
        $table->dropColumn('courier_id');
        $table->dropColumn('shipping_id');

        $schema->dropTable('courier');
        $schema->dropTable('shipping');

    }
}
