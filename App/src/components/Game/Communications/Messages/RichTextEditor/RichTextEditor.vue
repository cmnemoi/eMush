<template>
    <div class="message-input-advanced-overlay" v-if="visible" @click.self="cancel">
        <div class="text-format-dialog">
            <div class="format-controls">
                <RichTextEditorButton
                    v-for="button in richTextEditorButtons"
                    :key="button.type + button.action"
                    :type="button.type"
                    :label="$t(button.label)"
                    :title="$t(button.title)"
                    @click="executeRichEditorAction(button)"
                />
            </div>
            <RichTextEditorCharacterEmotes v-if="showCharacterGrid" :characters="characters" @character-selected="insertCharacter" />

            <textarea
                v-model="editedText"
                class="edit-area"
                @select="selectHighlightedText"
                @keydown.esc.exact.prevent="cancel"
                @keydown.enter.exact.prevent="confirm"
                ref="textEditor"
            ></textarea>

            <div class="preview-area" v-html="formattedPreview"></div>

            <div class="dialog-buttons">
                <button
                    class="format-button"
                    @click="cancel"
                >
                    <img :src="getImgUrl('comms/close.png')" alt="cancel">
                </button>
                <button
                    type="button"
                    class="format-button confirm-btn"
                    @click="confirm"
                    :title="$t('game.communications.buttonValidateAdvEditor')">
                    <img :src="getImgUrl('comms/submit.gif')" alt="send">
                </button>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import { getImgUrl } from "@/utils/getImgUrl";
import { characterEnum, CharacterInfos } from "@/enums/character";
import { formatText } from "@/utils/formatText";
import RichTextEditorCharacterEmotes from "./RichTextEditorCharacterEmotes.vue";
import RichTextEditorButton from "./RichTextEditorButton.vue";
import { richTextEditorButtons, RichTextEditorButtonConfig, FormattingType } from "./RichTextEditorConfig";

export default defineComponent({
    name: "RichTextEditor",
    components: {
        RichTextEditorCharacterEmotes,
        RichTextEditorButton
    },
    props: {
        initialText: {
            type: String,
            default: ""
        },
        visible: {
            type: Boolean,
            default: false
        }
    },
    data() {
        return {
            editedText: this.initialText,
            selection: {
                start: 0,
                end: 0,
                text: ""
            },
            showCharacterGrid: false,
            characters: characterEnum as {[key: string]: CharacterInfos},
            richTextEditorButtons
        };
    },
    computed: {
        formattedPreview(): string {
            // Conversion des marqueurs markdown en HTML pour la prévisualisation
            return formatText(this.editedText);
        }
    },
    watch: {
        visible(newVal) {
            if (!newVal) {
                return;
            }
            this.editedText = this.initialText;
            this.$nextTick(() => {
                (this.$refs.textEditor as HTMLTextAreaElement).focus();
            });
        }
    },
    methods: {
        getImgUrl,
        selectHighlightedText(): void {
            const element = this.$refs.textEditor as HTMLTextAreaElement;
            this.selection = {
                start: element.selectionStart,
                end: element.selectionEnd,
                text: this.editedText.substring(element.selectionStart, element.selectionEnd)
            };
        },

        executeRichEditorAction(button: RichTextEditorButtonConfig): void {
            switch (button.action) {
            case 'clearFormatting':
                this.clearFormatting();
                break;
            case 'applyFormatting':
                if (!button.actionParam) {
                    return;
                }
                this.applyFormatting(button.actionParam as FormattingType);
                break;
            case 'toggleCharacterGrid':
                this.toggleCharacterGrid();
                break;
            }
        },

        applyFormatting(type: FormattingType): void {
            const element = this.$refs.textEditor as HTMLTextAreaElement;
            const selection = this.getTextSelection(element);

            if (!this.isValidSelection(selection)) {
                return;
            }

            try {
                const formattedText = this.formatSelectedText(selection, type);
                this.updateTextWithFormatting(element, selection, formattedText);
            } catch (error) {
                console.error('Error applying formatting:', error);
            }
        },

        clearFormatting(): void {
            const element = this.$refs.textEditor as HTMLTextAreaElement;
            const selection = this.getTextSelection(element);

            if (!this.isValidSelection(selection)) {
                return;
            }

            try {
                const cleanText = this.clearSelectedTextFormatting(selection);
                this.updateTextWithCleanedFormatting(element, selection, cleanText);
            } catch (error) {
                console.error('Error clearing formatting:', error);
            }
        },

        insertCharacter(characterName: string): void {
            const element = this.$refs.textEditor as HTMLTextAreaElement;
            const cursorPosition = element.selectionStart;

            try {
                const formattedCharacter = this.formatCharacterName(characterName);
                this.insertTextAtPosition(element, cursorPosition, formattedCharacter);
                this.closeCharacterGrid();
            } catch (error) {
                console.error('Error inserting character:', error);
            }
        },

        cancel(): void {
            this.$emit('cancel');
            this.showCharacterGrid = false;
            this.editedText = "";
        },

        confirm(): void {
            this.$emit('send', this.editedText);
            this.showCharacterGrid = false;
            this.editedText = "";
        },

        toggleCharacterGrid(): void {
            this.showCharacterGrid = !this.showCharacterGrid;
        },

        isValidSelection(selection: { text: string }): boolean {
            return Boolean(selection.text);
        },

        getTextSelection(element: HTMLTextAreaElement): { start: number; end: number; text: string } {
            return {
                start: element.selectionStart,
                end: element.selectionEnd,
                text: this.editedText.substring(element.selectionStart, element.selectionEnd)
            };
        },

        formatSelectedText(selection: { start: number; end: number }, type: FormattingType): string {
            const selectedText = this.editedText.substring(selection.start, selection.end);

            const cleanText = this.cleanExistingFormatting(selectedText);
            return this.applyFormattingForType(cleanText, type);
        },

        updateTextWithFormatting(element: HTMLTextAreaElement, selection: { start: number; end: number }, formattedText: string): void {
            this.replaceTextInRange(selection.start, selection.end, formattedText);
            this.updateCursorPosition(element, selection.start + formattedText.length);
        },

        clearSelectedTextFormatting(selection: { start: number; end: number }): string {
            const selectedText = this.editedText.substring(selection.start, selection.end);

            return this.cleanExistingFormatting(selectedText);
        },

        updateTextWithCleanedFormatting(element: HTMLTextAreaElement, selection: { start: number; end: number }, cleanText: string): void {
            this.replaceTextInRange(selection.start, selection.end, cleanText);
            this.updateCursorPosition(element, selection.start + cleanText.length);
        },

        formatCharacterName(characterName: string): string {
            return `:${characterName}:`;
        },

        insertTextAtPosition(element: HTMLTextAreaElement, position: number, text: string): void {
            this.editedText =
                this.editedText.substring(0, position) +
                text +
                this.editedText.substring(position);

            this.updateCursorPosition(element, position + text.length);
        },

        closeCharacterGrid(): void {
            this.showCharacterGrid = false;
        },

        cleanExistingFormatting(text: string): string {
            return text.replace(/(^[*|~]+|[*|~]+$)/g, '');
        },

        updateCursorPosition(element: HTMLTextAreaElement, position: number): void {
            this.$nextTick(() => {
                element.focus();
                element.selectionStart = element.selectionEnd = position;
            });
        },

        replaceTextInRange(start: number, end: number, newText: string): void {
            this.editedText =
                this.editedText.substring(0, start) +
                newText +
                this.editedText.substring(end);
        },

        applyFormattingForType(text: string, type: FormattingType): string {
            switch (type) {
            case 'bold':
                return `**${text}**`;  // Gras
            case 'italic':
                return `*${text}*`;  // Italique
            case 'bolditalic':
                return `***${text}***`;  // Gras et italique
            case 'strike':
                return `~~${text}~~`;  // Barré
            default:
                return text; // Return original text if type is unknown
            }
        }
    }
});
</script>

<!-- Formattage CSS  =================================================================================  -->
<style lang="scss" scoped>

    .message-input-advanced-overlay {
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0);
        display: flex;
        flex-direction: row;
        position: sticky;
        max-width: 97%;
        justify-content: left;
        align-items: left;
        z-index: 1000;
    }

    .text-format-dialog {
        background-color: #fff;
        border-radius: 3px;
        padding: 10px;
        position: relative;
        width: 400px;
        max-width: 100%;
        min-width: 290px;
        max-height: 90vh;
        display: flex;
        flex-direction: column;
        gap: 10px;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
        span {
            display: inline; /* Forcer les <span> à être inline */
        }
    }

    .format-controls {
        display: flex;
        flex-flow: row wrap;
        gap: 5px;
        justify-content: flex-start;
        margin-bottom: 10px;
        flex-wrap: wrap;
    }

    .edit-area {
        width: 100%;
        min-height: 90px;
        padding: 8px;
        border: 1px solid #aad4e5;
        border-radius: 3px;
        font-family: inherit;
        resize: vertical;
    }

    .preview-area {
        width: 100%;
        min-height: 120px;
        max-height: 150px;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 3px;
        background-color: #f9f9f9;
        overflow-y: auto;
        scroll-behavior: smooth;
        display: inline;
        :deep(em) {
            color: lighten(#cf1830, 15);
        }
    }

    .dialog-buttons {
        display: flex;
        justify-content: flex-end;
        flex-flow: row wrap;
        gap: 10px;
        margin-top: 10px;
    }

    .confirm-btn {
        background-color: #008EE5;
        border: 1px solid #008EE5;
        color: white;

        &:hover {
            background-color: #015e97;
        }
    }

    .format-button {
        display: inline-block;
        cursor: pointer;
        @include button-style();
        width: 24px;
        margin-left: 4px;
        &:hover {
            background-color: #00B0EC;
        }
    }
    .character-grid {
        display: grid;
        position: absolute; /* Positionnement absolu pour superposer */
        top: 40px;
        left: 0px;
        z-index: auto; /* S'assure que la grille est au-dessus des autres éléments */
        grid-template-columns: repeat(5, 1fr); /* 4 colonnes pour 16 personnages */
        gap: 3px;
        margin-bottom: 10px;
        max-height: 220px;
        overflow-y: auto;
        border: 1px solid #aad4e5;
        border-radius: 3px;
        padding: 3px;
        background-color: white;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2); /* Ajoute une ombre pour l'effet popup */
    }

    .character-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        padding: 2px;
        border-radius: 3px;
        transition: background-color 0.2s;

        &:hover {
            background-color: #e9f5fb;
        }

        img {
            width: 16px;
            height: 16px;
            object-fit: cover;
            border-radius: 5%;
        }

        .character-name {
            margin-top: 4px;
            font-size: 11px;
            text-align: center;
        }
    }

    .character-btn {
        font-size: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

</style>

