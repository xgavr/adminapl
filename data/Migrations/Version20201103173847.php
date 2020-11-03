<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201103173847 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('supplier');
        $table->addColumn('amount', 'integer', ['notnull' => true, 'default' => 0]);        
        $table->addColumn('quantity', 'integer', ['notnull' => true, 'default' => 0]);        

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('supplier');
        $table->dropColumn('amount');
        $table->dropColumn('quantity');

    }
}
