<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Application\Entity\Supplier;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191216193930 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('supplier');
        $table->addColumn('prepay', 'integer', ['notnull' => true, 'default' => Supplier::PREPAY_OFF]);
        $table->addColumn('price_list', 'integer', ['notnull' => true, 'default' => Supplier::PRICE_LIST_OFF]);

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('supplier');
        $table->dropColumn('prepay');
        $table->dropColumn('price_list');
    }
}
