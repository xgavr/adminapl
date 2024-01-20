<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240119091825 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('fin_opu');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('period', 'date', ['notnull'=>true, 'comment' => 'Период отчета']);
        $table->addColumn('company_id', 'integer', ['notnull'=>true, 'comment' => 'Компания']);
        $table->addColumn('status', 'integer', ['notnull'=>true, 'comment' => 'Тип план-факт']);
        $table->addColumn('revenue_retail', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Выручка розничная']);
        $table->addColumn('revenue_tp', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Выручка ТП']);
        $table->addColumn('revenue_total', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Выручка общая']);
        $table->addColumn('purchase_retail', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Закупка розничная']);
        $table->addColumn('purchase_tp', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Закупка ТП']);
        $table->addColumn('purchase_total', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Закупка общая']);
        $table->addColumn('cost_retail', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Расходы розничные']);
        $table->addColumn('cost_tp', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Расходы ТП']);
        $table->addColumn('cost_fix', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Расходы постоянные']);
        $table->addColumn('cost_total', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Расходы всего']);
        $table->addColumn('zp_retail', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'ЗП розничные']);
        $table->addColumn('zp_tp', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'ЗП ТП']);
        $table->addColumn('zp_adm', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'ЗП администрации']);
        $table->addColumn('zp_total', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'ЗП всего']);
        $table->addColumn('profit', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Прибыль']);
        $table->addColumn('tax', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Налог']);
        $table->addColumn('fund', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Фонды']);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('legal', ['company_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'fin_opu_search_company_id_legal_id_fk');
        $table->addOption('engine' , 'InnoDB');

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('fin_opu');
    }
}
