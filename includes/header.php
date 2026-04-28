<?php
require_once __DIR__ . '/config.php';
$siteTitle = $pageTitle ?? SITE_NAME . ' - Campus Lost & Found';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= h($siteTitle) ?></title>
  <script>
    if (localStorage.getItem('findit_theme') === 'light') {
      document.documentElement.classList.add('light-mode-pre');
    }
  </script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600&display=swap" rel="stylesheet">
  <link href="<?= BASE_URL ?>assets/css/custom.css" rel="stylesheet">
</head>
<body>
<script src="<?= BASE_URL ?>assets/js/main.js" defer></script>
</body>
</html>
