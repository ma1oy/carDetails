# HOW TO USE?

- Сначало надо прописать настройки в php.ini:

    `disable_functions` - рекомендуеться сделать пустой
    
    `max_execution_time = 0`
    
    `max_input_time = -1`
    
    `memory_limit = -1`
    
    `post_max_size = 8G`
    
    `upload_max_file_size = 2G`
    
    `allow_url_fopen = On`
    
    `default_socket_timeout = 60`
    
    `cli.server_color = On`

- Создать базу данных с именем `carDetails`

- Создать таблицы с помощью `query.sql`

- Скачать парсер, в родительской папке открыть консоль и набрать: `php index.php`

- Ждать :)

P. S. Для парсинга можно использовать [VPS][https://ru.wikipedia.org/wiki/VPS]