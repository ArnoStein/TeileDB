<?php
declare(strict_types=1);

if (!function_exists('e')) {
    function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}

$title = isset($pageTitle) && $pageTitle !== '' ? $pageTitle : 'TeileDB';
$viewData = $viewData ?? [];
$contentTemplate = $contentTemplate ?? null;
$isLoggedIn = $viewData['isLoggedIn'] ?? false;
?><!doctype html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($title); ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 2rem; background: #f7f7f7; color: #222; }
        header { margin-bottom: 0.5rem; }
        nav { margin-bottom: 1.5rem; }
        nav a { margin-right: 1rem; text-decoration: none; color: #0d47a1; }
        nav a:hover { text-decoration: underline; }
        .auth-links { float: right; font-size: 0.9rem; }
        .auth-links a { margin-left: 0.75rem; color: #555; text-decoration: none; }
        .auth-links a:hover { text-decoration: underline; }
        table { border-collapse: collapse; width: 100%; background: #fff; }
        th, td { padding: 0.75rem; border-bottom: 1px solid #e5e5e5; text-align: left; }
        th { background: #fafafa; }
        .empty { padding: 1rem; background: #fff; border: 1px solid #e5e5e5; }
        .filters { margin-bottom: 1rem; }
        input, select, textarea { padding: 0.35rem 0.5rem; }
        .error { color: #b00020; margin-bottom: 0.5rem; }
        .notice { color: #0b6d0b; margin-bottom: 0.5rem; }
        .form-group { margin-bottom: 0.75rem; }
        button { padding: 0.45rem 0.9rem; }
        .tbl-fixed { table-layout: fixed; width: 100%; }
        .cell { overflow: hidden; }
        .ellipsis { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .nowrap { white-space: nowrap; }
        .table-scroll { overflow-x: auto; }
        .col-datetime { width: 180px; min-width: 180px; white-space: nowrap; }
        .col-comment { width: auto; }
        .status-block { margin: 1.25rem 0; background: #fff; padding: 1rem; border: 1px solid #e5e5e5; border-radius: 4px; }
        .status-header { display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 0.5rem; }
        .status-label { font-weight: 600; }
        .badge { display: inline-block; background: #f3f4f6; color: #111827; padding: 0.15rem 0.5rem; border-radius: 12px; font-weight: 600; border: 1px solid #d1d5db; }
        .notice-inline { color: #0b6d0b; font-size: 0.9rem; }
        .status-form { display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap; margin-top: 0.25rem; }
        .status-form select { min-width: 180px; }
        .comment-form { margin: 0.75rem 0 1rem; }
        .visually-hidden { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border: 0; }
        .scan-form { margin: 0 0 1rem; background: #fff; padding: 0.75rem 0.9rem; border: 1px solid #e5e5e5; border-radius: 4px; }
        .scan-form label { display: block; font-weight: 600; margin-bottom: 0.35rem; }
        .scan-controls { display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center; }
        .scan-controls input[type=\"text\"] { min-width: 220px; }
        .filters-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; align-items: start; }
        .filters-grid .field { display: block; }
        .hint { font-size: 0.9em; opacity: 0.85; margin-top: 4px; }
    </style>
</head>
<body>
<header>
    <h1><?php echo e($title); ?></h1>
    <?php if ($isLoggedIn): ?>
        <div class="auth-links">
            <a href="index.php?page=info">Info</a>
            <a href="index.php?page=account">Passwort ändern</a>
            <a href="index.php?page=logout">Logout</a>
        </div>
        <div style="clear: both;"></div>
    <?php endif; ?>
</header>
<nav>
    <a href="index.php?page=parts_list">Teileliste</a>
    <a href="index.php?page=part_types_list">Teiltypen</a>
</nav>
<main>
    <?php
    if ($contentTemplate && is_file($contentTemplate)) {
        extract($viewData, EXTR_SKIP);
        require $contentTemplate;
    } else {
        echo '<p>Kein Inhalt verfügbar.</p>';
    }
    ?>
</main>
</body>
</html>
