export function getSwfUrl(swfName: string): string {
    return new URL(`/src/assets/swfs/${swfName}`, import.meta.url).href;
}
