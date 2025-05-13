<template>
    <div class="message-input-advanced-overlay" v-if="visible" @click.self="cancel">
        <div class="text-format-dialog">
            <div class="format-controls">
                <button
                    type="button"
                    class="format-button"
                    @click="clearFormatting"
                    title="aucun">
                    <span>n</span>
                </button>
                <button
                    type="button"
                    class="format-button"
                    @click="applyFormatting('bold')"
                    title="B">
                    <span><b>B</b></span>
                </button>
                <button
                    type="button"
                    class="format-button"
                    @click="applyFormatting('italic')"
                    title="I">
                    <span><i>I</i></span>
                </button>
                <button
                    type="button"
                    class="format-button"
                    @click="applyFormatting('bolditalic')"
                    title="B+I">
                    <span><b><i>B+I</i></b></span>
                </button>
                <button
                    type="button"
                    class="format-button character-btn"
                    @click="toggleCharacterGrid"
                    title="Personnages">
                    <img :src="getImgUrl('comms/characters.png')" alt="characters">
                </button>
            </div>
            <!-- Grille de sélection des personnages -->
            <div v-if="showCharacterGrid" class="character-grid">
                <div
                    v-for="character in characters"
                    :key="character.key"
                    class="character-item"
                    @click="insertCharacter(character.keyName.toLowerCase())">
                    <img :src="character.head" :alt="character.name">
                    <div class="character-name">{{ character.name }}</div>
                </div>
            </div>

            <textarea
                v-model="editedText"
                class="edit-area"
                @select="handleTextSelection"
                ref="textEditor"
            ></textarea>

            <div class="preview-area" v-html="formattedPreview"></div>

            <div class="dialog-buttons">
                <button
                    class="format-button"
                    @click="cancel">
                    <img :src="getImgUrl('comms/close.png')" alt="cancel">
                </button>
                <button
                    type="button"
                    class="format-button confirm-btn"
                    @click="confirm"
                    title="Valider et envoyer le message">
                    <img :src="getImgUrl('comms/submit.gif')" alt="send">
                </button>
            </div>
        </div>
    </div>
</template>


  <!-- Script JavaScript ===============================================================================  -->

<script lang="ts">
import { defineComponent } from "vue";
import { getImgUrl } from "@/utils/getImgUrl";
import { characterEnum } from "@/enums/character";

export default defineComponent({
    name: "MessageInputAdvanced",
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
            characters: characterEnum
        };
    },
    mounted() {

    },
    computed: {
        formattedPreview(): string {
            // Conversion des marqueurs markdown en HTML pour la prévisualisation
            let formatted = this.editedText;

            // 1) Gras + Italique ***, 2) Gras **, 3) Italique *
            formatted = formatted.replace(/\*\*\*(.*?)\*\*\*/g, '<span style="font-weight:bold;font-style:italic;color:red;">$1</span>');
            formatted = formatted.replace(/\*\*((?!\*\*).+?)\*\*/g, '<span style="font-weight:bold;">$1</span>');
            formatted = formatted.replace(/\*((?!\*).+?)\*/g, '<span style="font-style:italic;color:red;">$1</span>');
            formatted = formatted.replace(/\n/g, '<br>');       // manage line feed
            formatted = formatted.replace(/\/\//g, '<br>');     // manage new line with '//' before inserting URL

            // Remplacer les codes de personnages par leurs icônes
            formatted = formatted.replace(/:([a-z_\ ]+):/g, (match, name) => {
                // Parcourir tous les personnages pour trouver la correspondance
                console.log("nom",name, "match", match);
                for (const key in this.characters) {
                    const character = this.characters[key];
                    // Vérifier si le nom correspond (ignorer la casse)
                    console.log(character);
                    if (character.name.toLowerCase() === name ||
                        character.name.toLowerCase().replace(/\s+/g, '_') === name) {
                        console.log("image: ",character.head);
                        return `<img src="${character.head}" alt="${character.name}" style="width:20px; height:20px; vertical-align:middle;">`;
                    }
                }
                // Si le personnage n'est pas trouvé, garder le texte original
                return match;
            });

            return formatted;
        }
    },
    watch: {
        visible(newVal) {
            if (newVal) {
                this.editedText = this.initialText;
                // Focus sur le textarea quand la fenêtre s'ouvre
                this.$nextTick(() => {
                    this.$refs.textEditor.focus();
                });
            }
        }
    },
    methods: {
        getImgUrl,
        handleTextSelection(): void {
            const element = this.$refs.textEditor;
            this.selection = {
                start: element.selectionStart,
                end: element.selectionEnd,
                text: this.editedText.substring(element.selectionStart, element.selectionEnd)
            };
        },

        applyFormatting(type: string): void {
            const element = this.$refs.textEditor;

            // Utiliser la sélection active
            const start = element.selectionStart;
            const end = element.selectionEnd;
            const selectedText = this.editedText.substring(start, end);

            if (!selectedText) {
                return;
            }

            // Supprimer tous les marqueurs de formatage
            const beforeIndex = this.beforeSelected(start);
            const afterIndex = this.afterSelected(end);
            const afterText = this.editedText.substring(afterIndex);

            // Supprimer les marqueurs existants
            const cleanText = this.editedText.substring(beforeIndex, afterIndex).replace(/(^\*+|\*+$)/g, '');
            // Remplacer le texte sélectionné par le texte nettoyé
            this.editedText = this.editedText.substring(0, beforeIndex) + cleanText + afterText;

            // Appliquer le formatage approprié
            let formattedText = cleanText;
            switch (type) {
            case 'bold':
                formattedText = `**${cleanText}**`;  // Gras
                break;
            case 'italic':
                formattedText = `*${cleanText}*`;  // Italique
                break;
            case 'bolditalic':
                formattedText = `***${cleanText}***`;  // Gras et italique
                break;
            }

            // Remplacer le texte sélectionné par le texte formaté
            this.editedText = this.editedText.substring(0, beforeIndex) + formattedText + afterText;
            console.log(this.editedText);

            // Focus et sélection
            this.$nextTick(() => {
                element.focus();
                const newCursorPosition = start + formattedText.length;
                element.selectionStart = element.selectionEnd = newCursorPosition;
            });
        },

        clearFormatting(): void {
            const element = this.$refs.textEditor;

            // Utiliser la sélection active
            const start = element.selectionStart;
            const end = element.selectionEnd;
            const selectedText = this.editedText.substring(start, end);

            if (!selectedText) {
                return;
            }

            // Supprimer tous les marqueurs de formatage
            const beforeIndex = this.beforeSelected(start);
            const afterIndex = this.afterSelected(end);
            const afterText = this.editedText.substring(afterIndex);

            // Supprimer les marqueurs existants
            const cleanText = this.editedText.substring(beforeIndex, afterIndex).replace(/(^\*+|\*+$)/g, '');
            // Remplacer le texte sélectionné par le texte nettoyé
            this.editedText = this.editedText.substring(0, beforeIndex) + cleanText + afterText;

            // Focus et sélection
            this.$nextTick(() => {
                element.focus();
                const newCursorPosition = start + cleanText.length;
                element.selectionStart = element.selectionEnd = newCursorPosition;
            });
        },
        beforeSelected(start: number): number {
            let index = start - 1; // Commence juste avant la sélection
            while (index >= 0 && this.editedText[index] === '*') {
                index--; // Continue à reculer tant que le caractère est '*'
            }
            return index + 1; // Retourne la position du premier '*'
        },
        afterSelected(end: number): number {
            let index = end; // Commence juste après la sélection
            while (index < this.editedText.length && this.editedText[index] === '*') {
                index++; // Continue à avancer tant que le caractère est '*'
            }
            return index; // Retourne la position après le dernier '*'
        },

        cancel(): void {
            this.$emit('cancel');
            this.showCharacterGrid = false;
            this.editedText = "";
        },

        confirm(): void {
            this.$emit('send', this.editedText);
            // this.$emit('confirm', this.editedText, true); // no more needed
            this.showCharacterGrid = false;
            this.editedText = "";
        },

        toggleCharacterGrid(): void {
            this.showCharacterGrid = !this.showCharacterGrid;
        },

        insertCharacter(characterName: string): void {
            const element = this.$refs.textEditor;
            const cursorPosition = element.selectionStart;

            // Formatage du nom pour l'insertion
            const formattedCharacter = `:${characterName}:`;

            // Insérer à la position du curseur
            this.editedText =
                this.editedText.substring(0, cursorPosition) +
                formattedCharacter +
                this.editedText.substring(cursorPosition);

            // Mettre à jour la position du curseur
            this.$nextTick(() => {
                element.focus();
                const newPosition = cursorPosition + formattedCharacter.length;
                element.selectionStart = element.selectionEnd = newPosition;
            });

            // Fermer la grille après sélection
            this.showCharacterGrid = false;
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
