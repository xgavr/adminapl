<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190211151316 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('car_attribute_group');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);
        $table->addColumn('name', 'string', ['notnull' => true, 'length' => 128]);
        $table->setPrimaryKey(['id']);
        $table->addOption('engine' , 'InnoDB');

        $table = $schema->createTable('car_attribute_type');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);
        $table->addColumn('car_attribute_group_id', 'integer', ['notnull'=>true]);
        $table->addColumn('name', 'string', ['notnull' => true, 'length' => 128]);
        $table->addColumn('title', 'string', ['notnull' => true, 'length' => 128]);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('car_attribute_group', ['car_attribute_group_id'], ['id'], 
                ['onUpdate'=>'CASCADE', 'onDelete' => 'CASCADE'], 'car_attr_group_id_car_attr_type_car_attr_group_id_fk');
        $table->addOption('engine' , 'InnoDB');
        
        $table = $schema->createTable('car');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);
        $table->addColumn('model_id', 'integer', ['notnull' => true]);
        $table->addColumn('td_id', 'integer', ['notnull' => true]);
        $table->addColumn('apl_id', 'integer', ['notnull' => true]);
        $table->addColumn('passenger', 'integer', ['notnull' => true]);
        $table->addColumn('commerc', 'integer', ['notnull' => true]);
        $table->addColumn('moto', 'integer', ['notnull' => true]);
        $table->addColumn('name', 'string', ['notnull' => true, 'length' => 256]);
        $table->addColumn('fullname', 'string', ['notnull' => true, 'length' => 256]);
        $table->addColumn('status', 'integer', ['notnull' => true, 'default' => 0]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['td_id'], 'td_id_uindx');
        $table->addIndex(['apl_id'], 'apl_id_indx');
        $table->addForeignKeyConstraint('model', ['model_id'], ['id'], 
                ['onUpdate'=>'CASCADE', 'onDelete' => 'CASCADE'], 'model_id_car_model_id_fk');
        $table->addOption('engine' , 'InnoDB');
        
        $table = $schema->createTable('car_attribute_value');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);
        $table->addColumn('car_attribute_type_id', 'integer', ['notnull'=>true]);
        $table->addColumn('car_id', 'integer', ['notnull'=>true]);
        $table->addColumn('value', 'string', ['notnull' => true, 'length' => 128]);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('car_attribute_type', ['car_attribute_type_id'], ['id'], 
                ['onUpdate'=>'CASCADE', 'onDelete' => 'CASCADE'], 'car_attr_type_id_car_attr_value_car_attr_type_id_fk');
        $table->addForeignKeyConstraint('car', ['car_id'], ['id'], 
                ['onUpdate'=>'CASCADE', 'onDelete' => 'CASCADE'], 'car_id_car_attribute_value_car_id_fk');
        $table->addOption('engine' , 'InnoDB');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('car_attribute_group');
        $schema->dropTable('car_attribute_type');
        $schema->dropTable('car');
        $schema->dropTable('car_attribute_value');
    }
}
