import {Door} from '../models/door.model';
import database from '../config/database';
import {Daedalus} from '../models/daedalus.model';
import {FindOneOptions} from 'typeorm/find-options/FindOneOptions';

export default class DoorRepository {
    public static findAll(): Promise<Door[]> {
        return database.then(async connection => {
            const doorRepository = connection.getRepository(Door);
            return doorRepository.find();
        });
    }

    public static find(
        id: number,
        options: FindOneOptions<Door> = {}
    ): Promise<Door | null> {
        return database.then(async connection => {
            const doorRepository = connection.getRepository(Door);
            return doorRepository
                .findOne(id, options)
                .then((result: Door | undefined) => {
                    return typeof result === 'undefined' ? null : result;
                });
        });
    }

    public static findByName(
        name: string,
        daedalus: Daedalus
    ): Promise<Door | null> {
        return database.then(async connection => {
            const doorRepository = connection.getRepository(Door);
            return doorRepository
                .createQueryBuilder('door')
                .innerJoinAndSelect('door.rooms', 'room')
                .where('room.daedalus = :daedalus', {daedalus: daedalus.id})
                .andWhere('door.name = :name', {name: name})
                .getOne()
                .then((result: Door | undefined) => {
                    return typeof result === 'undefined' ? null : result;
                });
        });
    }

    public static save(door: Door): Promise<Door> {
        return database.then(async connection => {
            const doorRepository = connection.getRepository(Door);
            return doorRepository.save(door);
        });
    }
}
