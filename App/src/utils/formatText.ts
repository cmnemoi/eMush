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
        case "ill":
            return `<img src="${require("@/assets/images/status/disease.png")}" alt="ill">`;
        case "pill":
            return `<img src="${require("@/assets/images/status/demoralized2.png")}" alt="pill">`;
        case "dead":
            return `<img src="${require("@/assets/images/dead.png")}" alt="dead">`;
        case "cat":
            return `<img src="${require("@/assets/images/char/body/cat.png")}" alt="cat">`;
        case "hurt":
            return `<img src="${require("@/assets/images/status/injury.png")}" alt="hurt">`;
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
    formattedText = formattedText.replaceAll(/:ill:/g, helpers.computeImageHtml("ill"));
    formattedText = formattedText.replaceAll(/:pill:/g, helpers.computeImageHtml("pill"));
    formattedText = formattedText.replaceAll(/:dead:/g, helpers.computeImageHtml("dead"));
    formattedText = formattedText.replaceAll(/:cat:/g, helpers.computeImageHtml("cat"));
    formattedText = formattedText.replaceAll(/:hurt:/g, helpers.computeImageHtml("hurt"));
    return formattedText;
}
