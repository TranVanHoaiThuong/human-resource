<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->e($title) ?></title>
    <link rel="icon" href="<?= $wwwroot ?>/assets/images/favicon.ico">
    <link rel="stylesheet" href="<?= $this->asset('/alertifyjs/css/alertify.min.css') ?>">
    <link rel="stylesheet" href="<?= $this->asset('/alertifyjs/css/themes/default.min.css') ?>">
    <link rel="stylesheet" href="<?= $this->asset('/fontawesome/css/all.min.css') ?>" />
    <link rel="stylesheet" href="<?= $this->public('/css/tailwind') ?>">
    <link rel="stylesheet" href="<?= $this->public('/css/app') ?>">
</head>
<body class="min-h-screen">
    <?= $this->section('content') ?>

    <script src="<?= $this->asset('/jquery/jquery.min.js') ?>"></script>
    <script src="<?= $this->asset('/alertifyjs/alertify.min.js') ?>"></script>
    <?= $this->section('scripts') ?>
</body>
</html>