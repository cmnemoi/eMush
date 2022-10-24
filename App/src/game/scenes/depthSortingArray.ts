import * as Phaser from "phaser";
import DaedalusScene from "@/game/scenes/daedalusScene";
import { IsometricCoordinates } from "@/game/types";
import IsometricGeom from "@/game/scenes/isometricGeom";
import DecorationObject from "@/game/objects/decorationObject";


export class DepthSortingArray {
    private scene : DaedalusScene;
    private elementArray: Array<DepthElement>;

    constructor(
        scene: DaedalusScene
    )
    {
        this.scene = scene;
        this.elementArray = [];
    }

    addElement(phaserObject: DecorationObject): Array<DepthElement>
    {
        const newElementDepth = new DepthElement(phaserObject);
        const ArrayLength = this.elementArray.length;
        if (ArrayLength === 0)
        {
            this.elementArray.push(newElementDepth);
        } else {
            for (let i = 0; i < ArrayLength; i++) {
                if (!newElementDepth.isInFront(this.elementArray[i])) {
                    this.elementArray.splice(i,0, newElementDepth);
                    break;
                }
                if (i === ArrayLength - 1) {
                    this.elementArray.push(newElementDepth);
                }
            }
        }

        return this.elementArray;
    }

    getElementArray(): Array<DepthElement>
    {
        return this.elementArray;
    }

    activateSorting(): void
    {
        for (let i = 0; i < this.elementArray.length; i++) {
            this.elementArray[i].getPhaserObject().setDepth(i*10 + 100);
        }
    }
}

export class DepthElement {
    private phaserObject: DecorationObject;
    public slope: number;
    public intersect: number;
    public isoCoords: IsometricCoordinates;

    public topRightCorner: IsometricCoordinates;
    public bottomLeftCorner: IsometricCoordinates;

    constructor(
        phaserObject: DecorationObject
    ) {
        this.phaserObject = phaserObject;

        this.slope = (phaserObject.isoGeom.getMinIso().y - phaserObject.isoGeom.getMaxIso().y) / (phaserObject.isoGeom.getMaxIso().x - phaserObject.isoGeom.getMinIso().x);
        this.intersect = phaserObject.isoGeom.getIsoCoords().y - this.slope * phaserObject.isoGeom.getIsoCoords().x;

        this.isoCoords = phaserObject.isoGeom.getIsoCoords();

        this.topRightCorner = phaserObject.isoGeom.getMaxIso();
        this.bottomLeftCorner = phaserObject.isoGeom.getMinIso();
    }

    // isInFront(otherElement: DepthElement): boolean
    // {
    //     return true;
    // }


    // isInFront(otherElement: DepthElement): boolean
    // {
    //
    //
    //
    //
    //     if (this.slope === otherElement.slope) {
    //
    //     }
    //     if (this.topRightCorner.x > otherElement.bottomLeftCorner.x && this.topRightCorner.y > otherElement.bottomLeftCorner.y) {
    //         return true;
    //     }
    //     return false;
    // }

    isInFront(otherElement: DepthElement): boolean   //Not working
    {
        // we compare the bottom left corner of the other element (the further corner from the camera)
        // with the top right corner of the current object
        //
        //         _ x
        //        |     ----------------          O the other element
        //        y     ---OOO++++++++++          + area in front of the other element
        //              ---OOO++++++++++          - area in the back of the other element
        //              ---+++++++++++++
        //              ---+++++++++++++

        const bottomRightCorner = new IsometricCoordinates(this.topRightCorner.x, this.bottomLeftCorner.y).toCartesianCoordinates();
        const topLeftCorner = new IsometricCoordinates(this.bottomLeftCorner.x, this.topRightCorner.y).toCartesianCoordinates();
        const otherBottomRightCorner = new IsometricCoordinates(otherElement.topRightCorner.x, otherElement.bottomLeftCorner.y).toCartesianCoordinates();
        const otherTopLeftCorner = new IsometricCoordinates(otherElement.bottomLeftCorner.x, otherElement.topRightCorner.y).toCartesianCoordinates();

        //if the objects don't overlap
        if (otherTopLeftCorner > bottomRightCorner || otherBottomRightCorner < topLeftCorner) {
            if (this.bottomLeftCorner.x > otherElement.bottomLeftCorner.x && this.bottomLeftCorner.y > otherElement.bottomLeftCorner.y) {
                return true;
            } else {
                return false;
            }
        }

        if (this.topRightCorner.x > otherElement.bottomLeftCorner.x && this.topRightCorner.y > otherElement.bottomLeftCorner.y) {
            return true;
        }
        return false;
    }


    /* isInFront(otherElement: DepthElement): boolean        //Not working
    {
        // test for intersection x-axis
        // (lower x value is in front)
        if (this.phaserObject.isoGeom.getMinIso().x >= otherElement.getPhaserObject().isoGeom.getMaxIso().x) { return true; }
        else if (otherElement.getPhaserObject().isoGeom.getMinIso().x >= this.phaserObject.isoGeom.getMaxIso().x) { return false; }

        // test for intersection y-axis
        // (lower y value is in front)
        if (this.phaserObject.isoGeom.getMinIso().y >= otherElement.getPhaserObject().isoGeom.getMaxIso().y) { return true; }
        else if (otherElement.getPhaserObject().isoGeom.getMinIso().y >= this.phaserObject.isoGeom.getMaxIso().y) { return false; }

        return false;
    }*/

    getIsoCoords(): IsometricCoordinates
    {
        return this.phaserObject.isoGeom.getIsoCoords();
    }

    getPhaserObject(): DecorationObject
    {
        return this.phaserObject;
    }

}

