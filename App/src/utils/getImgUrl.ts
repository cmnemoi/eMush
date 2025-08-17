const images = import.meta.glob('/src/assets/images/**/*.{png,jpg,jpeg,gif,svg,webp}', {
    eager: true, query: '?url', import: 'default'
});

export function getImgUrl(imgPath: string): string {
    return images[`/src/assets/images/${imgPath}`] || '';
}
