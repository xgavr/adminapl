<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180824084540 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('bank_account');
        $table->addColumn('api', 'integer', ['notnull' => false, 'default' => 2]);
        $table->addColumn('statement', 'integer', ['notnull' => false, 'default' => 2]);

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('bank_account');
        $table->dropColumn('api');
        $table->dropColumn('statement');
    }
}
