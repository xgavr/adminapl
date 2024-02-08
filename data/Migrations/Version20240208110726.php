<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Company\Entity\TaxMutual;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240208110726 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('tax_mutual');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('company_id', 'integer', ['notnull'=>true, 'comment' => 'Компания']);
        $table->addColumn('tax_id', 'integer', ['notnull'=>true, 'comment' => 'Сотрудник']);
        $table->addColumn('doc_id', 'integer', ['notnull'=>true, 'comment' => 'Номер документа']);
        $table->addColumn('doc_type', 'integer', ['notnull'=>true, 'comment' => 'Тип документа']);
        $table->addColumn('date_oper', 'date', ['notnull'=>true, 'comment' => 'Дата операции']);
        $table->addColumn('doc_key', 'string', ['notnull'=>true, 'length' => 64, 'comment' => 'Код документа']);
        $table->addColumn('doc_stamp', 'float', ['notnull'=>true, 'comment' => 'Метка документа']);
        $table->addColumn('amount', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Сумма']);
        $table->addColumn('status', 'integer', ['notnull'=>true,'default' => TaxMutual::STATUS_ACTIVE, 'comment' => 'Статус']);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['doc_type', 'doc_id'], 'doc_type_doc_id_indx');
        $table->addForeignKeyConstraint('legal', ['company_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'tax_mutual_company_id_legal_id_fk');
        $table->addForeignKeyConstraint('tax', ['tax_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'tax_mutual_tax_id_cost_id_fk');
        $table->addOption('engine' , 'InnoDB');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('tax_mutual');

    }
}
