<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250404090029 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('fin_balance');
        $table->addColumn('balance', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Баланс']);
        $table->addColumn('ktl', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Коэфициент текущей ликвидности']);
        $table->addColumn('kfl', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Коэфициент финансовой ликвидности']);
        $table->addColumn('ro', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Ресурсоотдача']);
        $table->addColumn('al', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Абсолютная ликвидность']);
        $table->addColumn('fn', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Финансовая независимость']);
        $table->addColumn('rsk', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Рентабельность СК']);
        $table->addColumn('ra', 'float', ['notnull'=>true, 'default' => 0, 'comment' => 'Рентабельность активов']);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('fin_balance');
        $table->dropColumn('balance');
        $table->dropColumn('ktl');
        $table->dropColumn('kfl');
        $table->dropColumn('ro');
        $table->dropColumn('al');
        $table->dropColumn('fn');
        $table->dropColumn('rsk');
        $table->dropColumn('ra');
    }
}
