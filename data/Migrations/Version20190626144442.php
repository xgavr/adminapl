<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190626144442 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {

        $table = $schema->createTable('vehicle_detail');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);
        $table->addColumn('name', 'string', ['notnull' => true, 'length' => 128]);
        $table->addColumn('name_apl', 'string', ['length' => 32, 'notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['name'], 'name_uindx');
        $table->addOption('engine' , 'InnoDB');
        
        $table = $schema->createTable('vehicle_detail_value');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);
        $table->addColumn('name', 'string', ['notnull' => true, 'length' => 128]);
        $table->addColumn('title', 'string', ['notnull' => true, 'length' => 128]);
        $table->addColumn('name_apl', 'string', ['length' => 128, 'notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['name'], 'name_uindx');
        $table->addOption('engine' , 'InnoDB');
        
        $table = $schema->createTable('vehicle_detail_car');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);
        $table->addColumn('car_id', 'integer', ['notnull'=>true]);
        $table->addColumn('vehicle_detail_id', 'integer', ['notnull'=>true]);
        $table->addColumn('vehicle_detail_value_id', 'integer', ['notnull'=>true]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['car_id', 'vehicle_detail_id'], 'car_vehicle_detail_uindx');
        $table->addForeignKeyConstraint('car', ['car_id'], ['id'], 
                ['onUpdate'=>'CASCADE', 'onDelete' => 'CASCADE'], 'car_id_car_vehicle_detail_car_id_fk');
        $table->addForeignKeyConstraint('vehicle_detail', ['vehicle_detail_id'], ['id'], 
                ['onUpdate'=>'CASCADE', 'onDelete' => 'CASCADE'], 'veh_detail_id_car_veh_detail_veh_detail_id_fk');
        $table->addForeignKeyConstraint('vehicle_detail_value', ['vehicle_detail_value_id'], ['id'], 
                ['onUpdate'=>'CASCADE', 'onDelete' => 'CASCADE'], 'veh_detail_val_id_veh_detail_val_veh_detail_val_id_fk');
        $table->addOption('engine' , 'InnoDB');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('vehicle_detail');
        $schema->dropTable('vehicle_detail_value');
        $schema->dropTable('vehicle_detail_car');

    }
}
