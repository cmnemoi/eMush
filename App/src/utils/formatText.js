const helpers = {
    computeImageHtml(key) {
        switch(key) {
        case "pa":
            return `<img src="${require("@/assets/images/pa.png")}" alt="pa">`;
        case "pm":
            return `<img src="${require("@/assets/images/pm.png")}" alt="pm">`;
        default:
            throw Error(`Unexpected key for replaced image: ${key}`);
        }
    }
};

Object.assign(module.exports, { formatText, helpers });

function formatText(text) {
    let formattedText = text;
    formattedText = formattedText.replaceAll(/\*\*(.[^*]*)\*\*/g, '<strong>$1</strong>');
    formattedText = formattedText.replaceAll(/\*(.*)\*/g, '<em>$1</em>');
    formattedText = formattedText.replaceAll(/:pa:/g, helpers.computeImageHtml("pa"));
    formattedText = formattedText.replaceAll(/:pm:/g, helpers.computeImageHtml("pm"));
    return formattedText;
}
