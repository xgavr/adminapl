<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Application\Entity\Ring;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210706094020 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('ring');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('mode', 'integer', ['notnull'=>true, 'default'=>Ring::MODE_NEW_ORDER]);
        $table->addColumn('status', 'integer', ['notnull'=>true, 'default'=> Ring::STATUS_ACTIVE]);
        $table->addColumn('date_created', 'datetime', ['notnull'=>true]);
        $table->addColumn('name', 'string', ['notnull'=>false, 'length' => 64]);
        $table->addColumn('phone', 'string', ['notnull'=>false, 'length' => 32]);
        $table->addColumn('vin', 'string', ['notnull'=>false, 'length' => 17]);
        $table->addColumn('info', 'string', ['notnull'=>false, 'length' => 512]);
        $table->addColumn('gds', 'json', ['notnull'=>false]);
        $table->addColumn('order_id', 'integer', ['notnull'=>false]);
        $table->addColumn('contact_id', 'integer', ['notnull'=>false]);
        $table->addColumn('contact_car_id', 'integer', ['notnull'=>false]);
        $table->addColumn('manger_id', 'integer', ['notnull'=>false]);
        $table->addColumn('user_id', 'integer', ['notnull'=>true]);
        $table->addColumn('office_id', 'integer', ['notnull'=>true]);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('orders', ['order_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'orders_id_ring_order_id_fk');
        $table->addForeignKeyConstraint('contact', ['contact_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'contact_id_ring_contact_id_fk');
        $table->addForeignKeyConstraint('contact_car', ['contact_car_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'contact_car_id_ring_contact_car_id_fk');
        $table->addForeignKeyConstraint('user', ['manager_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'user_id_ring_manager_id_fk');
        $table->addForeignKeyConstraint('user', ['user_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'user_id_ring_user_id_fk');
        $table->addForeignKeyConstraint('office', ['office_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'office_id_ring_office_id_fk');
        $table->addOption('engine' , 'InnoDB');

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('ring');
    }
}
