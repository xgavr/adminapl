<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Bank\Entity\QrCode;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230620042855 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('qrcode');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('account', 'string', ['notnull'=>true, 'length' => 256, 'comment' => 'Уникальный и неизменный идентификатор счёта юрлица']);
        $table->addColumn('merchant_id', 'string', ['notnull'=>true, 'length' => 12, 'comment' => 'Идентификатор ТСП в СБП']);
        $table->addColumn('amount', 'integer', ['notnull'=>true, 'comment' => 'Сумма в копейках']);
        $table->addColumn('currency', 'string', ['notnull'=>true, 'length' => 3,  'comment' => 'Валюта операции']);
        $table->addColumn('date_created', 'datetime', ['notnull'=>true]);
        $table->addColumn('payment_purpose', 'string', ['notnull'=>false, 'length' => 256,  'comment' => 'Дополнительная информация от ТСП']);
        $table->addColumn('qrc_type', 'integer', ['notnull'=>true, 'comment' => 'Тип QR-кода', 'default' => QrCode::QR_Dynamic]);
        $table->addColumn('qrc_id', 'string', ['notnull'=>true, 'length' => 128,  'comment' => 'Идентификатор QR-кода в СБП']);
        $table->addColumn('payload', 'string', ['notnull'=>true, 'length' => 512,  'comment' => 'Payload зарегистрированного QR-кода в СБП']);
        $table->addColumn('image_width', 'integer', ['notnull'=>true, 'comment' => 'Ширина изображения']);
        $table->addColumn('image_hieght', 'integer', ['notnull'=>true, 'comment' => 'Высота изображения']);
        $table->addColumn('image_media_type', 'string', ['notnull'=>true, 'length' => 24, 'comment' => 'Тип контента']);
        $table->addColumn('image_content', 'text', ['notnull'=>true, 'length' => 512, 'comment' => 'Содержимое изображения']);
        $table->addColumn('source_name', 'string', ['notnull'=>false, 'length' => 24, 'comment' => 'Название источника']);
        $table->addColumn('order_apl_id', 'integer', ['notnull'=>false, 'comment' => 'Номер заказа в Апл']);
        $table->addColumn('ttl', 'integer', ['notnull'=>true, 'default' => 0, 'comment' => 'Период использования QR-кода в минутах']);
        $table->addColumn('status', 'integer', ['notnull'=>true, 'comment' => 'Статус объекта', 'default' => QrCode::STATUS_ACTIVE]);        
        $table->addColumn('bank_account_id', 'integer', ['notnull'=>false]);
        $table->addColumn('office_id', 'integer', ['notnull'=>true]);
        $table->addColumn('order_id', 'integer', ['notnull'=>false]);
        $table->addColumn('contact_id', 'integer', ['notnull'=>false]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['qrc_id']);
        $table->addIndex(['order_apl_id', 'amount']);
        $table->addForeignKeyConstraint('bank_account', ['bank_account_id'], ['id'], 
                ['onUpdate'=>'CASCADE', 'onDelete'=>'CASCADE'], 'ba_id_qrcode_ba_id_fk');
        $table->addForeignKeyConstraint('office', ['office_id'], ['id'], 
                ['onUpdate'=>'CASCADE', 'onDelete'=>'CASCADE'], 'office_id_qrcode_offcice_id_fk');
        $table->addForeignKeyConstraint('orders', ['order_id'], ['id'], 
                ['onUpdate'=>'CASCADE', 'onDelete'=>'CASCADE'], 'order_id_qrcode_order_id_fk');
        $table->addForeignKeyConstraint('contact', ['contact_id'], ['id'], 
                ['onUpdate'=>'CASCADE', 'onDelete'=>'CASCADE'], 'contact_id_qrcode_contact_id_fk');
        $table->addOption('engine' , 'InnoDB');    
        
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('qrcode');
    }
}
