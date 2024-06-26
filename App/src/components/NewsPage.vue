<template>
    <div class="news-container">
        <div class="janice">
            <img :src="getImgUrl('janice.png')" alt="Janice">
        </div>
        <div class="news-feed">
            <NewsItem
                v-for="item in news"
                :news="item"
                @click="toggleNews(item)"
                :key="item.id"/>
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
import { News } from "@/entities/News";
import Pagination from "@/components/Utils/Datatable/Pagination.vue";
import NewsItem from "./NewsItem.vue";
import { getImgUrl } from "@/utils/getImgUrl";

export default defineComponent ({
    name: 'TheEnd',
    components: {
        Pagination,
        NewsItem
    },
    props: {
        numberOfNews: {
            type: Number,
            required: false
        }
    },
    data() {
        return {
            news: new Array<News>(),
            pagination: {
                currentPage: 1,
                pageSize: 10,
                totalItem: 1,
                totalPage: 1
            }

        };
    },
    methods: {
        getImgUrl,
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
            if (this.numberOfNews) {
                params.params['itemsPerPage'] = this.numberOfNews;
            }
            params.params['isPublished'] = true;
            qs.stringify(params.params['order'] = { ['publicationDate']: 'DESC' });

            await ApiService.get(urlJoin(import.meta.env.VITE_APP_API_URL+'news'), params)
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
                    console.error(error);
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
        }
    },
    async mounted() {
        await this.getNews();
    }
});

</script>

<style lang="scss" scoped>

.news-container {
    flex-direction: row;
    position: relative;
    max-width: $breakpoint-desktop-l;
    width: 100%;
    margin: 36px auto;
    padding: 12px 12px 42px 12px;

    @include corner-bezel(18.5px);

    box-shadow: inset 0 0 35px 25px rgba(15, 89, 171, 0.5);
    background-color: rgba(34, 38, 102, 0.5);
}

.janice {
    width: fit-content;
    max-width: 25%;
    margin-right: -2em;
    z-index: 5;
}

.news-feed {
    flex: 1;
    padding: 0.6em;
}

.pagination-container {
    flex-direction: row;
    justify-content: center;
    padding: 10px;
}

@media screen and (max-width: $breakpoint-mobile-l) {

    .janice { display: none; }
}

</style>
