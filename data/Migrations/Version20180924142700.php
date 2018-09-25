<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180924142700 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('unknown_producer');
        $table->addColumn('rawprice_count', 'integer', ['default' => 0, 'notnull' => true]);
        $table->addColumn('supplier_count', 'integer', ['default' => 0, 'notnull' => true]);
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('unknown_producer');
        $table->dropColumn('rawprice_count');
        $table->dropColumn('supplier_count');
    }
}
