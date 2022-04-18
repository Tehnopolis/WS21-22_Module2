# WS21-22_Module1
Второй модуль по тестовому заданию WorldSkills Russia

# Запуск

1. Поднять БД локально из дампа `db/dump.sql`
2. Прописать данные БД в `lib/Database.php`
3. Запустить сервер: `php -S localhost:8088`

# Прогресс
- [X] Authorization
- [X] Roles (Admin, Waiter, Cook)
- [X] Users
    - [X] Create (Admin)
- [X] Workshifts
    - [X] Get all (Admin)
    - [X] View (Admin)
    - [X] Create (Admin)
    - [X] Assign user (Admin)
- [X] Orders
    - [X] Get all (Waiter)
    - [X] View (Waiter)
    - [X] Create order (Waiter)
    - [X] Change order products (Waiter)
    - [X] Change order status (Waiter, Cook)