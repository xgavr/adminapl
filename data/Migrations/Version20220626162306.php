<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220626162306 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('movement');
        $table->addColumn('base_key', 'string', ['notnull' => true, 'default' => '', 'length' => 48]);
        $table->addIndex(['base_key'], 'base_key_indx');

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('movement');
        $table->dropIndex('base_key_indx');
        $table->dropColumn('base_key');
    }
}
