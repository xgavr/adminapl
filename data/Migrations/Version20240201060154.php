<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Company\Entity\CostMutual;
use Company\Entity\Cost;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240201060154 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('cost_mutual');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('company_id', 'integer', ['notnull'=>true, 'comment' => 'Компания']);
        $table->addColumn('cost_id', 'integer', ['notnull'=>true, 'comment' => 'Сотрудник']);
        $table->addColumn('doc_id', 'integer', ['notnull'=>true, 'comment' => 'Номер документа']);
        $table->addColumn('doc_type', 'integer', ['notnull'=>true, 'comment' => 'Тип документа']);
        $table->addColumn('date_oper', 'date', ['notnull'=>true, 'comment' => 'Дата операции']);
        $table->addColumn('doc_key', 'string', ['notnull'=>true, 'length' => 64, 'comment' => 'Код документа']);
        $table->addColumn('doc_stamp', 'integer', ['notnull'=>true, 'comment' => 'Метка документа']);
        $table->addColumn('amount', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Сумма']);
        $table->addColumn('status', 'integer', ['notnull'=>true,'default' => CostMutual::STATUS_ACTIVE, 'comment' => 'Статус']);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['doc_type', 'doc_id'], 'doc_type_doc_id_indx');
        $table->addForeignKeyConstraint('legal', ['company_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'cost_mutual_company_id_legal_id_fk');
        $table->addForeignKeyConstraint('cost', ['cost_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'cost_mutual_cost_id_cost_id_fk');
        $table->addOption('engine' , 'InnoDB');

        $table = $schema->getTable('cost');
        $table->addColumn('kind', 'integer', ['notnull'=>true, 'default' => Cost::KIND_EXP, 'comment' => 'Тип']);
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('cost_mutual');

        $table = $schema->getTable('cost');
        $table->dropColumn('kind');
    }
}
