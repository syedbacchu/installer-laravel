# installer-laravel | A Simple Laravel Project Installer with verify envato purchase licence

[![Latest Version](https://img.shields.io/github/release/syedbacchu/installer-laravel.svg?style=flat-square)](https://github.com/syedbacchu/installer-laravel/releases)
[![Issues](https://img.shields.io/github/issues/syedbacchu/installer-laravel.svg?style=flat-square)](https://github.com/syedbacchu/installer-laravel)
[![Stars](https://img.shields.io/github/stars/syedbacchu/installer-laravel.svg?style=social)](https://github.com/syedbacchu/installer-laravel)
[![Stars](https://img.shields.io/github/forks/syedbacchu/installer-laravel?style=flat-square)](https://github.com/syedbacchu/installer-laravel)
[![Total Downloads](https://img.shields.io/packagist/dt/sdtech/project-installer.svg?style=flat-square)](https://packagist.org/packages/sdtech/project-installer)

- [About](#about)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Uses](#Uses)

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

## configuration
1. Go to your config folder, then open "installer.php" file
2. Search here "env_path", under this change the "env_token" => 'change this value and set it your codecanyon token' .
3. 
 ``` bash
'env_path' => [
   'env_token' => 'your envato token here',
   'env_url_path' => 'https://api.envato.com/v1/market/private/user/verify-purchase:'
   ]
   ```
5. Another thing , verify purchase key is not mandatory, you can also manage this from config file->
6. 
```bash
'checkPurchaseCode' => true, 
```
 [true means verify purchase key mandatory, and false means not mandatory]

## Uses
1. Make a middleware and inside the middleware the code look like ->
```bash
public function handle(Request $request, Closure $next)
    {
        if (file_exists(storage_path('installed'))) {
            return $next($request);
        }

        return redirect(url('install'));
    }
```
2. Then add the middleware name to your route.

That's it :-)