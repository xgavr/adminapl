<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230919161523 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $table = $schema->getTable('retail');
        $table->dropColumn('doc_info');
    }

    public function down(Schema $schema) : void
    {
        $table = $schema->getTable('retail');
        $table->addColumn('doc_info', 'json', ['notnull' => true]);
    }
}
