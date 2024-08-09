<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240806143031 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Движение денежных средств';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('fin_dds');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('period', 'date', ['notnull'=>true, 'comment' => 'Период отчета']);
        $table->addColumn('company_id', 'integer', ['notnull'=>true, 'comment' => 'Компания']);
        $table->addColumn('status', 'integer', ['notnull'=>true, 'comment' => 'Тип план-факт']);
        
        $table->addColumn('bank_begin', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Банк начало']);
        $table->addColumn('cash_begin', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Касса начало']);
        $table->addColumn('accountant_begin', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Подотчет начало']);
        $table->addColumn('total_begin', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Всего начало']);
        
        $table->addColumn('bank_end', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Банк конец']);
        $table->addColumn('cash_end', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Касса конец']);
        $table->addColumn('accountant_end', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Подотчет конец']);
        $table->addColumn('total_end', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Всего конец']);

        $table->addColumn('revenue_in', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Поступление выручки']);
        $table->addColumn('revenue_out', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Возврат выручки']);
        
        $table->addColumn('supplier_out', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Оплата поставшикам']);
        $table->addColumn('supplier_in', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Возврат от поставщиков']);
        
        $table->addColumn('zp', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Зарплата']);
        $table->addColumn('tax', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Налоги']);
        $table->addColumn('cost', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Оплата услуг']);
        
        $table->addColumn('loans_in', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Поступление кредитов']);
        $table->addColumn('loans_out', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Возврат кредитов']);
        $table->addColumn('deposit_in', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Поступление депозитов']);
        $table->addColumn('deposit_out', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Возврат депозитов']);

        $table->addColumn('other_in', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Прочие поступления']);
        $table->addColumn('other_out', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Прочие выбытия']);
        
        $table->addColumn('total_in', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Всего поступления']);
        $table->addColumn('total_out', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Всего выбытия']);
        
        $table->addColumn('good_begin', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Товары начало']);
        $table->addColumn('good_end', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Товары конец']);
        $table->addColumn('good_in', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Товары поступило']);
        $table->addColumn('good_out', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Товары выбыло']);
        
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('legal', ['company_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'fin_dds_company_id_legal_id_fk');
        $table->addOption('engine' , 'InnoDB');

        $table = $schema->getTable('bank_balance');
        $table->addColumn('company_id', 'integer', ['notnull'=>true, 'comment' => 'Компания', 'default' => 20]);
        $table->addForeignKeyConstraint('legal', ['company_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'bank_balance_company_id_legal_id_fk');

        $table = $schema->getTable('bank_statement');
        $table->addColumn('company_id', 'integer', ['notnull'=>true, 'comment' => 'Компания', 'default' => 20]);
        $table->addForeignKeyConstraint('legal', ['company_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'bank_statement_company_id_legal_id_fk');

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('fin_dds');

        $table = $schema->getTable('bank_balance');
        $table->removeForeignKey('bank_balance_company_id_legal_id_fk');
        $table->dropColumn('company_id');

        $table = $schema->getTable('bank_statement');
        $table->removeForeignKey('bank_statement_company_id_legal_id_fk');
        $table->dropColumn('company_id');
    }
}
