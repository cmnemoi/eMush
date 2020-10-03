import {Player} from '../models/player.model';
import database from '../config/database';

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
                .findOne(id, {relations: ['room', 'room.doors']})
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
