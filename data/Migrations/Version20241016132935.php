<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Application\Entity\Raw;
use Application\Entity\Idoc;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241016132935 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('raw');
        $table->getColumn('supplier_id')->setNotnull(false);
        $table->addColumn('sender', 'string', ['notnull' => false, 'length' => 120, 'comment' => 'Почта отправителя']);
        $table->addColumn('subject', 'string', ['notnull' => false, 'length' => 120, 'comment' => 'Тема в письме отправителя']);

        $table = $schema->getTable('idoc');
        $table->getColumn('supplier_id')->setNotnull(false);
        $table->addColumn('sender', 'string', ['notnull' => false, 'length' => 120, 'comment' => 'Почта отправителя']);
        $table->addColumn('subject', 'string', ['notnull' => false, 'length' => 120, 'comment' => 'Тема в письме отправителя']);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('raw');
//        $table->getColumn('supplier_id')->setNotnull(true);
        $table->dropColumn('sender');
        $table->dropColumn('subject');
        
        $table = $schema->getTable('idoc');
//        $table->getColumn('supplier_id')->setNotnull(true);
        $table->dropColumn('sender');
        $table->dropColumn('subject');
    }
}
