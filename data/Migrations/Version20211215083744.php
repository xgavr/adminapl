<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Stock\Entity\Vt;
use Stock\Entity\VtGood;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211215083744 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        // this up() migration is auto-generated, please modify it to your needs

        $table = $schema->createTable('vt');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('date_created', 'datetime', ['notnull'=>true]);
        $table->addColumn('comment', 'string', ['notnull'=>false, 'length'=>128]);
        $table->addColumn('info', 'json', ['notnull'=>false, 'length'=>512]);
        $table->addColumn('apl_id', 'integer', ['notnull'=>true, 'default'=> 0]);
        $table->addColumn('status', 'integer', ['notnull'=>true, 'default'=> Vt::STATUS_ACTIVE]);
        $table->addColumn('status_doc', 'integer', ['notnull'=>true, 'default'=> Vt::STATUS_DOC_NOT_RECD]);
        $table->addColumn('status_ex', 'integer', ['notnull'=>true, 'default'=> Vt::STATUS_EX_NEW]);
        $table->addColumn('doc_no', 'string', ['notnull'=>false, 'length'=>64]);
        $table->addColumn('doc_date', 'date', ['notnull'=>false]);
        $table->addColumn('amount', 'float', ['notnull'=>true, 'default'=>0]);
        $table->addColumn('order_id', 'integer', ['notnull'=>true]);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('orders', ['order_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'order_id_vt_order_id_fk');
        $table->addOption('engine' , 'InnoDB');

        $table = $schema->createTable('vt_good');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('comment', 'string', ['notnull'=>false, 'length'=>128]);
        $table->addColumn('info', 'json', ['notnull'=>false, 'length'=>512]);
        $table->addColumn('status', 'integer', ['notnull'=>true, 'default'=> VtGood::STATUS_ACTIVE]);
        $table->addColumn('status_doc', 'integer', ['notnull'=>true, 'default'=> VtGood::STATUS_DOC_NOT_RECD]);
        $table->addColumn('quantity', 'float', ['notnull'=>true, 'default'=>0]);
        $table->addColumn('amount', 'float', ['notnull'=>true, 'default'=>0]);
        $table->addColumn('row_no', 'integer', ['notnull'=>true]);
        $table->addColumn('vt_id', 'integer', ['notnull'=>true]);
        $table->addColumn('good_id', 'integer', ['notnull'=>true]);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('vt', ['vt_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'vt_id_vt_good_vt_id_fk');
        $table->addForeignKeyConstraint('goods', ['good_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'good_id_vt_good_good_id_fk');
        $table->addOption('engine' , 'InnoDB');

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('vt');
        $schema->dropTable('vt_good');

    }
}
