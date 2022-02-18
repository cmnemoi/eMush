import DaedalusScene from "@/game/scenes/daedalusScene";
import DecorationObject from "@/game/objects/decorationObject";
import { DepthElement } from "@/game/scenes/depthSortingArray";
import IsometricGeom from "@/game/scenes/isometricGeom";
import { IsometricCoordinates } from "@/game/types";

export class SceneGrid {
    private scene : DaedalusScene;
    public polygonArray: Array<GridElement>;

    constructor(
        scene: DaedalusScene,
    )
    {
        this.scene = scene;
        this.polygonArray = [];
    }

    addSceneGeom(sceneGeom: Array<IsometricGeom>): void
    {
        sceneGeom.forEach((geom: IsometricGeom) => {this.polygonArray.push(new GridElement(geom));});
    }

    addObject(phaserObject: DecorationObject): void
    {
        //for each game object, the grid is divided into 4 other polygons
        //         _ x
        //        |     ---*************          O the object added
        //        y     ---*************
        //              ---OOO##########         - * polygons that are behind the object (further referenced as A and B)
        //              ---OOO##########         + # polygons that are in front of the object (further referenced as C and D)
        //              ---+++++++++++++
        //              ---+++++++++++++
        //
        // polygonArray is sorted in the order of depth (first element are the one on the back

        const objectGeom = phaserObject.isoGeom;

        // first get the polygon(s) to cut (max 4 polygons)
        const addedPolygonsIndexes: Array<number> = [];

        const polygonsToCutIndex: Array<number> = [];

        for (let i = 0; i < this.polygonArray.length; i++) {
            if (this.polygonArray[i].geom.isGeomTouchingGeom(objectGeom)) {
                polygonsToCutIndex.push(i);
            }
        }

        for (let i = 0; i < polygonsToCutIndex.length; i++) {
            const cuttedPolygonIndex = polygonsToCutIndex[i];

            if (!addedPolygonsIndexes.includes(cuttedPolygonIndex)) {
                const newAddedPolygonsIndexes = this.cutOnePolygon(phaserObject, cuttedPolygonIndex);


                // new splice indexes may have shifted the position of the old ones
                for (let j = 0; j < addedPolygonsIndexes.length; j++) {
                    if (addedPolygonsIndexes[j] > newAddedPolygonsIndexes[0]) {
                        addedPolygonsIndexes[j] = addedPolygonsIndexes[j] + newAddedPolygonsIndexes.length -1;
                    }
                }

                for (let j = 0; j < polygonsToCutIndex.length; j++) {
                    if (polygonsToCutIndex[j] > newAddedPolygonsIndexes[0]) {
                        polygonsToCutIndex[j] = polygonsToCutIndex[j] + newAddedPolygonsIndexes.length -1;
                    }
                }

                newAddedPolygonsIndexes.forEach((index: number) => (addedPolygonsIndexes.push(index)));
            }
        }

        // we may have added the object geometry twice
        // If several polygons have been cut, some new polygons may be grouped
        this.simplifyGrid(addedPolygonsIndexes);
    }

    updateDepth():void
    {
        for (let i = 0; i < this.polygonArray.length; i++) {
            const object = this.polygonArray[i].object;
            if (object !== undefined) {
                object.setDepth((i + 1)*10000);
            }
        }
    }

    getDepthOfPoint(isoCoords: IsometricCoordinates): number
    {
        const index = this.getPolygonFromPoint(isoCoords);
        if (index !== -1) {
            return (index + 1) * 10000;
        }
        return 0;
    }


    simplifyGrid(indexes?: Array<number>): void
    {
        let length = this.polygonArray.length;
        if (indexes !== undefined) {
            length = indexes.length;
        }

        for (let i = 0; i < length; i++) {
            let globalIndexi = i;
            if (indexes !== undefined) {
                globalIndexi = indexes[i];
            }

            const currentElement = this.polygonArray[globalIndexi];

            for (let j = i + 1; j < length; j++) {
                let globalIndexj = j;
                if (indexes !== undefined) {
                    globalIndexj = indexes[j];
                }

                const comparedElement = this.polygonArray[globalIndexj];

                // first lets check for duplicates
                if (
                    currentElement.geom.getIsoCoords().y === comparedElement.geom.getIsoCoords().y &&
                    currentElement.geom.getIsoSize().y === comparedElement.geom.getIsoSize().y &&
                    currentElement.geom.getIsoCoords().x === comparedElement.geom.getIsoCoords().x &&
                    currentElement.geom.getIsoSize().x === comparedElement.geom.getIsoSize().x
                ) {
                    // only keep the one with the biggest index
                    this.polygonArray.splice(Math.min(globalIndexj, globalIndexi), 1);

                    //we need to update the indexes that may have been changed
                    if (indexes !== undefined) {
                        for (let k = 0; k < length; k++) {
                            if (indexes[k] > Math.min(globalIndexj, globalIndexi)) {
                                indexes[k] = indexes[k] - 1;
                            }
                        }
                    }

                    break;

                    // now lets check if the shape can be simplified
                } else if (
                    currentElement.geom.getIsoSize().x === comparedElement.geom.getIsoSize().x &&
                    currentElement.geom.getIsoCoords().x === comparedElement.geom.getIsoCoords().x &&
                    currentElement.object === undefined && comparedElement.object === undefined
                ) {
                    //case where the two polygons are side by side
                    if (currentElement.geom.getMaxIso().y === comparedElement.geom.getMinIso().y ||
                        currentElement.geom.getMinIso().y === comparedElement.geom.getMaxIso().y) {
                        const newYSize = currentElement.geom.getIsoSize().y + comparedElement.geom.getIsoSize().y;
                        const newYCoord = Math.min(currentElement.geom.getMinIso().y, comparedElement.geom.getMinIso().y) + newYSize/2;
                        const newPolygon = new IsometricGeom(
                            new IsometricCoordinates(
                                currentElement.geom.getIsoCoords().x,
                                newYCoord
                            ),
                            new IsometricCoordinates(
                                currentElement.geom.getIsoSize().x,
                                newYSize
                            )
                        );
                        // the new polygon replace the on with biggest index
                        this.polygonArray[Math.max(globalIndexj, globalIndexi)] = new GridElement(newPolygon);
                        this.polygonArray.splice(Math.min(globalIndexj, globalIndexi), 1);
                        //we need to update the indexes that may have been changed
                        if (indexes !== undefined) {
                            for (let k = 0; k < length; k++) {
                                if (indexes[k] > Math.min(globalIndexj, globalIndexi)) {
                                    indexes[k] = indexes[k] - 1;
                                }
                            }
                        }
                        break;

                    } else if (currentElement.geom.isPointInGeom(comparedElement.geom.getIsoCoords()) ||
                        comparedElement.geom.isPointInGeom(currentElement.geom.getIsoCoords())
                    ) {
                        //Only keep the biggest polygon
                        if (currentElement.geom.getIsoSize().y > comparedElement.geom.getIsoSize().y) {
                            this.polygonArray.splice(globalIndexj, 1);

                            //we need to update the indexes that may have been changed
                            if (indexes !== undefined) {
                                for (let k = 0; k < length; k++) {
                                    if (indexes[k] > globalIndexj) {
                                        indexes[k] = indexes[k] - 1;
                                    }
                                }
                            }

                        } else {
                            this.polygonArray.splice(globalIndexi, 1);

                            //we need to update the indexes that may have been changed
                            if (indexes !== undefined) {
                                for (let k = 0; k < length; k++) {
                                    if (indexes[k] > globalIndexi) {
                                        indexes[k] = indexes[k] - 1;
                                    }
                                }
                            }
                        }
                        break;
                    }

                } else if (
                    currentElement.geom.getIsoSize().y === comparedElement.geom.getIsoSize().y &&
                    currentElement.geom.getIsoCoords().y === comparedElement.geom.getIsoCoords().y &&
                    currentElement.object === undefined && comparedElement.object === undefined
                ) {
                    //case where the two polygons are side by side
                    if (currentElement.geom.getMaxIso().x === comparedElement.geom.getMinIso().x ||
                        currentElement.geom.getMinIso().x === comparedElement.geom.getMaxIso().x
                    ) {
                        const newXSize = currentElement.geom.getIsoSize().x + comparedElement.geom.getIsoSize().x;
                        const newXCoord = Math.min(currentElement.geom.getMinIso().x, comparedElement.geom.getMinIso().x) + newXSize/2;

                        const newPolygon = new IsometricGeom(
                            new IsometricCoordinates(
                                newXCoord,
                                currentElement.geom.getIsoCoords().y
                            ),
                            new IsometricCoordinates(
                                newXSize,
                                currentElement.geom.getIsoSize().y
                            )
                        );

                        // the new polygon replace the on with biggest index
                        this.polygonArray[Math.max(globalIndexj, globalIndexi)] = new GridElement(newPolygon);
                        this.polygonArray.splice(Math.min(globalIndexj, globalIndexi), 1);

                        //we need to update the indexes that may have been changed
                        if (indexes !== undefined) {
                            for (let k = 0; k < length; k++) {
                                if (indexes[k] > Math.min(globalIndexj, globalIndexi)) {
                                    indexes[k] = indexes[k] - 1;
                                }
                            }
                        }
                        break;

                    } else if (currentElement.geom.isPointInGeom(comparedElement.geom.getIsoCoords()) ||
                        comparedElement.geom.isPointInGeom(currentElement.geom.getIsoCoords())
                    ) {
                        //Only keep the biggest polygon
                        if (currentElement.geom.getIsoSize().x > comparedElement.geom.getIsoSize().x) {
                            this.polygonArray.splice(globalIndexj, 1);
                            //we need to update the indexes that may have been changed
                            if (indexes !== undefined) {
                                for (let k = 0; k < length; k++) {
                                    if (indexes[k] > globalIndexj) {
                                        indexes[k] = indexes[k] - 1;
                                    }
                                }
                            }

                        } else {
                            this.polygonArray.splice(globalIndexi, 1);
                            //we need to update the indexes that may have been changed
                            if (indexes !== undefined) {
                                for (let k = 0; k < length; k++) {
                                    if (indexes[k] > globalIndexi) {
                                        indexes[k] = indexes[k] - 1;
                                    }
                                }
                            }
                        }

                        break;
                    }
                }
            }
        }
    }


    // add A, B, object, D, C polygons in polygonArray (order is important)
    // return the index where the polygons have been added
    cutOnePolygon(phaserObject: DecorationObject, index: number): Array<number>
    {
        const cuttedGeom = this.polygonArray[index].geom;
        const objectGeom = phaserObject.isoGeom;
        const polygonsIndexes: Array<number> = [];


        this.polygonArray.splice(index, 1);

        let spliceIndex = index;
        //Polygon A (only if not of the edge of the cut polygon)
        if (objectGeom.getMinIso().x > cuttedGeom.getMinIso().x) {
            const isoSizeA = new IsometricCoordinates(objectGeom.getMinIso().x - cuttedGeom.getMinIso().x, cuttedGeom.getIsoSize().y);
            const isoCoordsA = new IsometricCoordinates((objectGeom.getMinIso().x + cuttedGeom.getMinIso().x)/2, cuttedGeom.getIsoCoords().y);
            this.polygonArray.splice(spliceIndex, 0, new GridElement(new IsometricGeom(isoCoordsA, isoSizeA)));
            polygonsIndexes.push(spliceIndex);
            spliceIndex = spliceIndex + 1;
        }

        //Polygon B (only if not of the edge of the cut polygon)
        if (objectGeom.getMinIso().y > cuttedGeom.getMinIso().y) {
            const minX = Math.max(objectGeom.getMinIso().x, cuttedGeom.getMinIso().x);
            const isoSizeB = new IsometricCoordinates(
                cuttedGeom.getMaxIso().x - minX,
                objectGeom.getMinIso().y - cuttedGeom.getMinIso().y
            );
            const isoCoordsB = new IsometricCoordinates(
                (cuttedGeom.getMaxIso().x + minX)/2,
                (objectGeom.getMinIso().y + cuttedGeom.getMinIso().y)/2
            );
            this.polygonArray.splice(spliceIndex, 0, new GridElement(new IsometricGeom(isoCoordsB, isoSizeB)));
            polygonsIndexes.push(spliceIndex);
            spliceIndex = spliceIndex + 1;
        }

        // add the object geometry
        this.polygonArray.splice(spliceIndex, 0, new GridElement(objectGeom, phaserObject));
        polygonsIndexes.push(spliceIndex);
        spliceIndex = spliceIndex + 1;

        //Polygon D (only if not of the edge of the cut polygon)
        if (objectGeom.getMaxIso().x < cuttedGeom.getMaxIso().x) {
            const isoSizeD = new IsometricCoordinates(cuttedGeom.getMaxIso().x - objectGeom.getMaxIso().x, objectGeom.getIsoSize().y);
            const isoCoordsD = new IsometricCoordinates((cuttedGeom.getMaxIso().x + objectGeom.getMaxIso().x)/2, objectGeom.getIsoCoords().y);
            this.polygonArray.splice(spliceIndex, 0, new GridElement(new IsometricGeom(isoCoordsD, isoSizeD)));
            polygonsIndexes.push(spliceIndex);
            spliceIndex = spliceIndex + 1;
        }

        //Polygon C (only if not of the edge of the cut polygon)
        if (objectGeom.getMaxIso().y < cuttedGeom.getMaxIso().y) {
            const minX = Math.max(objectGeom.getMinIso().x, cuttedGeom.getMinIso().x);
            const isoSizeC = new IsometricCoordinates(
                cuttedGeom.getMaxIso().x - minX,
                cuttedGeom.getMaxIso().y - objectGeom.getMaxIso().y
            );
            const isoCoordsC = new IsometricCoordinates(
                (cuttedGeom.getMaxIso().x + minX)/2,
                (cuttedGeom.getMaxIso().y + objectGeom.getMaxIso().y)/2
            );
            this.polygonArray.splice(spliceIndex, 0, new GridElement(new IsometricGeom(isoCoordsC, isoSizeC)));
            polygonsIndexes.push(spliceIndex);
        }

        return polygonsIndexes;
    }

    getPolygonFromPoint(isoCoords: IsometricCoordinates): number
    {
        for (let i = 0; i < this.polygonArray.length; i++) {
            const gridElement = this.polygonArray[i];
            if (gridElement.geom.isPointInGeom(isoCoords)) {
                return i;
            }
        }

        return -1;
    }
}

export class GridElement {
    public geom: IsometricGeom;
    public object?: DecorationObject;

    constructor(geom: IsometricGeom, object?: DecorationObject) {
        this.geom = geom;
        this.object = object;
    }
}