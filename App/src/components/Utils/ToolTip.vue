<template>
    <tippy class="tippy-tooltip">
      <slot name='tooltip-trigger'></slot>

      <template #content>
          <slot name='tooltip-content'>

          </slot>
      </template>
    </tippy>
</template>

<script>
import { formatText } from "@/utils/formatText";

    export default {
    name: "Tooltip",
    methods:{
        formatContent(value) {
            if (! value) return '';
            return formatText(value.toString());
            }
        },
    props: {
        title: { 
            type: String,
            required: true
            },
        content: { 
            type: Array,
            required: true
            }
        }
    }
</script>