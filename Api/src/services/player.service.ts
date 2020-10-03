import {Player} from '../models/player.model';
import {Daedalus} from '../models/daedalus.model';
import {Room} from '../models/room.model';
import {RoomEnum} from '../enums/room.enum';
import eventManager from '../config/event.manager';
import {PlayerEvent} from '../events/player.event';
import PlayerRepository from '../repository/player.repository';
import GameConfig from '../../config/game.config';
import CharacterConfig from '../../config/character.config';
import {logger} from '../config/logger';

export default class PlayerService {
    public static findAll(): Promise<Player[]> {
        return PlayerRepository.findAll();
    }

    public static find(id: number): Promise<Player | null> {
        return PlayerRepository.find(id);
    }

    public static save(player: Player): Promise<Player> {
        return PlayerRepository.save(player);
    }

    public static async initPlayer(
        daedalus: Daedalus,
        character: string
    ): Promise<Player> {
        const player = new Player();

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
        player.user = 'TODO';
        player.daedalus = daedalus;
        player.character = character;
        player.skills = [];
        player.statuses = [];
        player.items = [];
        player.healthPoint = GameConfig.initHealthPoint;
        player.moralPoint = GameConfig.initMoralPoint;
        player.actionPoint = GameConfig.initActionPoint;
        player.movementPoint = GameConfig.initMovementPoint;
        player.satiety = GameConfig.initSatiety;
        player.isDirty = false;
        player.isMush = false;

        const characterConfig = CharacterConfig.find(
            characterConfigs => characterConfigs.name === character
        );
        if (typeof characterConfig !== 'undefined') {
            if (typeof characterConfig.statuses !== 'undefined') {
                player.statuses = characterConfig.statuses;
            }
        } else {
            logger.error('Cannot load config for character: ' + character);
        }

        eventManager.emit(PlayerEvent.PLAYER_AWAKEN, player);

        return PlayerRepository.save(player);
    }
}
