<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200326080012 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('title_token');
        $table->addColumn('frequency', 'integer', ['notnull' => true, 'default' => 0]);

        $table = $schema->getTable('title_bigram');
        $table->addColumn('frequency', 'integer', ['notnull' => true, 'default' => 0]);
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('title_token');
        $table->dropColumn('frequency');

        $table = $schema->getTable('title_bigram');
        $table->dropColumn('frequency');
    }
}
