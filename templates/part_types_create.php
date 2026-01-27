<?php
declare(strict_types=1);
?>
<section>
    <h2>Teiltyp anlegen</h2>

    <?php if (!empty($errors['global'])): ?>
        <div class="error"><?php echo e($errors['global']); ?></div>
    <?php endif; ?>

    <form method="post" action="index.php?page=part_types_create">
        <div class="form-group">
            <label for="name">Name *</label><br>
            <input type="text" id="name" name="name" maxlength="50" value="<?php echo e((string) ($formData['name'] ?? '')); ?>" required>
            <?php if (!empty($errors['name'])): ?>
                <div class="error"><?php echo e($errors['name']); ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="short_name">Kurzname *</label><br>
            <input type="text" id="short_name" name="short_name" maxlength="20" value="<?php echo e((string) ($formData['short_name'] ?? '')); ?>" required>
            <?php if (!empty($errors['short_name'])): ?>
                <div class="error"><?php echo e($errors['short_name']); ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="description">Beschreibung (optional)</label><br>
            <textarea id="description" name="description" rows="3" cols="40"><?php echo isset($formData['description']) ? e((string) $formData['description']) : ''; ?></textarea>
        </div>

        <button type="submit">Speichern</button>
        <a href="index.php?page=part_types_list">Abbrechen</a>
    </form>
</section>
