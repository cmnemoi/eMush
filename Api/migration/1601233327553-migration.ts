import {MigrationInterface, QueryRunner} from 'typeorm';

export class migration1601233327553 implements MigrationInterface {
    name = 'migration1601233327553';

    public async up(queryRunner: QueryRunner): Promise<void> {
        await queryRunner.query(
            'CREATE TABLE `room` (`id` int NOT NULL AUTO_INCREMENT, `name` varchar(255) NOT NULL, `statuses` text NOT NULL, `createdAt` datetime(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6), `updatedAt` datetime(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6), `daedalusId` int NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB'
        );
        await queryRunner.query(
            'CREATE TABLE `player` (`id` int NOT NULL AUTO_INCREMENT, `user` varchar(255) NOT NULL, `character` varchar(255) NOT NULL, `skills` text NOT NULL, `items` text NOT NULL, `statuses` text NOT NULL, `healthPoint` int NOT NULL, `moralPoint` int NOT NULL, `actionPoint` int NOT NULL, `movementPoint` int NOT NULL, `satiety` int NOT NULL, `isMush` tinyint NOT NULL, `isDirty` tinyint NOT NULL, `createdAt` datetime(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6), `updatedAt` datetime(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6), `daedalusId` int NULL, `roomId` int NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB'
        );
        await queryRunner.query(
            'CREATE TABLE `daedalus` (`id` int NOT NULL AUTO_INCREMENT, `oxygen` int NOT NULL, `fuel` int NOT NULL, `hull` int NOT NULL, `day` int NOT NULL, `cycle` int NOT NULL, `shield` int NOT NULL, `createdAt` datetime(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6), `updatedAt` datetime(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6), PRIMARY KEY (`id`)) ENGINE=InnoDB'
        );
        await queryRunner.query(
            'CREATE TABLE `room_log` (`id` int NOT NULL AUTO_INCREMENT, `roomId` int NOT NULL, `log` varchar(255) NOT NULL, `createdAt` datetime(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6), PRIMARY KEY (`id`)) ENGINE=InnoDB'
        );
        await queryRunner.query(
            'ALTER TABLE `room` ADD CONSTRAINT `FK_9b6dc42de7953a6b72df589cd19` FOREIGN KEY (`daedalusId`) REFERENCES `daedalus`(`id`) ON DELETE NO ACTION ON UPDATE NO ACTION'
        );
        await queryRunner.query(
            'ALTER TABLE `player` ADD CONSTRAINT `FK_a538541f4ca2e6471fd5c9f3589` FOREIGN KEY (`daedalusId`) REFERENCES `daedalus`(`id`) ON DELETE NO ACTION ON UPDATE NO ACTION'
        );
        await queryRunner.query(
            'ALTER TABLE `player` ADD CONSTRAINT `FK_145fea442eb4b687dbc6ebbefe3` FOREIGN KEY (`roomId`) REFERENCES `room`(`id`) ON DELETE NO ACTION ON UPDATE NO ACTION'
        );
    }

    public async down(queryRunner: QueryRunner): Promise<void> {
        await queryRunner.query(
            'ALTER TABLE `player` DROP FOREIGN KEY `FK_145fea442eb4b687dbc6ebbefe3`'
        );
        await queryRunner.query(
            'ALTER TABLE `player` DROP FOREIGN KEY `FK_a538541f4ca2e6471fd5c9f3589`'
        );
        await queryRunner.query(
            'ALTER TABLE `room` DROP FOREIGN KEY `FK_9b6dc42de7953a6b72df589cd19`'
        );
        await queryRunner.query('DROP TABLE `room_log`');
        await queryRunner.query('DROP TABLE `daedalus`');
        await queryRunner.query('DROP TABLE `player`');
        await queryRunner.query('DROP TABLE `room`');
    }
}
