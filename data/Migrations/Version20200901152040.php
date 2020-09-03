<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200901152040 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('Setting');
        $table->addColumn('err_code', 'integer', ['notnull' => false]);
        $table->addColumn('err_text', 'text', ['notnull' => false]);
        $table->addColumn('name', 'string', ['notnull' => false, 'length' => 128]);
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('Setting');
        $table->dropColumn('err_code');
        $table->dropColumn('err_text');
        $table->dropColumn('name');
    }
}
