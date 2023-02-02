<template>
    <div v-if="news" class="news_detail">
        <div class="flex-row">
            <Input
                :label="$t('admin.newsWrite.frenchtitle')"
                id="news_frenchTitle"
                v-model="news.frenchTitle"
                type="text"
                :errors="errors.frenchTitle"
            />
            <Input
                :label="$t('admin.newsWrite.englishTitle')"
                id="news_englishTitle"
                v-model="news.englishTitle"
                type="text"
            />
            <Input
                :label="$t('admin.newsWrite.spanishTitle')"
                id="news_spanishTitle"
                v-model="news.spanishTitle"
                type="text"
            />
        </div>
        <textarea
            :placeholder="$t('admin.newsWrite.frenchContent')"
            id="news_frenchContent"
            v-model="news.frenchContent"
        />
        <textarea
            :placeholder="$t('admin.newsWrite.englishContent')"
            id="news_englishContent"
            v-model="news.englishContent"
        />
        <textarea
            :placeholder="$t('admin.newsWrite.spanishContent')"
            id="news_spanishContent"
            v-model="news.spanishContent"
        />
        <button class="action-button" type="submit" @click="update">
            {{ $t('admin.save') }}
        </button>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import NewsService from "@/services/news.service";
import { News } from "@/entities/News";
import Input from "@/components/Utils/Input.vue";
import { handleErrors } from "@/utils/apiValidationErrors";

interface NewsData {
    news: News | null,
    errors: any,
}

export default defineComponent({
    name: "NewsWritePage",
    components: {
        Input,
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
            this.news.updatedAt = new Date();
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

</style>
