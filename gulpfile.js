// Turn off the gulp-notify
process.env.DISABLE_NOTIFIER = true;

var elixir = require('laravel-elixir');
var gulp = require('gulp');

var paths = {
    'publicJS': './public/js',
    'publicCSS': './public/css',
    'resJS': './resources/assets/js',
    'resCSS': './resources/assets/css',
    'bootstrap': './bower_components/bootstrap/dist',
    'angular': './resources/assets/angular',
    'ngFileUpload': './bower_components/ng-file-upload',
    'npm': './node_modules'
};

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Sass
 | file for our application, as well as publishing vendor resources.
 |
 */

elixir(function(mix) {
    //mix.phpUnit();

    /**
     * Copy bower js and css to the laravel resource asset dir
     */
    mix.copy([
            paths.ngFileUpload +'/ng-file-upload.min.js',
            paths.ngFileUpload +'/ng-file-upload-shim.min.js',
            paths.npm +'/please-wait/build/please-wait.min.js',
            paths.npm +'/selectize/dist/js/standalone/selectize.min.js',
            paths.npm +'/intro.js/minified/intro.min.js',
        ], paths.resJS)
        .copy(paths.angular +'/views', './public')
        .copy([
            paths.npm +'/please-wait/build/please-wait.css',
            paths.npm +'/spinkit/css/spinkit.css',
            paths.npm +'/selectize/dist/css/selectize.bootstrap3.css',
            paths.npm +'/intro.js/minified/introjs.min.css',
        ], paths.resCSS);

    // concat scripts
    mix.scriptsIn(paths.angular, paths.publicJS +'/app.js')
        .scriptsIn(paths.resJS);

    // Compile sass and less
    // z_index. file name prefix is last ordering on concat below.
    mix.sass('app.scss', paths.resCSS +'/z_index.app.scss.css')
       .sass('album.scss', paths.resCSS +'/z_index.album.css');

    // concat styles
    // https://github.com/laravel/elixir/blob/master/tasks/styles.js
    mix.stylesIn(paths.resCSS);

    // Versioning
    mix.version(['css/all.css', 'js/all.js', 'js/app.js']);

    /**
     * https://www.npmjs.com/package/laravel-elixir-browsersync
     * https://github.com/anheru88/laravel-elixir-browser-sync
     * Enable CORS in BrowserSync https://hondo.wtf/posts/enable-cors-in-browsersync/
     */
    mix.browserSync({
        proxy: 'console.motorgraph.local'
    });
});
