<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230911145227 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
//        $table = $schema->getTable('oem');
//        $table->addIndex(['oe'], 'oe_ftindx', ['fulltext']);

        $table = $schema->getTable('goods');
        $table->addIndex(['code'], 'code_ftindx', ['fulltext']);

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
//        $table = $schema->getTable('oem');
//        $table->dropIndex('oe_ftindx');

        $table = $schema->getTable('goods');
        $table->dropIndex('code_ftindx');
    }
}
