<template>
    <div class="tips">
        <input id="tips" type="checkbox" name="tips" />
        <label v-html="formatTipsContent(tipsContent)" for="tips"></label>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import { formatText } from "@/utils/formatText";

export default defineComponent ({
    name: "TerminalTips",
    props: {
        tipsContent: {
            type: String,
            required: true
        }
    },
    methods: {
        formatTipsContent: function(value: string): string {
            if (!value) return '';
            return formatText(value.toString());
        }
    }
});
</script>

<style  lang="scss" scoped>

.tips { @extend %retracted-tips; }

</style>