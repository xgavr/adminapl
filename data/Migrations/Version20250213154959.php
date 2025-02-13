<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Application\Entity\Goods;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250213154959 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('goods');
        $table->addColumn('check_oem', 'integer', ['notnull' => true, 'default' => Goods::CHECK_OEM_NO, 'comment' => 'OEM проверено']);
        $table->addColumn('check_description', 'integer', ['notnull' => true, 'default' => Goods::CHECK_DESCRIPTION_NO, 'comment' => 'Описание проверено']);
        $table->addColumn('check_image', 'integer', ['notnull' => true, 'default' => Goods::CHECK_IMAGE_NO, 'comment' => 'Картинка проверена']);
        $table->addColumn('check_car', 'integer', ['notnull' => true, 'default' => Goods::CHECK_CAR_NO, 'comment' => 'Машины проверены']);
        $table->addColumn('fasade_ex', 'integer', ['notnull' => true, 'default' => Goods::FASADE_EX_NEW, 'comment' => 'Экспорт в фасад']);
        $table->addIndex(['check_oem'], 'check_oem_indx');
        $table->addIndex(['check_description'], 'check_description_indx');
        $table->addIndex(['check_image'], 'check_image_indx');
        $table->addIndex(['check_car'], 'check_car_indx');
        $table->addIndex(['fasade_ex'], 'fasade_ex_indx');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('goods');
        $table->dropColumn('check_oem');
        $table->dropColumn('check_description');
        $table->dropColumn('check_image');
        $table->dropColumn('check_car');
        $table->dropColumn('fasade_ex');
    }
}
