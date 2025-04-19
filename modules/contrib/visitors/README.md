# Visitors

Visitors is a powerful, native Drupal web analytics tool empowering site builders and administrators with comprehensive insights into user behavior and site performance.

For a full description of the module, visit the
[project page](https://www.drupal.org/project/visitors).

Submit bug reports and feature suggestions, or track changes in the
[issue queue](https://www.drupal.org/project/issues/visitors).


[![pipeline status](https://git.drupalcode.org/project/visitors/badges/8.x-2.x/pipeline.svg)](https://git.drupalcode.org/project/visitors/-/commits/8.x-2.x)


[![coverage report](https://git.drupalcode.org/project/visitors/badges/8.x-2.x/coverage.svg)](https://git.drupalcode.org/project/visitors/-/commits/8.x-2.x)


## Requirements

This module requires the following modules:

- [Charts](https://www.drupal.org/project/charts)



## Installation

Install as you would normally install a contributed Drupal module. For further information, see [Installing Drupal Modules](https://www.drupal.org/docs/extending-drupal/installing-drupal-modules).

### Composer

Composer is the preferred method to install visitors.

<code>composer require 'drupal/visitors:^2'</code>

## Configuration

1. Enable the module at Administration > Extend.
1. Configure **admin/config/system/visitors**


## Development

If you haven't already, [install Docker and DDEV](https://ddev.readthedocs.io/en/latest/users/install/)

<pre>
git clone git@git.drupal.org:project/visitors.git
cd visitors
ddev config --project-type=drupal --docroot=web --php-version=8.3 --corepack-enable --project-name=visitors
ddev add-on get ddev/ddev-drupal-contrib
ddev start
ddev poser
ddev symlink-project
</pre>


## Maintainers

- Steven Ayers - [bluegeek9](https://www.drupal.org/u/bluegeek9)
