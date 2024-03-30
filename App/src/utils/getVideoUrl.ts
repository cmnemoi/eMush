export function getVideoUrl(videoName: string): string {
    return new URL(`/src/assets/videos/${videoName}`, import.meta.url).href;
}