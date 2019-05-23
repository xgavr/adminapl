<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Application\Entity\Rawprice;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190523053320 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('rawprice');
        $table->addColumn('status_ex', 'integer', ['notnull' => true, 'default' => Rawprice::EX_NEW]);
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('rawprice');
        $table->dropColumn('status_ex');
    }
}
