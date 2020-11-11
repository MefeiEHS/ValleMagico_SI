const mix = require("laravel-mix");
const dotenv = require("dotenv");

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.react("resources/js/app.js", "public/js")
    .sass("resources/sass/app.scss", "public/css")
    .sourceMaps();

// call dotenv and it will return an Object with a parsed key
const env = dotenv.config().parsed;
// reduce it to a nice object, the same as before
const envKeys = Object.keys(env).reduce((prev, next) => {
    prev[`process.env.${next}`] = JSON.stringify(env[next]);
    return prev;
}, {});

mix.webpackConfig(webpack => {
    return {
        output: {
            publicPath: "/valle/si/"
        },
        plugins: [new webpack.DefinePlugin(envKeys)]
    };
});
