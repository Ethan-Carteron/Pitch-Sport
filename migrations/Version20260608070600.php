<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260608070600 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE player ADD score INT NOT NULL');
        $this->addSql('ALTER TABLE "user" ADD club_id INT NOT NULL');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT FK_8D93D64961190A32 FOREIGN KEY (club_id) REFERENCES club (id) NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_8D93D64961190A32 ON "user" (club_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE player DROP score');
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT FK_8D93D64961190A32');
        $this->addSql('DROP INDEX IDX_8D93D64961190A32');
        $this->addSql('ALTER TABLE "user" DROP club_id');
    }
}
