<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260609094400 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Make user.club_id nullable and seed a test club';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "user" ALTER club_id DROP NOT NULL');
        $this->addSql("INSERT INTO club (name, created_date, is_deleted) VALUES ('test', NOW(), false)");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM club WHERE name = \'test\'');
        $this->addSql('ALTER TABLE "user" ALTER club_id SET NOT NULL');
    }
}
