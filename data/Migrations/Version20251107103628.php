<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251107103628 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->createTable('laximo_brand');
        $table->addColumn('id', 'integer', ['autoincrement'=>true]);
        $table->addColumn('laximo_id', 'integer', ['notnull'=>true, 'comment' => 'Ид производителя в laximo']);        
        $table->addColumn('name', 'string', ['notnull'=>true]);        
        $table->addColumn('is_original', 'integer', ['notnull'=>true, 'comment' => 'Оригинал']);        
        $table->setPrimaryKey(['id']);
        
        $table->addUniqueIndex(['laximo_id'], 'laximo_brand_laximo_id_uindx');
        
        $table->addOption('engine' , 'InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $schema->dropTable('laximo_brand');
    }
}
