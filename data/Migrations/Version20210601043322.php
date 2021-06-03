<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Application\Entity\ContactCar;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210601043322 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('contact_car');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('apl_id', 'integer', ['notnull'=>false]);
        $table->addColumn('date_created', 'datetime', ['notnull'=>true]);
        $table->addColumn('comment', 'string', ['notnull'=>false, 'length'=>264]);
        $table->addColumn('vin', 'string', ['notnull'=>false, 'length'=>17]);
        $table->addColumn('vin2', 'string', ['notnull'=>false, 'length'=>17]);
        $table->addColumn('status', 'integer', ['notnull'=>true, 'default'=> ContactCar::STATUS_ACTIVE]);
        $table->addColumn('yocm', 'integer', ['notnull'=>false]);
        $table->addColumn('wheel', 'integer', ['notnull'=>true, 'default' => ContactCar::WHEEL_LEFT]);
        $table->addColumn('tm', 'integer', ['notnull'=>true, 'default' => ContactCar::TM_UNKNOWN]);
        $table->addColumn('ac', 'integer', ['notnull'=>true, 'default' => ContactCar::AC_UNKNOWN]);
        $table->addColumn('md', 'string', ['notnull'=>false, 'length'=>64]);
        $table->addColumn('ed', 'string', ['notnull'=>false, 'length'=>64]);
        $table->addColumn('ep', 'string', ['notnull'=>false, 'length'=>64]);
        $table->addColumn('make_id', 'integer', ['notnull'=>false]);
        $table->addColumn('model_id', 'integer', ['notnull'=>false]);
        $table->addColumn('car_id', 'integer', ['notnull'=>false]);        
        $table->addColumn('contact_id', 'integer', ['notnull'=>true]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['vin'], 'vin_idx');
        $table->addForeignKeyConstraint('make', ['make_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'make_id_contact_car_make_id_fk');
        $table->addForeignKeyConstraint('model', ['model_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'model_id_contact_car_model_id_fk');
        $table->addForeignKeyConstraint('car', ['car_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'car_id_contact_car_car_id_fk');
        $table->addForeignKeyConstraint('contact', ['contact_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'contact_id_contact_car_contact_id_fk');
        $table->addOption('engine' , 'InnoDB');
        
        
        $table = $schema->getTable('orders');
//        $table->removeForeignKey('client_id_orders_client_id_fk');
        $table->dropColumn('client_id');
        
        $table->addColumn('contact_id', 'integer', ['notnull' => true]);
        $table->addForeignKeyConstraint('contact', ['contact_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'contact_id_order_contact_id_fk');
                
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('contact_car');
        
        $table = $schema->getTable('orders');
//        $table->removeForeignKey('contact_id_order_contact_id_fk');
        $table->dropColumn('contact_id');
        $table->addColumn('client_id', 'integer', ['notnull' => true]);
    }
}
