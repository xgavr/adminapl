<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Application\Entity\Ring;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210706094020 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('ring');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('doc_key', 'string', ['notnull'=>true, 'length'=>64]);
        $table->addColumn('date_oper', 'datetime', ['notnull'=>true]);
        $table->addColumn('status', 'integer', ['notnull'=>true, 'default'=> Retail::STATUS_ACTIVE]);
        $table->addColumn('revise', 'integer', ['notnull'=>true, 'default'=> Retail::REVISE_NOT]);
        $table->addColumn('amount', 'float', ['notnull'=>true, 'default'=>0]);
        $table->addColumn('contact_id', 'integer', ['notnull'=>true]);
        $table->addColumn('office_id', 'integer', ['notnull'=>false]);
        $table->addColumn('company_id', 'integer', ['notnull'=>true]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['doc_key'], 'doc_key_idx');
        $table->addIndex(['date_oper'], 'date_oper_idx');
        $table->addForeignKeyConstraint('contact', ['contact_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'contact_id_retail_contact_id_fk');
        $table->addForeignKeyConstraint('office', ['office_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'office_id_retail_office_id_fk');
        $table->addForeignKeyConstraint('legal', ['company_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'legal_id_retail_company_id_fk');
        $table->addOption('engine' , 'InnoDB');

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('ring');
    }
}
