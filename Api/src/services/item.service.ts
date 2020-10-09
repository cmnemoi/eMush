import {Room} from '../models/room.model';
import {Item} from '../models/item.model';
import ItemsConfig from '../../config/item.config';
import {logger} from '../config/logger';
import ItemRepository from '../repository/item.repository';
import {ItemsEnum} from '../enums/items.enum';

export default class ItemService {
    public static createItem(itemName: ItemsEnum, room: Room): Promise<Item> {
        const itemConfig = ItemsConfig.find(
            itemSearch => itemSearch.name === itemName
        );

        if (typeof itemConfig === 'undefined') {
            logger.error(itemName + ' does not exist or is not configurated');
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
        item.room = room;

        return ItemRepository.save(item);
    }
}
