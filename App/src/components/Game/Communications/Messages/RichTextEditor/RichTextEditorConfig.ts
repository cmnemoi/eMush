export type FormattingType = 'bold' | 'italic' | 'bolditalic' | 'strike';
export type RichTextEditorButtonType = 'erase' | 'characters' | FormattingType;

export interface RichTextEditorButtonConfig {
    type: RichTextEditorButtonType;
    label: string;
    title: string;
    action: 'clearFormatting' | 'applyFormatting' | 'toggleCharacterGrid';
    actionParam?: string;
}

export const richTextEditorButtons: RichTextEditorButtonConfig[] = [
    {
        type: 'erase',
        label: 'game.communications.eraseButtonTitle',
        title: 'game.communications.eraseButtonDescription',
        action: 'clearFormatting'
    },
    {
        type: 'bold',
        label: 'game.communications.boldButtonTitle',
        title: 'game.communications.boldButtonDescription',
        action: 'applyFormatting',
        actionParam: 'bold'
    },
    {
        type: 'italic',
        label: 'game.communications.italicButtonTitle',
        title: 'game.communications.italicButtonDescription',
        action: 'applyFormatting',
        actionParam: 'italic'
    },
    {
        type: 'bolditalic',
        label: 'game.communications.boldItalicButtonTitle',
        title: 'game.communications.boldItalicButtonDescription',
        action: 'applyFormatting',
        actionParam: 'bolditalic'
    },
    {
        type: 'strike',
        label: 'game.communications.strikeButtonTitle',
        title: 'game.communications.strikeButtonDescription',
        action: 'applyFormatting',
        actionParam: 'strike'
    },
    {
        type: 'characters',
        label: 'game.communications.charactersButtonTitle',
        title: 'game.communications.charactersButtonDescription',
        action: 'toggleCharacterGrid'
    }
];
