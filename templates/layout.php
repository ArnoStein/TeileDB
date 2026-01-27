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
    </style>
</head>
<body>
<header>
    <h1><?php echo e($title); ?></h1>
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
