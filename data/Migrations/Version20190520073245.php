<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190520073245 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('goods');
        $table->addColumn('min_price', 'float', ['notnull' => true, 'default' => 0.0]);
        $table->addColumn('mean_price', 'float', ['notnull' => true, 'default' => 0.0]);
        $table->addColumn('fix_price', 'float', ['notnull' => true, 'default' => 0.0]);
        $table->addColumn('markup', 'float', ['notnull' => true, 'default' => 0.0]);
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('goods');
        $table->dropColumn('min_price');
        $table->dropColumn('mean_price');
        $table->dropColumn('fix_price');
        $table->dropColumn('markup');
    }
}
