<?php
declare(strict_types=1);
?>
<section>
    <h2>Neues Teil anlegen</h2>

    <?php if (!empty($errors['global'])): ?>
        <div class="error"><?php echo e($errors['global']); ?></div>
    <?php endif; ?>

    <form method="post" action="index.php?page=parts_create">
        <div class="form-group">
            <label for="serial_number">Seriennummer *</label><br>
            <input type="text" id="serial_number" name="serial_number" maxlength="20" value="<?php echo e((string) ($formData['serial_number'] ?? '')); ?>" required>
            <div class="hint">Erlaubte Formate: 8HEX, xx-xx-xx-xx, SN:xx-xx-xx-xx (Legacy), optional Barcode GXXXXXXXX-XXXX.</div>
            <?php if (!empty($errors['serial_number'])): ?>
                <div class="error"><?php echo e($errors['serial_number']); ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="part_type_id">Teiltyp *</label><br>
            <select id="part_type_id" name="part_type_id" required>
                <option value="">Bitte wählen</option>
                <?php foreach ($partTypes as $type): ?>
                    <option value="<?php echo e((string) ($type['id'] ?? '')); ?>" <?php echo ((string) ($formData['part_type_id'] ?? '') === (string) ($type['id'] ?? '')) ? 'selected' : ''; ?>>
                        <?php echo e((string) ($type['short_name'] ?? '')); ?> – <?php echo e((string) ($type['name'] ?? '')); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (!empty($errors['part_type_id'])): ?>
                <div class="error"><?php echo e($errors['part_type_id']); ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="status_id">Status *</label><br>
            <select id="status_id" name="status_id" required>
                <option value="">Bitte wählen</option>
                <?php foreach ($statuses as $status): ?>
                    <option value="<?php echo e((string) ($status['id'] ?? '')); ?>" <?php echo ((string) ($formData['status_id'] ?? '') === (string) ($status['id'] ?? '')) ? 'selected' : ''; ?>>
                        <?php echo e((string) ($status['name'] ?? '')); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (!empty($errors['status_id'])): ?>
                <div class="error"><?php echo e($errors['status_id']); ?></div>
            <?php endif; ?>
        </div>

        <button type="submit">Speichern</button>
        <a href="index.php?page=parts_list">Abbrechen</a>
    </form>
</section>
