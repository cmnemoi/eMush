import {RoomLog} from '../models/roomLog.model';
import RoomLogRepository from '../repository/roomLog.repository';
import {LogEnum} from '../enums/log.enum';
import {Room} from '../models/room.model';
import {Player} from '../models/player.model';
import {VisibilityEnum} from '../enums/visibility.enum';
import GameConfig from '../../config/game.config';
import RandomService from './random.service';
import {logger} from '../config/logger';
import formatMessage from 'format-message';
import {CharactersEnum} from '../enums/characters.enum';
import {RoomEnum} from '../enums/room.enum';
import {ItemsEnum} from '../enums/items.enum';

interface LogProvider {
    message: string;
    weighting: number;
}

interface MessageParameters {
    character?: CharactersEnum | null;
    room?: RoomEnum | null;
    item?: ItemsEnum | null;
    number?: number | null;
}

export default class RoomLogService {
    public static findAll(): Promise<RoomLog[]> {
        return RoomLogRepository.findAll();
    }

    public static find(id: number): Promise<RoomLog | null> {
        return RoomLogRepository.find(id);
    }

    public static save(roomLog: RoomLog): Promise<RoomLog> {
        return RoomLogRepository.save(roomLog);
    }

    public static async createLog(
        logType: LogEnum,
        parameters: MessageParameters,
        room: Room,
        player: Player,
        visibility: VisibilityEnum,
        date: Date = new Date()
    ): Promise<void> {
        const roomLog = new RoomLog();
        roomLog.playerId = player.id;
        roomLog.roomId = room.id;
        roomLog.createdAt = date;
        roomLog.visibility = visibility;

        const logsMessages = await import(
            '../../locales/' + GameConfig.language + '/logs'
        );
        const logsPossible: LogProvider[] = logsMessages.default[logType];
        let sumWeight = 0;
        logsPossible.forEach(value => {
            sumWeight += value.weighting;
        });

        const randomValue = RandomService.random(sumWeight);
        sumWeight = 0;
        let selectedMessage = null;
        logsPossible.forEach(value => {
            if (sumWeight > randomValue) {
                return;
            }
            sumWeight += value.weighting;
            selectedMessage = value.message;
        });

        if (selectedMessage === null) {
            logger.error('Log could not be selected');
            return;
        }
        const params = await RoomLogService.loadParameters(parameters);

        formatMessage.setup({
            missingTranslation: 'ignore', // don't console.warn or throw an error when a translation is missing
        });
        roomLog.message = formatMessage(selectedMessage, params);

        RoomLogRepository.save(roomLog);
    }

    private static async loadParameters(
        parameters: MessageParameters
    ): Promise<MessageParameters> {
        if (
            typeof parameters.character !== 'undefined' &&
            parameters.character !== null
        ) {
            const character = await import(
                '../../locales/' + GameConfig.language + '/character'
            );
            parameters.character = character.default[parameters.character].name;
        }

        return parameters;
    }
}
