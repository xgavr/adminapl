<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Stock\Entity\Pt;
use Stock\Entity\PtGood;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210531100251 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('pt');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('date_created', 'datetime', ['notnull'=>true]);
        $table->addColumn('comment', 'string', ['notnull'=>false, 'length'=>128]);
        $table->addColumn('info', 'json', ['notnull'=>false, 'length'=>512]);
        $table->addColumn('apl_id', 'integer', ['notnull'=>true, 'default'=> 0]);
        $table->addColumn('status', 'integer', ['notnull'=>true, 'default'=> Pt::STATUS_ACTIVE]);
        $table->addColumn('status_doc', 'integer', ['notnull'=>true, 'default'=> Pt::STATUS_DOC_NOT_RECD]);
        $table->addColumn('status_ex', 'integer', ['notnull'=>true, 'default'=> Pt::STATUS_EX_NEW]);
        $table->addColumn('doc_no', 'string', ['notnull'=>false, 'length'=>64]);
        $table->addColumn('doc_date', 'date', ['notnull'=>false]);
        $table->addColumn('amount', 'float', ['notnull'=>true, 'default'=>0]);
        $table->addColumn('office_id', 'integer', ['notnull'=>false]);
        $table->addColumn('company_id', 'integer', ['notnull'=>true]);
        $table->addColumn('office2_id', 'integer', ['notnull'=>false]);
        $table->addColumn('company2_id', 'integer', ['notnull'=>true]);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('office', ['office_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'office_id_pt_office_id_fk');
        $table->addForeignKeyConstraint('legal', ['company_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'legal_id_pt_company_id_fk');
        $table->addForeignKeyConstraint('office', ['office2_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'office_id_pt_office2_id_fk');
        $table->addForeignKeyConstraint('legal', ['company2_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'legal_id_pt_company2_id_fk');
        $table->addOption('engine' , 'InnoDB');

        $table = $schema->createTable('pt_good');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('comment', 'string', ['notnull'=>false, 'length'=>128]);
        $table->addColumn('info', 'json', ['notnull'=>false, 'length'=>512]);
        $table->addColumn('status', 'integer', ['notnull'=>true, 'default'=> PtGood::STATUS_ACTIVE]);
        $table->addColumn('status_doc', 'integer', ['notnull'=>true, 'default'=> PtGood::STATUS_DOC_NOT_RECD]);
        $table->addColumn('quantity', 'float', ['notnull'=>true, 'default'=>0]);
        $table->addColumn('amount', 'float', ['notnull'=>true, 'default'=>0]);
        $table->addColumn('row_no', 'integer', ['notnull'=>true]);
        $table->addColumn('pt_id', 'integer', ['notnull'=>true]);
        $table->addColumn('good_id', 'integer', ['notnull'=>true]);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('pt', ['pt_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'pt_id_pt_good_pt_id_fk');
        $table->addForeignKeyConstraint('goods', ['good_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'good_id_pt_good_good_id_fk');
        $table->addOption('engine' , 'InnoDB');        

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('pt');
        $schema->dropTable('pt_good');
    }
}
