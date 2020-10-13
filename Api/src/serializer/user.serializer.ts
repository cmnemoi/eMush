import {User} from '../models/user.model';
import {Item} from '../models/item.model';
import {Room} from '../models/room.model';
import {
    Column,
    CreateDateColumn,
    JoinColumn,
    JoinTable,
    ManyToMany,
    ManyToOne,
    OneToMany,
    PrimaryGeneratedColumn,
    UpdateDateColumn,
} from 'typeorm';
import {Daedalus} from '../models/daedalus.model';
import {Player} from '../models/player.model';
import {Door} from '../models/door.model';
import {daedalusSerializer} from './daedalus.serializer';
import {itemSerializer} from './item.serializer';
import {playerSerializer} from './player.serializer';
import {doorSerializer} from './door.serializer';
import PlayerService from "../services/player.service";

export async function userSerializer(
    user: User,
    authenticatedUSer: User
): Promise<Record<string, unknown>> {
    const currentGame = await PlayerService.findCurrentPlayer(user)
    return {
        id: user.id,
        username: user.username,
        currentGame: currentGame?.id
    };
}
