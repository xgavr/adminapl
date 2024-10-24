<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Application\Entity\Supplier;
use Application\Entity\Oem;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241024140603 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('supplier');
        $table->addColumn('remove_price', 'integer', ['notnull' => true, 'default' => Supplier::REMOVE_PRICE_LIST_OFF, 'comment' => 'Удалять прайс лист']);

        $table = $schema->getTable('oem');
        $table->addColumn('update_rating', 'integer', ['notnull' => true, 'default' => Oem::RATING_FOR_UPDATE, 'comment' => 'Обновление рейтинга']);
        $table->addIndex(['update_rating'], 'update_rating_indx');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('oem');
        $table->dropIndex('update_rating_indx');
        $table->dropColumn('update_rating');

        $table = $schema->getTable('supplier');
        $table->dropColumn('remove_price');
    }
}
