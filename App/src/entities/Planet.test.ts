import { test, expect } from 'vitest';
import { Planet } from './Planet';
import { PlanetSector } from './PlanetSector';

test('toString should return planet text representation', () => {
    const planet = new Planet();
    planet.name = 'Earth';
    planet.orientation = 'North';
    planet.distance = 2;
    planet.sectors = [
        new PlanetSector().load({id: 1, name: 'Oxygen', isRevealed: true}),
        new PlanetSector().load({id: 2, name: '???', isRevealed: false}),
        new PlanetSector().load({id: 3, name: 'Volcano', isRevealed: true}),
        new PlanetSector().load({id: 4, name: 'Forest', isRevealed: true}),
        new PlanetSector().load({id: 6, name: '???', isRevealed: false}),
        new PlanetSector().load({id: 5, name: 'Volcano', isRevealed: true}),
    ];
    planet.actions = [];
    expect(planet.toString()).toBe(':planet: **Earth** (4/6)\n*North - 2 :fuel:*\nOxygen, Volcano (x2), Forest, ??? (x2)');
});