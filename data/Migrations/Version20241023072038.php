<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Stock\Entity\VtGood;
use Stock\Entity\Movement;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241023072038 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('vt_good');
        $table->addColumn('oe', 'string', ['notnull' => false, 'length' => 36, 'comment' => 'Оригинальный номер']);
        $table->addIndex(['good_id', 'oe', 'take'], 'good_oe_take_indx');
        
        $table = $schema->getTable('movement');
        $table->addColumn('oe', 'string', ['notnull' => false, 'length' => 36, 'comment' => 'Оригинальный номер']);
        $table->addIndex(['good_id', 'oe', 'status'], 'good_oe_status_indx');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('movement');
        $table->dropIndex('good_oe_status_indx');
        $table->dropColumn('oe');
        
        $table = $schema->getTable('vt_good');
        $table->dropIndex('good_oe_take_indx');
        $table->dropColumn('oe');
    }
}
