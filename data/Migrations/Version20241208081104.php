<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Application\Entity\Client;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241208081104 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('client');
        $table->addColumn('date_registration', 'date', ['notnull' => false, 'comment' => 'Дата первой регистрации']);
        $table->addColumn('date_order', 'date', ['notnull' => false, 'comment' => 'Дата первого заказа']);

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('client');
        $table->dropColumn('date_registration');
        $table->dropColumn('date_order');
    }
}
