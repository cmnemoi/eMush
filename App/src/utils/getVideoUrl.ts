const videos = import.meta.glob('/src/assets/videos/**/*.{mp4,webm,ogg,ogv}', {
    eager: true, query: '?url', import: 'default'
});

export function getVideoUrl(videoName: string): string {
    return videos[`/src/assets/videos/${videoName}`] || '';
}
