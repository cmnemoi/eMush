export function getVideoUrl(videoName: string): string {
    const assetsPath = import.meta.env.DEV ? '/src/assets/videos/' : '/assets/';

    return new URL(`${assetsPath}${videoName}`, import.meta.url).href;
}