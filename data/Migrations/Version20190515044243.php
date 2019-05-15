<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190515044243 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('article');
        $table->addColumn('mean_price', 'float', ['notnull' => true, 'default' => 0.0]);
        $table->addColumn('standart_deviation', 'float', ['notnull' => true, 'default' => 0.0]);
        $table->addColumn('total_rest', 'float', ['notnull' => true, 'default' => 0.0]);

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('article');
        $table->dropColumn('mean_price');
        $table->dropColumn('standart_deviation');
        $table->dropColumn('total_rest');        
    }
}
