<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Application\Entity\Car;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201220122838 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('car_fill_title');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);
        $table->addColumn('name', 'string', ['notnull' => true, 'length' => 128]);
        $table->addColumn('title', 'string', ['notnull' => true, 'length' => 128]);
        $table->setPrimaryKey(['id']);
        $table->addOption('engine' , 'InnoDB');

        $table = $schema->createTable('car_fill_type');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);
        $table->addColumn('name', 'string', ['notnull' => true, 'length' => 128]);
        $table->addColumn('title', 'string', ['notnull' => true, 'length' => 128]);
        $table->setPrimaryKey(['id']);
        $table->addOption('engine' , 'InnoDB');

        $table = $schema->createTable('car_fill_unit');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);
        $table->addColumn('name', 'string', ['notnull' => true, 'length' => 128]);
        $table->addColumn('title', 'string', ['notnull' => true, 'length' => 128]);
        $table->setPrimaryKey(['id']);
        $table->addOption('engine' , 'InnoDB');

        $table = $schema->createTable('car_fill_volume');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);
        $table->addColumn('car_fill_title_id', 'integer', ['notnull'=>true]);
        $table->addColumn('car_fill_type_id', 'integer', ['notnull'=>true]);
        $table->addColumn('car_fill_unit_id', 'integer', ['notnull'=>true]);
        $table->addColumn('car_id', 'integer', ['notnull'=>true]);
        $table->addColumn('lang', 'integer', ['notnull'=>true]);
        $table->addColumn('status', 'integer', ['notnull'=>true]);
        $table->addColumn('volume', 'string', ['notnull' => true, 'length' => 128]);
        $table->addColumn('info', 'string', ['notnull' => false, 'length' => 256]);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('car_fill_title', ['car_fill_title_id'], ['id'], 
                ['onUpdate'=>'CASCADE', 'onDelete' => 'CASCADE'], 'car_fill_title_id_car_fill_volume_car_fill_title_id_fk');
        $table->addForeignKeyConstraint('car_fill_type', ['car_fill_type_id'], ['id'], 
                ['onUpdate'=>'CASCADE', 'onDelete' => 'CASCADE'], 'car_fill_type_id_car_fill_volume_car_fill_type_id_fk');
        $table->addForeignKeyConstraint('car_fill_unit', ['car_fill_unit_id'], ['id'], 
                ['onUpdate'=>'CASCADE', 'onDelete' => 'CASCADE'], 'car_fill_unit_id_car_fill_volume_car_fill_unit_id_fk');
        $table->addForeignKeyConstraint('car', ['car_id'], ['id'], 
                ['onUpdate'=>'CASCADE', 'onDelete' => 'CASCADE'], 'car_id_car_fill_volume_car_id_fk');
        $table->addOption('engine' , 'InnoDB');
        
        $table = $schema->getTable('car');
        $table->addColumn('fill_volumes_flag', 'integer', ['notnull'=>true, 'default' => Car::FILL_VOLUMES_NO]);
        $table->addColumn('transfer_fill_volumes_flag', 'integer', ['notnull'=>true, 'default' => Car::FILL_VOLUMES_TRANSFER_NO]);
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('car_fill_title');
        $schema->dropTable('car_fill_type');
        $schema->dropTable('car_fill_unit');
        $schema->dropTable('car_fill_volume');

        $table = $schema->getTable('car');
        $table->dropColumn('fill_volumes_flag');
        $table->dropColumn('transfer_fill_volumes_flag');

    }
}
