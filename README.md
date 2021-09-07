# Wordpress Meilisearch plugin

## Introduction

Developer-friendly plugin to add Meilisearch and indexing to Wordpress. It's only purpose is to index any custom posts and to update and/or delete these based on actions in the CMS. It has basic support for indexing ACF fields as well.

## Development
To symlink this repo into a Wordpress project, add the following to composer.json:

```
"repositories": [
    {
        "type": "path",
        "url": "~/sites/wordpress-meilisearch",
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

## Install meilisearch
Install meilisearch, don't set a masterkey (yet) for easiest development.
You can view the install instructions [here](https://github.com/meilisearch/MeiliSearch).

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
