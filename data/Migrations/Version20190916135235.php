<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190916135235 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('token');
        $table->addColumn('pseudo_root', 'string', ['notnull' => false]);
        $table->addColumn('part_of_speech', 'integer', ['notnull' => true, 'default' => -1]);
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('token');
        $table->dropColumn('pseudo_root');
        $table->dropColumn('part_of_speech');
    }
}
