<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180823155049 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('bank_statement');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);
        $table->addColumn('bic', 'string', ['notnull' => true, 'length' => 9]);
        $table->addColumn('account', 'string', ['notnull' => true, 'length' => 20]);
        $table->addColumn('counterparty_account_number', 'string', ['notnull' => false, 'length' => 20]);
        $table->addColumn('counterparty_bank_bic', 'string', ['notnull' => false, 'length' => 9]);
        $table->addColumn('counterparty_bank_name', 'string', ['notnull' => false, 'length' => 256]);
        $table->addColumn('counterparty_inn', 'string', ['notnull' => false, 'length' => 12]);
        $table->addColumn('counterparty_kpp', 'string', ['notnull' => false, 'length' => 9]);
        $table->addColumn('counterparty_name', 'string', ['notnull' => false, 'length' => 256]);
        $table->addColumn('operation_type', 'integer', ['notnull' => false, 'default' => 0]);
        $table->addColumn('payment_amount', 'float', ['notnull' => true, 'default' => 0]);
        $table->addColumn('payment_bank_system_id', 'string', ['notnull' => false, 'length' => 128]);
        $table->addColumn('payment_charge_date', 'date', ['notnull' => true]);
        $table->addColumn('payment_date', 'date', ['notnull' => true]);
        $table->addColumn('payment_number', 'string', ['notnull' => false, 'length' => 32]);
        $table->addColumn('payment_purpose', 'string', ['notnull' => false, 'length' => 512]);
        $table->addColumn('supplier_bill_id', 'string', ['notnull' => false, 'length' => 32]);
        $table->addColumn('tax_info_document_date', 'string', ['notnull' => false, 'length' => 32]);
        $table->addColumn('tax_info_document_number', 'string', ['notnull' => false, 'length' => 64]);
        $table->addColumn('tax_info_kbk', 'string', ['notnull' => false, 'length' => 32]);
        $table->addColumn('tax_info_okato', 'string', ['notnull' => false, 'length' => 32]);
        $table->addColumn('tax_info_period', 'string', ['notnull' => false, 'length' => 32]);
        $table->addColumn('tax_info_reason_code', 'string', ['notnull' => false, 'length' => 32]);
        $table->addColumn('tax_info_status', 'string', ['notnull' => false, 'length' => 32]);
        $table->addColumn('x_payment_id', 'string', ['notnull' => true, 'length' => 128]);
        $table->addColumn('swap1', 'integer', ['notnull' => true, 'default' => 0]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['bic', 'account', 'payment_charge_date', 'x_payment_id'], 'bic_account_payment_charge_date_x_payment_id_uidx');
        $table->addOption('engine' , 'InnoDB');

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('bank_statement');

    }
}
