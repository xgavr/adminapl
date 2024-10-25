<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Application\Entity\Supplier;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241025172623 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('supplier');
        $table->addColumn('to_sup_email', 'string', ['notnull' => false, 'length' => 256, 'comment' => 'Почта приемник прайсов']);
        $table->addColumn('to_bill_email', 'string', ['notnull' => false, 'length' => 256, 'comment' => 'Почта приемник накладных']);        
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('supplier');
        $table->dropColumn('to_sup_email');
        $table->dropColumn('to_bill_email');
    }
}
