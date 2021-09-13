const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('assets/js/admin/admin.js', 'dist/js/wordpress-meilisearch-admin.js')
    .postCss('assets/css/admin/wordpress-meilisearch-admin.css', 'dist/css', [
        //
    ]);
