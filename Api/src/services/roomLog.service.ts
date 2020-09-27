import {RoomLog} from '../models/roomLog.model';
import RoomLogRepository from "../repository/roomLog.repository";

export default class RoomLogService {
    public static findAll(): Promise<RoomLog[]> {
        return RoomLogRepository.findAll();
    }

    public static find(id: number): Promise<RoomLog | null> {
        return RoomLogRepository.find(id);
    }

    public static save(roomLog: RoomLog): Promise<RoomLog> {
        return RoomLogRepository.save(roomLog);
    }
}
