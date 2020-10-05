import {Daedalus} from '../models/daedalus.model';
import DaedalusConfig from '../../config/daedalus.config';
import GameConfig from '../../config/game.config';
import {Room} from '../models/room.model';
import eventManager from '../config/event.manager';
import {DaedalusEvent} from '../events/daedalus.event';
import DaedalusRepository from '../repository/daedalus.repository';
import RoomService from './room.service';
import {DateTime} from 'luxon';
import ItemService from "./item.service";
import RandomService from "./random.service";

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
        daedalus.cycle = DaedalusService.getCycleFromDate(DateTime.utc());
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

        const numberOfRoomPossible = DaedalusConfig.randomItemPlace.places.length;
        for (const randomItemPlace of DaedalusConfig.randomItemPlace.items) {
            const selectedRoomName = DaedalusConfig.randomItemPlace.places[RandomService.random(numberOfRoomPossible)];
            const selectedRoom = daedalus.rooms.find(room => room.name === selectedRoomName)

            if (typeof selectedRoom === "undefined") {
                throw new Error(
                    selectedRoomName +
                    ' does not exist in the daedalus id: ' +
                    daedalus.id
                );

            }

            await ItemService.createItem(randomItemPlace, selectedRoom);
        }

        return DaedalusRepository.save(daedalus);
    }

    public static handleCycleChange(daedalus: Daedalus): boolean {
        const currentDate = DateTime.utc();
        const lastUpdate = DateTime.fromMillis(daedalus.updatedAt.getTime());
        const currentCycle = daedalus.cycle;
        const currentCycleStartedAt = DateTime.fromMillis(
            daedalus.updatedAt.getTime()
        )
            .setZone(GameConfig.timeZone)
            .set({hour: (currentCycle - 1) * GameConfig.cycleLength})
            .set({minute: 0})
            .set({second: 0})
            .set({millisecond: 0})
            .setZone('UTC');
        const cycleElapsed = DaedalusService.getNumberOfCycleElapsed(
            lastUpdate,
            currentDate
        );

        for (let i = 0; i < cycleElapsed; i++) {
            eventManager.emit(
                DaedalusEvent.DAEDALUS_NEW_CYCLE,
                daedalus,
                currentCycleStartedAt
                    .plus({hours: (i + 1) * GameConfig.cycleLength})
                    .toJSDate()
            );
        }

        return cycleElapsed !== 0;
    }

    private static getCycleFromDate(date: DateTime): number {
        return (
            Math.floor(
                date.setZone(GameConfig.timeZone).hour / GameConfig.cycleLength
            ) + 1
        );
    }

    private static getNumberOfCycleElapsed(
        start: DateTime,
        end: DateTime
    ): number {
        const startCycle = DaedalusService.getCycleFromDate(start);
        const endCycle = DaedalusService.getCycleFromDate(end);
        end.setZone(GameConfig.timeZone);
        start.setZone(GameConfig.timeZone);

        let dayDifference = 0;
        // We assume the inactivity is not more than a month
        if (end.month !== start.month) {
            dayDifference = start.daysInMonth - start.day + end.day;
        } else {
            dayDifference = end.day - start.day;
        }

        const numberCyclePerDay = 24 / GameConfig.cycleLength;

        return endCycle + dayDifference * numberCyclePerDay - startCycle;
    }
}
