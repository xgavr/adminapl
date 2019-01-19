<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190118155746 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('token_group');
        $table->dropIndex('ids_indx');
        $table->changeColumn('ids', ['length' => 128]);
        $table->addUniqueIndex(['ids'], 'ids_uindx');

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('token_group');
        $table->dropIndex('ids_uindx');
        $table->addIndex(['ids'], 'ids_indx');

    }
}
