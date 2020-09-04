<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Application\Entity\GenericGroup;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200904082327 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('generic_group');
        $table->addColumn('car_upload', 'integer', ['notnull' => true, 'default' => GenericGroup::CAR_ACTIVE]);        

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('generic_group');
        $table->dropColumn('car_upload');
    }
}
