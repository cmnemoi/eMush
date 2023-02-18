<template>
    <div v-if="news" class="news_detail">
        <table>
            <tbody>
                <tr>
                    <td>{{ $t('admin.newsWrite.frenchTitle') }}</td>
                    <td><Input v-model="news.frenchTitle" :errors="errors.frenchTitle" /></td>
                </tr>
                <tr>
                    <td>{{ $t('admin.newsWrite.englishTitle') }}</td>
                    <td><Input v-model="news.englishTitle" :errors="errors.englishTitle"/></td>
                </tr>
                <tr>
                    <td>{{ $t('admin.newsWrite.spanishTitle') }}</td>
                    <td><Input v-model="news.spanishTitle" :errors="errors.spanishTitle"/></td>
                </tr>
                <tr>
                    <td>{{ $t('admin.newsWrite.frenchContent') }}</td>
                    <td><textarea v-model="news.frenchContent" /></td>
                </tr>
                <tr>
                    <td>{{ $t('admin.newsWrite.englishContent') }}</td>
                    <td><textarea v-model="news.englishContent" /></td>
                </tr>
                <tr>
                    <td>{{ $t('admin.newsWrite.spanishContent') }}</td>
                    <td><textarea v-model="news.spanishContent" /></td>
                </tr>
            </tbody>
        </table>
        <button class="action-button"
                type="submit"
                @click="update"
                v-if="news.frenchTitle && news.frenchContent">
            {{ $t('admin.save') }}
        </button>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import Input from "@/components/Utils/Input.vue";
import NewsService from "@/services/news.service";
import { News } from "@/entities/News";
import { handleErrors } from "@/utils/apiValidationErrors";

interface NewsData {
    news: News | null,
    errors: any,
}

export default defineComponent({
    name: "NewsWritePage",
    components: {
        Input
    },
    data() : NewsData {
        return {
            news: null,
            errors: {}
        };
    },
    methods: {
        create(): void {
            if(!this.news) {
                console.error("News is null");
                return;
            }
            NewsService.createNews(this.news)
                .then((result: News | null) => {
                    this.news = result;
                    this.errors = {};
                })
                .catch((error: any) => {
                    this.errors = handleErrors(error);
                });
        },
        update(): void {
            if(!this.news) {
                console.error("News is null");
                return;
            }
            if (!this.news.id) {
                this.create();
                return;
            }
            NewsService.updateNews(this.news)
                .then((result: News | null) => {
                    this.news = result;
                    this.errors = {};
                })
                .catch((error: any) => {
                    this.errors = handleErrors(error);
                });
        }
    },
    beforeMount() {
        const newsId = String(this.$route.params.newsId);
        if(newsId === 'undefined') {
            this.news = new News();
            return;
        }
        NewsService.loadNews(Number(newsId))
            .then((result: News | null) => {
                this.news = result;
            })
            .catch((error: any) => {
                this.errors = handleErrors(error);
            });
        
    }
});
</script>

<style lang="scss" scoped>
    table {
    background: #222b6b;
    border-radius: 5px;
    border-collapse: collapse;
    border: thin solid #1B2256;
    margin-bottom: 1%;

    tbody tr {
        border-top: 1px solid rgba(0,0,0,0.2);

        &:hover,
        &:active { background: rgba(255, 255, 255, .03); }

        textarea {
            background: transparent;
            border: thin solid rgba(255, 255, 255, .25);
            color: #fff;
            font-size: 1.2em;
            font-weight: 300;
            letter-spacing: .05em;
            padding: 0.5em 0.5em 0.5em 0;
            width: 100%;
        }
    }

    th, td {
        padding: 1em 0.5em 1em 1.2em;
        vertical-align: middle;
        &::v-deep a, &::v-deep button {
            @include button-style();
            width: fit-content;
            padding: 2px 15px 4px;
        }
    }

    th {
        position: relative;
        opacity: .75;
        letter-spacing: .05em;
        text-align: left;
        font-weight: bold;
        border-bottom: 1px solid rgba(255, 255, 255, .75);
    }

}
</style>
