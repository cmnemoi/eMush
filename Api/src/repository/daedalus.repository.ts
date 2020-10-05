import {Daedalus} from '../models/daedalus.model';
import database from '../config/database';

export default class DaedalusRepository {
    public static findAll(): Promise<Daedalus[]> {
        return database.then(async connection => {
            const daedalusRepository = connection.getRepository(Daedalus);
            return daedalusRepository.find();
        });
    }

    public static find(id: number): Promise<Daedalus | null> {
        return database.then(async connection => {
            const daedalusRepository = connection.getRepository(Daedalus);
            return daedalusRepository
                .findOne(id, {relations: ['rooms', 'players', "players.room"]})
                .then((result: Daedalus | undefined) => {
                    return typeof result === 'undefined' ? null : result;
                });
        });
    }

    public static save(daedalus: Daedalus): Promise<Daedalus> {
        return database.then(async connection => {
            const daedalusRepository = connection.getRepository(Daedalus);

            return daedalusRepository.save(daedalus);
        });
    }
}
