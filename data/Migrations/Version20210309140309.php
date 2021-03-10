<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210309140309 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('supplier_api_setting');
        $table->addColumn('base_uri', 'string', ['notnull'=>false, 'length' => 256]);
        $table->addColumn('test_uri', 'string', ['notnull'=>false, 'length' => 256]);
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('supplier_api_setting');
        $table->dropColumn('base_uri');
        $table->dropColumn('test_uri');
    }
}
