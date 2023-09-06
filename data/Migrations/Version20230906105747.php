<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use ApiMarketPlace\Entity\MarketSaleReport;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230906105747 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('market_sale_report');
        $table->addColumn('report_type', 'integer', ['notnull' => true, 'default' => MarketSaleReport::TYPE_REPORT, 'comment' => 'Тип отчета']);
        $table->addColumn('comment', 'string', ['notnull' => false, 'length' => 256, 'comment' => 'Комментарий']);

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('market_sale_report');
        $table->dropColumn('report_type');
        $table->dropColumn('comment');

    }
}
