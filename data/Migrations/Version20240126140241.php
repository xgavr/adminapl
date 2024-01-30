<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Zp\Entity\Accrual;
use Zp\Entity\PersonalMutual;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240126140241 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('doc_calculator'); 
        $table->addColumn('personal_accrual_id', 'integer', ['notnull' => true, 'comment' => 'Начисление']);
        $table->addForeignKeyConstraint('personal_accrual', ['personal_accrual_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'doc_calculator_personal_accrual_id_personal_accrual_id_fk');

        $table = $schema->getTable('accrual'); 
        $table->addColumn('payment', 'integer', ['notnull' => true, 'default' => Accrual::PAYMENT_WORK, 'comment' => 'Тип начисления']);

        $table = $schema->getTable('personal_mutual'); 
        $table->addColumn('kind', 'integer', ['notnull' => true, 'default' => PersonalMutual::KIND_ACCRUAL, 'comment' => 'Вид операции']);
        $table->addColumn('accrual_id', 'integer', ['notnull' => true, 'comment' => 'Вид начисления']);
        $table->addForeignKeyConstraint('accrual', ['accrual_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'personal_mutual_accrual_id_accrual_id_fk');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('doc_calculator'); 
        $table->removeForeignKey('doc_calculator_personal_accrual_id_personal_accrual_id_fk');
        $table->dropColumn('personal_accrual_id');

        $table = $schema->getTable('accrual'); 
        $table->dropColumn('payment');

        $table = $schema->getTable('personal_mutual'); 
        $table->removeForeignKey('personal_mutual_accrual_id_accrual_id_fk');
        $table->dropColumn('accrual_id');
        $table->dropColumn('kind');
    }
}
