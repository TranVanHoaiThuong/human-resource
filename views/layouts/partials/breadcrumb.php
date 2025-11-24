<?php if (isset($breadcrumbs) && !empty($breadcrumbs)): ?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="/dashboard">
                <i class="bi bi-house-door"></i> Home
            </a>
        </li>
        <?php foreach ($breadcrumbs as $index => $crumb): ?>
            <?php if ($index === array_key_last($breadcrumbs)): ?>
                <li class="breadcrumb-item active" aria-current="page">
                    <?= $this->e($crumb['title']) ?>
                </li>
            <?php else: ?>
                <li class="breadcrumb-item">
                    <a href="<?= $crumb['url'] ?>">
                        <?= $this->e($crumb['title']) ?>
                    </a>
                </li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ol>
</nav>
<?php endif; ?>