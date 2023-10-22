<template>
    <div class="admin-config-container">
        <AdminConfigBanner></AdminConfigBanner>
        <div class="admin-container">
            <div class="admin-config-content-container">
                <router-view></router-view>
            </div>
        </div>
    </div></template>

<script lang="ts">
import { defineComponent } from "vue";
import AdminConfigBanner from "@/components/Admin/AdminConfigBanner.vue";

export default defineComponent ({
    name: "AdminConfigPage",
    components: {
        AdminConfigBanner
    }
});

</script>

<style lang="scss" scoped>

.admin-config-container { flex-direction: row; }

.admin-container {
    flex: 1;
    margin-right: 1.6em;

    &::v-deep {
        
        h2 { font-size: 1.6em; }

        h3 { font-size: 1.3em; }

        h2, h3 {
            margin: 2.8em 0 0.6em;
            
            &:first-child { margin-top: 0.6em; }
        }

        .checkbox-container {
            flex-direction: row;
            width: 31%;
            min-width: 200px;
            padding-left: 0.6em;
            margin-top: 1.15em;
            border: 1px solid transparentize(white, 0.8);
            border-radius: 1px;
            align-self: stretch;
            align-items: center;

            &:focus {
                outline: none;
                box-shadow: 0 0 0 3px transparentize(white, 0.85);
            }
        
            label { 
                padding: 0.6em;
                flex: 1;
                cursor: pointer;
                word-break: break-word;
            }
        }
    }

}

</style>
