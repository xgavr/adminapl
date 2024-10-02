<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241002113621 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('bill_gettings');
        $table->addColumn('real_supplier_id', 'integer', ['notnull'=>false, 'comment' => 'Реальный поставщик']);
        $table->addForeignKeyConstraint('supplier', ['real_supplier_id'], ['id'], 
                ['onDelete'=>'CASCADE', 'onUpdate'=>'CASCADE'], 'bill_gettings_real_supplier_id_supplier_id_fk');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('bill_gettings');
        $table->removeForeignKey('bill_gettings_real_supplier_id_supplier_id_fk');
        $table->dropColumn('real_supplier_id');
    }
}
