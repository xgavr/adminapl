<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190131140634 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('unknown_producer');
        $table->addColumn('intersect_update_flag', 'integer', ['notnull' => true, 'default' => 0]);

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('unknown_producer');
        $table->dropColumn('intersect_update_flag');
    }
}
