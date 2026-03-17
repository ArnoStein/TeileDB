<?php
declare(strict_types=1);
?>
<section>
    <form method="get" action="index.php" class="scan-form">
        <input type="hidden" name="page" value="part_detail">
        <label for="serial_number">Seriennummer scannen</label>
        <div class="scan-controls">
            <input type="text" id="serial_number" name="serial_number" autofocus>
            <button type="submit" class="visually-hidden">Anzeigen</button>
        </div>
    </form>
    <?php if (!empty($scanError)): ?>
        <div class="error"><?php echo e($scanError); ?></div>
    <?php endif; ?>

    <?php if ($part !== null): ?>
    <h2>Teil-Details</h2>
    <p><strong>Seriennummer:</strong> <?php echo e((string) \App\Domain\SerialFormatter::formatForDisplay((string) ($part['serial_number'] ?? ''))); ?></p>
    <p><strong>Teiltyp:</strong> <?php echo e((string) ($part['part_type_short_name'] ?? '')); ?> – <?php echo e((string) ($part['part_type_name'] ?? '')); ?></p>
    <p><strong>Erstellt am:</strong> <?php echo e(isset($part['created_at']) ? date('d.m.Y H:i', strtotime((string) $part['created_at'])) : ''); ?></p>

    <div class="status-block">
        <div class="status-header">
            <span class="status-label">Status:</span>
            <span class="badge"><?php echo e((string) ($part['status_name'] ?? '')); ?></span>
            <?php if (!empty($updated)): ?>
                <span class="notice-inline">Status gespeichert.</span>
            <?php endif; ?>
        </div>
        <?php if (!empty($errors['global'])): ?>
            <div class="error"><?php echo e($errors['global']); ?></div>
        <?php endif; ?>
        <form method="post" action="index.php?page=part_detail&id=<?php echo e((string) ($part['id'] ?? '')); ?>" class="status-form">
            <input type="hidden" name="action" value="status_update">
            <label for="status_id" class="visually-hidden">Status auswählen</label>
            <select id="status_id" name="status_id" required>
                <?php foreach ($statuses as $status): ?>
                    <option value="<?php echo e((string) ($status['id'] ?? '')); ?>" <?php echo ((string) ($part['status_id'] ?? '') === (string) ($status['id'] ?? '')) ? 'selected' : ''; ?>>
                        <?php echo e((string) ($status['name'] ?? '')); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (!empty($errors['status_id'])): ?>
                <div class="error"><?php echo e($errors['status_id']); ?></div>
            <?php endif; ?>
            <button type="submit">Status speichern</button>
        </form>
    </div>

    <h2 id="comments">Kommentare</h2>
    <?php if (!empty($commented)): ?>
        <div class="notice">Kommentar gespeichert.</div>
    <?php endif; ?>
    <?php if (!empty($commentErrors['global'])): ?>
        <div class="error"><?php echo e($commentErrors['global']); ?></div>
    <?php endif; ?>
    <form method="post" action="index.php?page=part_detail&id=<?php echo e((string) ($part['id'] ?? '')); ?>#comments" class="comment-form">
        <input type="hidden" name="action" value="comment_create">
        <div class="form-group">
            <label for="comment">Kommentar</label><br>
            <textarea id="comment" name="comment" rows="4" cols="60" required><?php echo e((string) ($commentInput ?? '')); ?></textarea>
            <?php if (!empty($commentErrors['comment'])): ?>
                <div class="error"><?php echo e($commentErrors['comment']); ?></div>
            <?php endif; ?>
        </div>
        <button type="submit">Kommentar speichern</button>
    </form>

    <?php if (!empty($comments)): ?>
        <div class="table-scroll">
        <table class="tbl-fixed">
            <colgroup>
                <col class="col-datetime">
                <col class="col-comment">
            </colgroup>
            <thead>
            <tr>
                <th>Datum + Uhrzeit</th>
                <th>Kommentar</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($comments as $comment): ?>
                <tr>
                    <td class="cell nowrap">
                        <?php echo e(isset($comment['created_at']) ? date('d.m.Y H:i', strtotime((string) $comment['created_at'])) : ''); ?>
                    </td>
                    <td class="cell">
                        <?php echo nl2br(e((string) ($comment['comment'] ?? '')), false); ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php else: ?>
        <div class="empty">Keine Kommentare vorhanden.</div>
    <?php endif; ?>
    <?php else: ?>
        <div class="empty">Kein Teil geladen. Bitte Seriennummer scannen.</div>
    <?php endif; ?>
</section>
