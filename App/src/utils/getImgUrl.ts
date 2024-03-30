export function getImgUrl(imgName: string): string {
    const assetsPath = import.meta.env.DEV ? '/src/assets/images/' : '/assets/images/';

    return new URL(`${assetsPath}${imgName}`, import.meta.url).href;
}