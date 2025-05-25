<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250525105105 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('good_relations');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);
        $table->addColumn('order_id', 'integer', ['notnull' => true, 'comment' => 'Заказ']);
        $table->addColumn('good_id', 'integer', ['notnull' => true, 'comment' => 'Товар']);
        $table->addColumn('good_related_id', 'integer', ['notnull' => true, 'comment' => 'Товар связанный']);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['order_id', 'good_id', 'good_related_id'], 'order_good_related_uindx');
        $table->addForeignKeyConstraint('orders', ['order_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'good_relations_order_id_orders_id_fk');
        $table->addForeignKeyConstraint('goods', ['good_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'good_relations_good_id_goods_id_fk');
        $table->addForeignKeyConstraint('goods', ['good_related_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'good_relations_good_related_id_goods_id_fk');
        $table->addOption('engine' , 'InnoDB');        
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('good_relations');
    }
}
