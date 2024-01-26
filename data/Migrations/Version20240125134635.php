<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Zp\Entity\DocCalculator;
use Zp\Entity\OrderCalculator;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240125134635 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('doc_calculator');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('date_oper', 'date', ['notnull'=>true, 'comment' => 'Дата операции']);
        $table->addColumn('date_created', 'datetime', ['notnull'=>true, 'comment' => 'Дата создания']);
        $table->addColumn('user_id', 'integer', ['notnull'=>true, 'comment' => 'Сотрудник']);
        $table->addColumn('company_id', 'integer', ['notnull'=>true, 'comment' => 'Компания']);
        $table->addColumn('position_id', 'integer', ['notnull'=>true, 'comment' => 'Должность']);
        $table->addColumn('accrual_id', 'integer', ['notnull'=>true, 'comment' => 'Начисление']);
        $table->addColumn('base', 'float', ['notnull'=>true, 'default' => 1, 'comment' => 'База расчета']);
        $table->addColumn('rate', 'float', ['notnull'=>true, 'default' => 1, 'comment' => 'Тариф']);
        $table->addColumn('num', 'float', ['notnull'=>true, 'default' => 1, 'comment' => 'Ставка']);
        $table->addColumn('amount', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Начислено']);
        $table->addColumn('status', 'integer', ['notnull'=>true,'default' => DocCalculator::STATUS_ACTIVE, 'comment' => 'Статус']);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('user', ['user_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'doc_calculator_user_id_user_id_fk');
        $table->addForeignKeyConstraint('legal', ['company_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'doc_calculator_company_id_legal_id_fk');
        $table->addForeignKeyConstraint('position', ['position_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'doc_calculator_position_id_position_id_fk');
        $table->addForeignKeyConstraint('accrual', ['accrual_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'doc_calculator_accrual_id_accrual_id_fk');
        $table->addOption('engine' , 'InnoDB');
                
        $table = $schema->createTable('order_calculator');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('date_oper', 'date', ['notnull'=>true, 'comment' => 'Дата операции']);
        $table->addColumn('date_created', 'datetime', ['notnull'=>true, 'comment' => 'Дата создания']);
        $table->addColumn('order_id', 'integer', ['notnull'=>true, 'comment' => 'Заказ']);
        $table->addColumn('doc_type', 'integer', ['notnull'=>true, 'comment' => 'Тип документа']);
        $table->addColumn('doc_id', 'integer', ['notnull'=>true, 'comment' => 'Ид документа']);
        $table->addColumn('company_id', 'integer', ['notnull'=>true, 'comment' => 'Компания']);
        $table->addColumn('office_id', 'integer', ['notnull'=>true, 'comment' => 'Офис']);
        $table->addColumn('user_id', 'integer', ['notnull'=>true, 'comment' => 'Сотрудник']);
        $table->addColumn('shipping_id', 'integer', ['notnull'=>false, 'comment' => 'Вид доставки']);
        $table->addColumn('courier_id', 'integer', ['notnull'=>false, 'comment' => 'Курьер']);
        $table->addColumn('amount', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Сумма заказа']);
        $table->addColumn('pay_amount', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Оплачено']);
        $table->addColumn('delivery_amount', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Сумма доставки']);
        $table->addColumn('base_amount', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Закупка']);
        $table->addColumn('status', 'integer', ['notnull'=>true,'default' => OrderCalculator::STATUS_ACTIVE, 'comment' => 'Статус']);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['doc_type', 'doc_id'], 'dox_type_doc_id_indx');
        $table->addForeignKeyConstraint('orders', ['order_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'order_calculator_order_id_orders_id_fk');
        $table->addForeignKeyConstraint('legal', ['company_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'order_calculator_company_id_legal_id_fk');
        $table->addForeignKeyConstraint('office', ['office_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'order_calculator_office_id_office_id_fk');
        $table->addForeignKeyConstraint('user', ['user_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'order_calculator_user_id_user_id_fk');
        $table->addForeignKeyConstraint('user', ['courier_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'order_calculator_courier_id_user_id_fk');
        $table->addForeignKeyConstraint('shipping', ['shipping_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'order_calculator_shipping_id_shipping_id_fk');
        $table->addOption('engine' , 'InnoDB');
                
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('doc_calculator');
        $schema->dropTable('order_calculator');
    }
}
