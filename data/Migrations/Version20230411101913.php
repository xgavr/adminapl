<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230411101913 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('comiss');
        $table->addColumn('doc_stamp', 'float', ['notnull' => true, 'default' => 0]);
        $table->addIndex(['doc_stamp'], 'doc_stamp_indx');

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('comiss');
        $table->dropIndex('doc_stamp_indx');
        $table->dropColumn('doc_stamp');

    }
}
