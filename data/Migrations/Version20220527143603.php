<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220527143603 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('office');
        $table->addColumn('sb_card', 'string', ['notnull' => false, 'length' => 64]);
        $table->addColumn('sb_owner', 'string', ['notnull' => false, 'length' => 128]);

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('office');
        $table->dropColumn('sb_card');
        $table->dropColumn('sb_owner');
    }
}
