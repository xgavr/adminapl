<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Cash\Entity\Cash;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211226145139 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('cash');
        $table->addColumn('refill_status', 'integer', ['notnull' => true, 'default' => Cash::REFILL_ACTIVE]);
        $table->addColumn('supplier_status', 'integer', ['notnull' => true, 'default' => Cash::SUPPLIER_ACTIVE]);
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('cash');
        $table->dropColumn('refill_status');
        $table->dropColumn('supplier_status');
    }
}
