import {MigrationInterface, QueryRunner} from 'typeorm';

export class migration1602266506472 implements MigrationInterface {
    name = 'migration1602266506472';

    public async up(queryRunner: QueryRunner): Promise<void> {
        await queryRunner.query(
            'CREATE TABLE `door` (`id` int NOT NULL AUTO_INCREMENT, `name` varchar(255) NOT NULL, `statuses` text NOT NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB'
        );
        await queryRunner.query(
            'CREATE TABLE `item` (`id` int NOT NULL AUTO_INCREMENT, `name` varchar(255) NOT NULL, `type` varchar(255) NOT NULL, `statuses` text NOT NULL, `isDismantable` tinyint NOT NULL, `isHeavy` tinyint NOT NULL, `isStackable` tinyint NOT NULL, `isHideable` tinyint NOT NULL, `isMoveable` tinyint NOT NULL, `isFireDestroyable` tinyint NOT NULL, `isFireBreakable` tinyint NOT NULL, `createdAt` datetime(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6), `updatedAt` datetime(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6), `roomId` int NULL, `playerId` int NULL, `personalId` int NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB'
        );
        await queryRunner.query(
            'CREATE TABLE `room` (`id` int NOT NULL AUTO_INCREMENT, `name` varchar(255) NOT NULL, `statuses` text NOT NULL, `createdAt` datetime(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6), `updatedAt` datetime(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6), `daedalusId` int NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB'
        );
        await queryRunner.query(
            'CREATE TABLE `user` (`id` int NOT NULL AUTO_INCREMENT, `userId` varchar(255) NOT NULL, `username` varchar(255) NOT NULL, `createdAt` datetime(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6), `updatedAt` datetime(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6), PRIMARY KEY (`id`)) ENGINE=InnoDB'
        );
        await queryRunner.query(
            'CREATE TABLE `player` (`id` int NOT NULL AUTO_INCREMENT, `gameStatus` varchar(255) NOT NULL, `character` varchar(255) NOT NULL, `skills` text NOT NULL, `statuses` text NOT NULL, `healthPoint` int NOT NULL, `moralPoint` int NOT NULL, `actionPoint` int NOT NULL, `movementPoint` int NOT NULL, `satiety` int NOT NULL, `isMush` tinyint NOT NULL, `isDirty` tinyint NOT NULL, `createdAt` datetime(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6), `updatedAt` datetime(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6), `userId` int NULL, `daedalusId` int NULL, `roomId` int NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB'
        );
        await queryRunner.query(
            'CREATE TABLE `daedalus` (`id` int NOT NULL AUTO_INCREMENT, `oxygen` int NOT NULL, `fuel` int NOT NULL, `hull` int NOT NULL, `day` int NOT NULL, `cycle` int NOT NULL, `shield` int NOT NULL, `createdAt` datetime(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6), `updatedAt` datetime(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6), PRIMARY KEY (`id`)) ENGINE=InnoDB'
        );
        await queryRunner.query(
            'CREATE TABLE `room_log` (`id` int NOT NULL AUTO_INCREMENT, `roomId` int NOT NULL, `playerId` int NOT NULL, `visibility` varchar(255) NOT NULL, `message` varchar(255) NOT NULL, `createdAt` datetime(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6), PRIMARY KEY (`id`)) ENGINE=InnoDB'
        );
        await queryRunner.query(
            'CREATE TABLE `room_doors_door` (`roomId` int NOT NULL, `doorId` int NOT NULL, INDEX `IDX_5101ffd88419b148ddaf030a22` (`roomId`), INDEX `IDX_da9f6f819a7a18873d997e15ce` (`doorId`), PRIMARY KEY (`roomId`, `doorId`)) ENGINE=InnoDB'
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
            'ALTER TABLE `player` ADD CONSTRAINT `FK_7687919bf054bf262c669d3ae21` FOREIGN KEY (`userId`) REFERENCES `user`(`id`) ON DELETE NO ACTION ON UPDATE NO ACTION'
        );
        await queryRunner.query(
            'ALTER TABLE `player` ADD CONSTRAINT `FK_a538541f4ca2e6471fd5c9f3589` FOREIGN KEY (`daedalusId`) REFERENCES `daedalus`(`id`) ON DELETE NO ACTION ON UPDATE NO ACTION'
        );
        await queryRunner.query(
            'ALTER TABLE `player` ADD CONSTRAINT `FK_145fea442eb4b687dbc6ebbefe3` FOREIGN KEY (`roomId`) REFERENCES `room`(`id`) ON DELETE NO ACTION ON UPDATE NO ACTION'
        );
        await queryRunner.query(
            'ALTER TABLE `room_doors_door` ADD CONSTRAINT `FK_5101ffd88419b148ddaf030a22c` FOREIGN KEY (`roomId`) REFERENCES `room`(`id`) ON DELETE CASCADE ON UPDATE NO ACTION'
        );
        await queryRunner.query(
            'ALTER TABLE `room_doors_door` ADD CONSTRAINT `FK_da9f6f819a7a18873d997e15cec` FOREIGN KEY (`doorId`) REFERENCES `door`(`id`) ON DELETE CASCADE ON UPDATE NO ACTION'
        );
    }

    public async down(queryRunner: QueryRunner): Promise<void> {
        await queryRunner.query(
            'ALTER TABLE `room_doors_door` DROP FOREIGN KEY `FK_da9f6f819a7a18873d997e15cec`'
        );
        await queryRunner.query(
            'ALTER TABLE `room_doors_door` DROP FOREIGN KEY `FK_5101ffd88419b148ddaf030a22c`'
        );
        await queryRunner.query(
            'ALTER TABLE `player` DROP FOREIGN KEY `FK_145fea442eb4b687dbc6ebbefe3`'
        );
        await queryRunner.query(
            'ALTER TABLE `player` DROP FOREIGN KEY `FK_a538541f4ca2e6471fd5c9f3589`'
        );
        await queryRunner.query(
            'ALTER TABLE `player` DROP FOREIGN KEY `FK_7687919bf054bf262c669d3ae21`'
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
            'DROP INDEX `IDX_da9f6f819a7a18873d997e15ce` ON `room_doors_door`'
        );
        await queryRunner.query(
            'DROP INDEX `IDX_5101ffd88419b148ddaf030a22` ON `room_doors_door`'
        );
        await queryRunner.query('DROP TABLE `room_doors_door`');
        await queryRunner.query('DROP TABLE `room_log`');
        await queryRunner.query('DROP TABLE `daedalus`');
        await queryRunner.query('DROP TABLE `player`');
        await queryRunner.query('DROP TABLE `user`');
        await queryRunner.query('DROP TABLE `room`');
        await queryRunner.query('DROP TABLE `item`');
        await queryRunner.query('DROP TABLE `door`');
    }
}
