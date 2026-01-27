<?php
declare(strict_types=1);

$ctx = require __DIR__ . '/../src/bootstrap.php';

$page = $_GET['page'] ?? 'parts_list';

switch ($page) {
    case 'parts_list':
        require __DIR__ . '/../src/Pages/parts_list.php';
        break;
    case 'parts_create':
        require __DIR__ . '/../src/Pages/parts_create.php';
        break;
    case 'part_detail':
        require __DIR__ . '/../src/Pages/part_detail.php';
        break;
    case 'part_types_list':
        require __DIR__ . '/../src/Pages/part_types_list.php';
        break;
    case 'part_types_create':
        require __DIR__ . '/../src/Pages/part_types_create.php';
        break;
    default:
        http_response_code(404);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Seite nicht gefunden.';
}
