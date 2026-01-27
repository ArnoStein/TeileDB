<?php
declare(strict_types=1);
?>
<section>
    <h2><?php echo $setupMode ? 'Passwort setzen' : 'Passwort ändern'; ?></h2>

    <?php if (!empty($success)): ?>
        <div class="notice">Passwort wurde aktualisiert.</div>
    <?php endif; ?>
    <?php if (!empty($errors['global'])): ?>
        <div class="error"><?php echo e($errors['global']); ?></div>
    <?php endif; ?>

    <p>Angemeldet als: <strong><?php echo e((string) ($user['username'] ?? '')); ?></strong></p>

    <form method="post" action="index.php?page=account">
        <?php if (!$setupMode): ?>
            <div class="form-group">
                <label for="current_password">Aktuelles Passwort</label><br>
                <input type="password" id="current_password" name="current_password">
                <?php if (!empty($errors['current_password'])): ?>
                    <div class="error"><?php echo e($errors['current_password']); ?></div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

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

        <button type="submit"><?php echo $setupMode ? 'Passwort setzen' : 'Passwort ändern'; ?></button>
    </form>
</section>
