<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250329043936 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('fin_balance');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('period', 'date', ['notnull'=>true, 'comment' => 'Период отчета']);
        $table->addColumn('company_id', 'integer', ['notnull'=>true, 'comment' => 'Компания']);
        $table->addColumn('status', 'integer', ['notnull'=>true, 'comment' => 'Тип план-факт']);
        
        $table->addColumn('goods', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Товары']);
        $table->addColumn('cash', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Деньги']);
        $table->addColumn('supplier_debtor', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Долг поставщиков']);
        $table->addColumn('client_debtor', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Долг покупателей']);        
        $table->addColumn('deposit', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Депозиты']);
        $table->addColumn('other_assets', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Прочие активы']);
        $table->addColumn('total_assets', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Всего активов']);

        $table->addColumn('supplier_credit', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Долг поставщикам']);
        $table->addColumn('client_credit', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Долг покупателям']);        
        $table->addColumn('zp', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Долг по ЗП']);
        $table->addColumn('loans', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Кредиты']);        
        $table->addColumn('other_passive', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Прочие пассивы']);
        $table->addColumn('income', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Прибыль']);
        $table->addColumn('dividends', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Дивиденты']);        
        $table->addColumn('total_passive', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Всего пассивов']);
        
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('legal', ['company_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'fin_balance_company_id_legal_id_fk');
        $table->addOption('engine' , 'InnoDB');

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('fin_balance');
    }
}
