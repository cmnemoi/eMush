import {Daedalus} from '../models/daedalus.model';
import {Identifier} from 'sequelize';
import DaedalusConfig from '../config/daedalus.config';
import GameConfig from '../config/game.config';
import {Room} from '../models/room.model';
import {Player} from '../models/player.model';
import moment from "moment";
import {Moment} from "moment-timezone";
import eventManager from "../config/event.manager";
import {DaedalusEvent} from "../events/daedalus.event";

export default class DaedalusService {
    public static findAll(): Promise<Daedalus[]> {
        return Daedalus.findAll<Daedalus>({});
    }

    public static find(id: Identifier): Promise<Daedalus | null> {
        return Daedalus.findByPk<Daedalus>(id, {
            include: [
                {
                    model: Room,
                    as: 'rooms',
                },
                {
                    model: Player,
                    as: 'players',
                },
            ],
        });
    }

    public static save(daedalus: Daedalus): Promise<Daedalus> {
        return daedalus.save();
    }

    public static async initDaedalus(): Promise<Daedalus> {
        const daedalus = Daedalus.build(
            {},
            {
                include: [{model: Room, as: 'rooms'}],
            }
        );
        daedalus.cycle = DaedalusService.getCycleFromDate(moment());
        daedalus.day = 1;
        daedalus.oxygen = DaedalusConfig.initOxygen;
        daedalus.fuel = DaedalusConfig.initFuel;
        daedalus.hull = DaedalusConfig.initHull;
        daedalus.shield = DaedalusConfig.initShield;

        const rooms: Room[] = [];

        await Promise.all(
            DaedalusConfig.rooms.map(async roomConfig => {
                const room = Room.build();
                room.name = roomConfig.name;
                rooms.push(room);
            })
        );

        daedalus.rooms = rooms;

        return daedalus.save();
    }

    public static handleCycleChange(daedalus: Daedalus): boolean {
        const currentDate = moment();
        const lastUpdate = moment(daedalus.updatedAt.toUTCString());

        const cycleElapsed = DaedalusService.getNumberOfCycleElapsed(lastUpdate, currentDate);
        console.log(daedalus.players)
        for (let i = 0; i < cycleElapsed; i++) {
            eventManager.emit(DaedalusEvent.DAEDALUS_NEW_CYCLE, daedalus);
        }

        return cycleElapsed !== 0;
    }

    private static getCycleFromDate(date: Moment): number {
        return Math.floor(date.tz(GameConfig.timeZone).hours() / GameConfig.cycleLength) + 1;
    }

    private static getNumberOfCycleElapsed(start: Moment, end: Moment): number {
        const startCycle = DaedalusService.getCycleFromDate(start);
        const endCycle = DaedalusService.getCycleFromDate(end);
        console.log(end.format());
        console.log(endCycle);

        let lastYearNumberOfDay = 0;
        // If not the same year, add the numbers of days in the previous year
        if (!end.isSame(start, 'year')) {
            lastYearNumberOfDay = start.isLeapYear() ? 366 : 365;
        }

        const dayDifference = end.tz(GameConfig.timeZone).dayOfYear() + lastYearNumberOfDay - start.tz(GameConfig.timeZone).dayOfYear();
        const numberCyclePerDay = 24 / GameConfig.cycleLength;

        return endCycle + (dayDifference * numberCyclePerDay) - startCycle

    }
}
