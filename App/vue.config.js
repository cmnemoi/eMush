module.exports = {
    chainWebpack: config => {config.plugin('html');},
    css: {
        loaderOptions: {
            // pass options to sass-loader
            // @/ is an alias to src/
            // so this assumes you have a file named src/variables.sass
            // Note: this option is named as "additionalData" in sass-loader v9
            sass: {
                additionalData: `
                    @import "@/assets/scss/_variables.scss";
                    @import "@/assets/scss/_placeholders.scss";
                    @import "@/assets/scss/_mixins.scss";
                `
            }
        }
    }
};
