<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use GoodMap\Entity\Rack;
use GoodMap\Entity\Fold;
use GoodMap\Entity\Shelf;
use GoodMap\Entity\Cell;
use GoodMap\Entity\FoldBalance;
use GoodMap\Entity\FoldDoc;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241004154828 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Карта склада';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('rack');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('office_id', 'integer', ['notnull'=>true, 'comment' => 'Склад']);
        $table->addColumn('code', 'string', ['notnull'=>false, 'length' => 120, 'comment' => 'Код']);
        $table->addColumn('name', 'string', ['notnull'=>false, 'length' => 120, 'comment' => 'Наименование']);
        $table->addColumn('comment', 'string', ['notnull'=>false, 'length' => 120, 'comment' => 'Комментарий']);
        $table->addColumn('fold_count', 'integer', ['notnull'=>true,'default' => 0, 'comment' => 'Движений']);
        $table->addColumn('status', 'integer', ['notnull'=>true,'default' => Rack::STATUS_ACTIVE, 'comment' => 'Статус']);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('office', ['office_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'rack_office_id_office_id_fk');
        $table->addOption('engine' , 'InnoDB');
        
        $table = $schema->createTable('shelf');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('rack_id', 'integer', ['notnull'=>true, 'comment' => 'Стеллаж']);
        $table->addColumn('code', 'string', ['notnull'=>false, 'length' => 120, 'comment' => 'Код']);
        $table->addColumn('name', 'string', ['notnull'=>false, 'length' => 120, 'comment' => 'Наименование']);
        $table->addColumn('comment', 'string', ['notnull'=>false, 'length' => 120, 'comment' => 'Комментарий']);
        $table->addColumn('fold_count', 'integer', ['notnull'=>true,'default' => 0, 'comment' => 'Движений']);
        $table->addColumn('status', 'integer', ['notnull'=>true,'default' => Shelf::STATUS_ACTIVE, 'comment' => 'Статус']);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('rack', ['rack_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'shelf_rack_id_rack_id_fk');
        $table->addOption('engine' , 'InnoDB');
        
        $table = $schema->createTable('cell');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('shelf_id', 'integer', ['notnull'=>true, 'comment' => 'Полка']);
        $table->addColumn('code', 'string', ['notnull'=>false, 'length' => 120, 'comment' => 'Код']);
        $table->addColumn('name', 'string', ['notnull'=>false, 'length' => 120, 'comment' => 'Наименование']);
        $table->addColumn('comment', 'string', ['notnull'=>false, 'length' => 120, 'comment' => 'Комментарий']);
        $table->addColumn('fold_count', 'integer', ['notnull'=>true,'default' => 0, 'comment' => 'Движений']);
        $table->addColumn('status', 'integer', ['notnull'=>true,'default' => Cell::STATUS_ACTIVE, 'comment' => 'Статус']);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('shelf', ['shelf_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'cell_shelf_id_shelf_id_fk');
        $table->addOption('engine' , 'InnoDB');

        $table = $schema->createTable('fold');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('status', 'integer', ['notnull'=>true,'default' => Fold::STATUS_ACTIVE, 'comment' => 'Статус']);
        $table->addColumn('doc_key', 'string', ['notnull'=>false, 'length' => 24, 'comment' => 'Ключ документа']);
        $table->addColumn('doc_type', 'integer', ['notnull'=>false, 'comment' => 'Тип документа']);
        $table->addColumn('doc_id', 'integer', ['notnull'=>false, 'comment' => 'Ид документа']);
        $table->addColumn('doc_stamp', 'float', ['notnull'=>false, 'comment' => 'Штамп документа']);
        $table->addColumn('date_oper', 'datetime', ['notnull'=>true, 'comment' => 'Дата']);
        $table->addColumn('quantity', 'float', ['notnull'=>true, 'comment' => 'Количество']);
        $table->addColumn('good_id', 'integer', ['notnull'=>true, 'comment' => 'Товар']);
        $table->addColumn('office_id', 'integer', ['notnull'=>true, 'comment' => 'Склад']);
        $table->addColumn('rack_id', 'integer', ['notnull'=>false, 'comment' => 'Стеллаж']);
        $table->addColumn('shelf_id', 'integer', ['notnull'=>false, 'comment' => 'Полка']);
        $table->addColumn('cell_id', 'integer', ['notnull'=>false, 'comment' => 'Ячейка']);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['doc_key'], 'doc_key_indx');
        $table->addIndex(['doc_id', 'doc_type', 'date_oper', 'status'], 'doc_id_type_indx');
        $table->addIndex(['date_oper'], 'date_oper_indx');
        $table->addIndex(['doc_stamp'], 'doc_stamp_indx');
        $table->addForeignKeyConstraint('goods', ['good_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'fold_good_id_goods_id_fk');
        $table->addForeignKeyConstraint('office', ['office_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'fold_office_id_office_id_fk');
        $table->addForeignKeyConstraint('rack', ['rack_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'fold_rack_id_rack_id_fk');
        $table->addForeignKeyConstraint('shelf', ['shelf_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'fold_shelf_id_shelf_id_fk');
        $table->addForeignKeyConstraint('cell', ['cell_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'fold_cell_id_cell_id_fk');
        $table->addOption('engine' , 'InnoDB');
        
        $table = $schema->createTable('fold_balance');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('status', 'integer', ['notnull'=>true,'default' => FoldBalance::STATUS_ACTIVE, 'comment' => 'Статус']);
        $table->addColumn('rest', 'float', ['notnull'=>true, 'comment' => 'Остаток']);
        $table->addColumn('good_id', 'integer', ['notnull'=>true, 'comment' => 'Товар']);
        $table->addColumn('office_id', 'integer', ['notnull'=>true, 'comment' => 'Склад']);
        $table->addColumn('rack_id', 'integer', ['notnull'=>false, 'comment' => 'Стеллаж']);
        $table->addColumn('shelf_id', 'integer', ['notnull'=>false, 'comment' => 'Полка']);
        $table->addColumn('cell_id', 'integer', ['notnull'=>false, 'comment' => 'Ячейка']);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['good_id', 'office_id', 'rack_id', 'shelf_id', 'cell_id'], 'location_uindx');
        $table->addForeignKeyConstraint('goods', ['good_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'fold_balance_good_id_goods_id_fk');
        $table->addForeignKeyConstraint('office', ['office_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'fold_balance_office_id_office_id_fk');
        $table->addForeignKeyConstraint('rack', ['rack_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'fold_balance_rack_id_rack_id_fk');
        $table->addForeignKeyConstraint('shelf', ['shelf_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'fold_balance_shelf_id_shelf_id_fk');
        $table->addForeignKeyConstraint('cell', ['cell_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'fold_balance_cell_id_cell_id_fk');
        $table->addOption('engine' , 'InnoDB');
        
        $table = $schema->createTable('fold_doc');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('status', 'integer', ['notnull'=>true,'default' => FoldDoc::STATUS_ACTIVE, 'comment' => 'Статус']);
        $table->addColumn('kind', 'integer', ['notnull'=>true,'default' => FoldDoc::KIND_IN, 'comment' => 'Вид']);
        $table->addColumn('doc_date', 'date', ['notnull'=>true, 'comment' => 'Дата']);
        $table->addColumn('date_created', 'datetime', ['notnull'=>true, 'comment' => 'Дата создания']);
        $table->addColumn('quantity', 'float', ['notnull'=>true, 'comment' => 'Количество']);
        $table->addColumn('good_id', 'integer', ['notnull'=>true, 'comment' => 'Товар']);
        $table->addColumn('office_id', 'integer', ['notnull'=>true, 'comment' => 'Склад']);
        $table->addColumn('rack_id', 'integer', ['notnull'=>false, 'comment' => 'Стеллаж']);
        $table->addColumn('shelf_id', 'integer', ['notnull'=>false, 'comment' => 'Полка']);
        $table->addColumn('cell_id', 'integer', ['notnull'=>false, 'comment' => 'Ячейка']);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['doc_date', 'status'], 'date_status_indx');
        $table->addForeignKeyConstraint('goods', ['good_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'fold_doc_good_id_goods_id_fk');
        $table->addForeignKeyConstraint('office', ['office_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'fold_doc_office_id_office_id_fk');
        $table->addForeignKeyConstraint('rack', ['rack_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'fold_doc_rack_id_rack_id_fk');
        $table->addForeignKeyConstraint('shelf', ['shelf_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'fold_doc_shelf_id_shelf_id_fk');
        $table->addForeignKeyConstraint('cell', ['cell_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'fold_doc_cell_id_cell_id_fk');
        $table->addOption('engine' , 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('fold_doc');
        $schema->dropTable('fold_balance');
        $schema->dropTable('fold');
        $schema->dropTable('cell');
        $schema->dropTable('shelf');
        $schema->dropTable('rack');
    }
}
