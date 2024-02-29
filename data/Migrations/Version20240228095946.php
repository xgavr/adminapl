<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Zp\Entity\PersonalRevise;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240228095946 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('personal_revise');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('company_id', 'integer', ['notnull'=>true, 'comment' => 'Компания']);
        $table->addColumn('user_id', 'integer', ['notnull'=>true, 'comment' => 'Сотрудник']);
        $table->addColumn('accrual_id', 'integer', ['notnull'=>true, 'comment' => 'Начсиление']);
        $table->addColumn('doc_date', 'date', ['notnull'=>true, 'comment' => 'Дата документа']);
        $table->addColumn('doc_num', 'string', ['notnull'=>false, 'comment' => 'Номер документа']);
        $table->addColumn('date_created', 'datetime', ['notnull'=>true, 'comment' => 'Дата создания']);
        $table->addColumn('amount', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Сумма']);
        $table->addColumn('comment', 'string', ['notnull'=>false, 'length' => 120, 'comment' => 'Комментарий']);
        $table->addColumn('status', 'integer', ['notnull'=>true,'default' => PersonalRevise::STATUS_ACTIVE, 'comment' => 'Статус']);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('legal', ['company_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'personal_revise_company_id_company_id_fk');
        $table->addForeignKeyConstraint('user', ['user_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'personal_revise_user_id_user_id_fk');
        $table->addForeignKeyConstraint('accrual', ['accrual_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'personal_revise_accrual_id_accrual_id_fk');
        $table->addOption('engine' , 'InnoDB');

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('personal_revise');        
    }
}
