<?php
declare(strict_types=1);
?>
<section>
    <h2>Login</h2>
    <?php if (!empty($errors['global'])): ?>
        <div class="error"><?php echo e($errors['global']); ?></div>
    <?php endif; ?>
    <form method="post" action="index.php?page=login">
        <div class="form-group">
            <label for="username">Benutzername</label><br>
            <input type="text" id="username" name="username" value="<?php echo e($usernameInput); ?>">
            <?php if (!empty($errors['username'])): ?>
                <div class="error"><?php echo e($errors['username']); ?></div>
            <?php endif; ?>
        </div>

        <?php if ($setupMode): ?>
            <div class="form-group">
                <label for="new_password">Neues Passwort</label><br>
                <input type="password" id="new_password" name="new_password">
                <?php if (!empty($errors['new_password'])): ?>
                    <div class="error"><?php echo e($errors['new_password']); ?></div>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="new_password_confirm">Neues Passwort (Wiederholung)</label><br>
                <input type="password" id="new_password_confirm" name="new_password_confirm">
                <?php if (!empty($errors['new_password_confirm'])): ?>
                    <div class="error"><?php echo e($errors['new_password_confirm']); ?></div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="form-group">
                <label for="password">Passwort</label><br>
                <input type="password" id="password" name="password">
                <?php if (!empty($errors['password'])): ?>
                    <div class="error"><?php echo e($errors['password']); ?></div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <button type="submit">Anmelden</button>
    </form>
</section>
