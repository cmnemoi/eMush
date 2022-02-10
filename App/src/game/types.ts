export class IsometricCoordinates extends Phaser.Geom.Point
{
    constructor(x: number, y: number){
        super(x, y);
    }

    toCartesianCoordinates(): CartesianCoordinates
    {
        return new CartesianCoordinates((this.x - this.y), (this.x + this.y)/2);
    }
}

export class CartesianCoordinates extends Phaser.Geom.Point
{
    constructor(x: number, y: number){
        super(x, y);
    }

    toIsometricCoordinates(): IsometricCoordinates
    {
        return new IsometricCoordinates(this.y + this.x/2, this.y - this.x/2);
    }
}
