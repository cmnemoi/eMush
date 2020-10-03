import {RoomLog} from '../models/roomLog.model';
import database from '../config/database';

export default class RoomLogRepository {
    public static findAll(): Promise<RoomLog[]> {
        return database.then(async connection => {
            const roomLogRepository = connection.getRepository(RoomLog);
            return roomLogRepository.find();
        });
    }

    public static find(id: number): Promise<RoomLog | null> {
        return database.then(async connection => {
            const roomLogRepository = connection.getRepository(RoomLog);
            return roomLogRepository
                .findOne(id)
                .then((result: RoomLog | undefined) => {
                    return typeof result === 'undefined' ? null : result;
                });
        });
    }

    public static save(roomLog: RoomLog): Promise<RoomLog> {
        return database.then(async connection => {
            const roomLogRepository = connection.getRepository(RoomLog);
            return roomLogRepository.save(roomLog);
        });
    }
}
