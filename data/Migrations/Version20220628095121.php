<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220628095121 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('supplier_order');
        $table->addColumn('apl_id', 'integer', ['notnull' => false]);
        $table->addIndex(['apl_id'], 'apl_id_indx');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('supplier_order');
        $table->dropIndex('apl_id_indx');
        $table->dropColumn('apl_id');
    }
}
