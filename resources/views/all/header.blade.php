<?php
    $headerSettings = $systemSettings ?? [];
    $headerSiteName = trim((string) ($headerSettings['website_name'] ?? 'Family Tree System'));
    if ($headerSiteName === '') {
        $headerSiteName = 'Family Tree System';
    }

    $storedLogoPath = trim((string) ($headerSettings['logo_path'] ?? ''));
    $faviconUrl = trim((string) ($headerSettings['logo_url'] ?? ''));
    if ($faviconUrl === '' && $storedLogoPath !== '') {
        $faviconUrl = (preg_match('#^https?://#i', $storedLogoPath) || str_starts_with($storedLogoPath, 'data:'))
            ? $storedLogoPath
            : asset(ltrim($storedLogoPath, '/'));
    }

    $styleVersion = @filemtime($_SERVER['DOCUMENT_ROOT'] . '/css/style.css') ?: time();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo e($pageTitle ?? $headerSiteName); ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Sora:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="<?php echo e($faviconUrl); ?>">
    <link rel="stylesheet" href="/css/style.css?v=<?php echo e($styleVersion); ?>">

    <style>
        html, body {
            margin: 0;
            min-height: 100%;
        }

        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        main {
            flex: 1 0 auto;
            width: 100%;
        }
    </style>
</head>
<body class="<?php echo e($pageClass ?? 'page-default'); ?>">
<main>
