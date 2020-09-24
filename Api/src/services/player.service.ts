import {Player} from '../models/player.model';
import {Identifier} from 'sequelize';
import {Daedalus} from '../models/daedalus.model';
import {Room} from '../models/room.model';

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
                include: [{model: Daedalus, as: 'daedalus'}],
            }
        );

        const room = Room.create({});
        player.daedalus = daedalus;
        player.character = character;
        player.room = await room; // daedalus.getRoom('laboratory');
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

        return player.save();
    }
}
