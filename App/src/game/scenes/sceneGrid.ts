import DaedalusScene from "@/game/scenes/daedalusScene";
import DecorationObject from "@/game/objects/decorationObject";
import IsometricGeom from "@/game/scenes/isometricGeom";
import { IsometricCoordinates } from "@/game/types";
import Tile = Phaser.Tilemaps.Tile;
import { NavMeshGrid } from "@/game/scenes/navigationGrid";

export class SceneGrid {
    public depthSortingArray: Array<GridElement>;

    private scene : DaedalusScene;
    private readonly shrinkAmount: number;

    constructor(
        scene: DaedalusScene,
        shrinkAmount: number
    )
    {
        this.scene = scene;

        this.shrinkAmount = shrinkAmount;
        this.depthSortingArray = [];
    }

    addSceneGeom(size: IsometricCoordinates, groundTilesThickness: number, ): void
    {
        const sceneGeom = new IsometricGeom(
            new IsometricCoordinates(size.x/2 + groundTilesThickness, size.y/2 + groundTilesThickness),
            size
        );
        this.depthSortingArray.push(new GridElement(sceneGeom, false));
    }

    addGroundGeom(groundLayer: Phaser.Tilemaps.LayerData, groundTilesThickness: number): void
    {
        let maxX = 0;
        let maxY = 0;
        let minX = groundLayer.width;
        let minY = groundLayer.height;

        const isoTileSize = groundLayer.tileHeight;

        for (let i =0; i<groundLayer.width; i++) {
            for (let j = 0; j<groundLayer.height; j++) {
                const currentTile: Tile = groundLayer.data[j][i];
                if (currentTile.index !== -1) {
                    minX = Math.min(minX, i);
                    minY = Math.min(minY, j);
                    maxX = Math.max(maxX, i);
                    maxY = Math.max(maxY, j);
                }
            }
        }

        const groundGeom = new IsometricGeom(
            new IsometricCoordinates((minX + maxX)/2 * isoTileSize + groundTilesThickness, (minY + maxY)/2* isoTileSize + groundTilesThickness),
            new IsometricCoordinates((maxX - minX+1) * isoTileSize, (maxY - minY+1)* isoTileSize)
        );

        this.addGeom(groundGeom, true);
    }

    finalizeGroundMesh() {
        const polygonsToEnlarge: Array<GridElement>  = [];
        for (let i = 0; i < this.depthSortingArray.length; i++) {
            const currentPolygon = this.depthSortingArray[i];
            if (!currentPolygon.isNavigable) {
                polygonsToEnlarge.push(currentPolygon);
                this.depthSortingArray.splice(i, 1);
                i=i-1;
            }
        }

        for (let i = 0; i < polygonsToEnlarge.length; i++) {
            this.addGeom(polygonsToEnlarge[i].geom.enlargeGeom(this.shrinkAmount), false);
        }

        this.simplifyGrid();
    }

    buildNavMeshGrid(): NavMeshGrid
    {
        const navMeshGrid = new NavMeshGrid(this.scene);

        for (let i = 0; i < this.depthSortingArray.length; i++) {
            const currentPolygon =this.depthSortingArray[i];
            if (currentPolygon.isNavigable) {
                navMeshGrid.addPolygon(
                    currentPolygon.geom.getMinIso().x,
                    currentPolygon.geom.getMaxIso().x,
                    currentPolygon.geom.getMinIso().y,
                    currentPolygon.geom.getMaxIso().y,
                    this.getDepthOfPolygon(i),
                );
            }
        }

        navMeshGrid.buildPhaserNavMesh();

        return navMeshGrid;
    }

    addObject(phaserObject: DecorationObject): void
    {
        const addedPolygonsIndexes = this.addGeom(phaserObject.isoGeom.enlargeGeom(this.shrinkAmount), !phaserObject.collides, phaserObject);
        //const addedPolygonsIndexes = this.addGeom(phaserObject.isoGeom, !phaserObject.collides, phaserObject);

        // we may have added the object geometry twice
        // If several polygons have been cut, some new polygons may be grouped
        this.simplifyGrid(addedPolygonsIndexes);
        this.finalizeGrid();
    }

    addGeom(objectGeom: IsometricGeom, isNavigable: boolean, phaserObject?: DecorationObject): Array<number>
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


        // first get the polygon(s) to cut (max 4 polygons)
        const polygonsToCutIndex: Array<number> = [];
        for (let i = 0; i < this.depthSortingArray.length; i++) {
            if (this.depthSortingArray[i].geom.isGeomOverlapingGeom(objectGeom) &&
                this.depthSortingArray[i].object === undefined
            ) {
                polygonsToCutIndex.push(i);
            }
        }

        const addedPolygonsIndexes: Array<number> = [];
        for (let i = 0; i < polygonsToCutIndex.length; i++) {
            const cuttedPolygonIndex = polygonsToCutIndex[i];

            if (!addedPolygonsIndexes.includes(cuttedPolygonIndex)) {
                const newAddedPolygonsIndexes = this.cutOnePolygon(objectGeom, cuttedPolygonIndex, isNavigable, phaserObject);


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

        return addedPolygonsIndexes;
    }

    updateDepth():void
    {
        this.finalizeGrid();

        for (let i = 0; i < this.depthSortingArray.length; i++) {
            const currentElement = this.depthSortingArray[i];
            const object = currentElement.object;

            if (object !== undefined && object.depth === 0) {
                object.setDepth((i + 1)*10000);
            }
        }
    }

    finalizeGrid(): void
    {
        for (let i = 0; i < this.depthSortingArray.length; i++) {
            const currentElement = this.depthSortingArray[i];

            if (currentElement.object === undefined &&
                !currentElement.isNavigable
            ) {
                this.depthSortingArray.splice(i, 1);
                i =i-1;
            }
        }
    }

    getDepthOfPoint(isoCoords: IsometricCoordinates): number
    {
        const index = this.getPolygonFromPoint(isoCoords);

        return this.getDepthOfPolygon(index);
    }

    getDepthOfPolygon(index: number): number
    {
        if (index !== -1) {
            return (index + 1) * 10000;
        }

        return -1;
    }

    simplifyGrid(indexes?: Array<number>): void
    {
        if (indexes === undefined) {
            indexes = Array.from(Array(this.depthSortingArray.length), (v, k) => k);
        }

        const length = indexes.length;

        for (let i = 0; i < length; i++) {
            const globalIndexi = indexes[i];

            const currentElement = this.depthSortingArray[globalIndexi];

            for (let j = i + 1; j < length; j++) {
                let globalIndexj = j;
                if (indexes !== undefined) {
                    globalIndexj = indexes[j];
                }

                const comparedElement = this.depthSortingArray[globalIndexj];

                // first lets check for duplicates
                if (
                    currentElement.geom.getIsoCoords().y === comparedElement.geom.getIsoCoords().y &&
                    currentElement.geom.getIsoSize().y === comparedElement.geom.getIsoSize().y &&
                    currentElement.geom.getIsoCoords().x === comparedElement.geom.getIsoCoords().x &&
                    currentElement.geom.getIsoSize().x === comparedElement.geom.getIsoSize().x
                ) {
                    // only keep the one with the biggest index
                    this.depthSortingArray.splice(globalIndexi, 1);

                    //we need to update the indexes that may have been changed
                    for (let k = 0; k < length; k++) {
                        if (indexes[k] > globalIndexi) {
                            indexes[k] = indexes[k] - 1;
                        }
                    }

                    break;

                    // check if one polygon totally include another one
                } else if (
                    comparedElement.geom.isGeomContainingGeom(currentElement.geom) &&
                    currentElement.object === undefined
                ) {
                    //remove the current geometry
                    this.depthSortingArray.splice(globalIndexi, 1);

                    //we need to update the indexes that may have been changed
                    for (let k = 0; k < length; k++) {
                        if (indexes[k] > globalIndexi) {
                            indexes[k] = indexes[k] - 1;
                        }
                    }

                    break;

                    // now lets check if the shape can be simplified
                } else if (
                    currentElement.geom.getIsoSize().x === comparedElement.geom.getIsoSize().x &&
                    currentElement.geom.getIsoCoords().x === comparedElement.geom.getIsoCoords().x &&
                    currentElement.object === undefined && comparedElement.object === undefined &&
                    currentElement.isNavigable === comparedElement.isNavigable
                ) {
                    //case where the two polygons are side by side
                    if (currentElement.geom.getMaxIso().y === comparedElement.geom.getMinIso().y ||
                        currentElement.geom.getMinIso().y === comparedElement.geom.getMaxIso().y
                    ) {
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
                        this.depthSortingArray[Math.max(globalIndexj, globalIndexi)] = new GridElement(newPolygon, currentElement.isNavigable);
                        this.depthSortingArray.splice(Math.min(globalIndexj, globalIndexi), 1);
                        //we need to update the indexes that may have been changed
                        if (indexes !== undefined) {
                            for (let k = 0; k < length; k++) {
                                if (indexes[k] > Math.min(globalIndexj, globalIndexi)) {
                                    indexes[k] = indexes[k] - 1;
                                }
                            }
                        }
                        break;

                    }
                } else if (
                    currentElement.geom.getIsoSize().y === comparedElement.geom.getIsoSize().y &&
                    currentElement.geom.getIsoCoords().y === comparedElement.geom.getIsoCoords().y &&
                    currentElement.object === undefined && comparedElement.object === undefined &&
                    currentElement.isNavigable === comparedElement.isNavigable
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
                        this.depthSortingArray[Math.max(globalIndexj, globalIndexi)] = new GridElement(newPolygon, currentElement.isNavigable);
                        this.depthSortingArray.splice(Math.min(globalIndexj, globalIndexi), 1);

                        //we need to update the indexes that may have been changed
                        if (indexes !== undefined) {
                            for (let k = 0; k < length; k++) {
                                if (indexes[k] > Math.min(globalIndexj, globalIndexi)) {
                                    indexes[k] = indexes[k] - 1;
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
    cutOnePolygon(
        objectGeom: IsometricGeom,
        index: number,
        isNavigable: boolean,
        phaserObject?: DecorationObject
    ): Array<number>
    {
        const cuttedPolygon = this.depthSortingArray[index];
        const cuttedGeom = cuttedPolygon.geom;
        const polygonsIndexes: Array<number> = [];

        this.depthSortingArray.splice(index, 1);

        let spliceIndex = index;
        //Polygon A (only if not of the edge of the cut polygon)
        if (objectGeom.getMinIso().x > cuttedGeom.getMinIso().x) {
            const isoSizeA = new IsometricCoordinates(objectGeom.getMinIso().x - cuttedGeom.getMinIso().x, cuttedGeom.getIsoSize().y);
            const isoCoordsA = new IsometricCoordinates((objectGeom.getMinIso().x + cuttedGeom.getMinIso().x)/2, cuttedGeom.getIsoCoords().y);
            this.depthSortingArray.splice(spliceIndex, 0, new GridElement(new IsometricGeom(isoCoordsA, isoSizeA), cuttedPolygon.isNavigable));
            polygonsIndexes.push(spliceIndex);
            spliceIndex = spliceIndex + 1;
        }

        //Polygon B (only if not of the edge of the cut polygon)
        if (objectGeom.getMinIso().y > cuttedGeom.getMinIso().y) {
            const minX = Math.max(objectGeom.getMinIso().x, cuttedGeom.getMinIso().x);
            const maxX = Math.min(cuttedGeom.getMaxIso().x, cuttedGeom.getMaxIso().x);

            const isoSizeB = new IsometricCoordinates(
                maxX - minX,
                objectGeom.getMinIso().y - cuttedGeom.getMinIso().y
            );
            const isoCoordsB = new IsometricCoordinates(
                (maxX + minX)/2,
                (objectGeom.getMinIso().y + cuttedGeom.getMinIso().y)/2
            );
            this.depthSortingArray.splice(spliceIndex, 0, new GridElement(new IsometricGeom(isoCoordsB, isoSizeB), cuttedPolygon.isNavigable));
            polygonsIndexes.push(spliceIndex);
            spliceIndex = spliceIndex + 1;
        }

        // add the object geometry
        if (isNavigable) {
            this.depthSortingArray.splice(spliceIndex, 0, new GridElement(objectGeom, isNavigable, phaserObject));
        } else {
            this.depthSortingArray.splice(spliceIndex, 0, new GridElement(objectGeom.enlargeGeom(-this.shrinkAmount), isNavigable, phaserObject));
        }

        polygonsIndexes.push(spliceIndex);
        spliceIndex = spliceIndex + 1;

        //Polygon D (only if not of the edge of the cut polygon)
        if (objectGeom.getMaxIso().x < cuttedGeom.getMaxIso().x) {
            const minY = Math.max(objectGeom.getMinIso().y, cuttedGeom.getMinIso().y);
            const maxY = Math.min(objectGeom.getMaxIso().y, cuttedGeom.getMaxIso().y);

            const isoSizeD = new IsometricCoordinates(
                cuttedGeom.getMaxIso().x - objectGeom.getMaxIso().x,
                maxY - minY
            );
            const isoCoordsD = new IsometricCoordinates(
                (cuttedGeom.getMaxIso().x + objectGeom.getMaxIso().x)/2,
                (maxY + minY)/2
            );
            this.depthSortingArray.splice(spliceIndex, 0, new GridElement(new IsometricGeom(isoCoordsD, isoSizeD), cuttedPolygon.isNavigable));
            polygonsIndexes.push(spliceIndex);
            spliceIndex = spliceIndex + 1;
        }

        //Polygon C (only if not of the edge of the cut polygon)
        if (objectGeom.getMaxIso().y < cuttedGeom.getMaxIso().y) {
            const minX = Math.max(objectGeom.getMinIso().x, cuttedGeom.getMinIso().x);
            const maxX = Math.min(cuttedGeom.getMaxIso().x, cuttedGeom.getMaxIso().x);

            const isoSizeC = new IsometricCoordinates(
                maxX - minX,
                cuttedGeom.getMaxIso().y - objectGeom.getMaxIso().y
            );
            const isoCoordsC = new IsometricCoordinates(
                (maxX + minX)/2,
                (cuttedGeom.getMaxIso().y + objectGeom.getMaxIso().y)/2
            );
            this.depthSortingArray.splice(spliceIndex, 0, new GridElement(new IsometricGeom(isoCoordsC, isoSizeC), cuttedPolygon.isNavigable));
            polygonsIndexes.push(spliceIndex);
        }

        return polygonsIndexes;
    }

    getPolygonFromPoint(isoCoords: IsometricCoordinates): number
    {
        for (let i = 0; i < this.depthSortingArray.length; i++) {
            const gridElement = this.depthSortingArray[i];
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
    public isNavigable: boolean

    constructor(geom: IsometricGeom, isNavigable: boolean, object?: DecorationObject) {
        this.geom = geom;
        this.object = object;
        this.isNavigable = isNavigable;
    }
}
