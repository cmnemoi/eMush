<template>
    <div class="news-container">
        <div class="janice">
            <img src="@/assets/images/janice.png" alt="Janice">
        </div>
        <div :class="item.hidden ? 'news hidden' : 'news'"
             v-for="item in news" 
             :key="item.id"
        >
            <div class="news-french-title" v-if="localeIsFrench()" @click="toggleNews(item)">
                <h2>{{ item.frenchTitle }}</h2>
                <p>{{ $t('newsPage.updatedAt') }} {{ formatDate(item.updatedAt) }}</p>
            </div>
            <div class="news-french-content" v-if="localeIsFrench()">
                <p>{{ item.frenchContent }}</p>
            </div>
            <div class="news-english-title" v-if="localeIsEnglish()" @click="toggleNews(item)">
                <h2>{{ item.englishTitle }}</h2>
                <p>{{ $t('newsPage.updatedAt') }} {{ formatDate(item.updatedAt) }}</p>
            </div>
            <div class="news-english-content" v-if="localeIsEnglish()">
                <p>{{ item.englishContent }}</p>
            </div>
            <div class="news-spanish-title" v-if="localeIsSpanish()" @click="toggleNews(item)">
                <h2>{{ item.spanishTitle }}</h2>
                <p>{{ $t('newsPage.updatedAt') }} {{ formatDate(item.updatedAt) }}</p>
            </div>
            <div class="news-spanish-content" v-if="localeIsSpanish()">
                <p>{{ item.spanishContent }}</p>²
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import urlJoin from "url-join";
import ApiService from "@/services/api.service";
import { GameLocales } from "@/i18n";
import { News } from "@/entities/News";


interface NewsState {
    news: News[];
}

export default defineComponent ({
    name: 'TheEnd',
    data: function (): NewsState {
        return {
            news: [],
        };
    },
    methods: {
        async getNews() {
            await ApiService.get(urlJoin(process.env.VUE_APP_API_URL+'news'))
                .then((result) => {
                    for (const newsData of result.data['hydra:member']) {
                        this.news.push((new News()).load(newsData));
                    }
                    this.fillEmptyNews();
                    this.sortNewsByUpdatedAt();
                    this.displayFirstNews();
                })
                .catch((error) => {
                    console.log(error);
                });
        },
        displayFirstNews() {
            this.news[0].hidden = false;
        },
        toggleNews(news: News) {
            // do not hide the first news (it's ugly)
            if (news !== this.news[0])
            {
                news.hidden = !news.hidden;
            }
        },
        localeIsFrench() {
            return this.$i18n.locale.split('-')[0] === GameLocales.FR;
        },
        localeIsEnglish() {
            return this.$i18n.locale.split('-')[0] === GameLocales.EN;
        },
        localeIsSpanish() {
            return this.$i18n.locale.split('-')[0] === GameLocales.ES;
        },
        formatDate(date: Date) {
            if (date === null) {
                return '';
            }
            return date.toLocaleDateString(this.$i18n.locale);
        },
        fillEmptyNews() {
            this.news.forEach((news) => {
                if (news.englishTitle === null) {
                    news.englishTitle = news.frenchTitle;
                }
                if (news.englishContent === null) {
                    news.englishContent = news.frenchContent;
                }
                if (news.spanishTitle === null) {
                    news.spanishTitle = news.frenchTitle;
                }
                if (news.spanishContent === null) {
                    news.spanishContent = news.frenchContent;
                }
            });
        },
        sortNewsByUpdatedAt() {
            this.news.sort((a, b) => {
                if (a.updatedAt === null) {
                    return 1;
                }
                if (b.updatedAt === null) {
                    return -1;
                }
                return b.updatedAt.getTime() - a.updatedAt.getTime();
            });
        },
    },
    async mounted() {
        await this.getNews();
    },
});

</script>

<style lang="scss">

.news-container {
    display: grid;
    position: relative;
    max-width: 1080px;
    width: 100%;
    margin: 36px auto;
    padding: 12px 12px 42px 12px;

    @include corner-bezel(18.5px);

    box-shadow: inset 0 0 35px 25px rgba(15, 89, 171, 0.5);
    background-color: rgba(34, 38, 102, 0.5);
}

.janice {
    grid-column: 1;
    grid-row: 1 / 2;
    width: 250px;
}

.news {
    grid-column: 2 / 200;
    border: 1px solid #576077;
    background-color: #222b6b;
    box-shadow: 0px 4px 4px rgba(0, 0, 0, 0.4);
    padding: 10px;
    margin: 10px;

    h2 {
        color: #D24781;
        margin-bottom: 0;
    }
}

.hidden {
    max-height: 100px;
    
    p {
        display: none;
    }

    h2 {
        color: #D24781;
        margin-bottom: 13.6px;
    }
}

</style>