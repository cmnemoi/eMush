module.exports = {
  chainWebpack: config => {
      config
          .plugin('html')
          .tap(args => {
              args[0].title = "Mush - Jeu de survie dans l'espace : Vous êtes le seul espoir de l'humanité !";
              return args;
          })
  },
  css: {
      loaderOptions: {
          // pass options to sass-loader
          // @/ is an alias to src/
          // so this assumes you have a file named src/variables.sass
          // Note: this option is named as "prependData" in sass-loader v8
          sass: { 
              prependData: `
                  @import "@/assets/scss/_mixins.scss";
              `
          },
      }
  }
}