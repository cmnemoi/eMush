import {Room} from "../models/room.model";
import database from "../config/database";
import {Player} from "../models/player.model";

export default class RoomRepository {
    public static findAll(): Promise<Room[]> {
        return database.then(async connection =>{
            const roomRepository = connection.getRepository(Room);
            return roomRepository.find()
        });
    }

    public static find(id: number): Promise<Room | null> {
        return database.then(async connection =>{
            const roomRepository = connection.getRepository(Room);
            return roomRepository.findOne(id).then((result: Room | undefined) => {
                return typeof result === 'undefined' ? null : result;
            })
        });
    }

    public static save(room: Room): Promise<Room> {
        return database.then(async connection =>{
            const roomRepository = connection.getRepository(Room);
            return roomRepository.save(room)
        });
    }

}