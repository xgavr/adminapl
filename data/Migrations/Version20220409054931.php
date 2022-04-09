<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Application\Entity\BillSetting;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220409054931 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('bill_setting');
        $table->addColumn('rule_cell', 'integer', ['notnull' => true, 'default' => BillSetting::RULE_CELL_ALL]);

        $table = $schema->getTable('idoc');
        $table->addColumn('info', 'text', ['notnull' => false]);

        $table = $schema->getTable('ptu');
        $table->addColumn('idoc_id', 'integer', ['notnull' => false]);
        $table->addForeignKeyConstraint('idoc', ['idoc_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'ptu_idoc_id_idoc_id_fk');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('bill_setting');
        $table->dropColumn('rule_cell');

        $table = $schema->getTable('idoc');
        $table->dropColumn('info');

        $table = $schema->getTable('ptu');
        $table->removeForeignKey('ptu_idoc_id_idoc_id_fk');
        $table->dropColumn('idoc_id');
    }
}
