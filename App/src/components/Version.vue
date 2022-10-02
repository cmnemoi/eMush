<template>
    <div class="footer">
        <div class="version_wrapper">
            <span
                class="text"
            ><svg
                 class="triangle"
                 aria-hidden="true"
                 focusable="false"
                 data-prefix="fa"
                 data-icon="exclamation-triangle"
                 role="img"
                 xmlns="http://www.w3.org/2000/svg"
                 viewBox="0 0 576 512"
                 data-fa-i2svg=""
             >
                 <path
                     fill="currentColor"
                     d="M569.517 440.013C587.975 472.007 564.806 512 527.94 512H48.054c-36.937 0-59.999-40.055-41.577-71.987L246.423 23.985c18.467-32.009 64.72-31.951 83.154 0l239.94 416.028zM288 354c-25.405 0-46 20.595-46 46s20.595 46 46 46 46-20.595 46-46-20.595-46-46-46zm-43.673-165.346l7.418 136c.347 6.364 5.609 11.346 11.982 11.346h48.546c6.373 0 11.635-4.982 11.982-11.346l7.418-136c.375-6.874-5.098-12.654-11.982-12.654h-63.383c-6.884 0-12.356 5.78-11.981 12.654z"
                 ></path>
             </svg>
                <b>Alpha Version</b><br />
                {{ $t('alpha.issue') }}
                <a href="https://discord.gg/Jb8Nwjck6r" target="_blank">Discord</a>
            </span>
            <div class="versionId">
                <div>
                    <svg
                        style="height: 11px; width: 11px"
                        aria-hidden="true"
                        focusable="false"
                        data-prefix="fas"
                        data-icon="globe-europe"
                        role="img"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 496 512"
                        data-fa-i2svg=""
                    >
                        <path
                            fill="currentColor"
                            d="M248 8C111 8 0 119 0 256s111 248 248 248 248-111 248-248S385 8 248 8zm200 248c0 22.5-3.9 44.2-10.8 64.4h-20.3c-4.3 0-8.4-1.7-11.4-4.8l-32-32.6c-4.5-4.6-4.5-12.1.1-16.7l12.5-12.5v-8.7c0-3-1.2-5.9-3.3-8l-9.4-9.4c-2.1-2.1-5-3.3-8-3.3h-16c-6.2 0-11.3-5.1-11.3-11.3 0-3 1.2-5.9 3.3-8l9.4-9.4c2.1-2.1 5-3.3 8-3.3h32c6.2 0 11.3-5.1 11.3-11.3v-9.4c0-6.2-5.1-11.3-11.3-11.3h-36.7c-8.8 0-16 7.2-16 16v4.5c0 6.9-4.4 13-10.9 15.2l-31.6 10.5c-3.3 1.1-5.5 4.1-5.5 7.6v2.2c0 4.4-3.6 8-8 8h-16c-4.4 0-8-3.6-8-8s-3.6-8-8-8H247c-3 0-5.8 1.7-7.2 4.4l-9.4 18.7c-2.7 5.4-8.2 8.8-14.3 8.8H194c-8.8 0-16-7.2-16-16V199c0-4.2 1.7-8.3 4.7-11.3l20.1-20.1c4.6-4.6 7.2-10.9 7.2-17.5 0-3.4 2.2-6.5 5.5-7.6l40-13.3c1.7-.6 3.2-1.5 4.4-2.7l26.8-26.8c2.1-2.1 3.3-5 3.3-8 0-6.2-5.1-11.3-11.3-11.3H258l-16 16v8c0 4.4-3.6 8-8 8h-16c-4.4 0-8-3.6-8-8v-20c0-2.5 1.2-4.9 3.2-6.4l28.9-21.7c1.9-.1 3.8-.3 5.7-.3C358.3 56 448 145.7 448 256zM130.1 149.1c0-3 1.2-5.9 3.3-8l25.4-25.4c2.1-2.1 5-3.3 8-3.3 6.2 0 11.3 5.1 11.3 11.3v16c0 3-1.2 5.9-3.3 8l-9.4 9.4c-2.1 2.1-5 3.3-8 3.3h-16c-6.2 0-11.3-5.1-11.3-11.3zm128 306.4v-7.1c0-8.8-7.2-16-16-16h-20.2c-10.8 0-26.7-5.3-35.4-11.8l-22.2-16.7c-11.5-8.6-18.2-22.1-18.2-36.4v-23.9c0-16 8.4-30.8 22.1-39l42.9-25.7c7.1-4.2 15.2-6.5 23.4-6.5h31.2c10.9 0 21.4 3.9 29.6 10.9l43.2 37.1h18.3c8.5 0 16.6 3.4 22.6 9.4l17.3 17.3c3.4 3.4 8.1 5.3 12.9 5.3H423c-32.4 58.9-93.8 99.5-164.9 103.1z"
                        ></path>
                    </svg>
                    <a
                        style="text-decoration: none; margin-left: 4px"
                        :href="`https://gitlab.com/eternaltwin/mush/mush/-/tags/${version}`"
                        target="_blank"
                    >
                        {{ version }}
                    </a>
                </div>
                <div>
                    <svg
                        style="height: 11px; width: 11px"
                        aria-hidden="true"
                        focusable="false"
                        data-prefix="fas"
                        data-icon="code-branch"
                        role="img"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 384 512"
                        data-fa-i2svg=""
                    >
                        <path
                            fill="currentColor"
                            d="M384 144c0-44.2-35.8-80-80-80s-80 35.8-80 80c0 36.4 24.3 67.1 57.5 76.8-.6 16.1-4.2 28.5-11 36.9-15.4 19.2-49.3 22.4-85.2 25.7-28.2 2.6-57.4 5.4-81.3 16.9v-144c32.5-10.2 56-40.5 56-76.3 0-44.2-35.8-80-80-80S0 35.8 0 80c0 35.8 23.5 66.1 56 76.3v199.3C23.5 365.9 0 396.2 0 432c0 44.2 35.8 80 80 80s80-35.8 80-80c0-34-21.2-63.1-51.2-74.6 3.1-5.2 7.8-9.8 14.9-13.4 16.2-8.2 40.4-10.4 66.1-12.8 42.2-3.9 90-8.4 118.2-43.4 14-17.4 21.1-39.8 21.6-67.9 31.6-10.8 54.4-40.7 54.4-75.9zM80 64c8.8 0 16 7.2 16 16s-7.2 16-16 16-16-7.2-16-16 7.2-16 16-16zm0 384c-8.8 0-16-7.2-16-16s7.2-16 16-16 16 7.2 16 16-7.2 16-16 16zm224-320c8.8 0 16 7.2 16 16s-7.2 16-16 16-16-7.2-16-16 7.2-16 16-16z"
                        ></path>
                    </svg>
                    <a
                        style="text-decoration: none; margin-left: 4px"
                        :href="`https://gitlab.com/eternaltwin/mush/mush/-/commit/${release}`"
                        target="_blank"
                    >
                        {{ release.substring(0, 8) }}
                    </a>
                </div>
                <div>
                    <svg
                        style="height: 11px; width: 11px"
                        aria-hidden="true"
                        focusable="false"
                        data-prefix="fa"
                        data-icon="flask"
                        role="img"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 448 512"
                        data-fa-i2svg=""
                    >
                        <path
                            fill="currentColor"
                            d="M437.2 403.5L320 215V64h8c13.3 0 24-10.7 24-24V24c0-13.3-10.7-24-24-24H120c-13.3 0-24 10.7-24 24v16c0 13.3 10.7 24 24 24h8v151L10.8 403.5C-18.5 450.6 15.3 512 70.9 512h306.2c55.7 0 89.4-61.5 60.1-108.5zM137.9 320l48.2-77.6c3.7-5.2 5.8-11.6 5.8-18.4V64h64v160c0 6.9 2.2 13.2 5.8 18.4l48.2 77.6h-172z"
                        ></path>
                    </svg>
                    {{ channel }}
                </div>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { version } from '../../package.json';

export default defineComponent({
    name: 'Version',
    data() {
        return {
            version: version as string,
            release: process.env.VUE_APP_API_RELEASE_COMMIT as string,
            channel: process.env.VUE_APP_API_RELEASE_CHANNEL as string
        };
    }
});
</script>

<style lang="scss" scoped>
.footer {
	box-sizing: border-box;
	color: rgb(250, 227, 206);
	display: block;
	float: left;
	font-family: Nunito, Century Gothic, Arial, Trebuchet MS, Verdana, Open Sans, sans-serif;
	font-size: 11px;
	position: fixed;
	bottom: 10px;
	right: 10px;
	width: 395.833px;
	z-index: 10;
}
.version_wrapper {
	align-items: center;
	background-attachment: scroll;
	background-clip: border-box;
	background-color: rgb(117, 0, 0);
	background-image: none;
	background-origin: padding-box;
	background-position-x: 0%;
	background-position-y: 0%;
	background-repeat: repeat;
	background-size: auto;
	border-bottom-color: rgb(255, 0, 0);
	border-bottom-style: solid;
	border-bottom-width: 1px;
	border-image-repeat: stretch;
	border-image-source: none;
	border-left-color: rgb(255, 0, 0);
	border-left-style: solid;
	border-left-width: 1px;
	border-right-color: rgb(255, 0, 0);
	border-right-style: solid;
	border-right-width: 1px;
	border-top-color: rgb(255, 0, 0);
	border-top-style: solid;
	border-top-width: 1px;
	box-shadow: rgb(0, 0, 0) 0px 0px 3px 0px;
	box-sizing: border-box;
	color: rgb(255, 255, 255);
	display: flex;
	flex-direction: row;
	flex-wrap: nowrap;
	font-family: Nunito, Century Gothic, Arial, Trebuchet MS, Verdana, Open Sans, sans-serif;
	font-size: 11px;
	margin-bottom: 2px;
	margin-left: 10px;
	margin-right: 10px;
	margin-top: 2px;
	outline-color: rgb(0, 0, 0);
	outline-style: solid;
	outline-width: 1px;
	padding-bottom: 3px;
	padding-left: 3px;
	padding-right: 3px;
	padding-top: 3px;
}
.triangle {
	width: 11px;
	height: 11px;
	margin-right: 5px;
	margin-top: -15px;
}
.versionId {
	flex-grow: 1;
	div {
		flex-direction: initial;
		margin: 1px;
	}
}
.text {
	flex-grow: 2;
}
</style>
