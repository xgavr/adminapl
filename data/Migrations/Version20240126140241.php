<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

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

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('doc_calculator'); 
        $table->removeForeignKey('doc_calculator_personal_accrual_id_personal_accrual_id_fk');
        $table->dropColumn('personal_accrual_id');
    }
}
