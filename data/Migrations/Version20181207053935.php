<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181207053935 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('producer');
        $table->addColumn('apl_id', 'integer', ['notnull' => true, 'default' => 0]);
        $table = $schema->getTable('goods');
        $table->addColumn('apl_id', 'integer', ['notnull' => true, 'default' => 0]);

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('producer');
        $table->dropColumn('apl_id');
        $table = $schema->getTable('goods');
        $table->dropColumn('apl_id');

    }
}
