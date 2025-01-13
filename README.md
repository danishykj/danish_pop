# Danish Pop Module

This module offers the ability to use the Spotify api to showcase Artists on your Drupal 11 website.

1. There is a configuration form to configure the Spotify endpoints which allows you to configure the api details and also to test the api if its working fine or not. There is a menu item whose url is admin/config/danish_pop
2. There is another form and menu item to add artists individually(upto 20) on the url admin/config/artist_add
3. A custom block which displays the list of artists on the application.
4. A cron job to bring the artists from api to the application database.

This module facilitates talking to the Spotify API for you, but be warned, as with any third party API if you abuse 
your access you can get cut off without warning. Make sure to obey Spotify's terms of use regarding their APIs.

## Requirements

This module requires only ultimate_cron module which needs to be installed before installing it. The ultimate cron is used to create a cronjob which excutes a custom function to import list of artists from the api to the drupal backend.

## Installation (required, unless a separate INSTALL.md is provided)

This module requires the latest drupal 11 installation https://www.drupal.org/project/drupal/releases/11.0.0

Before installing install the required module ultimate_cron with this command "composer require 'drupal/ultimate_cron:^2.0@beta' "

Install as you would normally install a contributed Drupal module. For further information, 
see [Installing Drupal Modules](https://www.drupal.org/docs/extending-drupal/installing-drupal-modules).

## Configuration

Enable the module at Administration > Extend.

Create a developer account at https://developer.spotify.com/.

After installing the module(ultimate_cron and danish_pop) on your site, navigate to the settings page. 

You will then create a new application on the Spotify developer site. You will mainly use the Web API. 

On this configuration form admin/config/danish_pop, Copy the client secret and client ID from your Spotify app into Drupal and save. Also make sure to have the auth url and api url copied in the configuration form.

You can test the endpoint by hitting the `/danish_pop`. If the api is working fine you will get the success message.

Once the api is working correctly go to the cron settings page admin/config/system/cron/jobs and run the cronjob with the title "Adding the artist data". This will import the default artists on your application which should be visible on the homepage. On click on individual content you will be able to see the artist detail page(Not visible to anonymous user) which is the extension of node twig file.

All the configuration entities are placed in the install folder which will be installed after the module is successfully installed.

## System Requirment
Drupal 11
php:8.3