<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190412135827 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('apl_payment');
        $table->addColumn('status', 'integer', ['notnull' => true, 'default' => 1]);
        $table = $schema->getTable('acquiring');
        $table->addColumn('status', 'integer', ['notnull' => true, 'default' => 1]);
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('apl_payment');
        $table->dropColumn('status');
        $table = $schema->getTable('acquiring');
        $table->dropColumn('status');
    }
}
