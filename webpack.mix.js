const mix = require('laravel-mix');
const tailwindcss = require('tailwindcss')('./tailwind.config.js');

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
/*  eslint-disable indent */
mix.webpackConfig({
  externals: {
    '@wordpress/element': ['wp', 'element'],
    '@wordpress/plugins': ['wp', 'plugins'],
    '@wordpress/blocks': ['wp', 'blocks'],
    '@wordpress/edit-post': ['wp', 'editPost'],
    '@wordpress/i18n': ['wp', 'i18n'],
    '@wordpress/editor': ['wp', 'editor'],
    '@wordpress/components': ['wp', 'components'],
    '@wordpress/blob': ['wp', 'blob'],
    '@wordpress/data': ['wp', 'data'],
    '@wordpress/html-entities': ['wp', 'htmlEntities'],
    '@wordpress/compose': ['wp', 'compose'],
  }
})
.sass('assets/scss/admin-plugin-page.scss', 'public/css')
.sass('assets/scss/admin-global.scss', 'public/css')
.sass('assets/scss/editor.scss', 'public/css')
.react('assets/js/admin.js', 'public/js')
.js('assets/js/editor.js', 'public/js')
.copyDirectory('assets/images/', 'public/images/')
.browserSync({
  proxy: 'wp.localhost',
  files: [ '*.php', 'lib/', 'config/', 'public/'],
  open: false,
  ghostMode: false,
})
.options({
  processCssUrls: false,
  postCss: [tailwindcss],
})
.sourceMaps(false, 'eval-source-map');
