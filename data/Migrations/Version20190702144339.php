<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Application\Entity\Model;
use Application\Entity\Car;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190702144339 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table1 = $schema->getTable('model');
        $table1->addColumn('transfer_flag', 'integer', ['notnull' => true, 'default' => Model::TRANSFER_NO]);
        $table2 = $schema->getTable('car');
        $table2->addColumn('transfer_flag', 'integer', ['notnull' => true, 'default' => Car::TRANSFER_NO]);

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table1 = $schema->getTable('model');
        $table1->dropColumn('transfer_flag');
        $table2 = $schema->getTable('car');
        $table2->dropColumn('transfer_flag');

    }
}
