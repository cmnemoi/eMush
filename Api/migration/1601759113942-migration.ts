import {MigrationInterface, QueryRunner} from 'typeorm';

export class migration1601759113942 implements MigrationInterface {
    name = 'migration1601759113942';

    public async up(queryRunner: QueryRunner): Promise<void> {
        await queryRunner.query(
            'CREATE TABLE `item` (`id` int NOT NULL AUTO_INCREMENT, `name` varchar(255) NOT NULL, `type` varchar(255) NOT NULL, `statuses` text NOT NULL, `isDismantable` tinyint NOT NULL, `isHeavy` tinyint NOT NULL, `isStackable` tinyint NOT NULL, `isHideable` tinyint NOT NULL, `isMoveable` tinyint NOT NULL, `isFireDestroyable` tinyint NOT NULL, `isFireBreakable` tinyint NOT NULL, `createdAt` datetime(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6), `updatedAt` datetime(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6), `roomId` int NULL, `playerId` int NULL, `personalId` int NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB'
        );
        await queryRunner.query('ALTER TABLE `player` DROP COLUMN `items`');
        await queryRunner.query(
            'ALTER TABLE `room` DROP FOREIGN KEY `FK_9b6dc42de7953a6b72df589cd19`'
        );
        await queryRunner.query(
            'ALTER TABLE `room` CHANGE `daedalusId` `daedalusId` int NULL'
        );
        await queryRunner.query(
            'ALTER TABLE `player` DROP FOREIGN KEY `FK_a538541f4ca2e6471fd5c9f3589`'
        );
        await queryRunner.query(
            'ALTER TABLE `player` DROP FOREIGN KEY `FK_145fea442eb4b687dbc6ebbefe3`'
        );
        await queryRunner.query(
            'ALTER TABLE `player` CHANGE `daedalusId` `daedalusId` int NULL'
        );
        await queryRunner.query(
            'ALTER TABLE `player` CHANGE `roomId` `roomId` int NULL'
        );
        await queryRunner.query(
            'ALTER TABLE `item` ADD CONSTRAINT `FK_ae68a819aeb86e627d528acf10e` FOREIGN KEY (`roomId`) REFERENCES `room`(`id`) ON DELETE NO ACTION ON UPDATE NO ACTION'
        );
        await queryRunner.query(
            'ALTER TABLE `item` ADD CONSTRAINT `FK_db17aef71ac2c1e2e6d27ff19e7` FOREIGN KEY (`playerId`) REFERENCES `player`(`id`) ON DELETE NO ACTION ON UPDATE NO ACTION'
        );
        await queryRunner.query(
            'ALTER TABLE `item` ADD CONSTRAINT `FK_3b3739a1a5c5248950816150cc5` FOREIGN KEY (`personalId`) REFERENCES `player`(`id`) ON DELETE NO ACTION ON UPDATE NO ACTION'
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
        await queryRunner.query(
            'ALTER TABLE `item` DROP FOREIGN KEY `FK_3b3739a1a5c5248950816150cc5`'
        );
        await queryRunner.query(
            'ALTER TABLE `item` DROP FOREIGN KEY `FK_db17aef71ac2c1e2e6d27ff19e7`'
        );
        await queryRunner.query(
            'ALTER TABLE `item` DROP FOREIGN KEY `FK_ae68a819aeb86e627d528acf10e`'
        );
        await queryRunner.query(
            "ALTER TABLE `player` CHANGE `roomId` `roomId` int NULL DEFAULT 'NULL'"
        );
        await queryRunner.query(
            "ALTER TABLE `player` CHANGE `daedalusId` `daedalusId` int NULL DEFAULT 'NULL'"
        );
        await queryRunner.query(
            'ALTER TABLE `player` ADD CONSTRAINT `FK_145fea442eb4b687dbc6ebbefe3` FOREIGN KEY (`roomId`) REFERENCES `room`(`id`) ON DELETE NO ACTION ON UPDATE NO ACTION'
        );
        await queryRunner.query(
            'ALTER TABLE `player` ADD CONSTRAINT `FK_a538541f4ca2e6471fd5c9f3589` FOREIGN KEY (`daedalusId`) REFERENCES `daedalus`(`id`) ON DELETE NO ACTION ON UPDATE NO ACTION'
        );
        await queryRunner.query(
            "ALTER TABLE `room` CHANGE `daedalusId` `daedalusId` int NULL DEFAULT 'NULL'"
        );
        await queryRunner.query(
            'ALTER TABLE `room` ADD CONSTRAINT `FK_9b6dc42de7953a6b72df589cd19` FOREIGN KEY (`daedalusId`) REFERENCES `daedalus`(`id`) ON DELETE NO ACTION ON UPDATE NO ACTION'
        );
        await queryRunner.query(
            'ALTER TABLE `player` ADD `items` text NOT NULL'
        );
        await queryRunner.query('DROP TABLE `item`');
    }
}
