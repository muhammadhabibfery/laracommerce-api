<h1 align="center">
LaraCommerce  REST API
</h1>

<h5 align="center">
REST API for E-Commerce platform with admin panel integration.
</h5>

<p align="center">
    <a href="https://github.com/muhammadhabibfery/laracommerce-api/actions/workflows/ci.yml">
    <img alt="GitHub Workflow Status" src="https://img.shields.io/github/actions/workflow/status/muhammadhabibfery/laracommerce-api/ci.yml?logo=github">
    <a href="https://www.php.net">
        <img src="https://img.shields.io/badge/php-%3E%3D8.1-%23777BB4" />
    </a>
    <a href="https://laravel.com">
        <img src="https://img.shields.io/badge/laravel-9.x-%23EC4E3D" />
    </a>
</p>

</br>

| [Admin Panel Features][] | [Requirements][] | [Install][] | [How to setting][] | [API Docs][] | [License][] |

## Admin Panel Features 
- <img src="public/images/admin-panel.png" alt="Preview" width="75%"/>

- |<h3>Menu  </h3>       |       Description                                                                  |
  |-----------------------|-----------------------------------------------------------------------------------|
  |<b>Admin               | </b>Create employee and manage all users.                                         |
  |<b>Finance             | </b>Manage the finances.                                                          |
  |<b>Withdraw            | </b>Manage the merchant's withdraw request.                                                 |
  |<b>Orders              | </b>Manage about all orders.                                                      |
  |<b>Profile             | </b>Edit user's profile and password.                                             |


## Requirements

	PHP = ^7.4
    laravel = ^7.0
    laravel/sanctum = ^2.15
    laravel/ui = ^2.1
    kavist/rajaongkir = ^1.1
    midtrans/midtrans-php = ^2.5

## Install

Clone repo

```
git clone https://github.com/muhammadhabibfery/LaraBooks-API.git
```

Install Composer


[Download Composer](https://getcomposer.org/download/)


composer update/install 

```
composer install
```

Install Nodejs


[Download Node.js](https://nodejs.org/en/download/)


NPM dependencies
```
npm install
```

Using Laravel Mix 

```
npm run dev
```

## How to setting 

Go into .env file change Database and Email credentials. Then setup the rajaongkir and midtrans configuration with your own credentials
```
RAJAONGKIR_API_KEY=<Your-API-Key>

MIDTRANS_SERVER_KEY = <Your-Server-Key>
MIDTRANS_PRODUCTION = false
MIDTRANS_SANITIZED = true
MIDTRANS_3DS = true|false
```

Run the migration

```
php artisan migrate
```

Or run the migration with seeder if you want seeding the related data

```
php artisan migrate --seed
```

Generate a New Application Key

```
php artisan key:generate
```

Create a symbolic link

```
php artisan storage:link
```

## API Docs
<img src="public/images/LaraBooks-API.png" alt="Preview" width="60%"/>
</br>
<p style="font-weight: bold;">
Complete REST API Documentation can be found <a href="https://documenter.getpostman.com/view/25234064/2s8Z75TqXU">here</a>
</p>


## License

> Copyright (C) 2023 Muhammad Habib Fery.  
**[⬆ back to top](#larabooks-rest-api)**

[Admin Panel Features]:#admin-panel-features
[Requirements]:#requirements
[Install]:#install
[How to setting]:#how-to-setting
[API Docs]:#api-docs
[License]:#license


<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400"></a></p>

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/d/total.svg" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/v/stable.svg" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/license.svg" alt="License"></a>
</p>
