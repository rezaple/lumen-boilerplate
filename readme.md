# [Lumen 5.8 Boilerplate]

A boilerplate made from [Lumen 5.8.*](https://lumen.laravel.com/), authenticated with [laravel/passport](https://github.com/laravel/passport)



### Installing

- after cloning/downloding this repo, first open to terminal then change directory to a project directory.
- sample, in linux, `cd lumen-boilerplate`.
- run `composer install` to install project dependencies.
- copy `.env.example` to `.env`. (dont just rename it), for team reference purpose.
- prepare you environment in `.env`
- run `php artisan key:generate`, this will generate key in .env file
- run `php artisan migrate` and `php artisan db:seed`, this will migrate your database and example data user, 
- run `php artisan passport:install` to install credential laravel passport.
- if you not familiar with  [laravel/passport](https://github.com/laravel/passport), you must see  [this](https://github.com/laravel/passport) first.
- how to login? see [laravel/passport](https://github.com/laravel/passport)


## License

This project is licensed under the MIT License 
