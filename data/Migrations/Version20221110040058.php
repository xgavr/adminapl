<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221110040058 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('market_price_setting');
        $table->addColumn('retail_filter', 'integer', ['notnull' => true, 'default' => 0]);

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('market_price_setting');
        $table->dropColumn('retail_filter');
    }
}
