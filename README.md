# LostOnCampus

LostOnCampus is a campus lost-and-found board built for the Gachon University AI-assisted web project. The service lets users register lost or found items, browse recent posts, search by keyword, edit or delete posts with a password, and submit inquiries.

## Project Status

This repository contains the MVP implementation and planning documents completed so far.

- Project topic confirmed: campus lost-and-found board
- Naver Cloud server and Cloud DB for MySQL prepared
- Apache + PHP + MySQL runtime configured on Ubuntu 24.04
- MySQL tables designed for `lost_items` and `inquiries`
- PHP MVP implemented with CRUD, search, inquiry submission, and validation
- Error handling added for empty input, wrong passwords, invalid IDs, DB failures, and invalid requests
- Apache deployment tested with local HTTP responses

## Tech Stack

| Area | Stack |
|---|---|
| Cloud | Naver Cloud Platform |
| Web Server | Apache 2.4 |
| Server Language | PHP 8 |
| Database | Cloud DB for MySQL 8.4 |
| Frontend | HTML, CSS, JavaScript |
| OS | Ubuntu 24.04 LTS |

## Features

| Feature | Description |
|---|---|
| Main page | Shows service purpose, search box, and recent posts |
| Lost/found board | Lists registered lost and found items |
| Post detail | Shows item type, title, location, content, contact, and dates |
| Create post | Saves a lost/found item to MySQL |
| Edit post | Updates a post after password verification |
| Delete post | Deletes a post after password verification |
| Search | Searches title, content, and location |
| Inquiry | Saves user inquiries or reports |
| Validation | Shows user-friendly validation messages |
| Error handling | Hides internal DB/server details from users |

## Directory Structure

```text
.
├── api/
│   ├── inquiry_action.php
│   └── item_action.php
├── config/
│   └── db.php
├── css/
│   └── style.css
├── includes/
│   ├── footer.php
│   ├── functions.php
│   └── header.php
├── js/
│   └── main.js
├── pages/
│   ├── inquiry.php
│   ├── item_edit.php
│   ├── item_list.php
│   ├── item_view.php
│   ├── item_write.php
│   └── search.php
├── sql/
│   └── schema.sql
├── index.php
├── 기획문서.md
├── 기능명세서.md
└── 실행가이드.md
```

## Database

The MVP uses two tables:

- `lost_items`: lost/found item posts
- `inquiries`: inquiry and report messages

Create or verify the tables using:

```bash
mysql -h DB_HOST -P 3306 -u DB_USER -p lost_on_campus
```

Then run:

```sql
SOURCE /path/to/sql/schema.sql;
```

## Configuration

Database credentials must be provided through environment variables or edited locally in `config/db.php`.

```bash
export LOC_DB_HOST="db-47u9u6.vpc-cdb.ntruss.com"
export LOC_DB_PORT="3306"
export LOC_DB_NAME="lost_on_campus"
export LOC_DB_USER="DB_USER"
export LOC_DB_PASS="DB_PASSWORD"
```

Do not commit real DB passwords, private keys, or cloud credentials.

## Deployment

On the Ubuntu Apache server, copy only the application files into `/var/www/html`.

```bash
sudo rsync -a index.php config includes css js pages api /var/www/html/
sudo chown -R www-data:www-data /var/www/html
sudo find /var/www/html -type d -exec chmod 755 {} \;
sudo find /var/www/html -type f -exec chmod 644 {} \;
sudo systemctl restart apache2
```

Open the site:

```text
http://SERVER_PUBLIC_IP
```

## Verification Checklist

- Main page returns `HTTP 200`
- Post creation saves data to `lost_items`
- List and detail pages show saved posts
- Search works for title, content, and location
- Edit/delete require the correct password
- Inquiry form saves data to `inquiries`
- Empty inputs show validation messages
- Invalid post IDs show a safe user-facing error
- DB errors do not expose passwords, SQL details, or server paths

## Documentation

- `기획문서.md`: project planning and evaluation mapping
- `기능명세서.md`: functional specification
- `실행가이드.md`: server deployment and validation guide
- `가천대학교-AI활용_프로젝트.md`: evaluation rubric summary

## Maintainer

- Name: RyuGernwoo
- Email: qesadgun@gmail.com
