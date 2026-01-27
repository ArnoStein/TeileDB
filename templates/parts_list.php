<?php
declare(strict_types=1);
?>
<p><a href="index.php?page=parts_create">Neues Teil</a></p>
<section class="filters">
    <form method="get">
        <input type="hidden" name="page" value="parts_list">
        <label>
            Suche (Serial/Typ):
            <input type="text" name="q" value="<?php echo isset($filters['search']) ? e((string) $filters['search']) : ''; ?>" placeholder="SN-… oder Typ">
        </label>
        <label>
            Status:
            <select name="status_id">
                <option value="">Alle</option>
                <?php if (!empty($statuses)): ?>
                    <?php foreach ($statuses as $status): ?>
                        <option value="<?php echo e((string) ($status['id'] ?? '')); ?>" <?php echo ((string) ($selectedStatusId ?? '') === (string) ($status['id'] ?? '')) ? 'selected' : ''; ?>>
                            <?php echo e((string) ($status['name'] ?? '')); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </label>
        <button type="submit">Filtern</button>
    </form>
</section>

<?php if (!empty($parts)): ?>
<table class="tbl-fixed">
    <colgroup>
        <col style="width:25%">
        <col style="width:35%">
        <col style="width:20%">
        <col style="width:20%">
        <col style="width:10%">
    </colgroup>
    <thead>
    <tr>
        <th>Seriennummer</th>
        <th>Teiltyp</th>
        <th>Status</th>
        <th>Erstellt am</th>
        <th>Details</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($parts as $part): ?>
        <tr>
            <td class="cell ellipsis nowrap" title="<?php echo e((string) ($part['serial_number'] ?? '')); ?>">
                <?php echo e((string) ($part['serial_number'] ?? '')); ?>
            </td>
            <td class="cell ellipsis" title="<?php echo e((string) ($part['part_type_name'] ?? '')); ?>">
                <?php echo e((string) ($part['part_type_short_name'] ?? '')); ?>
            </td>
            <td class="cell nowrap">
                <?php echo e((string) ($part['status_name'] ?? '')); ?>
            </td>
            <td class="cell nowrap">
                <?php echo e(isset($part['created_at']) ? date('d.m.Y H:i', strtotime((string) $part['created_at'])) : ''); ?>
            </td>
            <td class="cell nowrap"><a href="index.php?page=part_detail&id=<?php echo e((string) ($part['id'] ?? '')); ?>">Details</a></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php else: ?>
    <div class="empty">Keine Teile vorhanden.</div>
<?php endif; ?>
