import {Item} from '../models/item.model';
import database from '../config/database';

export default class ItemRepository {
    public static findAll(): Promise<Item[]> {
        return database.then(async connection => {
            const itemRepository = connection.getRepository(Item);
            return itemRepository.find();
        });
    }

    public static find(id: number): Promise<Item | null> {
        return database.then(async connection => {
            const itemRepository = connection.getRepository(Item);
            return itemRepository
                .findOne(id)
                .then((result: Item | undefined) => {
                    return typeof result === 'undefined' ? null : result;
                });
        });
    }

    public static save(item: Item): Promise<Item> {
        return database.then(async connection => {
            const itemRepository = connection.getRepository(Item);
            return itemRepository.save(item);
        });
    }

    public static remove(item: Item): Promise<Item> {
        return database.then(async connection => {
            const itemRepository = connection.getRepository(Item);
            return itemRepository.remove(item);
        });
    }
}
