import {Identifier} from 'sequelize';
import {RoomLog} from '../models/roomLog.model';

export default class RoomLogService {
    public static findAll(): Promise<RoomLog[]> {
        return RoomLog.findAll<RoomLog>({});
    }

    public static find(id: Identifier): Promise<RoomLog | null> {
        return RoomLog.findByPk<RoomLog>(id);
    }

    public static save(roomLog: RoomLog): Promise<RoomLog> {
        return roomLog.save();
    }
}
