import { test, expect } from 'vitest';
import { Planet } from './Planet';
import { PlanetSector } from './PlanetSector';

test('toString should return planet text representation', () => {
    const planet = new Planet();
    planet.name = 'Earth';
    planet.orientation = 'North';
    planet.distance = 2;
    planet.sectors = [
        new PlanetSector().load({ id: 1, name: 'Oxygen', isRevealed: true }),
        new PlanetSector().load({ id: 2, name: '???', isRevealed: false }),
        new PlanetSector().load({ id: 3, name: 'Volcano', isRevealed: true }),
        new PlanetSector().load({ id: 4, name: 'Forest', isRevealed: true }),
        new PlanetSector().load({ id: 6, name: '???', isRevealed: false }),
        new PlanetSector().load({ id: 5, name: 'Volcano', isRevealed: true })
    ];
    planet.actions = [];
    expect(planet.toString()).toBe(':planet: **Earth** (4/6)\n*North - 2 :fuel:*\nOxygen, Volcano (x2), Forest, ??? (x2)');
});

test('toString should return planet text representation', () => {
    const planet = new Planet();
    planet.name = 'Earth';
    planet.orientation = 'North';
    planet.distance = 2;
    planet.sectors = [
        new PlanetSector().load({ id: 1, name: '???', isRevealed: false }),
        new PlanetSector().load({ id: 2, name: '???', isRevealed: false }),
        new PlanetSector().load({ id: 3, name: '???', isRevealed: false }),
        new PlanetSector().load({ id: 4, name: '???', isRevealed: false }),
        new PlanetSector().load({ id: 5, name: '???', isRevealed: false }),
        new PlanetSector().load({ id: 6, name: '???', isRevealed: false }),
        new PlanetSector().load({ id: 7, name: '???', isRevealed: false }),
        new PlanetSector().load({ id: 8, name: '???', isRevealed: false }),
        new PlanetSector().load({ id: 9, name: 'Oxygen', isRevealed: true }),
        new PlanetSector().load({ id: 11, name: '???', isRevealed: false }),
        new PlanetSector().load({ id: 12, name: '???', isRevealed: false }),
        new PlanetSector().load({ id: 13, name: '???', isRevealed: false })
    ];
    planet.actions = [];
    expect(planet.toString()).toBe(':planet: **Earth** (1/12)\n*North - 2 :fuel:*\nOxygen, ??? (x11)');
});
