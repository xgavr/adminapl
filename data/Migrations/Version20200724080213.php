<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200724080213 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('ptu_good');
        $table->addColumn('row_no', 'integer', ['notnull'=>true]);

        $table = $schema->getTable('movement');
        $table->addColumn('doc_row_no', 'integer', ['notnull'=>true]);

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('movement');
        $table->dropColumn('doc_row_no');

        $table = $schema->getTable('ptu_good');
        $table->dropColumn('row_no');
    }
}
