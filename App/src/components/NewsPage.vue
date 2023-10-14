<template>
    <div class="news-container">
        <div class="janice">
            <img src="@/assets/images/janice.png" alt="Janice">
        </div>
        <div class="news-feed">
            <section :class="item.hidden ? 'news hidden' : 'news'"
                     v-for="item in news" 
                     :key="item.id"
            >
                <div class="news-french title" v-if="localeIsFrench()" @click="toggleNews(item)">
                    <img class="news-cover" src="@/assets/images/mush-cover.png">
                    <h2>{{ item.frenchTitle }}</h2>
                    <p><img class="flag" src="@/assets/images/lang_fr.png" alt="🇫🇷"> {{ $t('newsPage.updatedAt') }} {{ formatDate(item.updatedAt) }}</p>
                </div>
                <div class="news-french content" v-if="localeIsFrench()">
                    <p v-html="item.frenchContent" />
                </div>
                <div class="news-english title" v-if="localeIsEnglish()" @click="toggleNews(item)">
                    <img class="news-cover" src="@/assets/images/mush-cover.png">
                    <h2>{{ item.englishTitle }}</h2>
                    <p><img class="flag" src="@/assets/images/lang_en.png" alt="🇬🇧"> {{ $t('newsPage.updatedAt') }} {{ formatDate(item.updatedAt) }}</p>
                </div>
                <div class="news-english content" v-if="localeIsEnglish()">
                    <p v-html="item.englishContent" />
                </div>
            </section>
            <div class="pagination-container">
                <Pagination
                    :page-count="Math.ceil(pagination.totalPage)"
                    :click-handler="paginationClick"
                    :prev-text="$t('util.prev')"
                    :next-text="$t('util.next')"
                    :container-class="'className'"
                ></Pagination>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import urlJoin from "url-join";
import qs from "qs";
import ApiService from "@/services/api.service";
import { GameLocales } from "@/i18n";
import { News } from "@/entities/News";
import Pagination from "@/components/Utils/Datatable/Pagination.vue";

export default defineComponent ({
    name: 'TheEnd',
    components: {
        Pagination
    },
    data() {
        return {
            news: new Array<News>(),
            pagination: {
                currentPage: 1,
                pageSize: 10,
                totalItem: 1,
                totalPage: 1
            },
            
        };
    },
    methods: {
        async getNews() {
            const params: any = {
                header: {
                    'accept': 'application/ld+json'
                },
                params: {},
                paramsSerializer: qs.stringify
            };
            if (this.pagination.currentPage) {
                params.params['page'] = this.pagination.currentPage;
            }
            if (this.pagination.pageSize) {
                params.params['itemsPerPage'] = this.pagination.pageSize;
            }
            qs.stringify(params.params['order'] = { 'id': 'DESC' });
            await ApiService.get(urlJoin(import.meta.env.VITE_API_URL+'news'), params)
                .then((result) => {
                    this.news = new Array<News>();
                    for (const newsData of result.data['hydra:member']) {
                        this.news.push((new News()).load(newsData));
                    }
                    this.fillEmptyNews();
                    this.displayFirstNews();

                    return result.data;
                })
                .then((data) => {
                    this.pagination.totalItem = data['hydra:totalItems'];
                    this.pagination.totalPage = data['hydra:totalItems'] / this.pagination.pageSize;
                })
                .catch((error) => {
                    console.log(error);
                });
        },
        displayFirstNews() {
            this.news[0].hidden = false;
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
        localeIsFrench() {
            return this.$i18n.locale.split('-')[0] === GameLocales.FR;
        },
        localeIsEnglish() {
            return this.$i18n.locale.split('-')[0] === GameLocales.EN;
        },
        formatDate(date: Date) {
            if (date === null) {
                return '';
            }
            return date.toLocaleDateString(this.$i18n.locale);
        },
        paginationClick(page: number) {
            this.pagination.currentPage = page;
            this.getNews();
        },
        toggleNews(news: News) {
            // do not hide the first news (it's ugly)
            if (news !== this.news[0])
            {
                news.hidden = !news.hidden;
            }
        },
    },
    async mounted() {
        await this.getNews();
    },
});

</script>

<style lang="scss">

.news-container {
    flex-direction: row;
    position: relative;
    max-width: 1080px;
    width: 100%;
    margin: 36px auto;
    padding: 12px 12px 42px 12px;

    @include corner-bezel(18.5px);

    box-shadow: inset 0 0 35px 25px rgba(15, 89, 171, 0.5);
    background-color: rgba(34, 38, 102, 0.5);
}

.janice { width: fit-content; }

.news-feed {
    flex: 1;
    padding: 0.6em;
}

.news {
    border: 1px solid #576077;
    background-color: #222b6b;
    box-shadow: 0px 4px 4px rgba(0, 0, 0, 0.4);
    padding: 1.2em;
    margin: .4em;

    img {
        width: fit-content;
        height: fit-content;
    }

    p { line-height: 1.4em; }

    a { color: #D24781; }

    &:not(:first-child) .title { cursor: pointer; } // for hidden news interaction cue
}

.title {
    display: block;

    img.news-cover {
        float:left;
        width: 48px;
        margin-right: 1.2em;
    }

    h2, p {
        margin: 0.1em;
    }

    h2 {
        font-size: 2.2rem;
        color: #D24781;
    }

    p {
        opacity: 0.85;
        font-size: 0.8em;
        letter-spacing: 0.03em;
    }

    .flag {
        vertical-align: baseline;
        padding-right: 0.4em;
    }
}

.hidden {
    opacity: 0.75;

    .content p { display: none; }
}

.pagination-container {
    flex-direction: row;
    justify-content: center;
    padding: 10px;
}

</style>