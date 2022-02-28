import { CartesianCoordinates, IsometricCoordinates } from "@/game/types";
import IsometricGeom from "@/game/scenes/isometricGeom";


export class NavMeshGrid
{
    public geomArray: Array<IsometricGeom>;
    private cumuProbaNavigablePolygons: Array<number>;


    constructor() {
        this.geomArray = [];
        this.cumuProbaNavigablePolygons = [0];
    }

    addPolygon(minX: number, maxX: number, minY: number, maxY: number): Array<IsometricGeom>
    {
        this.geomArray.push(new IsometricGeom(
            new IsometricCoordinates((minX + maxX)/2, (minY +maxY)/2),
            new IsometricCoordinates((maxX - minX), (maxY - minY)),
        ));

        this.cumuProbaNavigablePolygons.push((maxX - minX) * (maxY - minY));


        return this.geomArray;
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

    exportForNavMesh(): Array<Array<{x: number, y: number}>>
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
        return navMeshPolygons;
    }

    cutPathWithGrid(path: Phaser.Geom.Point[]): Phaser.Geom.Point[]
    {
        let currentPoint = path[0];

        let currentGeomIndex = this.getGeomFromPoint(currentPoint);
        //check if next point is out of the current polygon
        if (currentGeomIndex === -1) {
            throw new Error('point should be in grid');
        }
        let currentGeom = this.geomArray[currentGeomIndex];


        for (let i = 1; i < path.length; i++) {
            const nextPoint = path[i];

            //the intermediate points are in both polygons (on the edge of each polygon)
            // we need to assign a new polygon different from the current of if the next point is not in current polygon
            if (currentGeom.isPointInGeom(new IsometricCoordinates(nextPoint.x, nextPoint.y))) {
                currentPoint = nextPoint;
            } else {
                const borderPoint = new Phaser.Geom.Point(0, 0);
                let escapeSide = 0;

                if (currentPoint.x === nextPoint.x) {
                    borderPoint.x = currentPoint.x;
                    if (currentPoint.y <= nextPoint.y) {
                        escapeSide = 3;
                        borderPoint.y = currentGeom.getMaxIso().y;
                    } else {
                        escapeSide = 4;
                        borderPoint.y = currentGeom.getMinIso().y;
                    }
                } else {
                    const slope = (currentPoint.y - nextPoint.y) / (currentPoint.x - nextPoint.x);
                    const intersect = currentPoint.y - (slope * currentPoint.x);


                    //find the new point that is on the border of the current polygon
                    //escape side 1 = right, 2 = left, 3 = top, 4 = bottom
                    if (nextPoint.x > currentPoint.x) {
                        borderPoint.x = currentGeom.getMaxIso().x;
                        borderPoint.y = slope * borderPoint.x + intersect;
                        escapeSide = 1;
                    } else {
                        borderPoint.x = currentGeom.getMinIso().x;
                        borderPoint.y = slope * borderPoint.x + intersect;
                        escapeSide = 2;
                    }

                    // now check if the escape point is ot the top or bottom
                    if (borderPoint.y > currentGeom.getMaxIso().y) {
                        borderPoint.y = currentGeom.getMaxIso().y;
                        borderPoint.x = (borderPoint.y - intersect) / slope;
                        escapeSide = 3;
                    } else if (borderPoint.y < currentGeom.getMinIso().y) {
                        borderPoint.y = currentGeom.getMinIso().y;
                        borderPoint.x = (borderPoint.y - intersect) / slope;
                        escapeSide = 4;
                    }
                }


                // find the adjacent polygon
                for (let j = 0; j < this.geomArray.length - 1; j++) {
                    const testedGeom = this.geomArray[j];

                    if (j !== currentGeomIndex &&
                        (
                            //if escape on right or left
                            (
                                ((escapeSide === 1 && currentGeom.getMaxIso().x === testedGeom.getMinIso().x) ||
                                (escapeSide === 2 && currentGeom.getMinIso().x === testedGeom.getMaxIso().x)) &&
                                testedGeom.getMinIso().y <= borderPoint.y && testedGeom.getMaxIso().y >= borderPoint.y
                            ) ||
                            // if escape top or bottom
                            (
                                ((escapeSide === 3 && currentGeom.getMaxIso().y === testedGeom.getMinIso().y) ||
                                (escapeSide === 4 && currentGeom.getMinIso().y === testedGeom.getMaxIso().y)) &&
                                testedGeom.getMinIso().x <= borderPoint.x && testedGeom.getMaxIso().x >= borderPoint.x
                            )
                        )
                    ) {
                        currentGeomIndex = j;
                        currentGeom = testedGeom;
                        path.splice(i,0, borderPoint);
                        currentPoint = borderPoint;
                    }
                }
            }
        }

        return path;
    }
}