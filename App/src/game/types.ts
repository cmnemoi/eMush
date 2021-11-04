export type IsometricCoordinates = {x: number, y: number}

export type CartesianCoordinates = {x: number, y: number}

export type IsometricDistance = {x: number, y: number}

export type CartesianDistance = {x: number, y: number}


export function toCartesianCoords(isoCoords: IsometricCoordinates | IsometricDistance): CartesianCoordinates
{
    return { x: (isoCoords.x - isoCoords.y), y : (isoCoords.x + isoCoords.y)/2 };
}

export function toIsometricCoords(cartCoords: CartesianCoordinates | CartesianDistance): IsometricCoordinates
{
    return {
        x: cartCoords.y + cartCoords.x/2,
        y: cartCoords.y - cartCoords.x/2
    };
}
