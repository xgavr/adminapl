<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Application\Entity\MarketPriceSetting;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220325090104 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('market_price_setting');
        $table->addColumn('rate_setting', 'integer', ['notnull' => true, 'default' => MarketPriceSetting::RATE_IGNORE]);

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('market_price_setting');
        $table->dropColumn('rate_setting');
    }
}
