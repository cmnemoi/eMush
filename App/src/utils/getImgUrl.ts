export function getImgUrl(imgName: string): string {
    return new URL(`/src/assets/images/${imgName}`, import.meta.url).href;
}
