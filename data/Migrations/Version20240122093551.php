<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Zp\Entity\Position;
use Zp\Entity\Accrual;
use Zp\Entity\Personal;
use Zp\Entity\PositionAccrual;
use Zp\Entity\PersonalAccrual;
use Zp\Entity\PersonalMutual;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240122093551 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('position');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('parent_id', 'integer', ['notnull'=>false, 'comment' => 'группа']);
        $table->addColumn('apl_id', 'integer', ['notnull'=>false, 'comment' => 'Код АПЛ']);
        $table->addColumn('name', 'string', ['notnull'=>true, 'length' => 64, 'comment' => 'Наименование должности']);
        $table->addColumn('comment', 'string', ['notnull'=>false,'length' => 512, 'comment' => 'Описание должности']);
        $table->addColumn('status', 'integer', ['notnull'=>true,'default' => Position::STATUS_ACTIVE, 'comment' => 'Статус']);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('position', ['parent_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'personal_parent_id_personal_id_fk');
        $table->addOption('engine' , 'InnoDB');

        $table = $schema->createTable('accrual');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('apl_id', 'integer', ['notnull'=>false, 'comment' => 'Код АПЛ']);
        $table->addColumn('name', 'string', ['notnull'=>true, 'length' => 64, 'comment' => 'Наименование начисления']);
        $table->addColumn('comment', 'string', ['notnull'=>false,'length' => 512, 'comment' => 'Описание ничисления']);
        $table->addColumn('status', 'integer', ['notnull'=>true,'default' => Accrual::STATUS_ACTIVE, 'comment' => 'Статус']);
        $table->addColumn('basis', 'integer', ['notnull'=>true,'default' => Accrual::BASE_NONE, 'comment' => 'База']);
        $table->setPrimaryKey(['id']);
        $table->addOption('engine' , 'InnoDB');

        $table = $schema->createTable('personal');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('apl_id', 'integer', ['notnull'=>false, 'comment' => 'Код АПЛ']);
        $table->addColumn('company_id', 'integer', ['notnull'=>true, 'comment' => 'Компания']);
        $table->addColumn('doc_date', 'date', ['notnull'=>true, 'comment' => 'Дата документа']);
        $table->addColumn('date_created', 'date', ['notnull'=>true, 'comment' => 'Дата создания']);
        $table->addColumn('comment', 'string', ['notnull'=>false,'length' => 512, 'comment' => 'Комментарий']);
        $table->addColumn('status', 'integer', ['notnull'=>true,'default' => Personal::STATUS_ACTIVE, 'comment' => 'Статус']);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('legal', ['company_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'personal_company_id_legal_id_fk');
        $table->addOption('engine' , 'InnoDB');

        $table = $schema->createTable('position_accrual');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('position_id', 'integer', ['notnull'=>true, 'comment' => 'Должность']);
        $table->addColumn('accrual_id', 'integer', ['notnull'=>true, 'comment' => 'Начисление']);
        $table->addColumn('rate', 'float', ['notnull'=>true, 'default' => 1, 'comment' => 'Размер']);
        $table->addColumn('status', 'integer', ['notnull'=>true,'default' => PositionAccrual::STATUS_ACTIVE, 'comment' => 'Статус']);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('position', ['position_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'position_accrual_position_id_position_id_fk');
        $table->addForeignKeyConstraint('accrual', ['accrual_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'position_accrual_accrual_id_accrual_id_fk');
        $table->addOption('engine' , 'InnoDB');
        
        $table = $schema->createTable('personal_accrual');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('user_id', 'integer', ['notnull'=>true, 'comment' => 'Сотрудник']);
        $table->addColumn('accrual_id', 'integer', ['notnull'=>true, 'comment' => 'Начисление']);
        $table->addColumn('rate', 'float', ['notnull'=>true, 'default' => 1, 'comment' => 'Размер']);
        $table->addColumn('status', 'integer', ['notnull'=>true,'default' => PersonalAccrual::STATUS_ACTIVE, 'comment' => 'Статус']);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('user', ['user_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'personal_accrual_user_id_user_id_fk');
        $table->addForeignKeyConstraint('accrual', ['accrual_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'personal_accrual_accrual_id_accrual_id_fk');
        $table->addOption('engine' , 'InnoDB');

        $table = $schema->createTable('personal_mutual');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);        
        $table->addColumn('company_id', 'integer', ['notnull'=>true, 'comment' => 'Компания']);
        $table->addColumn('user_id', 'integer', ['notnull'=>true, 'comment' => 'Сотрудник']);
        $table->addColumn('doc_id', 'integer', ['notnull'=>true, 'comment' => 'Номер документа']);
        $table->addColumn('doc_type', 'integer', ['notnull'=>true, 'comment' => 'Тип документа']);
        $table->addColumn('date_oper', 'date', ['notnull'=>true, 'comment' => 'Дата операции']);
        $table->addColumn('doc_key', 'string', ['notnull'=>true, 'length' => 64, 'comment' => 'Код документа']);
        $table->addColumn('doc_stamp', 'float', ['notnull'=>true, 'comment' => 'Метка документа']);
        $table->addColumn('amount', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Сумма']);
        $table->addColumn('status', 'integer', ['notnull'=>true,'default' => PersonalMutual::STATUS_ACTIVE, 'comment' => 'Статус']);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['doc_type', 'doc_id'], 'doc_type_doc_id_indx');
        $table->addForeignKeyConstraint('legal', ['company_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'personal_mutual_company_id_company_id_fk');
        $table->addForeignKeyConstraint('user', ['user_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'personal_mutual_user_id_user_id_fk');
        $table->addOption('engine' , 'InnoDB');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('personal_mutual');        
        $schema->dropTable('position_accrual');        
        $schema->dropTable('personal_accrual');        
        $schema->dropTable('personal');        
        $schema->dropTable('accrual');        
        $schema->dropTable('position');        
    }
}
