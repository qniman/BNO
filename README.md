# Архив БНО

Сайт-архив «Бюро нетипичных объектов»: публикация объектов, документов, статей и справочных страниц.

## Как устроено

- **Страница** — единица контента. У неё есть заголовок, адрес, краткое описание, теги, статус (черновик или опубликовано) и история правок.
- **Тип страницы** задаёт раздел сайта (`/obj`, `/doc`…), шаблон вывода и набор своих полей: класс опасности, гриф, дата, ссылки на другие страницы. Типы и поля настраиваются в админке («Регистратура → Типы страниц»).
- **Текст** набирается в блочном редакторе и хранится как JSON (Tiptap). На сервере его рендерит `app/Content/ContentRenderer.php`.
- **Блоки:** цензура █████, раскрывающийся блок, ограниченный доступ, документ-вставка (бланк), врезка, изображение, таблица, автосписок страниц, карточка страницы, сноска.
- **Связи:** ссылки на страницы хранятся по id, поэтому не ломаются при переименовании. На каждой странице выводится блок «Упоминается в материалах».

Шаблоны вывода: `resources/views/pages/layouts/` (object, document, article, info).

## Запуск

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm install
npm run build
php artisan serve
```

Админка: `/admin`. Демо-вход из сидера: `admin@bno.local` / `archive-demo`. На боевом сервере смените его.

Для разработки с пересборкой фронтенда на лету: `composer dev`.

## Развёртывание на сервере

Нужен VPS с **Ubuntu 22.04 или 24.04**: 1 ядро, 1 ГБ памяти, 10 ГБ диска (Timeweb Cloud, Selectel, Aeza и т. п.), плюс домен с A-записью на IP сервера.

**Первая установка** — одна команда на сервере (по SSH под root):

```bash
curl -fsSL https://raw.githubusercontent.com/qniman/BNO/master/deploy/install.sh | sudo bash -s -- --domain archive.example.ru --email you@example.ru --admin you@example.ru
```

Скрипт ставит PHP 8.4, nginx, Composer и Node.js, скачивает сайт в `/var/www/bno`, настраивает базу, получает HTTPS-сертификат и включает файрвол. В конце выводит пароль администратора — сохраните его. Без `--domain` сайт откроется по IP без HTTPS. С `--demo` загрузятся демо-страницы.

**Обновление** вручную:

```bash
sudo bash /var/www/bno/deploy/deploy.sh
```

**Автоматическое обновление** при пуше в `master` — через GitHub Actions (`.github/workflows/deploy.yml`):

1. На своём компьютере создайте ключ: `ssh-keygen -t ed25519 -f bno_deploy -N ""`.
2. Добавьте открытый ключ на сервер: содержимое `bno_deploy.pub` → в конец `/root/.ssh/authorized_keys`.
3. В GitHub: Settings → Secrets and variables → Actions → New repository secret:
   - `SSH_HOST` — IP или домен сервера;
   - `SSH_USER` — `root`;
   - `SSH_KEY` — содержимое закрытого ключа `bno_deploy`;
   - `SSH_PORT` — если SSH не на 22-м порту.

После этого каждый пуш в `master` прогоняет тесты и обновляет сайт. Пока секреты не заданы, выполняются только тесты.

Новый администратор или смена пароля: `cd /var/www/bno && sudo -u www-data php artisan bno:admin почта@пример.ру`.

## iOS-приложение

В папке [`ios/`](ios/README.md) — приложение-оболочка для iPhone с настройкой адреса сервера. Собирается в GitHub Actions без Mac.

## Тесты

```bash
php artisan test
```
