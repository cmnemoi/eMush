<template>
    <div class="text-format-dialog-overlay" v-if="visible" @click.self="cancel">
      <div class="text-format-dialog">
        <div class="format-controls">
          <button type="button" class="format-button" @click="clearFormatting" title="aucun">
            <span>n</span>
          </button>
          <button type="button" class="format-button" @click="applyFormatting('bold')" title="B">
            <span><b>B</b></span>
          </button>
          <button type="button" class="format-button" @click="applyFormatting('italic')" title="I">
            <span><i>I</i></span>
          </button>
          <button type="button" class="format-button" @click="applyFormatting('bolditalic')" title="B+I">
            <span><b><i>B+I</i></b></span>
          </button>
        </div>
        
        <textarea 
          v-model="editedText" 
          class="edit-area"
          @select="handleTextSelection"
          ref="textEditor"
        ></textarea>
        
        <div class="preview-area" v-html="formattedPreview"></div>
        
        <div class="dialog-buttons">
          <button class="format-button cancel-btn" @click="cancel">Annuler</button>
          <button class="format-button confirm-btn" @click="confirm"><img :src="getImgUrl('comms/submit.gif')" alt="submit"></button>
        </div>
      </div>
    </div>
  </template>

    
  <!-- Script JavaScript ===============================================================================  -->
  
  <script lang="ts">
  import { defineComponent } from "vue";
  import { getImgUrl } from "@/utils/getImgUrl";
  
  export default defineComponent({
    name: "TextFormatDialog",
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
        }
      };
    },
    computed: {
      formattedPreview(): string {
        // Conversion des marqueurs markdown en HTML pour la prévisualisation
        let formatted = this.editedText;
        
        // Gras + Italique (***texte***)
        formatted = formatted.replace(/\*\*\*(.*?)\*\*\*/g, '<span style="font-weight:bold;font-style:italic;color:red;">$1</span>');
        
        // Italique (**texte**)
        formatted = formatted.replace(/\*\*((?!\*\*).+?)\*\*/g, '<span style="font-style:italic;color:red;">$1</span>');

        // Gras (*texte*)
        formatted = formatted.replace(/\*((?!\*).+?)\*/g, '<span style="font-weight:bold;">$1</span>');
        
        // Convertir les sauts de ligne
        formatted = formatted.replace(/\/\//g, '<br>');
        
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
        
        // Récupérer le texte sélectionné
        const selectedText = this.editedText.substring(start, end);
        
        if (!selectedText) {
          return;
        }
        
        // Appliquer le formatage approprié
        let formattedText = selectedText;
        switch (type) {
          case 'bold':
            formattedText = `*${selectedText}*`;  // Gras
            break;
          case 'italic':
            formattedText = `**${selectedText}**`;  // Italique
            break;
          case 'bolditalic':
            formattedText = `***${selectedText}***`;  // Gras et italique
            break;
        }
        
        // Remplacer le texte sélectionné par le texte formaté
        this.editedText = this.editedText.substring(0, start) + formattedText + this.editedText.substring(end);
        console.log(this.editedText)
        
        // Focus et sélection
        this.$nextTick(() => {
          element.focus();
          element.selectionStart = start;
          element.selectionEnd = start + formattedText.length;
        });
      },
      
      clearFormatting(): void {
        const element = this.$refs.textEditor;
        
        // Utiliser la sélection active
        const start = element.selectionStart;
        const end = element.selectionEnd;
        
        // Récupérer le texte sélectionné
        const selectedText = this.editedText.substring(start, end);
        
        if (!selectedText) {
          return;
        }
        
        // Supprimer tous les marqueurs de formatage
        let cleanText = selectedText
          .replace(/\*\*\*(.*?)\*\*\*/g, '$1')  // Supprimer gras+italique
          .replace(/\*\*(.*?)\*\*/g, '$1')      // Supprimer italique
          .replace(/\*(.*?)\*/g, '$1');         // Supprimer gras
        
        // Remplacer le texte sélectionné par le texte nettoyé
        this.editedText = this.editedText.substring(0, start) + cleanText + this.editedText.substring(end);
        
        // Focus et sélection
        this.$nextTick(() => {
          element.focus();
          element.selectionStart = start;
          element.selectionEnd = start + cleanText.length;
        });
      },
      
      cancel(): void {
        this.$emit('cancel');
      },
      
      confirm(): void {
        this.$emit('confirm', this.editedText);
      }
    }
  });
  </script>
  
  <!-- Formattage CSS  =================================================================================  -->
  <style lang="scss" scoped>

  .text-format-dialog-overlay {
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    flex-direction: row;
    position: relative;
    justify-content: center;
    align-items: center;
    z-index: 1000;
  }
  
  .text-format-dialog {
    background-color: #fff;
    border-radius: 5px;
    padding: 15px;
    position: relative;
    width: 300px;
    max-width: 400px;
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
    gap: 5px;
    justify-content: flex-start;
    margin-bottom: 10px;
  }
  
  .edit-area {
    width: 100%;
    min-height: 100px;
    padding: 8px;
    border: 1px solid #aad4e5;
    border-radius: 3px;
    font-family: inherit;
    resize: vertical;
  }
  
  .preview-area {
    width: 100%;
    min-height: 100px;
    max-height: 120px;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 3px;
    background-color: #f9f9f9;
    overflow-y: auto;
    display: inline;
  }

  
  .dialog-buttons {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 10px;
  }
  
  .dialog-btn {
    padding: 8px 15px;
    border-radius: 3px;
    cursor: pointer;
    font-weight: bold;
  }
  
  .cancel-btn {
    background-color: #f5f5f5;
    border: 1px solid #ccc;
    
    &:hover {
      background-color: #e5e5e5;
    }
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
    cursor: pointer;
    @include button-style();
    width: 24px;
    margin-left: 4px;
    &:hover {
      background-color: #00B0EC;
    }
  }
</style>
  