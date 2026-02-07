<?php
declare(strict_types=1);
?>
<section>
    <h2>Info</h2>
    <p>Version: <?php echo e((string) ($version ?? '')); ?></p>
    <p>PHP-Version: <?php echo e((string) ($phpVersion ?? '')); ?></p>
    <p>Serverzeit: <?php echo e((string) ($serverTime ?? '')); ?></p>
    <p>Teiledatenbank von Arno via Webinterface. Programmiert von Arno mit freundlicher Unterstützung von ChatGPT und Codex.</p>
</section>
