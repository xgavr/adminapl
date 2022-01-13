<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Stock\Entity\Revise;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220111060015 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('revise');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('apl_id', 'integer', ['notnull'=>false]);
        $table->addColumn('date_created', 'datetime', ['notnull'=>true]);
        $table->addColumn('doc_date', 'date', ['notnull'=>true]);
        $table->addColumn('doc_no', 'string', ['notnull'=>false, 'length'=>64]);
        $table->addColumn('comment', 'string', ['notnull'=>false, 'length'=>256]);
        $table->addColumn('info', 'text', ['notnull'=>false]);
        $table->addColumn('amount', 'float', ['notnull'=>true, 'default' => 0.0]);
        $table->addColumn('status', 'integer', ['notnull'=>true, 'default'=> Revise::STATUS_ACTIVE]);
        $table->addColumn('status_doc', 'integer', ['notnull'=>true, 'default'=> Revise::STATUS_DOC_NOT_RECD]);
        $table->addColumn('status_ex', 'integer', ['notnull'=>true, 'default'=> Revise::STATUS_EX_NEW]);
        $table->addColumn('office_id', 'integer', ['notnull'=>true]);
        $table->addColumn('company_id', 'integer', ['notnull'=>true]);
        $table->addColumn('legal_id', 'integer', ['notnull'=>false]);
        $table->addColumn('contract_id', 'integer', ['notnull'=>false]);
        $table->addColumn('contact_id', 'integer', ['notnull'=>false]);
        $table->addColumn('user_creator_id', 'integer', ['notnull'=>false]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['doc_date'], 'doc_date_indx');
        $table->addForeignKeyConstraint('office', ['office_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'office_id_revise_office_id_fk');
        $table->addForeignKeyConstraint('legal', ['company_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'legal_id_revise_company_id_fk');
        $table->addForeignKeyConstraint('legal', ['legal_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'legal_id_revise_legal_id_fk');
        $table->addForeignKeyConstraint('contract', ['contract_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'contract_id_revise_contract_id_fk');
        $table->addForeignKeyConstraint('contact', ['contact_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'contact_id_revise_contact_id_fk');
        $table->addForeignKeyConstraint('user', ['user_creator_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'user_id_revise_user_creator_id_fk');
        $table->addOption('engine' , 'InnoDB');

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('revise');
    }
}
