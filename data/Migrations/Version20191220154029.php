<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Application\Entity\Rate;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191220154029 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        
        $table = $schema->createTable('scale');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('name', 'string', ['notnull'=>true, 'length'=>128]);
        $table->addColumn('min_price', 'float', ['notnull' => true, 'default' => 0]);
        $table->addColumn('max_price', 'float', ['notnull' => true, 'default' => 0]);
        $table->setPrimaryKey(['id']);
        $table->addOption('engine' , 'InnoDB');
        
        $table = $schema->createTable('scale_treshold');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('scale_id', 'integer', ['notnull'=>true]);
        $table->addColumn('treshold', 'float', ['notnull'=>true]); //порог
        $table->addColumn('rate', 'float', ['notnull'=>true]); //процент наценки
        $table->addColumn('rounding', 'integer', ['notnull'=>true, 'default' => -1]); //округление
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('scale', ['scale_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'scale_treshold_scale_id_scale_id_fk');
        $table->addOption('engine' , 'InnoDB');
        
        
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('rate');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('name', 'string', ['notnull'=>true, 'length'=>128]);
        $table->addColumn('status', 'integer', ['notnull'=>true, 'default' => Rate::STATUS_ACTIVE]);
        $table->addColumn('mode', 'integer', ['notnull'=>true, 'default' => Rate::MODE_MARKUP]);
        $table->addColumn('min_price', 'float', ['notnull' => true, 'default' => 0]);
        $table->addColumn('max_price', 'float', ['notnull' => true, 'default' => 0]);
        $table->addColumn('scale_id', 'integer', ['notnull'=>true]);
        $table->addColumn('office_id', 'integer', ['notnull'=>true]);
        $table->addColumn('supplier_id', 'integer', ['notnull'=>false]);
        $table->addColumn('producer_id', 'integer', ['notnull'=>false]);
        $table->addColumn('generic_group_id', 'integer', ['notnull'=>false]);
        $table->addColumn('token_group_id', 'integer', ['notnull'=>false]);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('scale', ['scale_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'rate_scale_id_scale_id_fk');
        $table->addForeignKeyConstraint('office', ['office_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'rate_office_id_office_id_fk');
        $table->addForeignKeyConstraint('supplier', ['supplier_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'rate_supplier_id_supplier_id_fk');
        $table->addForeignKeyConstraint('producer', ['producer_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'rate_producer_id_producer_id_fk');
        $table->addForeignKeyConstraint('generic_group', ['generic_group_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'rate_g_group_id_g_group_id_fk');
        $table->addForeignKeyConstraint('token_group', ['token_group_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'rate_t_group_id_t_group_id_fk');
        $table->addOption('engine' , 'InnoDB');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('rate');
        $table->removeForeignKey('rate_scale_id_scale_id_fk');
        $table->removeForeignKey('rate_office_id_office_id_fk');
        $table->removeForeignKey('rate_supplier_id_supplier_id_fk');
        $table->removeForeignKey('rate_producer_id_producer_id_fk');
        $table->removeForeignKey('rate_g_group_id_g_group_id_fk');
//        $table->removeForeignKey('rate_t_group_id_t_group_id_fk');
        $schema->dropTable('rate');
        $schema->dropTable('scale_treshold');
        $schema->dropTable('scale');
    }
}
