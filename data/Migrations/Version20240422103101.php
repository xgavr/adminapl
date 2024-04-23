<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240422103101 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('order_calculator');
        $table->addColumn('rate', 'float', ['notnull' => true, 'default' => 0, 'comment' => 'Доля']);
        $table->addColumn('accrual_amount', 'float', ['notnull' => true, 'default' => 0, 'comment' => 'Начисленно']);
        $table->addColumn('position_num', 'float', ['notnull' => true, 'default' => 0, 'comment' => 'Ставка']);
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('order_calculator');
        $table->dropColumn('rate');
        $table->dropColumn('accrual_amount');
        $table->dropColumn('position_num');
    }
}
