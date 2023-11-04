<template>
    <div class="tips" v-if="content">
        <input id="tips" type="checkbox" name="tips" />
        <label for="tips" v-html="formatText(content)"></label>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import { formatText } from "@/utils/formatText";

export default defineComponent ({
    name: "TerminalTips",
    props: {
        content: {
            type: String,
            required: true
        }
    },
    methods: {
        formatText(text: string): string {
            if (!text) {
                return '';
            }

            return formatText(text);
        }
    }
});
</script>

<style  lang="scss" scoped>

.tips { @extend %retracted-tips; }

</style>
