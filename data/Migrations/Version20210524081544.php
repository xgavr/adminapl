<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Stock\Entity\Ot;
use Stock\Entity\OtGood;
use Stock\Entity\Comiss;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210524081544 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $table = $schema->createTable('ot');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('date_created', 'datetime', ['notnull'=>true]);
        $table->addColumn('comment', 'string', ['notnull'=>false, 'length'=>128]);
        $table->addColumn('info', 'json', ['notnull'=>false, 'length'=>512]);
        $table->addColumn('apl_id', 'integer', ['notnull'=>true, 'default'=> 0]);
        $table->addColumn('status', 'integer', ['notnull'=>true, 'default'=> Ot::STATUS_ACTIVE]);
        $table->addColumn('status_doc', 'integer', ['notnull'=>true, 'default'=> Ot::STATUS_DOC_NOT_RECD]);
        $table->addColumn('status_ex', 'integer', ['notnull'=>true, 'default'=> Ot::STATUS_EX_NEW]);
        $table->addColumn('doc_no', 'string', ['notnull'=>false, 'length'=>64]);
        $table->addColumn('doc_date', 'date', ['notnull'=>false]);
        $table->addColumn('amount', 'float', ['notnull'=>true, 'default'=>0]);
        $table->addColumn('office_id', 'integer', ['notnull'=>false]);
        $table->addColumn('company_id', 'integer', ['notnull'=>true]);
        $table->addColumn('comiss_id', 'integer', ['notnull'=>false]);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('office', ['office_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'office_id_ot_office_id_fk');
        $table->addForeignKeyConstraint('legal', ['company_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'legal_id_ot_company_id_fk');
        $table->addForeignKeyConstraint('user', ['comiss_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'user_id_ot_comiss_id_fk');
        $table->addOption('engine' , 'InnoDB');

        $table = $schema->createTable('ot_good');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('comment', 'string', ['notnull'=>false, 'length'=>128]);
        $table->addColumn('info', 'json', ['notnull'=>false, 'length'=>512]);
        $table->addColumn('status', 'integer', ['notnull'=>true, 'default'=> OtGood::STATUS_ACTIVE]);
        $table->addColumn('status_doc', 'integer', ['notnull'=>true, 'default'=> OtGood::STATUS_DOC_NOT_RECD]);
        $table->addColumn('quantity', 'float', ['notnull'=>true, 'default'=>0]);
        $table->addColumn('amount', 'float', ['notnull'=>true, 'default'=>0]);
        $table->addColumn('row_no', 'integer', ['notnull'=>true]);
        $table->addColumn('ot_id', 'integer', ['notnull'=>true]);
        $table->addColumn('good_id', 'integer', ['notnull'=>true]);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('ot', ['ot_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'ot_id_ot_good_ot_id_fk');
        $table->addForeignKeyConstraint('goods', ['good_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'good_id_ot_good_good_id_fk');
        $table->addOption('engine' , 'InnoDB');        
        // this up() migration is auto-generated, please modify it to your needs

        $table = $schema->createTable('comiss');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('doc_key', 'string', ['notnull'=>true, 'length'=>64]);
        $table->addColumn('doc_row_key', 'string', ['notnull'=>true, 'length'=>64]);
        $table->addColumn('doc_row_no', 'integer', ['notnull'=>true]);
        $table->addColumn('date_oper', 'datetime', ['notnull'=>true]);
        $table->addColumn('status', 'integer', ['notnull'=>true, 'default'=> Comiss::STATUS_ACTIVE]);
        $table->addColumn('quantity', 'float', ['notnull'=>true, 'default'=>0]);
        $table->addColumn('amount', 'float', ['notnull'=>true, 'default'=>0]);
        $table->addColumn('good_id', 'integer', ['notnull'=>true]);
        $table->addColumn('office_id', 'integer', ['notnull'=>false]);
        $table->addColumn('company_id', 'integer', ['notnull'=>true, 'default' => 0]);
        $table->addColumn('user_id', 'integer', ['notnull'=>true]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['doc_key'], 'doc_key_idx');
        $table->addIndex(['date_oper'], 'date_oper_idx');
        $table->addForeignKeyConstraint('goods', ['good_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'good_id_comiss_good_id_fk');
        $table->addForeignKeyConstraint('office', ['office_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'office_id_comiss_office_id_fk');
        $table->addForeignKeyConstraint('legal', ['company_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'legal_id_comiss_company_id_fk');
        $table->addForeignKeyConstraint('user', ['user_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'user_id_comiss_user_id_fk');
        $table->addOption('engine' , 'InnoDB');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('ot');
        $schema->dropTable('ot_good');
        $schema->dropTable('comiss');

    }
}
