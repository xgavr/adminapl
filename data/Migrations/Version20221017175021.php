<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Company\Entity\Contract;
use Bank\Entity\Statement;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221017175021 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('bank_account');
        $table->addColumn('cash_id', 'integer', ['notnull' => false]);
        $table->addForeignKeyConstraint('cash', ['cash_id'], ['id'], 
                ['onUpdate'=>'CASCADE'], 'bank_account_cash_id_cash_id_fk');

        $table = $schema->getTable('contract');
        $table->addColumn('nds', 'integer', ['notnull' => true, 'default' => Contract::NDS_20]);

        $table = $schema->getTable('bank_statement');
        $table->addColumn('pay', 'integer', ['notnull' => true, 'default' => Statement::PAY_NEW]);
        $table->addColumn('cash_doc_id', 'integer', ['notnull' => false]);
        $table->addForeignKeyConstraint('cash_doc', ['cash_doc_id'], ['id'], 
                ['onUpdate'=>'CASCADE'], 'statement_cash_doc_id_cash_doc_id_fk');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('bank_account');
        $table->removeForeignKey('bank_account_cash_id_cash_id_fk');
        $table->dropColumn('cash_id');

        $table = $schema->getTable('contract');
        $table->dropColumn('nds');

        $table = $schema->getTable('bank_statement');
        $table->dropColumn('pay');
        $table->removeForeignKey('statement_cash_doc_id_cash_doc_id_fk');
        $table->dropColumn('cash_doc_id');
    }
}
