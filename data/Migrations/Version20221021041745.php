<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Cash\Entity\Cash;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221021041745 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('market_price_setting');
        $table->addColumn('token_filter', 'text', ['notnull' => false, 'length' => 512]);
        $table->addColumn('minus_token_filter', 'text', ['notnull' => false, 'length' => 512]);

        $table = $schema->getTable('cash');
        $table->addColumn('payment', 'integer', ['notnull' => true, 'default' => Cash::PAYMENT_CASH]);
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('market_price_setting');
        $table->dropColumn('token_filter');
        $table->dropColumn('minus_token_filter');

        $table = $schema->getTable('cash');
        $table->dropColumn('payment');
    }
}
