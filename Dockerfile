# 1. 베이스 이미지 설정
# PHP 8.2와 Apache 웹 서버가 포함된 공식 이미지를 사용합니다.
# 이 이미지를 기반으로 애플리케이션 환경을 구축합니다.
FROM php:8.2-apache

# 2. PHP 확장 모듈(PDO, PDO MySQL) 설치
# Docker에서 제공하는 공식 헬퍼 스크립트를 사용하여 PHP가 MySQL 데이터베이스에
# 안전하게 접속할 수 있도록 pdo와 pdo_mysql 확장을 설치하고 활성화합니다.
# apt-get update는 패키지 목록을 최신화하며, docker-php-ext-install이 의존하는
# 라이브러리들이 원활히 설치되도록 돕습니다.
RUN docker-php-ext-install pdo pdo_mysql

# 3. 프로젝트 소스 코드 복사
# 현재 디렉터리(Dockerfile 위치)의 모든 파일을 컨테이너 내부의 Apache 웹 서버
# 기본 문서 루트인 /var/www/html/ 경로로 복사합니다.
COPY . /var/www/html/

# 4. uploads 디렉터리 권한 설정
# 사용자가 업로드한 파일이 저장될 /var/www/html/uploads 디렉터리의 소유권을
# Apache 프로세스를 실행하는 www-data 사용자와 그룹에게 부여합니다.
# 이 설정을 통해 PHP 스크립트가 해당 디렉터리에 파일을 정상적으로 쓸 수 있게 됩니다.
RUN chown -R www-data:www-data /var/www/html/uploads
