<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Application\Entity\Goods;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190607150742 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('goods');
        $table->addColumn('status_oem_ex', 'integer', ['notnull' => true, 'default' => Goods::OEM_EX_NEW]);
        $table->addColumn('status_car_ex', 'integer', ['notnull' => true, 'default' => Goods::CAR_EX_NEW]);
        $table->addColumn('status_img_ex', 'integer', ['notnull' => true, 'default' => Goods::IMG_EX_NEW]);
        $table->addColumn('status_price_ex', 'integer', ['notnull' => true, 'default' => Goods::PRICE_EX_NEW]);

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('goods');
        $table->dropColumn('status_oem_ex');
        $table->dropColumn('status_car_ex');
        $table->dropColumn('status_img_ex');
        $table->dropColumn('status_price_ex');
    }
}
