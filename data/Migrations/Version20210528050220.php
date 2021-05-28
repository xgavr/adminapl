<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Stock\Entity\St;
use Stock\Entity\StGood;
use Company\Entity\Cost;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210528050220 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('cost');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('name', 'string', ['notnull'=>true, 'length'=>256]);        
        $table->addColumn('apl_id', 'integer', ['notnull' => true, 'default' => 0]);
        $table->addColumn('status', 'integer', ['notnull'=>true, 'default'=> Cost::STATUS_ACTIVE]);
        $table->setPrimaryKey(['id']);
        $table->addOption('engine' , 'InnoDB');

        $table = $schema->createTable('st');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('date_created', 'datetime', ['notnull'=>true]);
        $table->addColumn('comment', 'string', ['notnull'=>false, 'length'=>128]);
        $table->addColumn('info', 'json', ['notnull'=>false, 'length'=>512]);
        $table->addColumn('apl_id', 'integer', ['notnull'=>true, 'default'=> 0]);
        $table->addColumn('status', 'integer', ['notnull'=>true, 'default'=> St::STATUS_ACTIVE]);
        $table->addColumn('status_doc', 'integer', ['notnull'=>true, 'default'=> St::STATUS_DOC_NOT_RECD]);
        $table->addColumn('status_ex', 'integer', ['notnull'=>true, 'default'=> St::STATUS_EX_NEW]);
        $table->addColumn('write_off', 'integer', ['notnull'=>true, 'default'=> St::WRITE_PAY]);
        $table->addColumn('doc_no', 'string', ['notnull'=>false, 'length'=>64]);
        $table->addColumn('doc_date', 'date', ['notnull'=>false]);
        $table->addColumn('amount', 'float', ['notnull'=>true, 'default'=>0]);
        $table->addColumn('office_id', 'integer', ['notnull'=>false]);
        $table->addColumn('company_id', 'integer', ['notnull'=>true]);
        $table->addColumn('user_id', 'integer', ['notnull'=>false]);
        $table->addColumn('cost_id', 'integer', ['notnull'=>false]);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('office', ['office_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'office_id_st_office_id_fk');
        $table->addForeignKeyConstraint('legal', ['company_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'legal_id_st_company_id_fk');
        $table->addForeignKeyConstraint('user', ['user_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'user_id_st_user_id_fk');
        $table->addForeignKeyConstraint('cost', ['cost_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'cost_id_st_cost_id_fk');
        $table->addOption('engine' , 'InnoDB');

        $table = $schema->createTable('st_good');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('comment', 'string', ['notnull'=>false, 'length'=>128]);
        $table->addColumn('info', 'json', ['notnull'=>false, 'length'=>512]);
        $table->addColumn('status', 'integer', ['notnull'=>true, 'default'=> StGood::STATUS_ACTIVE]);
        $table->addColumn('status_doc', 'integer', ['notnull'=>true, 'default'=> StGood::STATUS_DOC_NOT_RECD]);
        $table->addColumn('quantity', 'float', ['notnull'=>true, 'default'=>0]);
        $table->addColumn('amount', 'float', ['notnull'=>true, 'default'=>0]);
        $table->addColumn('row_no', 'integer', ['notnull'=>true]);
        $table->addColumn('st_id', 'integer', ['notnull'=>true]);
        $table->addColumn('good_id', 'integer', ['notnull'=>true]);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('st', ['st_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'st_id_st_good_ot_id_fk');
        $table->addForeignKeyConstraint('goods', ['good_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'good_id_st_good_good_id_fk');
        $table->addOption('engine' , 'InnoDB');        
        
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('st_good');
        $schema->dropTable('st');
        $schema->dropTable('cost');
    }
}
