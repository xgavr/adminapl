<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Application\Entity\Goods;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190523093958 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('goods');
        $table->addColumn('status_rawprice_ex', 'integer', ['notnull' => true, 'default' => Goods::RAWPRICE_EX_NEW]);

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('goods');
        $table->dropColumn('status_rawprice_ex');
    }
}
