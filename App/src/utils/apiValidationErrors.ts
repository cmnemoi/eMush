export function handleErrors(errors: { propertyPath: string, message: string }[]): {[key: string]: string[]} {
    const result : any = {};
    errors.forEach((datum : { propertyPath: string, message: string }) => {
        if (datum.propertyPath !== undefined) {
            if (result[datum.propertyPath] === undefined) {
                result[datum.propertyPath] = [];
            }
            result[datum.propertyPath].push(datum.message);
        }
    });

    return result;
}

