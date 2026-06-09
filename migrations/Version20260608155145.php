<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260608155145 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE club ALTER created_by_id DROP NOT NULL');
        $this->addSql('ALTER TABLE club ALTER updated_by_id DROP NOT NULL');
        $this->addSql('ALTER TABLE club ALTER deleted_by_id DROP NOT NULL');
        $this->addSql('ALTER TABLE player DROP CONSTRAINT fk_98197a65df2ab4e5');
        $this->addSql('DROP INDEX idx_98197a65df2ab4e5');
        $this->addSql('ALTER TABLE player ADD name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE player ADD club_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE player DROP firste_name');
        $this->addSql('ALTER TABLE player DROP last_name');
        $this->addSql('ALTER TABLE player DROP club_id_id');
        $this->addSql('ALTER TABLE player ALTER created_by_id DROP NOT NULL');
        $this->addSql('ALTER TABLE player ALTER updated_by_id DROP NOT NULL');
        $this->addSql('ALTER TABLE player ALTER deleted_by_id DROP NOT NULL');
        $this->addSql('ALTER TABLE player ADD CONSTRAINT FK_98197A6561190A32 FOREIGN KEY (club_id) REFERENCES club (id) NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_98197A6561190A32 ON player (club_id)');
        $this->addSql('ALTER TABLE wellness_questions DROP CONSTRAINT fk_15fa1107c036e511');
        $this->addSql('DROP INDEX idx_15fa1107c036e511');
        $this->addSql('ALTER TABLE wellness_questions ADD player_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE wellness_questions DROP player_id_id');
        $this->addSql('ALTER TABLE wellness_questions ALTER created_by_id DROP NOT NULL');
        $this->addSql('ALTER TABLE wellness_questions ALTER updated_by_id DROP NOT NULL');
        $this->addSql('ALTER TABLE wellness_questions ALTER deleted_by_id DROP NOT NULL');
        $this->addSql('ALTER TABLE wellness_questions ADD CONSTRAINT FK_15FA110799E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_15FA110799E6F5DF ON wellness_questions (player_id)');
        $this->addSql('ALTER TABLE workload DROP CONSTRAINT fk_1203aa7bc036e511');
        $this->addSql('DROP INDEX idx_1203aa7bc036e511');
        $this->addSql('ALTER TABLE workload ADD deceleration INT DEFAULT NULL');
        $this->addSql('ALTER TABLE workload ADD player_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE workload DROP player_id_id');
        $this->addSql('ALTER TABLE workload ALTER created_date TYPE DATE');
        $this->addSql('ALTER TABLE workload ALTER created_date DROP DEFAULT');
        $this->addSql('ALTER TABLE workload ALTER created_by_id DROP NOT NULL');
        $this->addSql('ALTER TABLE workload ALTER updated_by_id DROP NOT NULL');
        $this->addSql('ALTER TABLE workload ALTER deleted_by_id DROP NOT NULL');
        $this->addSql('ALTER TABLE workload ADD CONSTRAINT FK_1203AA7B99E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_1203AA7B99E6F5DF ON workload (player_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE club ALTER created_by_id SET NOT NULL');
        $this->addSql('ALTER TABLE club ALTER updated_by_id SET NOT NULL');
        $this->addSql('ALTER TABLE club ALTER deleted_by_id SET NOT NULL');
        $this->addSql('ALTER TABLE player DROP CONSTRAINT FK_98197A6561190A32');
        $this->addSql('DROP INDEX IDX_98197A6561190A32');
        $this->addSql('ALTER TABLE player ADD last_name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE player ADD club_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE player DROP club_id');
        $this->addSql('ALTER TABLE player ALTER created_by_id SET NOT NULL');
        $this->addSql('ALTER TABLE player ALTER updated_by_id SET NOT NULL');
        $this->addSql('ALTER TABLE player ALTER deleted_by_id SET NOT NULL');
        $this->addSql('ALTER TABLE player RENAME COLUMN name TO firste_name');
        $this->addSql('ALTER TABLE player ADD CONSTRAINT fk_98197a65df2ab4e5 FOREIGN KEY (club_id_id) REFERENCES club (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_98197a65df2ab4e5 ON player (club_id_id)');
        $this->addSql('ALTER TABLE wellness_questions DROP CONSTRAINT FK_15FA110799E6F5DF');
        $this->addSql('DROP INDEX IDX_15FA110799E6F5DF');
        $this->addSql('ALTER TABLE wellness_questions ADD player_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE wellness_questions DROP player_id');
        $this->addSql('ALTER TABLE wellness_questions ALTER created_by_id SET NOT NULL');
        $this->addSql('ALTER TABLE wellness_questions ALTER updated_by_id SET NOT NULL');
        $this->addSql('ALTER TABLE wellness_questions ALTER deleted_by_id SET NOT NULL');
        $this->addSql('ALTER TABLE wellness_questions ADD CONSTRAINT fk_15fa1107c036e511 FOREIGN KEY (player_id_id) REFERENCES player (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_15fa1107c036e511 ON wellness_questions (player_id_id)');
        $this->addSql('ALTER TABLE workload DROP CONSTRAINT FK_1203AA7B99E6F5DF');
        $this->addSql('DROP INDEX IDX_1203AA7B99E6F5DF');
        $this->addSql('ALTER TABLE workload ADD player_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE workload DROP deceleration');
        $this->addSql('ALTER TABLE workload DROP player_id');
        $this->addSql('ALTER TABLE workload ALTER created_date TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE workload ALTER created_date SET DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE workload ALTER created_by_id SET NOT NULL');
        $this->addSql('ALTER TABLE workload ALTER updated_by_id SET NOT NULL');
        $this->addSql('ALTER TABLE workload ALTER deleted_by_id SET NOT NULL');
        $this->addSql('ALTER TABLE workload ADD CONSTRAINT fk_1203aa7bc036e511 FOREIGN KEY (player_id_id) REFERENCES player (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_1203aa7bc036e511 ON workload (player_id_id)');
    }
}
