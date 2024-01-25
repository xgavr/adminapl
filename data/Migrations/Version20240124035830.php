<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240124035830 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('position');
        $table->addColumn('sort', 'string', ['notnull' => false, 'length'=>12, 'comment' => 'Сортировка']);
        $table->addColumn('company_id', 'integer', ['notnull' => true, 'comment' => 'Компания']);
        $table->addIndex(['sort'], 'sort_indx');
        $table->addForeignKeyConstraint('legal', ['company_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'position_company_id_legal_id_fk');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('position');
        $table->removeForeignKey('position_company_id_legal_id_fk');
        $table->dropColumn('company_id');
        $table->dropColumn('sort');
    }
}
