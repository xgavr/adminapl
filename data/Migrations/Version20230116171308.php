<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230116171308 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('bid');
        $table->addColumn('oe', 'string', ['notnull' => false, 'length' => 24]);
        $table->addIndex(['oe']);

        $table = $schema->getTable('selection');
        $table->addColumn('oe', 'string', ['notnull' => true, 'length' => 24, 'default' => '']);
        $table->addIndex(['oe']);
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('bid');
        $table->dropColumn('oe');

        $table = $schema->getTable('selection');
        $table->dropColumn('oe');
    }
}
