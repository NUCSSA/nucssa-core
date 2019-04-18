const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management (this is borrowed from Laravel)
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.sass('assets/scss/admin.scss', 'public/css')
   .react('assets/js/admin.js', 'public/js')
   .copyDirectory('assets/images/', 'public/images/')
   .copyDirectory('assets/fonts/', 'public/fonts/')
   .browserSync({
     proxy: "wp.localhost",
     files: [ '*.php', 'lib/', 'config/', 'public/'],
     open: false,
     ghostMode: false,
   })
   .options({
     processCssUrls: false,
   });