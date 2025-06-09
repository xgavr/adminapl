<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Application\Entity\Order;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250609074724 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('orders');
        $table->addColumn('fasade_ex', 'integer', ['notnull' => true, 'default' => Order::FASADE_EX_NEW]);
        $table->addIndex(['fasade_ex'], 'fasade_ex_indx');

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('orders');
        $table->dropColumn('fasade_ex');

    }
}
