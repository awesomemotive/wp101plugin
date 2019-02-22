# Contributing to the development of the WP101 Plugin

The WP101 Plugin is the client-facing end of [the WP101 Plugin SaaS](https://app.wp101plugin.com) and is used to present video training right within WordPress.

## Installing dependencies

After cloning the repository, development dependencies are installed via [npm](https://npmjs.com) and [Composer](https://getcomposer.org):

```sh
# Install JavaScript dependencies
$ npm install

# Install PHP dependencies
$ composer install
```

## Coding standards

This plugin uses [the WordPress coding standards](https://make.wordpress.org/core/handbook/best-practices/coding-standards/php/), which are enforced via [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer).

## Compiling assets

Front-end assets (CSS and JavaScript) are light in the plugin, but are compiled and concatenated via [Grunt]:

```sh
# Compile assets.
$ grunt
```

## Running tests

Tests for the plugin are written using [the WordPress core test suite](https://make.wordpress.org/core/handbook/testing/automated-testing/phpunit/), and are run automatically as part of the plugin's Continuous Integration (CI) pipeline.

When contributing code, please include appropriate tests!

## Building a release

When the plugin is ready for release, we use [Grunt] to build our release:

```sh
$ grunt build
```

This will compile all assets, then copy the files necessary for inclusion into a `dist/` directory. This directory can be copied directly into the `trunk` directory of the WordPress plugin's Subversion repo:

```sh
cp -r dist/* /path/to/svn/wp101/trunk
```

[Grunt]: https://gruntjs.com/
