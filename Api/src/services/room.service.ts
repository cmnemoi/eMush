import {Room} from '../models/room.model';
import RoomRepository from "../repository/room.repository";

export default class RoomService {
    public static findAll(): Promise<Room[]> {
        return RoomRepository.findAll();
    }

    public static find(id: number): Promise<Room | null> {
        return RoomRepository.find(id);
    }

    public static save(room: Room): Promise<Room> {
        return RoomRepository.save(room);
    }
}
