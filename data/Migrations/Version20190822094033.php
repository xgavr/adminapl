<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190822094033 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('car');
        $table->addColumn('good_count', 'integer', ['notnull' => true, 'default' => 0]);
        $table->addIndex(['good_count'], 'goodCount_indx');
        $table = $schema->getTable('model');
        $table->addColumn('good_count', 'integer', ['notnull' => true, 'default' => 0]);
        $table->addIndex(['good_count'], 'goodCount_indx');
        $table = $schema->getTable('make');
        $table->addColumn('good_count', 'integer', ['notnull' => true, 'default' => 0]);
        $table->addIndex(['good_count'], 'goodCount_indx');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('car');
        $table->dropIndex('goodCount_indx');
        $table->dropColumn('good_count');
        $table = $schema->getTable('model');
        $table->dropIndex('goodCount_indx');
        $table->dropColumn('good_count');
        $table = $schema->getTable('make');
        $table->dropIndex('goodCount_indx');
        $table->dropColumn('good_count');

    }
}
