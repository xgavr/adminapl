<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Application\Entity\MarketPriceSetting;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211124222516 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('market_price_setting');
        $table->addColumn('description_format', 'integer', ['notnull'=>true, 'default'=> MarketPriceSetting::DESCRIPTION_FORMAT_HTML]);
        $table->addColumn('description_set', 'integer', ['notnull'=>true, 'default'=> MarketPriceSetting::DESCRIPTION_SET_NAME_COMMENT]);

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('market_price_setting');
        $table->dropColumn('description_format');
        $table->dropColumn('description_set');
    }
}
