<?php $this->layout('layouts/auth', $this->data) ?>

<div class="flex h-screen w-full">
    <div class="w-full hidden md:inline-block">
        <img class="h-full" src="https://raw.githubusercontent.com/prebuiltui/prebuiltui/main/assets/login/leftSideImage.png" alt="leftSideImage">
    </div>

    <div class="w-full flex flex-col items-center justify-center">
        <form class="md:w-96 w-80 flex flex-col items-center justify-center" action="/login" method="post">
            <h2 class="text-4xl text-gray-900 font-medium mb-4"><?= __('auth.signin') ?></h2>
            <!-- <p class="text-sm text-gray-500/90 mt-3">Welcome back! Please sign in to continue</p> -->

            <!-- <button type="button" class="w-full mt-8 bg-gray-500/10 flex items-center justify-center h-12 rounded-full">
                <img src="https://raw.githubusercontent.com/prebuiltui/prebuiltui/main/assets/login/googleLogo.svg" alt="googleLogo">
            </button>

            <div class="flex items-center gap-4 w-full my-5">
                <div class="w-full h-px bg-gray-300/90"></div>
                <p class="w-full text-nowrap text-sm text-gray-500/90">or sign in with email</p>
                <div class="w-full h-px bg-gray-300/90"></div>
            </div> -->

            <div class="flex items-center w-full bg-transparent border border-gray-300/60 h-12 rounded-full overflow-hidden pl-6 gap-2">
                <i class="fa-solid fa-user"></i>
                <input type="text" name="username" placeholder="Username" class="bg-transparent text-gray-500/80 placeholder-gray-500/80 outline-none text-sm w-full h-full" value="<?= $this->e($username ?? '') ?>" required>                 
            </div>

            <div class="flex items-center mt-6 w-full bg-transparent border border-gray-300/60 h-12 rounded-full overflow-hidden pl-6 gap-2">
                <i class="fa-solid fa-lock"></i>
                <input type="password" name="password" placeholder="Password" class="bg-transparent text-gray-500/80 placeholder-gray-500/80 outline-none text-sm w-full h-full" required>
            </div>

            <div class="w-full flex items-center justify-between mt-8 text-gray-500/80">
                <div class="flex items-center gap-2">
                    <input class="h-5" type="checkbox" id="remember" name="remember">
                    <label class="text-sm" for="remember"><?= __('auth.rememberme') ?></label>
                </div>
                <!-- <a class="text-sm underline" href="#">Forgot password?</a> -->
            </div>

            <button type="submit" class="mt-8 w-full h-11 rounded-full text-white bg-indigo-500 hover:opacity-90 transition-opacity">
                <?= __('auth.signin') ?>
            </button>
        </form>
    </div>
</div>

<?= $this->start('scripts') ?>
<script>
    <?php if (isset($error)): ?>
        alertify.set('notifier','position', 'top-right');
        alertify.error('<?= $this->e($error) ?>');
    <?php endif; ?>
</script>
<?= $this->end() ?>