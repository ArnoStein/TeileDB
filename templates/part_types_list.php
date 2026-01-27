<?php
declare(strict_types=1);
?>
<section>
    <p><a href="index.php?page=part_types_create">Neuer Teiltyp</a></p>

    <?php if (!empty($errorMessage)): ?>
        <div class="error"><?php echo e($errorMessage); ?></div>
    <?php endif; ?>

    <?php if (!empty($partTypes)): ?>
        <table>
            <thead>
            <tr>
                <th>Name</th>
                <th>Kurzname</th>
                <th>Beschreibung</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($partTypes as $type): ?>
                <tr>
                    <td><?php echo e((string) ($type['name'] ?? '')); ?></td>
                    <td><?php echo e((string) ($type['short_name'] ?? '')); ?></td>
                    <td><?php echo e((string) ($type['description'] ?? '')); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="empty">Keine Teiltypen vorhanden.</div>
    <?php endif; ?>
</section>
