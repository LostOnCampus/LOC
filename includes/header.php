<?php
$pageTitle = $pageTitle ?? 'LostOnCampus';
?>
<!doctype html>
<html lang="ko">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($pageTitle) ?> | LostOnCampus</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
<header class="site-header">
    <div class="header-inner">
        <a class="brand" href="/">LostOnCampus</a>
        <nav class="site-nav" aria-label="주요 메뉴">
            <a href="/">메인</a>
            <a href="/pages/item_list.php">목록</a>
            <a href="/pages/item_write.php">등록</a>
            <a href="/pages/inquiry.php">문의</a>
        </nav>
    </div>
</header>
<main class="container">
