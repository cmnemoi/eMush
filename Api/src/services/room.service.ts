import {Room} from '../models/room.model';
import RoomRepository from '../repository/room.repository';
import {Door} from '../models/door.model';
import DoorRepository from '../repository/door.repository';
import {Daedalus} from '../models/daedalus.model';
import {Item} from '../models/item.model';
import ItemRepository from '../repository/item.repository';
import {logger} from '../config/logger';
import ItemsConfig from '../../config/item.config';

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
        roomConfig: any,
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
            const itemConfig = ItemsConfig.find(
                itemSearch => itemSearch.name === itemName
            );

            if (typeof itemConfig === 'undefined') {
                logger.error(
                    itemName + ' does not exist or is not configurated'
                );
                throw new Error(
                    itemName + ' does not exist or is not configurated'
                );
            }

            const item = new Item();
            item.name = itemConfig.name;
            item.type = itemConfig.type;
            item.isHeavy = itemConfig.isHeavy;
            item.isDismantable = itemConfig.isDismantable;
            item.isStackable = itemConfig.isStackable;
            item.isHideable = itemConfig.isHideable;
            item.isMoveable = itemConfig.isMoveable;
            item.isFireDestroyable = itemConfig.isFireDestroyable;
            item.isFireBreakable = itemConfig.isFireBreakable;

            room.items.push(item);
            await ItemRepository.save(item);
        }
        await RoomRepository.save(room);

        return room;
    }
}
