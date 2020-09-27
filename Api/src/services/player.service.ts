import {Player} from '../models/player.model';
import {Identifier} from 'sequelize';
import {Daedalus} from '../models/daedalus.model';
import {Room} from '../models/room.model';
import {RoomEnum} from '../enums/room.enum';
import eventManager from '../config/event.manager';
import {PlayerEvent} from '../events/player.event';

export default class PlayerService {
    public static findAll(): Promise<Player[]> {
        return Player.findAll<Player>({});
    }

    public static find(name: Identifier): Promise<Player | null> {
        return Player.findByPk<Player>(name);
    }

    public static save(player: Player): Promise<Player> {
        return player.save();
    }

    public static async initPlayer(
        daedalus: Daedalus,
        character: string
    ): Promise<Player> {
        const player = Player.build(
            {},
            {
                include: [
                    {model: Daedalus, as: 'daedalus'},
                    {model: Room, as: 'room'},
                ],
            }
        );

        const room = daedalus.getRoom(RoomEnum.LABORATORY);
        if (room instanceof Room) {
            player.room = room;
        } else {
            throw new Error(
                RoomEnum.LABORATORY +
                    ' does not exist on Daedalus : ' +
                    daedalus.id
            );
        }

        player.daedalus = daedalus;
        player.character = character;
        player.skills = [];
        player.statuses = [];
        player.items = [];
        player.healthPoint = 10;
        player.moralPoint = 10;
        player.actionPoint = 10;
        player.movementPoint = 10;
        player.satiety = 10;
        player.isDirty = false;
        player.isMush = false;

        eventManager.emit(PlayerEvent.PLAYER_AWAKEN, player);

        return player.save();
    }
}
