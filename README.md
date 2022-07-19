# Модуль перезаписи адресов сайта
##### Проект на стадии разработки
```bash
Перезапись адресов сайта хранящиеся в БД сайта для фрейморка laravel минимум 8 версия
```
## Установка
```bash
composer require avxman/rewrite-url
```
##### Запустить команду
```bash
php artisan vendor:publish --tag="avxman-rewrite-url"
```
##### Закомментировать срочку в файле
```bash
'providers' => [
    ...
    // App\Providers\RouteServiceProvider::class,
    ...
],
```
##### Добавить код в файле Database\Seeders\DatabaseSeeder.php
```bash
    public function run()
    {
        ...
        dump('Заполняем фейковыми данными - '.floor(memory_get_usage()/1024/1024)."МБ");
        $this->call([
            RouteGroupsSeeder::class,
            RoutesSeeder::class,
        ]);
        dump('Завершено заполнение фейковыми данными  - '.floor(memory_get_usage()/1024/1024)."МБ");
        ...
    }
```
