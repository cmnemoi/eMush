export function formatText(text) {
    let formattedText = text;
    formattedText = formattedText.replaceAll(/\*\*(.*)\*\*/g, '<strong>$1</strong>');
    formattedText = formattedText.replaceAll(/\*(.*)\*/g, '<em>$1</em>');
    formattedText = formattedText.replaceAll(/:pa:/g, '<img src="'+require("@/assets/images/pa.png")+'" alt="pa">');
    formattedText = formattedText.replaceAll(/:pm:/g, '<img src="'+require("@/assets/images/pm.png")+'" alt="pm">');
    return formattedText;
}
