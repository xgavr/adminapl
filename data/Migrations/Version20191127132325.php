<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191127132325 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('token');
        $table->addColumn('gf', 'integer', ['notnull' => true, 'default' => 0]);
        $table = $schema->getTable('bigram');
        $table->addColumn('gf', 'integer', ['notnull' => true, 'default' => 0]);
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('token');
        $table->dropColumn('gf');
        $table = $schema->getTable('bigram');
        $table->dropColumn('gf');
    }
}
