<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Cash\Entity\CashDoc;
use Cash\Entity\CashTransaction;
use Cash\Entity\UserTransaction;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211218163134 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('cash_doc');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('apl_id', 'integer', ['notnull'=>false]);
        $table->addColumn('date_created', 'datetime', ['notnull'=>true]);
        $table->addColumn('date_oper', 'datetime', ['notnull'=>true]);
        $table->addColumn('comment', 'string', ['notnull'=>false, 'length'=>256]);
        $table->addColumn('info', 'text', ['notnull'=>false]);
        $table->addColumn('amount', 'float', ['notnull'=>true, 'default' => 0.0]);
        $table->addColumn('status', 'integer', ['notnull'=>true, 'default'=> CashDoc::STATUS_ACTIVE]);
        $table->addColumn('check_status', 'integer', ['notnull'=>true, 'default'=> CashDoc::CHECK_ACTIVE]);
        $table->addColumn('kind', 'integer', ['notnull'=>true, 'default'=> CashDoc::KIND_IN_PAYMENT_CLIENT]);
        $table->addColumn('cash_id', 'integer', ['notnull'=>false]);
        $table->addColumn('cash_refill_id', 'integer', ['notnull'=>false]);
        $table->addColumn('user_id', 'integer', ['notnull'=>false]);
        $table->addColumn('user_refill_id', 'integer', ['notnull'=>false]);
        $table->addColumn('contact_id', 'integer', ['notnull'=>false]);
        $table->addColumn('order_id', 'integer', ['notnull'=>false]);
        $table->addColumn('vt_id', 'integer', ['notnull'=>false]);
        $table->addColumn('cost_id', 'integer', ['notnull'=>false]);
        $table->addColumn('legal_id', 'integer', ['notnull'=>false]);
        $table->addColumn('company_id', 'integer', ['notnull'=>false]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['date_oper'], 'date_oper_indx');
        $table->addIndex(['kind'], 'kind_indx');
        $table->addForeignKeyConstraint('cash', ['cash_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'cash_id_cashdoc_cash_id_fk');
        $table->addForeignKeyConstraint('cash', ['cash_refill_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'cash_id_cashdoc_cash_refill_id_fk');
        $table->addForeignKeyConstraint('user', ['user_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'user_id_cashdoc_user_id_fk');
        $table->addForeignKeyConstraint('user', ['user_refill_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'user_id_cashdoc_user_refill_id_fk');
        $table->addForeignKeyConstraint('contact', ['contact_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'contact_id_cashdoc_contact_id_fk');
        $table->addForeignKeyConstraint('orders', ['order_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'orders_id_cashdoc_order_id_fk');
        $table->addForeignKeyConstraint('vt', ['vt_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'vt_id_cashdoc_vt_id_fk');
        $table->addForeignKeyConstraint('cost', ['cost_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'cost_id_cashdoc_cost_id_fk');
        $table->addForeignKeyConstraint('legal', ['legal_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'legal_id_cashdoc_legal_id_fk');
        $table->addForeignKeyConstraint('legal', ['company_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'legal_id_cashdoc_company_id_fk');
        $table->addOption('engine' , 'InnoDB');

        $table = $schema->createTable('cash_transaction');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('date_created', 'datetime', ['notnull'=>true]);
        $table->addColumn('date_oper', 'date', ['notnull'=>true]);
        $table->addColumn('amount', 'float', ['notnull'=>true, 'default' => 0.0]);
        $table->addColumn('status', 'integer', ['notnull'=>true, 'default'=> CashTransaction::STATUS_ACTIVE]);
        $table->addColumn('cash_doc_id', 'integer', ['notnull'=>true]);
        $table->addColumn('cash_id', 'integer', ['notnull'=>true]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['cash_doc_id', 'date_oper'], 'cash_doc_id_date_oper_indx');
        $table->addForeignKeyConstraint('cash_doc', ['cash_doc_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'cashdoc_id_cashtrans_cashdoc_id_fk');
        $table->addForeignKeyConstraint('cash', ['cash_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'cash_id_cashtrans_cash_id_fk');
        $table->addOption('engine' , 'InnoDB');

        $table = $schema->createTable('user_transaction');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('date_created', 'datetime', ['notnull'=>true]);
        $table->addColumn('date_oper', 'date', ['notnull'=>true]);
        $table->addColumn('amount', 'float', ['notnull'=>true, 'default' => 0.0]);
        $table->addColumn('status', 'integer', ['notnull'=>true, 'default'=> UserTransaction::STATUS_ACTIVE]);
        $table->addColumn('cash_doc_id', 'integer', ['notnull'=>true]);
        $table->addColumn('user_id', 'integer', ['notnull'=>true]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['cash_doc_id', 'date_oper'], 'cash_doc_id_date_oper_indx');
        $table->addForeignKeyConstraint('cash_doc', ['cash_doc_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'cashdoc_id_usertrans_cashdoc_id_fk');
        $table->addForeignKeyConstraint('user', ['user_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'user_id_usertrans_user_id_fk');
        $table->addOption('engine' , 'InnoDB');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('cash_doc');
        $schema->dropTable('cash_transaction');
        $schema->dropTable('user_transaction');
    }
}
