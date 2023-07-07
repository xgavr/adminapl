<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Bank\Entity\QrCodePayment;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230706085649 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('qrcode_payment');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('ref_transaction_id', 'string', ['notnull'=>true, 'length' => 128, 'comment' => 'Идентификатор операции или ID запроса возврата']);
        $table->addColumn('amount', 'float', ['notnull'=>true, 'comment' => 'Сумма в рублях', 'default' => 0]);
        $table->addColumn('purpose', 'string', ['notnull'=>false, 'length' => 128,  'comment' => 'Назначение платежа']);
        $table->addColumn('payment_message', 'string', ['notnull'=>false, 'length' => 64,  'comment' => 'Текстовое представление статуса']);
        $table->addColumn('date_created', 'datetime', ['notnull'=>true]);
        $table->addColumn('status', 'integer', ['notnull'=>true, 'comment' => 'Статус объекта', 'default' => QrCodePayment::STATUS_ACTIVE]);        
        $table->addColumn('payment_type', 'integer', ['notnull'=>true, 'comment' => 'Тип операции', 'default' => QrCodePayment::TYPE_PAYMENT]);        
        $table->addColumn('payment_status', 'integer', ['notnull'=>true, 'comment' => 'Статус операции', 'default' => QrCodePayment::PAYMENT_CONFIRMING]);                
        $table->addColumn('qrcode_id', 'integer', ['notnull'=>true]);
        $table->addColumn('bank_account_id', 'integer', ['notnull'=>false]);
        $table->addColumn('office_id', 'integer', ['notnull'=>true]);
        $table->addColumn('order_id', 'integer', ['notnull'=>false]);
        $table->addColumn('contact_id', 'integer', ['notnull'=>false]);
        $table->addColumn('cash_doc_id', 'integer', ['notnull'=>false]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['ref_transaction_id']);
        $table->addIndex(['payment_type']);
        $table->addForeignKeyConstraint('qrcode', ['qrcode_id'], ['id'], 
                ['onUpdate'=>'CASCADE', 'onDelete'=>'CASCADE'], 'qrcode_id_qrcodepayment_qrcode_id_fk');
        $table->addForeignKeyConstraint('bank_account', ['bank_account_id'], ['id'], 
                ['onUpdate'=>'CASCADE', 'onDelete'=>'CASCADE'], 'ba_id_payment_ba_id_fk');
        $table->addForeignKeyConstraint('office', ['office_id'], ['id'], 
                ['onUpdate'=>'CASCADE', 'onDelete'=>'CASCADE'], 'office_id_payment_offcice_id_fk');
        $table->addForeignKeyConstraint('orders', ['order_id'], ['id'], 
                ['onUpdate'=>'CASCADE', 'onDelete'=>'CASCADE'], 'order_id_payment_order_id_fk');
        $table->addForeignKeyConstraint('contact', ['contact_id'], ['id'], 
                ['onUpdate'=>'CASCADE', 'onDelete'=>'CASCADE'], 'contact_id_qrcodepayment_contact_id_fk');
        $table->addForeignKeyConstraint('cash_doc', ['cash_doc_id'], ['id'], 
                ['onUpdate'=>'CASCADE', 'onDelete'=>'CASCADE'], 'cashdoc_id_qrcodepayment_cashdoc_id_fk');
        $table->addOption('engine' , 'InnoDB');    

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('qrcode_payment');
    }
}
