<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use ApiMarketPlace\Entity\Marketplace;
use ApiMarketPlace\Entity\MarketSaleReportItem;
use ApiMarketPlace\Entity\MarketSaleReport;
use Stock\Entity\Comitent;
use Stock\Entity\ComitentBalance;
use Stock\Entity\ComissBalance;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230428065646 extends AbstractMigration
{
    /**
     * @param boolean $enabled
     */
    protected function setForeignKeyChecks($enabled)
    {
        $connection = $this->connection;
        $platform = $connection->getDatabasePlatform();
        if ($platform instanceof MySqlPlatform) {
            $connection->exec(sprintf('SET foreign_key_checks = %s;', (int)$enabled));
        }
    }

    /**
     * @param Schema $schema
     */
    public function preUp(Schema $schema): void
    {
        parent::preUp($schema);
        $this->setForeignKeyChecks(false);
    }

    /**
     * @param Schema $schema
     */
    public function postUp(Schema $schema): void
    {
        parent::postUp($schema);
        $this->setForeignKeyChecks(true);
    }

    /**
     * @param Schema $schema
     */
    public function preDown(Schema $schema): void
    {
        parent::preDown($schema);
        $this->setForeignKeyChecks(false);
    }

    /**
     * @param Schema $schema
     */
    public function postDown(Schema $schema): void
    {
        parent::postDown($schema);
        $this->setForeignKeyChecks(true);
    }
    
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('market_sale_report');
        $table->addColumn('id', 'integer', ['autoincrement'=>true, 'comment' => 'идентификатор']);        
        $table->addColumn('marketplace_id', 'integer', ['notnull'=>true, 'comment' => 'торговая площадка']);
        $table->addColumn('status', 'integer', ['notnull'=>true, 'comment' => 'статус']);
        $table->addColumn('date_created', 'datetime', ['notnull'=>true, 'comment' => 'дата создания']);
        $table->addColumn('num', 'string', ['notnull'=>false, 'length' => 24, 'comment' => 'номер отчета']);
        $table->addColumn('doc_date', 'date', ['notnull'=>true, 'comment' => 'дата отчета']);
        $table->addColumn('start_date', 'date', ['notnull'=>true, 'comment' => 'начало периода']);
        $table->addColumn('stop_date', 'date', ['notnull'=>true, 'comment' => 'конец периода']);
        $table->addColumn('contract_id', 'integer', ['notnull'=>false, 'comment' => 'договор']);
        $table->addColumn('doc_amount', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'сумма отчета']);
        $table->addColumn('total_amount', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'сумма начислено']);
        $table->addColumn('vat_amount', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'сумма НДС']);
        $table->addColumn('currency_code', 'string', ['notnull'=>false, 'length' => 6, 'default' => 'RUR', 'comment' => 'валюта отчета']);
        $table->addColumn('status_doc', 'integer', ['notnull'=>true, 'default' => MarketSaleReport::STATUS_DOC_NOT_RECD, 'comment' => 'Получено']);
        $table->addColumn('status_ex', 'integer', ['notnull'=>true, 'default' => MarketSaleReport::STATUS_EX_NEW, 'comment' => 'Отправлено']);
        $table->addColumn('status_account', 'integer', ['notnull'=>true, 'default' => MarketSaleReport::STATUS_ACCOUNT_NO, 'comment' => 'Учтено']);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('marketplace', ['marketplace_id'], ['id'], 
                ['onUpdate'=>'CASCADE', 'onDelete'=>'CASCADE'], 'marketplace_id_msr_marketplace_id_fk');
        $table->addForeignKeyConstraint('contract', ['contract_id'], ['id'], 
                ['onUpdate'=>'CASCADE', 'onDelete'=>'CASCADE'], 'contract_id_msr_contract_id_fk');
        $table->addOption('engine' , 'InnoDB');    

        $table = $schema->createTable('market_sale_report_item');
        $table->addColumn('id', 'integer', ['autoincrement'=>true, 'comment' => 'идентификатор']);        
        $table->addColumn('market_sale_report_id', 'integer', ['notnull'=>true, 'comment' => 'отчет торговой площадки']);
        $table->addColumn('product_id', 'string', ['notnull'=>false, 'length' => 24, 'comment' => 'ид товара на площадке']);
        $table->addColumn('offer_id', 'string', ['notnull'=>false, 'length' => 24, 'comment' => 'ид товара в апл']);
        $table->addColumn('product_name', 'string', ['notnull'=>false, 'length' => 256, 'comment' => 'наименование товара на площадке']);
        $table->addColumn('good_id', 'integer', ['notnull'=>false, 'comment' => 'товар']);
        $table->addColumn('barcode', 'string', ['notnull'=>false, 'length' => 64, 'comment' => 'баркод']);
        $table->addColumn('price', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'цена']);
        $table->addColumn('commission_percent', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'процент комиссии']);
        $table->addColumn('price_sale', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'цена продажи']);
        $table->addColumn('sale_qty', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'количество продано']);
        $table->addColumn('sale_amount', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'сумма продажи']);
        $table->addColumn('sale_discount', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Доплата за счёт торговой площадки']);
        $table->addColumn('sale_commission', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Комиссия за реализованный товар с учётом скидок и наценки']);
        $table->addColumn('sale_price_seller', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Итого к начислению за реализованный товар']);
        $table->addColumn('return_sale', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Цена реализации']);
        $table->addColumn('return_qty', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Количество возвращённого товара']);
        $table->addColumn('return_amount', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Возвращено на сумму']);
        $table->addColumn('return_discount', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Доплата за счёт торговой площадки']);
        $table->addColumn('return_commission', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Комиссия с учётом количества товара']);
        $table->addColumn('return_price_seller', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Итого возвращено']);
        $table->addColumn('take', 'integer', ['notnull'=>true, 'default' => MarketSaleReportItem::TAKE_NO, 'comment' => 'Проведено']);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('market_sale_report', ['market_sale_report_id'], ['id'], 
                ['onUpdate'=>'CASCADE', 'onDelete'=>'CASCADE'], 'msr_id_msr_msrei_msr_id_fk');
        $table->addOption('engine' , 'InnoDB');    
        $table->addForeignKeyConstraint('goods', ['good_id'], ['id'], 
                ['onUpdate'=>'CASCADE', 'onDelete'=>'CASCADE'], 'good_id_msrei_good_id_fk');
        $table->addOption('engine' , 'InnoDB'); 
        
        $table = $schema->getTable('marketplace');
        $table->addColumn('contact_id', 'integer', ['notnull' => false]);
        $table->addColumn('contract_id', 'integer', ['notnull' => false]);
        $table->addColumn('market_type', 'integer', ['notnull' => true, 'default' => Marketplace::TYPE_UNKNOWN]);
        $table->addForeignKeyConstraint('contact', ['contact_id'], ['id'], 
                ['onUpdate'=>'CASCADE', 'onDelete'=>'CASCADE'], 'contact_id_marketplace_contact_id_fk');
        $table->addForeignKeyConstraint('contract', ['contract_id'], ['id'], 
                ['onUpdate'=>'CASCADE', 'onDelete'=>'CASCADE'], 'contract_id_marketplace_contract_id_fk');
        
        $table = $schema->getTable('orders');
        $table->addColumn('contract_id', 'integer', ['notnull' => false]);
        $table->addForeignKeyConstraint('contract', ['contract_id'], ['id'], 
                ['onUpdate'=>'CASCADE', 'onDelete'=>'CASCADE'], 'contract_id_orders_contract_id_fk');

        $table = $schema->createTable('comitent');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('doc_key', 'string', ['notnull'=>true, 'length'=>64]);
        $table->addColumn('doc_type', 'integer', ['notnull' => true, 'default' => 0]);
        $table->addColumn('doc_id', 'integer', ['notnull' => true, 'default' => 0]);
        $table->addColumn('doc_row_key', 'string', ['notnull'=>true, 'length'=>64]);
        $table->addColumn('doc_row_no', 'integer', ['notnull'=>true]);
        $table->addColumn('doc_stamp', 'float', ['notnull' => true, 'default' => 0]);
        $table->addColumn('base_key', 'string', ['notnull'=>true, 'length'=>64]);
        $table->addColumn('base_type', 'integer', ['notnull' => true, 'default' => 0]);
        $table->addColumn('base_id', 'integer', ['notnull' => true, 'default' => 0]);
        $table->addColumn('date_oper', 'datetime', ['notnull'=>true]);
        $table->addColumn('status', 'integer', ['notnull'=>true, 'default'=> Comitent::STATUS_ACTIVE]);
        $table->addColumn('quantity', 'float', ['notnull'=>true, 'default'=>0]);
        $table->addColumn('amount', 'float', ['notnull'=>true, 'default'=>0]);
        $table->addColumn('good_id', 'integer', ['notnull'=>true]);
        $table->addColumn('legal_id', 'integer', ['notnull'=>false]);
        $table->addColumn('company_id', 'integer', ['notnull'=>true, 'default' => 0]);
        $table->addColumn('contract_id', 'integer', ['notnull'=>true]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['doc_key'], 'doc_key_idx');
        $table->addIndex(['date_oper'], 'date_oper_idx');
        $table->addIndex(['doc_type', 'doc_id'], 'doc_indx');
        $table->addIndex(['doc_stamp'], 'doc_stamp_indx');
        $table->addForeignKeyConstraint('goods', ['good_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'good_id_comitent_good_id_fk');
        $table->addForeignKeyConstraint('legal', ['company_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'legal_id_comitent_company_id_fk');
        $table->addForeignKeyConstraint('legal', ['legal_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'legal_id_comitent_legal_id_fk');
        $table->addForeignKeyConstraint('contract', ['contract_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'contract_id_comitent_contract_id_fk');
        $table->addOption('engine' , 'InnoDB');
        
        $table = $schema->createTable('comitent_balance');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('good_id', 'integer', ['notnull'=>true]);
        $table->addColumn('legal_id', 'integer', ['notnull' => true]);
        $table->addColumn('company_id', 'integer', ['notnull' => true]);
        $table->addColumn('contract_id', 'integer', ['notnull' => true]);
        $table->addColumn('base_stamp', 'float', ['notnull' => false]);
        $table->addColumn('rest', 'float', ['notnull' => true, 'default' => 0]);
        $table->addColumn('price', 'float', ['notnull' => true, 'default' => 0]);
        $table->addColumn('status', 'integer', ['notnull' => true, 'default' => ComitentBalance::STATUS_ACTIVE]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['good_id', 'company_id', 'legal_id', 'contract_id'], 'good_leg_com_con_uindx');
        $table->addForeignKeyConstraint('goods', ['good_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'good_id_com_bal_good_id_fk');
        $table->addForeignKeyConstraint('legal', ['legal_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'legal_id_com_bal_legal_id_fk');
        $table->addForeignKeyConstraint('legal', ['company_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'legal_id_com_bal_company_id_fk');
        $table->addForeignKeyConstraint('contract', ['contract_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'contract_id_com_bal_contract_id_fk');
        $table->addForeignKeyConstraint('register', ['base_stamp'], ['doc_stamp'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'reg_doc_stamp_com_bal_base_stamp_fk');
        $table->addOption('engine' , 'InnoDB');        
        
        $table = $schema->createTable('comiss_balance');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('good_id', 'integer', ['notnull'=>true]);
        $table->addColumn('contact_id', 'integer', ['notnull' => true]);
        $table->addColumn('base_stamp', 'float', ['notnull' => false]);
        $table->addColumn('rest', 'float', ['notnull' => true, 'default' => 0]);
        $table->addColumn('price', 'float', ['notnull' => true, 'default' => 0]);
        $table->addColumn('status', 'integer', ['notnull' => true, 'default' => ComissBalance::STATUS_ACTIVE]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['good_id', 'contact_id'], 'good_contact_uindx');
        $table->addForeignKeyConstraint('goods', ['good_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'good_id_comiss_bal_good_id_fk');
        $table->addForeignKeyConstraint('contact', ['contact_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'contact_id_comiss_bal_contact_id_fk');
        $table->addForeignKeyConstraint('register', ['base_stamp'], ['doc_stamp'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'reg_doc_stamp_comiss_bal_base_stamp_fk');
        $table->addOption('engine' , 'InnoDB');        
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('market_sale_report_item');
        $schema->dropTable('market_sale_report');
        $schema->dropTable('comitent');
        $schema->dropTable('comitent_balance');
        $schema->dropTable('comiss_balance');

        $table = $schema->getTable('marketplace');
        $table->removeForeignKey('contact_id_marketplace_contact_id_fk');
        $table->removeForeignKey('contract_id_marketplace_contract_id_fk');
        $table->dropColumn('contact_id');
        $table->dropColumn('contract_id');
        $table->dropColumn('market_type');

        $table = $schema->getTable('orders');
        $table->removeForeignKey('contract_id_orders_contract_id_fk');
        $table->dropColumn('contract_id');
    }
}
