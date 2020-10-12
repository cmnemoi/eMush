import {MigrationInterface, QueryRunner} from "typeorm";

export class migration1602538831131 implements MigrationInterface {
    name = 'migration1602538831131'

    public async up(queryRunner: QueryRunner): Promise<void> {
        await queryRunner.query("CREATE TABLE `door` (`id` int NOT NULL AUTO_INCREMENT, `name` varchar(255) NOT NULL, `statuses` text NOT NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB");
        await queryRunner.query("CREATE TABLE `item` (`id` int NOT NULL AUTO_INCREMENT, `name` varchar(255) NOT NULL, `type` varchar(255) NOT NULL, `statuses` text NOT NULL, `is_dismantable` tinyint NOT NULL, `is_heavy` tinyint NOT NULL, `is_stackable` tinyint NOT NULL, `is_hideable` tinyint NOT NULL, `is_movable` tinyint NOT NULL, `is_fire_destroyable` tinyint NOT NULL, `is_fire_breakable` tinyint NOT NULL, `created_at` datetime(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6), `updated_at` datetime(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6), `room_id` int NULL, `player_id` int NULL, `personal_id` int NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB");
        await queryRunner.query("CREATE TABLE `room` (`id` int NOT NULL AUTO_INCREMENT, `name` varchar(255) NOT NULL, `statuses` text NOT NULL, `created_at` datetime(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6), `updated_at` datetime(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6), `daedalus_id` int NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB");
        await queryRunner.query("CREATE TABLE `user` (`id` int NOT NULL AUTO_INCREMENT, `user_id` varchar(255) NOT NULL, `username` varchar(255) NOT NULL, `created_at` datetime(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6), `updated_at` datetime(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6), PRIMARY KEY (`id`)) ENGINE=InnoDB");
        await queryRunner.query("CREATE TABLE `player` (`id` int NOT NULL AUTO_INCREMENT, `game_status` varchar(255) NOT NULL, `character` varchar(255) NOT NULL, `skills` text NOT NULL, `statuses` text NOT NULL, `health_point` int NOT NULL, `moral_point` int NOT NULL, `action_point` int NOT NULL, `movement_point` int NOT NULL, `satiety` int NOT NULL, `mush` tinyint NOT NULL, `created_at` datetime(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6), `updated_at` datetime(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6), `userId` int NULL, `daedalus_id` int NULL, `room_id` int NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB");
        await queryRunner.query("CREATE TABLE `daedalus` (`id` int NOT NULL AUTO_INCREMENT, `oxygen` int NOT NULL, `fuel` int NOT NULL, `hull` int NOT NULL, `day` int NOT NULL, `cycle` int NOT NULL, `shield` int NOT NULL, `created_at` datetime(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6), `updated_at` datetime(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6), PRIMARY KEY (`id`)) ENGINE=InnoDB");
        await queryRunner.query("CREATE TABLE `room_log` (`id` int NOT NULL AUTO_INCREMENT, `room_id` int NOT NULL, `player_id` int NOT NULL, `visibility` varchar(255) NOT NULL, `message` varchar(255) NOT NULL, `created_at` datetime(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6), PRIMARY KEY (`id`)) ENGINE=InnoDB");
        await queryRunner.query("CREATE TABLE `room_doors_door` (`roomId` int NOT NULL, `doorId` int NOT NULL, INDEX `IDX_5101ffd88419b148ddaf030a22` (`roomId`), INDEX `IDX_da9f6f819a7a18873d997e15ce` (`doorId`), PRIMARY KEY (`roomId`, `doorId`)) ENGINE=InnoDB");
        await queryRunner.query("ALTER TABLE `item` ADD CONSTRAINT `FK_1465e9f0ace918feede31d55347` FOREIGN KEY (`room_id`) REFERENCES `room`(`id`) ON DELETE NO ACTION ON UPDATE NO ACTION");
        await queryRunner.query("ALTER TABLE `item` ADD CONSTRAINT `FK_c2689d87febf0f07c1f3399fb83` FOREIGN KEY (`player_id`) REFERENCES `player`(`id`) ON DELETE NO ACTION ON UPDATE NO ACTION");
        await queryRunner.query("ALTER TABLE `item` ADD CONSTRAINT `FK_5e19da6e23790cb234f4c45e572` FOREIGN KEY (`personal_id`) REFERENCES `player`(`id`) ON DELETE NO ACTION ON UPDATE NO ACTION");
        await queryRunner.query("ALTER TABLE `room` ADD CONSTRAINT `FK_d81d67bbfe594d322f3ea0ce3e2` FOREIGN KEY (`daedalus_id`) REFERENCES `daedalus`(`id`) ON DELETE NO ACTION ON UPDATE NO ACTION");
        await queryRunner.query("ALTER TABLE `player` ADD CONSTRAINT `FK_7687919bf054bf262c669d3ae21` FOREIGN KEY (`userId`) REFERENCES `user`(`id`) ON DELETE NO ACTION ON UPDATE NO ACTION");
        await queryRunner.query("ALTER TABLE `player` ADD CONSTRAINT `FK_710995dec4c5b6ff967108746d6` FOREIGN KEY (`daedalus_id`) REFERENCES `daedalus`(`id`) ON DELETE NO ACTION ON UPDATE NO ACTION");
        await queryRunner.query("ALTER TABLE `player` ADD CONSTRAINT `FK_1b9dc0cfae7a7e69999acd0547f` FOREIGN KEY (`room_id`) REFERENCES `room`(`id`) ON DELETE NO ACTION ON UPDATE NO ACTION");
        await queryRunner.query("ALTER TABLE `room_doors_door` ADD CONSTRAINT `FK_5101ffd88419b148ddaf030a22c` FOREIGN KEY (`roomId`) REFERENCES `room`(`id`) ON DELETE CASCADE ON UPDATE NO ACTION");
        await queryRunner.query("ALTER TABLE `room_doors_door` ADD CONSTRAINT `FK_da9f6f819a7a18873d997e15cec` FOREIGN KEY (`doorId`) REFERENCES `door`(`id`) ON DELETE CASCADE ON UPDATE NO ACTION");
    }

    public async down(queryRunner: QueryRunner): Promise<void> {
        await queryRunner.query("ALTER TABLE `room_doors_door` DROP FOREIGN KEY `FK_da9f6f819a7a18873d997e15cec`");
        await queryRunner.query("ALTER TABLE `room_doors_door` DROP FOREIGN KEY `FK_5101ffd88419b148ddaf030a22c`");
        await queryRunner.query("ALTER TABLE `player` DROP FOREIGN KEY `FK_1b9dc0cfae7a7e69999acd0547f`");
        await queryRunner.query("ALTER TABLE `player` DROP FOREIGN KEY `FK_710995dec4c5b6ff967108746d6`");
        await queryRunner.query("ALTER TABLE `player` DROP FOREIGN KEY `FK_7687919bf054bf262c669d3ae21`");
        await queryRunner.query("ALTER TABLE `room` DROP FOREIGN KEY `FK_d81d67bbfe594d322f3ea0ce3e2`");
        await queryRunner.query("ALTER TABLE `item` DROP FOREIGN KEY `FK_5e19da6e23790cb234f4c45e572`");
        await queryRunner.query("ALTER TABLE `item` DROP FOREIGN KEY `FK_c2689d87febf0f07c1f3399fb83`");
        await queryRunner.query("ALTER TABLE `item` DROP FOREIGN KEY `FK_1465e9f0ace918feede31d55347`");
        await queryRunner.query("DROP INDEX `IDX_da9f6f819a7a18873d997e15ce` ON `room_doors_door`");
        await queryRunner.query("DROP INDEX `IDX_5101ffd88419b148ddaf030a22` ON `room_doors_door`");
        await queryRunner.query("DROP TABLE `room_doors_door`");
        await queryRunner.query("DROP TABLE `room_log`");
        await queryRunner.query("DROP TABLE `daedalus`");
        await queryRunner.query("DROP TABLE `player`");
        await queryRunner.query("DROP TABLE `user`");
        await queryRunner.query("DROP TABLE `room`");
        await queryRunner.query("DROP TABLE `item`");
        await queryRunner.query("DROP TABLE `door`");
    }

}
