<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260121095052 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('user');
        $table->addColumn('zp_rs', 'string', ['notnull' => false, 'comment' => 'Расчетный счет для ЗП']);
        $table->addColumn('zp_bik', 'string', ['notnull' => false, 'comment' => 'БИК банка для ЗП']);
        $table->addIndex(['zp_rs'], 'zp_rs_indx');

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $table = $schema->getTable('user');
        $table->dropColumn('zp_rs');
        $table->dropColumn('zp_bik');
    }
}
