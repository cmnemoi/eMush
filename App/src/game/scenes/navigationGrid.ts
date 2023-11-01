import { CartesianCoordinates, IsometricCoordinates } from "@/game/types";
import IsometricGeom from "@/game/scenes/isometricGeom";
import DaedalusScene from "@/game/scenes/daedalusScene";
//@ts-ignore
import { PhaserNavMeshPlugin, PhaserNavMesh } from "phaser-navmesh";

export class NavMeshGrid
{
    public geomArray: Array<IsometricGeom>;
    public depthArray: Array<number>;
    public phaserNavMesh : PhaserNavMesh;

    private navMeshPlugin!: PhaserNavMeshPlugin;

    private scene : DaedalusScene;
    private cumuProbaNavigablePolygons: Array<number>;


    constructor(scene: DaedalusScene) {
        this.geomArray = [];
        this.depthArray = [];
        this.cumuProbaNavigablePolygons = [0];

        this.scene = scene;

        this.phaserNavMesh = new PhaserNavMesh(this.navMeshPlugin, this.scene, 'pathfinding', []);
    }

    addPolygon(minX: number, maxX: number, minY: number, maxY: number, depth: number): Array<IsometricGeom>
    {
        this.geomArray.push(new IsometricGeom(
            new IsometricCoordinates((minX + maxX)/2, (minY +maxY)/2),
            new IsometricCoordinates((maxX - minX), (maxY - minY))
        ));

        this.depthArray.push(depth);

        const lastProba = this.cumuProbaNavigablePolygons[this.cumuProbaNavigablePolygons.length -1];
        this.cumuProbaNavigablePolygons.push((maxX - minX) * (maxY - minY) + lastProba);


        return this.geomArray;
    }

    getCharacterPath(startPoint: IsometricCoordinates, finishPoint: IsometricCoordinates): MushPath
    {
        if (!this.phaserNavMesh.isPointInMesh(startPoint)) {
            startPoint = this.getClosestPoint(startPoint);
        }

        const path = this.phaserNavMesh.findPath({ x: startPoint.x, y: startPoint.y }, { x: finishPoint.x, y: finishPoint.y });

        if (path !== null) {
            return this.convertNavMeshPathToMushPath(path);
        }

        return [];
    }

    getClosestPoint(point: IsometricCoordinates): IsometricCoordinates
    {
        if (this.phaserNavMesh.isPointInMesh(point)){
            return point;
        }

        let closestPoint = new IsometricCoordinates(this.geomArray[0].getMaxIso().x,this.geomArray[0].getMaxIso().y);

        for (let i = 0; i < this.geomArray.length; i++) {
            const isoGeom = this.geomArray[i];

            let iClosestPoint = new IsometricCoordinates(0,0);

            //closest point depends on where the point is
            if (point.x >= isoGeom.getMaxIso().x && point.y >= isoGeom.getMaxIso().y ) { //top right corner
                iClosestPoint = new IsometricCoordinates(isoGeom.getMaxIso().x, isoGeom.getMaxIso().y);
            } else if (point.x >= isoGeom.getMaxIso().x && point.y <= isoGeom.getMinIso().y ) { //bottom right corner
                iClosestPoint = new IsometricCoordinates(isoGeom.getMaxIso().x, isoGeom.getMinIso().y);
            } else if (point.x <= isoGeom.getMinIso().x && point.y >= isoGeom.getMaxIso().y ) { //top left corner
                iClosestPoint = new IsometricCoordinates(isoGeom.getMinIso().x, isoGeom.getMaxIso().y);
            } else if (point.x <= isoGeom.getMinIso().x && point.y <= isoGeom.getMinIso().y ) { //bottom left corner
                iClosestPoint = new IsometricCoordinates(isoGeom.getMinIso().x, isoGeom.getMinIso().y);
            } else if (point.x > isoGeom.getMaxIso().x) { //right
                iClosestPoint = new IsometricCoordinates(isoGeom.getMaxIso().x, point.y);
            } else if (point.x < isoGeom.getMinIso().x) { //left
                iClosestPoint = new IsometricCoordinates(isoGeom.getMinIso().x, point.y);
            } else if (point.y > isoGeom.getMaxIso().y) { //top
                iClosestPoint = new IsometricCoordinates(point.x, isoGeom.getMaxIso().y);
            } else { //bottom
                iClosestPoint = new IsometricCoordinates(point.x, isoGeom.getMinIso().y);
            }

            if (iClosestPoint.getDistance(point) < closestPoint.getDistance(point)) {
                closestPoint = iClosestPoint;
            }
        }

        return closestPoint;
    }

    getGeomFromPoint(isoPoint: Phaser.Geom.Point): number
    {
        for (let i = 0; i < this.geomArray.length; i++) {
            const isoGeom = this.geomArray[i];
            if (isoGeom.isPointInGeom(new IsometricCoordinates(isoPoint.x, isoPoint.y))) {
                return i;
            }
        }

        return -1;
    }


    getRandomPoint(): CartesianCoordinates
    {
        const cumuProba = this.cumuProbaNavigablePolygons;

        const maxProba = cumuProba[cumuProba.length-1];

        const randomIndex = Math.random()*maxProba;

        for (let i = 0; i <  cumuProba.length; i++) {
            if (cumuProba[i+1] > randomIndex) {
                const randomPoint = this.geomArray[i].getRandomPoint(new Phaser.Geom.Point);
                return new CartesianCoordinates(randomPoint.x, randomPoint.y);
            }
        }

        const randomPoint = this.geomArray[cumuProba.length - 1].getRandomPoint(new Phaser.Geom.Point(0, 0));
        return new CartesianCoordinates(randomPoint.x, randomPoint.y);
    }

    buildPhaserNavMesh(): PhaserNavMesh
    {
        const navMeshPolygons: Array<Array<{x: number, y: number}>> = [];

        for(let i = 0; i < this.geomArray.length; i++)
        {
            const currentGeom = this.geomArray[i];
            navMeshPolygons.push(
                [
                    { x: currentGeom.getMaxIso().x, y: currentGeom.getMaxIso().y },
                    { x: currentGeom.getMaxIso().x, y: currentGeom.getMinIso().y },
                    { x: currentGeom.getMinIso().x, y: currentGeom.getMinIso().y },
                    { x: currentGeom.getMinIso().x, y: currentGeom.getMaxIso().y }
                ]
            );
        }

        this.phaserNavMesh = new PhaserNavMesh(this.navMeshPlugin, this.scene, 'pathfinding', navMeshPolygons);

        return this.phaserNavMesh;
    }

    cutPathWithGrid(path: Phaser.Geom.Point[]): { point: IsometricCoordinates, depth: number }[]
    {
        let currentPoint = new IsometricCoordinates(path[0].x, path[0].y);
        let currentGeomIndex = this.getGeomFromPoint(currentPoint);

        //check if next point is out of the current polygon
        if (currentGeomIndex === -1) {
            throw new Error('point should be in grid');
        }
        let currentGeom = this.geomArray[currentGeomIndex];
        const crossedPolygons = [currentGeomIndex];

        let cutPath = [{ point: currentPoint, depth: this.depthArray[currentGeomIndex] }];


        for (let i = 1; i < path.length; i++) {
            const nextPoint = new IsometricCoordinates( path[i].x, path[i].y );
            currentPoint = cutPath[cutPath.length - 1].point;

            //the intermediate points are in both polygons (on the edge of each polygon)
            // we need to assign a new polygon different from the current of if the next point is not in current polygon
            if (
                currentPoint.x !== nextPoint.x ||
                currentPoint.y !== nextPoint.y
            ) {
                const escapeSide = this.getEscapeSide(currentPoint, nextPoint, currentGeom);

                let nextX = nextPoint.x;
                let nextY = nextPoint.y;

                switch (escapeSide) {
                case 1:
                    nextX = currentGeom.getMaxIso().x;
                    nextY = this.getPointOnSide(nextY, currentGeom.getMaxIso().y, currentGeom.getMinIso().y);
                    break;
                case 2:
                    nextX = currentGeom.getMinIso().x;
                    nextY = this.getPointOnSide(nextY, currentGeom.getMaxIso().y, currentGeom.getMinIso().y);
                    break;
                case 3:
                    nextY = currentGeom.getMaxIso().y;
                    nextX = this.getPointOnSide(nextX, currentGeom.getMaxIso().x, currentGeom.getMinIso().x);
                    break;
                case 4:
                    nextY = currentGeom.getMinIso().y;
                    nextX = this.getPointOnSide(nextX, currentGeom.getMaxIso().x, currentGeom.getMinIso().x);
                    break;
                }

                cutPath = this.randomizeIntermediatePoint(cutPath, new IsometricCoordinates(nextX, nextY), this.depthArray[currentGeomIndex]);


                if (escapeSide !==0) {
                    const lastPoint = cutPath[cutPath.length - 1].point;


                    // find the adjacent polygon
                    for (let j = 0; j < this.geomArray.length ; j++) {
                        const testedGeom = this.geomArray[j];

                        if (!crossedPolygons.includes(j) &&
                            (
                                //if escape on right or left
                                (
                                    (([1, 5, 8].includes(escapeSide) && currentGeom.getMaxIso().x === testedGeom.getMinIso().x) ||
                                        ([2, 6, 7].includes(escapeSide) && currentGeom.getMinIso().x === testedGeom.getMaxIso().x)) &&
                                    testedGeom.getMinIso().y <= lastPoint.y && testedGeom.getMaxIso().y >= lastPoint.y
                                ) ||
                                // if escape top or bottom
                                (
                                    (([3, 5, 6].includes(escapeSide) && currentGeom.getMaxIso().y === testedGeom.getMinIso().y) ||
                                        ([4, 7, 8].includes(escapeSide) && currentGeom.getMinIso().y === testedGeom.getMaxIso().y)) &&
                                    testedGeom.getMinIso().x <= lastPoint.x && testedGeom.getMaxIso().x >= lastPoint.x
                                )
                            )
                        ) {
                            currentGeomIndex = j;
                            currentGeom = this.geomArray[currentGeomIndex];
                            crossedPolygons.push(j);
                            i = i-1;
                            break;
                        }
                    }
                }
            }
        }

        return cutPath;
    }

    getPointOnSide(next: number, currentGeomMax: number, currentGeomMin: number): number
    {
        if (next>currentGeomMax) { return currentGeomMax; }
        if (next<currentGeomMin) { return currentGeomMin; }
        return next;
    }

    // 1 is right (E), 2 is left (W), 3 is top (S), 4 is bottom (N)
    //corners topRight = 5, topLeft = 6, bottomLeft = 7, bottomRight = 8
    getEscapeSide(currentPoint: IsometricCoordinates, nextPoint: IsometricCoordinates, currentGeom: IsometricGeom): number
    {
        const slopeX = (currentPoint.y - nextPoint.y) / (currentPoint.x - nextPoint.x);
        const slopeY = (currentPoint.x - nextPoint.x) / (currentPoint.y - nextPoint.y);

        // handle cases where the point is on the corner of the currentGeom
        if (nextPoint.x === currentGeom.getMaxIso().x &&
            nextPoint.y === currentGeom.getMaxIso().y       //top right
        ) {
            return 5;
        } else if (nextPoint.x === currentGeom.getMinIso().x &&
            nextPoint.y === currentGeom.getMaxIso().y       //top left
        ) {
            return 6;
        } else if (nextPoint.x === currentGeom.getMinIso().x &&
            nextPoint.y === currentGeom.getMinIso().y       //bottom left
        ) {
            return 7;
        } else if (nextPoint.x === currentGeom.getMaxIso().x &&
            nextPoint.y === currentGeom.getMinIso().y       //bottom right
        ) {
            return 8;

        } else if ( // right
            nextPoint.x >= currentGeom.getMaxIso().x &&
            currentGeom.getMaxIso().y >= currentPoint.y + (currentGeom.getMaxIso().x - currentPoint.x) * slopeX &&
            currentGeom.getMinIso().y <= currentPoint.y + (currentGeom.getMaxIso().x - currentPoint.x) * slopeX
        ) {
            return 1;
        } else if ( //left
            nextPoint.x <= currentGeom.getMinIso().x &&
            currentGeom.getMaxIso().y >= currentPoint.y + (currentGeom.getMinIso().x - currentPoint.x) * slopeX &&
            currentGeom.getMinIso().y <= currentPoint.y + (currentGeom.getMinIso().x - currentPoint.x) * slopeX
        ) {
            return 2;
        } else if ( //top
            nextPoint.y >= currentGeom.getMaxIso().y &&
            currentGeom.getMaxIso().x >= currentPoint.x + (currentGeom.getMaxIso().y - currentPoint.y) * slopeY &&
            currentGeom.getMinIso().x <= currentPoint.x + (currentGeom.getMaxIso().y - currentPoint.y) * slopeY
        ) {
            return 3;
        } else if ( //bottom
            nextPoint.y <= currentGeom.getMinIso().y &&
            currentGeom.getMaxIso().x >= currentPoint.x + (currentGeom.getMinIso().y - currentPoint.y) * slopeY &&
            currentGeom.getMinIso().x <= currentPoint.x + (currentGeom.getMinIso().y - currentPoint.y) * slopeY
        ) {
            return 4;
        }


        return 0;
    }



    randomizeIntermediatePoint(cutPath: { point: IsometricCoordinates, depth: number }[], nextPoint: IsometricCoordinates, depth: number): { point: IsometricCoordinates, depth: number }[]
    {
        const currentPoint = cutPath[cutPath.length -1].point;
        if (Math.random() >0.5) {
            cutPath.push({
                point: new IsometricCoordinates(currentPoint.x, nextPoint.y),
                depth: depth
            });
        } else {
            cutPath.push({
                point: new IsometricCoordinates(nextPoint.x, currentPoint.y),
                depth: depth
            });
        }

        cutPath.push({
            point: nextPoint,
            depth: depth
        });
        return cutPath;
    }

    convertNavMeshPathToMushPath(path: Phaser.Geom.Point[]): MushPath
    {
        const cutPath = this.cutPathWithGrid(path);

        const firstPoint = (new IsometricCoordinates(cutPath[0].point.x, cutPath[0].point.y)).toCartesianCoordinates();
        const mushPath: MushPath = [{ "direction": 'none', "cartX": firstPoint.x, "cartY": firstPoint.y, "depth": cutPath[0].depth }];

        for (let i=1; i < cutPath.length; i++) {
            const cartPoint = (new IsometricCoordinates(cutPath[i].point.x, cutPath[i].point.y)).toCartesianCoordinates();
            const direction = this.getDirection(
                new IsometricCoordinates(cutPath[i-1].point.x, cutPath[i-1].point.y),
                new IsometricCoordinates(cutPath[i].point.x, cutPath[i].point.y)
            );

            if (direction !== undefined) {
                mushPath.push({ "direction": direction, "cartX": cartPoint.x, "cartY": cartPoint.y, "depth": cutPath[i].depth });
            }
        }

        return mushPath;
    }

    //Get direction from two points in isometric format
    // Iso directions    Iso coordinates     Cart coordinates
    //  W   N                                     _x
    //   \ /                  / \                |
    //   / \                 y   x               y
    //  S   E
    getDirection(start: IsometricCoordinates, finish: IsometricCoordinates): string | undefined
    {
        const deltaEW = finish.x - start.x;
        const deltaNS = finish.y - start.y;

        if (deltaNS !== 0 && deltaEW !== 0){
            throw new Error('EW and NS cannot both change at the same time');
        }

        if (deltaNS > 0) {
            return 'south';
        } else if (deltaNS < 0) {
            return 'north';
        } else if (deltaEW > 0) {
            return 'east';
        } else if (deltaEW < 0) {
            return 'west';
        }
    }
}

export type MushPath = Array<{ direction: string, cartX: number, cartY: number, depth: number }>
