# LostOnCampus

LostOnCampus는 캠퍼스 안에서 발생하는 분실물과 습득물 정보를 등록하고 검색할 수 있는 웹 기반 분실물 게시판입니다. 가천대학교 AI 활용 웹사이트 구축 프로젝트를 위해 제작했으며, 추후 다른 대학에도 적용할 수 있도록 학교명에 종속되지 않는 범용 이름을 사용했습니다.

사용자는 분실물/습득물 게시글을 등록하고, 목록과 상세 화면에서 내용을 확인하며, 키워드 검색, 댓글, 문의/제보 기능을 사용할 수 있습니다. 게시글 수정과 삭제는 등록 시 입력한 비밀번호로 처리합니다.

## 진행 현황

- 주제 확정: 캠퍼스 분실물 찾기 게시판
- Naver Cloud 서버 및 Cloud DB for MySQL 구축
- Ubuntu 24.04 환경에서 Apache + PHP + MySQL 연동
- `lost_items`, `item_comments`, `inquiries` 테이블 설계 및 적용
- 게시글 CRUD, 검색, 이미지 업로드, 댓글, 문의/제보 기능 구현
- 필수값 누락, 잘못된 게시글 ID, 비밀번호 불일치, DB 오류에 대한 오류 처리 구현
- 민감정보를 `.env` 파일에서 읽도록 분리
- Apache 웹 루트 배포 및 로컬 HTTP 응답 테스트 완료

## 기술 스택

| 구분 | 사용 기술 |
|---|---|
| 클라우드 | Naver Cloud Platform |
| 서버 OS | Ubuntu 24.04 LTS |
| 웹 서버 | Apache 2.4 |
| 서버 언어 | PHP 8 |
| 데이터베이스 | Cloud DB for MySQL 8.4 |
| 프론트엔드 | HTML, CSS, JavaScript |

## 주요 기능

| 기능 | 설명 |
|---|---|
| 메인 페이지 | 서비스 안내, 검색, 최근 게시글 표시 |
| 게시글 목록 | 분실물/습득물 게시글 최신순 조회 |
| 게시글 상세 | 유형, 제목, 장소, 내용, 연락처, 이미지, 댓글 표시 |
| 게시글 등록 | 분실물/습득물 정보를 MySQL에 저장 |
| 이미지 업로드 | 게시글에 선택 이미지 첨부, 안전한 파일명으로 저장 |
| 게시글 수정 | 비밀번호 확인 후 게시글 내용 수정 |
| 게시글 삭제 | 비밀번호 확인 후 게시글과 첨부 이미지 삭제 |
| 댓글 | 게시글 상세 화면에서 댓글 등록 및 조회 |
| 검색 | 제목, 내용, 장소 기준 키워드 검색 |
| 문의/제보 | 이름, 연락처, 메시지를 DB에 저장 |
| 입력값 검증 | 빈 입력, 길이 초과, 잘못된 파일 형식 안내 |
| 오류 처리 | 내부 DB 정보, 서버 경로, 비밀번호가 화면에 노출되지 않도록 처리 |

## 디렉터리 구조

```text
.
├── api/
│   ├── comment_action.php
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
│   ├── migration_20260630_comments_images.sql
│   └── schema.sql
├── uploads/
│   └── items/
├── index.php
├── 기획문서.md
├── 기능명세서.md
└── 실행가이드.md
```

## 데이터베이스

이 프로젝트는 세 개의 주요 테이블을 사용합니다.

| 테이블 | 설명 |
|---|---|
| `lost_items` | 분실물/습득물 게시글 저장, 선택 이미지 경로 포함 |
| `item_comments` | 게시글 상세 화면의 댓글 저장 |
| `inquiries` | 문의/제보 메시지 저장 |

초기 테이블 생성:

```bash
mysql -h DB_HOST -P 3306 -u DB_USER -p lost_on_campus
```

```sql
SOURCE /path/to/sql/schema.sql;
```

기존 `lost_items`, `inquiries` 테이블이 이미 있는 경우에는 이미지 업로드와 댓글 기능을 추가하는 마이그레이션을 실행합니다.

```sql
SOURCE /path/to/sql/migration_20260630_comments_images.sql;
```

## 환경 변수 설정

DB 접속 정보는 코드에 직접 저장하지 않고 `.env` 파일 또는 서버 환경변수에서 읽습니다. 실제 비밀번호는 Git에 커밋하지 않습니다.

개발 환경 예시:

```bash
cp .env.example .env
nano .env
```

운영 서버에서는 웹 루트 바깥에 `.env`를 두는 것을 권장합니다.

```bash
sudo cp .env.example /var/www/.env
sudo nano /var/www/.env
sudo chown root:www-data /var/www/.env
sudo chmod 640 /var/www/.env
```

`.env` 예시:

```bash
LOC_DB_HOST=db-47u9u6.vpc-cdb.ntruss.com
LOC_DB_PORT=3306
LOC_DB_NAME=lost_on_campus
LOC_DB_USER=DB계정명
LOC_DB_PASS=DB비밀번호
```

## 배포 방법

Ubuntu Apache 서버 기준으로, 애플리케이션 파일만 `/var/www/html`에 복사합니다.

```bash
sudo rsync -a index.php config includes css js pages api uploads /var/www/html/
sudo chown -R www-data:www-data /var/www/html
sudo find /var/www/html -type d -exec chmod 755 {} \;
sudo find /var/www/html -type f -exec chmod 644 {} \;
sudo chmod 775 /var/www/html/uploads /var/www/html/uploads/items
sudo systemctl restart apache2
```

브라우저에서 접속:

```text
http://서버공인IP
```

## 검증 체크리스트

- 메인 페이지가 `HTTP 200`으로 응답한다.
- 게시글 등록 시 `lost_items` 테이블에 저장된다.
- 이미지 업로드 시 `uploads/items` 아래에 안전한 랜덤 파일명으로 저장된다.
- 게시글 상세 화면에 이미지가 표시된다.
- 댓글 등록 시 `item_comments` 테이블에 저장되고 상세 화면에 표시된다.
- 목록, 상세, 검색 화면에서 저장된 게시글을 조회할 수 있다.
- 게시글 수정/삭제는 올바른 비밀번호일 때만 처리된다.
- 문의/제보 입력값이 `inquiries` 테이블에 저장된다.
- 빈 입력과 잘못된 접근에 대해 사용자 안내 메시지가 표시된다.
- DB 오류 발생 시 비밀번호, SQL 상세 오류, 서버 내부 경로가 화면에 노출되지 않는다.

## 프로젝트 문서

| 문서 | 설명 |
|---|---|
| `기획문서.md` | 프로젝트 기획, 아키텍처, 일정, 역할 분담 |
| `기능명세서.md` | 기능 목록, 상세 기능 명세, DB 명세, 테스트 기준 |
| `실행가이드.md` | 서버 설정, 배포, DB 마이그레이션, 검증 절차 |
| `가천대학교-AI활용_프로젝트.md` | 프로젝트 평가 기준 요약 |

## 보안 메모

- 실제 DB 비밀번호는 `.env`에만 저장하고 Git에 커밋하지 않습니다.
- 업로드 이미지는 `uploads/items/`에 저장하며 PHP 실행을 차단합니다.
- SQL Injection 방지를 위해 PDO Prepared Statement를 사용합니다.
- 게시글 수정/삭제 비밀번호는 `password_hash()`로 해시 저장합니다.

## 작성자

- 이름: RyuGernwoo
- 이메일: qesadgun@gmail.com
