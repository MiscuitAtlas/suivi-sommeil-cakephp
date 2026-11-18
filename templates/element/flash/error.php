<?php
/**
 * @var \App\View\AppView $this
 * @var string $message
 * @var array<string, mixed> $params
 */
if (!isset($params['escape']) || $params['escape'] !== false) {
    $message = h($message);
}
?>
<div class="flash error" role="alert"><?= $message ?></div>
