export const helpers = {
    computeImageHtml(key: string): string {
        switch(key) {
        case "hp":
            return `<img src="${require("@/assets/images/lp.png")}" alt="hp">`;
        case "pa":
            return `<img src="${require("@/assets/images/pa.png")}" alt="pa">`;
        case "pm":
            return `<img src="${require("@/assets/images/pm.png")}" alt="pm">`;
        case "pmo":
            return `<img src="${require("@/assets/images/moral.png")}" alt="pmo">`;
        case "triumph":
            return `<img src="${require("@/assets/images/triumph.png")}" alt="pmo">`;
        case "dead":
            return `<img src="${require("@/assets/images/dead.png")}" alt="dead">`;
        default:
            throw Error(`Unexpected key for replaced image: ${key}`);
        }
    }
};

export function formatText(text: string): string {
    let formattedText = text;
    formattedText = formattedText.replaceAll(/\*\*(.[^*]*)\*\*/g, '<strong>$1</strong>');
    formattedText = formattedText.replaceAll(/\*(.[^*]*)\*/g, '<em>$1</em>');
    formattedText = formattedText.replaceAll(/\/\//g, '<br>');
    formattedText = formattedText.replaceAll(/:pa:/g, helpers.computeImageHtml("pa"));
    formattedText = formattedText.replaceAll(/:pm:/g, helpers.computeImageHtml("pm"));
    formattedText = formattedText.replaceAll(/:pmo:/g, helpers.computeImageHtml("pmo"));
    formattedText = formattedText.replaceAll(/:hp:/g, helpers.computeImageHtml("hp"));
    formattedText = formattedText.replaceAll(/:triumph:/g, helpers.computeImageHtml("triumph"));
    formattedText = formattedText.replaceAll(/:dead:/g, helpers.computeImageHtml("dead"));
    return formattedText;
}
