<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Zp\Entity\Accrual;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240124110335 extends AbstractMigration
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
        $table = $schema->getTable('personal_accrual');
        $table->addColumn('personal_id', 'integer', ['notnull' => true, 'comment' => 'Документ основание']);
        $table->addColumn('company_id', 'integer', ['notnull' => true, 'comment' => 'Компания']);
        $table->addColumn('row_no', 'integer', ['notnull' => true, 'comment' => 'Номер строки']);
        $table->addColumn('date_oper', 'date', ['notnull' => true, 'comment' => 'Дата начала действия']);
        $table->addForeignKeyConstraint('personal', ['personal_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'personal_accrual_personal_id_personal_id_fk');
        $table->addForeignKeyConstraint('legal', ['company_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'personal_accrual_company_id_legal_id_fk');

        $table = $schema->getTable('accrual');
        $table->addColumn('oper_kind', 'integer', ['notnull' => true, 'default' => Accrual::KIND_PERCENT, 'comment' => 'Способ расчета']);

        $table = $schema->getTable('position');
        $table->addColumn('num', 'float', ['notnull' => true, 'default' => 0, 'comment' => 'Количество ставок']);

        $table = $schema->getTable('personal');
        $table->addColumn('user_id', 'integer', ['notnull' => true, 'comment' => 'Сотрудник']);
        $table->addColumn('position_id', 'integer', ['notnull' => true, 'comment' => 'Должность']);
        $table->addColumn('position_num', 'float', ['notnull' => true, 'default' => 0, 'comment' => 'Ставка']);
        $table->addForeignKeyConstraint('user', ['user_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'personal_user_id_user_id_fk');
        $table->addForeignKeyConstraint('position', ['position_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'personal_position_id_position_id_fk');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('personal_accrual');
        $table->removeForeignKey('personal_accrual_personal_id_personal_id_fk');
        $table->removeForeignKey('personal_accrual_company_id_legal_id_fk');
        $table->dropColumn('company_id');
        $table->dropColumn('personal_id');
        $table->dropColumn('row_no');
        $table->dropColumn('date_oper');

        $table = $schema->getTable('personal');
        $table->removeForeignKey('personal_user_id_user_id_fk');
        $table->removeForeignKey('personal_position_id_position_id_fk');
        $table->dropColumn('user_id');
        $table->dropColumn('position_id');
        $table->dropColumn('position_num');

        $table = $schema->getTable('accrual');
        $table->dropColumn('oper_kind');

        $table = $schema->getTable('position');
        $table->dropColumn('num');
    }
}
