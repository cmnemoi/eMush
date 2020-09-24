import {Room} from '../models/room.model';
import {Identifier} from 'sequelize';

export default class RoomService {
    public static findAll(): Promise<Room[]> {
        return Room.findAll<Room>({});
    }

    public static find(name: Identifier): Promise<Room | null> {
        return Room.findByPk<Room>(name);
    }

    public static save(character: Room): Promise<Room> {
        return character.save();
    }
}
