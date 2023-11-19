<?php declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Bank\Entity\Payment;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231117192754 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('bank_payment');
        $table->addColumn('payment_auto', 'integer', ['notnull' => true, 'default' => Payment::PAYMENT_AUTO_ONE]);
        $table->addColumn('payment_auto_day', 'integer', ['notnull' => true, 'default' => 1]);
        $table->addColumn('payment_auto_stop_date', 'date', ['notnull' => false]);

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('bank_payment');
        $table->dropColumn('payment_auto');
        $table->dropColumn('payment_auto_day');
        $table->dropColumn('payment_auto_stop_date');

    }
}
