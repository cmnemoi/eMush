import {Daedalus} from '../models/daedalus.model';
import DaedalusConfig from '../../config/daedalus.config';
import GameConfig from '../../config/game.config';
import {Room} from '../models/room.model';
import moment, {Moment} from 'moment-timezone';
import eventManager from '../config/event.manager';
import {DaedalusEvent} from '../events/daedalus.event';
import DaedalusRepository from '../repository/daedalus.repository';
import RoomRepository from '../repository/room.repository';
import RoomService from './room.service';

export default class DaedalusService {
    public static findAll(): Promise<Daedalus[]> {
        return DaedalusRepository.findAll();
    }

    public static find(id: number): Promise<Daedalus | null> {
        return DaedalusRepository.find(id);
    }

    public static save(daedalus: Daedalus): Promise<Daedalus> {
        return DaedalusRepository.save(daedalus);
    }

    public static async initDaedalus(): Promise<Daedalus> {
        const daedalus = new Daedalus();
        daedalus.cycle = DaedalusService.getCycleFromDate(moment());
        daedalus.day = 1;
        daedalus.oxygen = DaedalusConfig.initOxygen;
        daedalus.fuel = DaedalusConfig.initFuel;
        daedalus.hull = DaedalusConfig.initHull;
        daedalus.shield = DaedalusConfig.initShield;

        const rooms: Room[] = [];

        await DaedalusRepository.save(daedalus);

        for (const roomConfig of DaedalusConfig.rooms) {
            const room = await RoomService.initRoom(roomConfig, daedalus);
            rooms.push(room);
        }

        daedalus.rooms = rooms;

        return DaedalusRepository.save(daedalus);
    }

    public static handleCycleChange(daedalus: Daedalus): boolean {
        const currentDate = moment();
        const lastUpdate = moment(daedalus.updatedAt.toUTCString());

        const cycleElapsed = DaedalusService.getNumberOfCycleElapsed(
            lastUpdate,
            currentDate
        );

        for (let i = 0; i < cycleElapsed; i++) {
            eventManager.emit(DaedalusEvent.DAEDALUS_NEW_CYCLE, daedalus);
        }

        return cycleElapsed !== 0;
    }

    private static getCycleFromDate(date: Moment): number {
        return (
            Math.floor(
                date.tz(GameConfig.timeZone).hours() / GameConfig.cycleLength
            ) + 1
        );
    }

    private static getNumberOfCycleElapsed(start: Moment, end: Moment): number {
        const startCycle = DaedalusService.getCycleFromDate(start);
        const endCycle = DaedalusService.getCycleFromDate(end);

        let lastYearNumberOfDay = 0;
        // If not the same year, add the numbers of days in the previous year
        if (!end.isSame(start, 'year')) {
            lastYearNumberOfDay = start.isLeapYear() ? 366 : 365;
        }

        const dayDifference =
            end.tz(GameConfig.timeZone).dayOfYear() +
            lastYearNumberOfDay -
            start.tz(GameConfig.timeZone).dayOfYear();
        const numberCyclePerDay = 24 / GameConfig.cycleLength;

        return endCycle + dayDifference * numberCyclePerDay - startCycle;
    }
}
