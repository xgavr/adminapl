<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Company\Entity\BankAccount;
use Bank\Entity\Payment;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221007040214 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('bank_account');
        $table->addColumn('account_type', 'integer', ['notnull' => true, 'default' => BankAccount::ACСOUNT_CHECKING]);
        
        $table = $schema->createTable('bank_payment');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);
        $table->addColumn('bank_account_id', 'integer', ['notnull' => true]);
        $table->addColumn('user_id', 'integer', ['notnull' => true]);
        $table->addColumn('supplier_id', 'integer', ['notnull' => false]);
        $table->addColumn('counterparty_account_number', 'string', ['notnull' => false, 'length' => 20]);
        $table->addColumn('counterparty_bank_bic', 'string', ['notnull' => false, 'length' => 9]);
        $table->addColumn('counterparty_inn', 'string', ['notnull' => false, 'length' => 12]);
        $table->addColumn('counterparty_kpp', 'string', ['notnull' => false, 'length' => 9]);
        $table->addColumn('counterparty_name', 'string', ['notnull' => false, 'length' => 256]);
        $table->addColumn('payment_amount', 'float', ['notnull' => true, 'default' => 0]);
        $table->addColumn('payment_date', 'date', ['notnull' => true]);
        $table->addColumn('payment_priority', 'string', ['notnull' => true, 'length' => 1, 'default' => '1']);
        $table->addColumn('payment_purpose', 'string', ['notnull' => false, 'length' => 512]);
        $table->addColumn('nds', 'integer', ['notnull' => false, 'default' => Payment::NDS_20]);
        $table->addColumn('payment_purpose_code', 'string', ['notnull' => false, 'length' => 1, 'default' => '']);
        $table->addColumn('supplier_bill_id', 'string', ['notnull' => false, 'length' => 32]);
        $table->addColumn('tax_info_document_date', 'string', ['notnull' => false, 'length' => 32]);
        $table->addColumn('tax_info_document_number', 'string', ['notnull' => false, 'length' => 64]);
        $table->addColumn('tax_info_kbk', 'string', ['notnull' => false, 'length' => 32]);
        $table->addColumn('tax_info_okato', 'string', ['notnull' => false, 'length' => 32]);
        $table->addColumn('tax_info_period', 'string', ['notnull' => false, 'length' => 32]);
        $table->addColumn('tax_info_reason_code', 'string', ['notnull' => false, 'length' => 32]);
        $table->addColumn('tax_info_status', 'string', ['notnull' => false, 'length' => 32]);
        $table->addColumn('status', 'integer', ['notnull' => true, 'default' => Payment::STATUS_ACTIVE]);
        $table->addColumn('payment_type', 'integer', ['notnull' => true, 'default' => Payment::PAYMENT_TYPE_NORMAL]);
        $table->addColumn('status_message', 'string', ['notnull' => false, 'length' => 58]);
        $table->addColumn('request_id', 'string', ['notnull' => false, 'length' => 256]);
        $table->addColumn('date_created', 'datetime', ['notnull' => true]);        
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('bank_account', ['bank_account_id'], ['id'], 
                ['onUpdate'=>'CASCADE'], 'payment_account_id_bank_account_id_fk');
        $table->addForeignKeyConstraint('user', ['user_id'], ['id'], 
                ['onUpdate'=>'CASCADE'], 'bank_payment_user_id_user_id_fk');
        $table->addForeignKeyConstraint('supplier', ['supplier_id'], ['id'], 
                ['onUpdate'=>'CASCADE'], 'bank_payment_supplier_id_supplier_id_fk');
        $table->addOption('engine' , 'InnoDB');
        
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('bank_account');
        $table->dropColumn('account_type');
        
        $schema->dropTable('bank_payment');
    }
}
