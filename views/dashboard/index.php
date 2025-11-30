<?php $this->layout('layouts/app', $this->data) ?>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- Tổng số nhân viên -->
    <div class="card">
        <h5 class="text-gray-600 text-sm font-medium mb-2">Tổng nhân viên</h5>
        <h2 class="text-3xl font-bold text-gray-800"><?= $stats['total_employees'] ?></h2>
    </div>
    
    <!-- Có mặt hôm nay -->
    <div class="card">
        <h5 class="text-gray-600 text-sm font-medium mb-2">Có mặt hôm nay</h5>
        <h2 class="text-3xl font-bold text-green-600"><?= $stats['present_today'] ?></h2>
    </div>
    
    <!-- Đang nghỉ phép -->
    <div class="card">
        <h5 class="text-gray-600 text-sm font-medium mb-2">Đang nghỉ phép</h5>
        <h2 class="text-3xl font-bold text-orange-500"><?= $stats['on_leave'] ?></h2>
    </div>
</div>

<?= $this->start('scripts') ?>
<script src="<?= $this->public('/js/dashboard/dashboard') ?>"></script>
<?= $this->end() ?>