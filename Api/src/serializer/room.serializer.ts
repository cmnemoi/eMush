import {User} from '../models/user.model';
import {Item} from '../models/item.model';
import {Room} from '../models/room.model';
import {Player} from '../models/player.model';
import {Door} from '../models/door.model';
import {daedalusSerializer} from './daedalus.serializer';
import {itemSerializer} from './item.serializer';
import {playerSerializer} from './player.serializer';
import {doorSerializer} from './door.serializer';

export function roomSerializer(
    room: Room,
    user: User
): Record<string, unknown> {
    const result: {
        [key: string]:
            | number
            | string
            | Date
            | string[]
            | Record<string, unknown>[]
            | Record<string, unknown>;
    } = {
        id: room.id,
        name: room.name,
        statuses: room.statuses,
        createdAt: room.createdAt,
        updatedAt: room.updatedAt,
    };

    if (typeof room.daedalus !== 'undefined') {
        result.daedalus = daedalusSerializer(room.daedalus, user);
    }

    if (typeof room.players !== 'undefined') {
        const players: Record<string, unknown>[] = [];
        room.players.forEach((player: Player) =>
            players.push(playerSerializer(player, user))
        );
        result.players = players;
    }

    if (typeof room.items !== 'undefined') {
        const items: Record<string, unknown>[] = [];
        room.items.forEach((item: Item) =>
            items.push(itemSerializer(item, user))
        );
        result.items = items;
    }

    if (typeof room.doors !== 'undefined') {
        const doors: Record<string, unknown>[] = [];
        room.doors.forEach((door: Door) =>
            doors.push(doorSerializer(door, user))
        );
        result.doors = doors;
    }

    return result;
}
