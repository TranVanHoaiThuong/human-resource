<?php $this->layout('layouts/app', $this->data) ?>

<div class="row">
    <!-- Tổng số nhân viên -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Tổng nhân viên</h5>
                <h2><?= $stats['total_employees'] ?></h2>
            </div>
        </div>
    </div>
    
    <!-- Có mặt hôm nay -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Có mặt hôm nay</h5>
                <h2><?= $stats['present_today'] ?></h2>
            </div>
        </div>
    </div>
    
    <!-- Đang nghỉ phép -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Đang nghỉ phép</h5>
                <h2><?= $stats['on_leave'] ?></h2>
            </div>
        </div>
    </div>
</div>

<?= $this->start('scripts') ?>
<script src="<?= $this->public('/js/dashboard/dashboard') ?>"></script>
<?= $this->end() ?>