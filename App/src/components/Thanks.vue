<template>
    <div class="footer">
        <div class="wrapper">
            <strong>{{ $t('footer.emush') }}</strong>
            <div class="content">
                <i18n-t keypath="footer.text1" tag="div" class="text">
                    <template #mush>
                        <a style="white-space: nowrap" href="http://www.mush.vg/" target="_blank"> Mush </a>
                    </template>
                </i18n-t>
                <i18n-t keypath="footer.text2" tag="div" class="text">
                    <template #Gitlab>
                        <a style="white-space: nowrap" href="https://gitlab.com/eternaltwin/mush/mush/" target="_blank"> GitLab </a>
                    </template>
                </i18n-t>
                <i18n-t keypath="footer.text3" tag="div" class="text">
                    <template #EternalTwin>
                        <a style="white-space: nowrap" href="https://eternal-twin.net/" target="_blank"> Eternaltwin </a>
                    </template>
                    <template #OpenCollective>
                        <a style="white-space: nowrap" href="https://opencollective.com/eternaltwin" target="_blank"> OpenCollective </a>
                    </template>
                </i18n-t>
            </div>
        </div>
        <div class="wrapper">
            <strong>{{ $t('footer.developpers') }}</strong>
            <div class="content">
                <p v-for="dev in randomDev" :key="dev">{{ dev }}</p>
            </div>
        </div>
        <div class="wrapper">
            <strong>{{ $t('footer.thanks') }}</strong>
            <div class="content">
                <p v-for="helper in randomHelpers" :key="helper">{{ helper }}</p>
                <!-- <i18n-t keypath="footer.alpha-testers" tag="div" class="text">
                    <template #alpha-testers>
                        <p>alpha-testers</p>
                    </template>
                </i18n-t> -->
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { developers, helpers } from '@/enums/thanks';

export default defineComponent({
    name: 'Thanks',
    data() {
        return {
            developers: developers,
            randomDev: [] as Array<string>,
            helpers: helpers,
            randomHelpers: [] as Array<string>
        };
    },
    mounted() {
        this.randomDev = this.developers.sort(() => 0.5 - Math.random());
        this.randomHelpers = this.helpers.sort(() => 0.5 - Math.random());
    }
});
</script>

<style lang="scss" scoped>
.text {
	text-align: justify;
	padding: 0.5em;
}
.wrapper {
	font-family: Nunito, Century Gothic, Arial, Trebuchet MS, Verdana, Open Sans, sans-serif;
	background: rgb(34, 38, 102, 0.5);
	margin-bottom: 5px;
	font-size: 11px;
	font-variant: small-caps;
	padding: 0 0 5px;
	text-align: center;
	width: 29%;
	height: fit-content;
	color: grey;
	text-shadow: #8e3e26;
	& strong {
		display: block;
		background: rgba(0, 0, 0, 0.5);
	}
}
.footer {
	box-sizing: border-box;
	margin-top: 15px;
	color: rgb(250, 227, 206);
	justify-content: space-around;
	display: flex;
	float: left;
	font-family: Nunito, Century Gothic, Arial, Trebuchet MS, Verdana, Open Sans, sans-serif;
	font-size: 11px;
	position: relative;
	top: -40px;
	bottom: 10px;
	left: 10px;
	width: 98%;
    flex-direction: initial;
}
.content {
    p{
        margin: 0.2rem;
        a {
            white-space: nowrap;
        }
    }
}
</style>
