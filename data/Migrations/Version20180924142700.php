<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180924142700 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('unknown_producer');
        $table->addColumn('train', 'integer', ['default' => 3, 'notnull' => true]);
        $table->addColumn('prediction', 'integer', ['default' => 3, 'notnull' => true]);
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('unknown_producer');
        $table->dropColumn('train');
        $table->dropColumn('prediction');
    }
}
