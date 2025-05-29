import { FormattingType } from "@/components/Game/Communications/Messages/RichTextEditor/RichTextEditorConfig";

export interface TextSelection {
    start: number;
    end: number;
}

export interface FormatOperationResult {
    newFullText: string;
    modifiedPart: string;
}

export function cleanExistingFormatting(text: string): string {
    let cleanedText = text;
    let previousText = '';

    const patterns = [
        /\*\*\*(.*?)\*\*\*/g, // bolditalic
        /\*\*(.*?)\*\*/g,   // bold
        /~~(.*?)~~/g,       // strike
        /\*(.*?)\*/g        // italic
    ];

    do {
        previousText = cleanedText;
        for (const pattern of patterns) {
            cleanedText = cleanedText.replace(pattern, '$1');
        }
    } while (cleanedText !== previousText);

    cleanedText = cleanedText.replace(/(^[*~]+|[*~]+$)/g, '');

    return cleanedText;
}

export function applyFormattingForType(text: string, type: FormattingType): string {
    switch (type) {
    case 'bold':
        return `**${text}**`;
    case 'italic':
        return `*${text}*`;
    case 'bolditalic':
        return `***${text}***`;
    case 'strike':
        return `~~${text}~~`;
    default:
        return text;
    }
}

export function formatCharacterName(characterName: string): string {
    return `:${characterName}:`;
}

export function applySelectedTextFormatting(
    currentFullText: string,
    selection: TextSelection,
    type: FormattingType
): FormatOperationResult {
    const selectedText = currentFullText.substring(selection.start, selection.end);
    const cleanText = cleanExistingFormatting(selectedText);
    const formattedText = applyFormattingForType(cleanText, type);

    const newFullText =
        currentFullText.substring(0, selection.start) +
        formattedText +
        currentFullText.substring(selection.end);

    return {
        newFullText,
        modifiedPart: formattedText
    };
}

export function clearSelectedTextFormattingLogic(
    currentFullText: string,
    selection: TextSelection
): FormatOperationResult {
    const selectedText = currentFullText.substring(selection.start, selection.end);
    const cleanText = cleanExistingFormatting(selectedText);

    const newFullText =
        currentFullText.substring(0, selection.start) +
        cleanText +
        currentFullText.substring(selection.end);

    return {
        newFullText,
        modifiedPart: cleanText
    };
}

export function insertTextAtPositionLogic(
    currentFullText: string,
    position: number,
    textToInsert: string
): string {
    return (
        currentFullText.substring(0, position) +
        textToInsert +
        currentFullText.substring(position)
    );
}
