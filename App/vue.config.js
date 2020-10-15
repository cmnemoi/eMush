//vue.config.js
module.exports = {
    chainWebpack: config => {
        config
            .plugin('html')
            .tap(args => {
                args[0].title = "Mush - Jeu de survie dans l'espace : Vous êtes le seul espoir de l'humanité !";
                return args;
            })
    }
}