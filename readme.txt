=== Wordpress Meilisearch plugin ===
Contributors: daan, roel, jan-maarten
Donate link: https://september.digital/
Tags: meilisearch, search, search engine
Requires at least: 5.7
Tested up to: 5.7
Stable tag: 1.0
Requires PHP: 7.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Developer-friendly plugin to add Meilisearch and indexing to Wordpress. It's only purpose is to index any posts, custom post types and to update and/or delete these based on actions in the CMS. It also has support for post meta.

== Description ==
Developer-friendly plugin to add Meilisearch and indexing to Wordpress. It's only purpose is to index any posts, custom post types and to update and/or delete these based on actions in the CMS. It also has support for post meta.

The plugin will index any post type selected in the settings after you save or update it.

## Install meilisearch
1. Set up Meilisearch and run it. You can view the install instructions [here](https://github.com/meilisearch/MeiliSearch).
2. Install the meilisearch plugin
3. Fill in the hostname, port and index name. If you do not have an index yet, you can fill in any name. The Meilisearch plugin will create one for you.
4. Click on save and you are good to go!

## Development
1. `composer install`
2. `npm install` and `npm run dev` to compile the assets or `npm run watch` if you want to watch changes

To symlink this repo into a Wordpress project, add the following to your composer.json:

```
"repositories": [
    {
        "type": "path",
        "url": "~/sites/wordpress-meilisearch", //This is the path to the plugin
        "options": {
            "symlink": true
        }
    }
]
```

```
"require": {
    "septemberdigital/wordpress-meilisearch": "*"
}
```

and run `composer update`



## Todo before 1.0 release

- [ ] Logo & branding?
- [ ] Add error handling
- [ ] Clean up code
- [ ] Add build process for dist files
- [ ] Add editor config, phpcs, etc
- [ ] Currently, acf is indexed as a json object.. is that sufficient for using it? Or should they become their own fields in Meilisearch?
- [ ] Feature: add option to configure what to index as a json object
- [ ] Add simple example for frontend
- [ ] Check against [Wordpress plugin rules](https://developer.wordpress.org/plugins/wordpress-org/planning-your-plugin/)
- [ ] Create usefull [readme](https://developer.wordpress.org/plugins/wordpress-org/how-your-readme-txt-works/), with samples and introduction on how to run Meilisearch on production
- [ ] Write a tutorial/blog posts
- [ ] Work on a announcement strategy
- [ ] Is this really open source, and how will we work on github
- [ ] Publish on Wordpress directory
