<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Application\Entity\Model;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190628143959 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('model');
        $table->addColumn('construction_from', 'integer', ['notnull' => true, 'default' => Model::COSTRUCTION_MAX_PERIOD]);
        $table->addColumn('construction_to', 'integer', ['notnull' => true, 'default' => Model::COSTRUCTION_MAX_PERIOD]);
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('model');
        $table->dropColumn('construction_from');
        $table->dropColumn('construction_to');
    }
}
