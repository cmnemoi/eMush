import {Player} from '../models/player.model';
import database from '../config/database';
import {FindConditions} from 'typeorm/find-options/FindConditions';
import {FindOneOptions} from 'typeorm/find-options/FindOneOptions';

export default class PlayerRepository {
    public static findAll(): Promise<Player[]> {
        return database.then(async connection => {
            const playerRepository = connection.getRepository(Player);
            return playerRepository.find();
        });
    }

    public static find(id: number): Promise<Player | null> {
        return database.then(async connection => {
            const playerRepository = connection.getRepository(Player);
            return playerRepository
                .findOne(id, {
                    relations: ['room', 'room.doors', 'items', 'room.items'],
                })
                .then((result: Player | undefined) => {
                    return typeof result === 'undefined' ? null : result;
                });
        });
    }

    public static findOneByCriteria(
        criteria: FindConditions<Player>,
        options: FindOneOptions<Player>
    ): Promise<Player | null> {
        return database.then(async connection => {
            const playerRepository = connection.getRepository(Player);
            return playerRepository
                .findOne(criteria, options)
                .then((result: Player | undefined) => {
                    return typeof result === 'undefined' ? null : result;
                });
        });
    }

    public static save(player: Player): Promise<Player> {
        return database.then(async connection => {
            const playerRepository = connection.getRepository(Player);
            return playerRepository.save(player);
        });
    }
}
