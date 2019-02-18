<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190218153159 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('goods');
        $table->addColumn('status_car', 'integer', ['notnull' => true, 'default' => 1]);
        $table->addColumn('status_image', 'integer', ['notnull' => true, 'default' => 1]);
        $table->addColumn('status_description', 'integer', ['notnull' => true, 'default' => 1]);
        $table->addColumn('status_group', 'integer', ['notnull' => true, 'default' => 1]);
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('goods');
        $table->dropColumn('status_car');
        $table->dropColumn('status_image');
        $table->dropColumn('status_description');
        $table->dropColumn('status_group');
        
    }
}
