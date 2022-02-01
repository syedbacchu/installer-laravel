# installer-laravel | A Simple Laravel Project Installer with verify envato purchase licence

- [About](#about)
- [Requirements](#requirements)
- [Installation](#installation)

## About

A simple laravel project installer with verify envato product licence which gives you a nice interface to setup your project, verify product, database and other configuration just by following the setup wizard.
The current features are :

- Check For Server Requirements.
- Check For Folders Permissions.
- Verify Envato purchase licence.
- Ability to set database information with a simple form wizard.
- Migrate The Database.
- Seed The Tables.

## Requirements

* [Laravel 5.5+](https://laravel.com/docs/installation)

## Installation
1. From your projects root folder in terminal run:

```bash
    composer require sdtech/project-installer
```
2. Publish the packages views, config file, assets, and language files by running the following from your projects root folder:

```bash
    php artisan vendor:publish --tag=projectinstaller
```