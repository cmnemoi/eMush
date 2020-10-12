import {User} from '../models/user.model';
import {Item} from '../models/item.model';

export function itemSerializer(
    item: Item,
    user: User
): Record<string, unknown> {
    return {
        id: item.id,
        name: item.name,
        type: item.type,
        statuses: item.statuses,
        room: item.room,
        player: item.player,
        isDismantable: item.isDismantable,
        isHeavy: item.isHeavy,
        personal: item.personal,
        isStackable: item.isStackable,
        isHideable: item.isHideable,
        isMoveable: item.isMovable,
        isFireDestroyable: item.isFireDestroyable,
        isFireBreakable: item.isFireBreakable,
    };
}
