<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230808135118 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('market_sale_report_item');
        $table->addColumn('base_amount', 'float', ['notnull' => true, 'default' => 0, 'comment' => 'Закупка']);

        $table = $schema->getTable('comitent');
        $table->addColumn('base_amount', 'float', ['notnull' => true, 'default' => 0, 'comment' => 'Закупка']);

        $table = $schema->getTable('market_sale_report');
        $table->addColumn('base_amount', 'float', ['notnull' => true, 'default' => 0, 'comment' => 'Закупка']);
        $table->addColumn('cost_amount', 'float', ['notnull' => true, 'default' => 0, 'comment' => 'Расходы']);

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('market_sale_report_item');
        $table->dropColumn('base_amount');

        $table = $schema->getTable('comitent');
        $table->dropColumn('base_amount');

        $table = $schema->getTable('market_sale_report');
        $table->dropColumn('base_amount');
        $table->dropColumn('cost_amount');

    }
}
