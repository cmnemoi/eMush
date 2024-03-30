export function getImgUrl(imgName: string): string {
    const assetsPath = import.meta.env.DEV ? '/src/assets/images/' : '/assets/';

    return new URL(`${assetsPath}${imgName}`, import.meta.url).href;
}