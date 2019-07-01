<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Application\Entity\VehicleDetail;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190701130400 extends AbstractMigration
{
    
    /**
     * @param boolean $enabled
     */
    protected function setForeignKeyChecks($enabled)
    {
        $connection = $this->connection;
        $platform = $connection->getDatabasePlatform();
        if ($platform instanceof MySqlPlatform) {
            $connection->exec(sprintf('SET foreign_key_checks = %s;', (int)$enabled));
        }
    }

    /**
     * @param Schema $schema
     */
    public function preUp(Schema $schema)
    {
        parent::preUp($schema);
        $this->setForeignKeyChecks(false);
    }

    /**
     * @param Schema $schema
     */
    public function postUp(Schema $schema)
    {
        parent::postUp($schema);
        $this->setForeignKeyChecks(true);
    }

    /**
     * @param Schema $schema
     */
    public function preDown(Schema $schema)
    {
        parent::preDown($schema);
        $this->setForeignKeyChecks(false);
    }

    /**
     * @param Schema $schema
     */
    public function postDown(Schema $schema)
    {
        parent::postDown($schema);
        $this->setForeignKeyChecks(true);
    }
    
    
    public function up(Schema $schema) : void
    {
        
//        $schema->dropTable('vehicle_detail');
//        $schema->dropTable('vehicle_detail_value');
//        $schema->dropTable('vehicle_detail_car');
        
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('vehicle_detail');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);
        $table->addColumn('name', 'string', ['notnull' => true, 'length' => 128]);
        $table->addColumn('name_apl', 'string', ['length' => 32, 'notnull' => false]);
        $table->addColumn('status_edit', 'integer', ['notnull' => true, 'default' => VehicleDetail::CANNOT_VALUE_EDIT]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['name'], 'name_uindx');
        $table->addOption('engine' , 'InnoDB');
        
        $table = $schema->createTable('vehicle_detail_value');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);
        $table->addColumn('vehicle_detail_id', 'integer', ['notnull'=>true]);
        $table->addColumn('name', 'string', ['notnull' => true, 'length' => 128]);
        $table->addColumn('name_apl', 'string', ['length' => 128, 'notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['vehicle_detail_id', 'name'], 'v_d_id_name_uindx');
        $table->addForeignKeyConstraint('vehicle_detail', ['vehicle_detail_id'], ['id'], 
                ['onUpdate'=>'CASCADE', 'onDelete' => 'CASCADE'], 'v_d_id_v_d_v_v_d_id_fk');
        $table->addOption('engine' , 'InnoDB');
        
        $table = $schema->createTable('vehicle_detail_car');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);
        $table->addColumn('car_id', 'integer', ['notnull'=>true]);
        $table->addColumn('vehicle_detail_value_id', 'integer', ['notnull'=>true]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['car_id', 'vehicle_detail_value_id'], 'car_v_d_v_uindx');
        $table->addForeignKeyConstraint('car', ['car_id'], ['id'], 
                ['onUpdate'=>'CASCADE', 'onDelete' => 'CASCADE'], 'car_id_car_v_d_car_id_fk');
        $table->addForeignKeyConstraint('vehicle_detail_value', ['vehicle_detail_value_id'], ['id'], 
                ['onUpdate'=>'CASCADE', 'onDelete' => 'CASCADE'], 'v_d_v_id_v_d_v_v_d_v_id_fk');
        $table->addOption('engine' , 'InnoDB');

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
//        $schema->dropTable('vehicle_detail');
//        $schema->dropTable('vehicle_detail_value');
//        $schema->dropTable('vehicle_detail_car');

//        $table = $schema->createTable('vehicle_detail');
//        $table->addColumn('id', 'integer', ['autoincrement'=>true]);
//        $table->addColumn('name', 'string', ['notnull' => true, 'length' => 128]);
//        $table->addColumn('name_apl', 'string', ['length' => 32, 'notnull' => false]);
//        $table->setPrimaryKey(['id']);
//        $table->addUniqueIndex(['name'], 'name_uindx');
//        $table->addOption('engine' , 'InnoDB');
//        
//        $table = $schema->createTable('vehicle_detail_value');
//        $table->addColumn('id', 'integer', ['autoincrement'=>true]);
//        $table->addColumn('name', 'string', ['notnull' => true, 'length' => 128]);
//        $table->addColumn('title', 'string', ['notnull' => true, 'length' => 128]);
//        $table->addColumn('name_apl', 'string', ['length' => 128, 'notnull' => false]);
//        $table->setPrimaryKey(['id']);
//        $table->addUniqueIndex(['name'], 'name_uindx');
//        $table->addOption('engine' , 'InnoDB');
//        
//        $table = $schema->createTable('vehicle_detail_car');
//        $table->addColumn('id', 'integer', ['autoincrement'=>true]);
//        $table->addColumn('car_id', 'integer', ['notnull'=>true]);
//        $table->addColumn('vehicle_detail_id', 'integer', ['notnull'=>true]);
//        $table->addColumn('vehicle_detail_value_id', 'integer', ['notnull'=>true]);
//        $table->setPrimaryKey(['id']);
//        $table->addUniqueIndex(['car_id', 'vehicle_detail_id'], 'car_vehicle_detail_uindx');
//        $table->addForeignKeyConstraint('car', ['car_id'], ['id'], 
//                ['onUpdate'=>'CASCADE', 'onDelete' => 'CASCADE'], 'car_id_car_vehicle_detail_car_id_fk');
//        $table->addForeignKeyConstraint('vehicle_detail', ['vehicle_detail_id'], ['id'], 
//                ['onUpdate'=>'CASCADE', 'onDelete' => 'CASCADE'], 'veh_detail_id_car_veh_detail_veh_detail_id_fk');
//        $table->addForeignKeyConstraint('vehicle_detail_value', ['vehicle_detail_value_id'], ['id'], 
//                ['onUpdate'=>'CASCADE', 'onDelete' => 'CASCADE'], 'veh_detail_val_id_veh_detail_val_veh_detail_val_id_fk');
//        $table->addOption('engine' , 'InnoDB');
    }
}
