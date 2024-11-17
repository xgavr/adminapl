<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Application\Entity\OrderPhone;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241117085336 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('order_phone');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('order_id', 'integer', ['notnull'=>true, 'comment' => 'Заказ']);
        $table->addColumn('phone_id', 'integer', ['notnull'=>true, 'comment' => 'Телефон']);
        $table->addColumn('kind', 'integer', ['notnull'=>true, 'comment' => 'Вид телефона']);
        $table->addColumn('comment', 'string', ['notnull'=>false, 'length' => 120, 'comment' => 'Комментарий']);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['order_id', 'phone_id'], 'order_phone_uindx');
        $table->addForeignKeyConstraint('orders', ['order_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'order_phone_order_id_order_id_fk');
        $table->addForeignKeyConstraint('phone', ['phone_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'order_phone_phone_id_phone_id_fk');
        $table->addOption('engine' , 'InnoDB');
        
        $table = $schema->createTable('order_email');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('order_id', 'integer', ['notnull'=>true, 'comment' => 'Заказ']);
        $table->addColumn('email_id', 'integer', ['notnull'=>true, 'comment' => 'Email']);
        $table->addColumn('comment', 'string', ['notnull'=>false, 'length' => 120, 'comment' => 'Комментарий']);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['order_id', 'email_id'], 'order_phone_uindx');
        $table->addForeignKeyConstraint('orders', ['order_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'order_email_order_id_order_id_fk');
        $table->addForeignKeyConstraint('email', ['email_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'order_email_email_id_email_id_fk');
        $table->addOption('engine' , 'InnoDB');
        
        $table = $schema->getTable('orders');
        $table->addColumn('client_name', 'string', ['notnull' => false, 'length' => 120, 'comment' => 'Имя покупателя']);
        
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('order_phone');
        $schema->dropTable('order_email');
        
        $table = $schema->getTable('orders');
        $table->dropColumn('client_name');
    }
}
