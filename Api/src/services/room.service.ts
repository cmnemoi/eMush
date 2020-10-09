import {Room} from '../models/room.model';
import RoomRepository from '../repository/room.repository';
import {Door} from '../models/door.model';
import DoorRepository from '../repository/door.repository';
import {Daedalus} from '../models/daedalus.model';
import ItemService from './item.service';
import {RoomConfig} from '../../config/daedalus.config';

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

    public static async initRoom(
        roomConfig: RoomConfig,
        daedalus: Daedalus
    ): Promise<Room> {
        const room = new Room();
        room.name = roomConfig.name;
        room.statuses = [];
        room.doors = [];
        room.items = [];
        room.daedalus = daedalus;
        await RoomRepository.save(room);
        for (const doorName of roomConfig.doors) {
            let door = await DoorRepository.findByName(doorName, daedalus);
            if (door === null) {
                door = new Door();
                door.name = doorName;
                door.statuses = [];
                await DoorRepository.save(door);
            }
            room.doors.push(door);
            await RoomRepository.save(room);
        }
        for (const itemName of roomConfig.items) {
            const item = await ItemService.createItem(itemName, room);

            room.addItem(item);
        }
        await RoomRepository.save(room);

        return room;
    }
}
