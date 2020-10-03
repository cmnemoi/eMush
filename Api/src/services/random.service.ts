import {Daedalus} from '../models/daedalus.model';
import DaedalusConfig from '../../config/daedalus.config';
import GameConfig from '../../config/game.config';
import {Room} from '../models/room.model';
import moment, {Moment} from 'moment-timezone';
import eventManager from '../config/event.manager';
import {DaedalusEvent} from '../events/daedalus.event';
import DaedalusRepository from '../repository/daedalus.repository';
import RoomService from './room.service';

export default class RandomService {
    public static random(nbValuePossible = 100): number {
        return Math.floor(Math.random() * nbValuePossible);
    }
}
