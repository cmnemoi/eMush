import {Player} from '../models/player.model';
import {User} from '../models/user.model';
import {Item} from '../models/item.model';
import {itemSerializer} from './item.serializer';

export function playerSerializer(
    player: Player,
    user: User
): Record<string, unknown> {
    const result: {
        [key: string]:
            | number
            | boolean
            | string
            | Record<string, unknown>[]
            | Record<string, unknown>
            | string[];
    } = {
        id: player.id,
        gameStatus: player.gameStatus,
        daedalus: player.daedalus.id,
        room: player.room.id,
        character: player.character,
        skills: player.skills,
        statuses: player.statuses,
    };

    // These fields are only visible to the user
    if (user.id === player.user.id) {
        result.items = [];
        player.items.forEach((item: Item) => itemSerializer(item, user));
        result.healthPoint = player.healthPoint;
        result.moralPoint = player.moralPoint;
        result.actionPoint = player.actionPoint;
        result.movementPoint = player.movementPoint;
        result.mush = player.isMush();
        result.createdAt = player.createdAt.toUTCString();
        result.updatedAt = player.updatedAt.toUTCString();
    }

    return result;
}
