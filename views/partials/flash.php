<?php
$flashData = $flashData ?? consume_flash();
if ($flashData === null) {
    return;
}

$type = (string) ($flashData['type'] ?? 'error');
$variants = [
    'success' => [
        'class' => 'flash--success',
        'icon'  => '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>',
        'role'  => 'status',
    ],
    'info' => [
        'class' => 'flash--info',
        'icon'  => '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 2"/></svg>',
        'role'  => 'status',
    ],
    'warning' => [
        'class' => 'flash--warning',
        'icon'  => '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 3.9 2.7 17a2 2 0 0 0 1.7 3h15.2a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/></svg>',
        'role'  => 'status',
    ],
    'error' => [
        'class' => 'flash--error',
        'icon'  => '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01"/></svg>',
        'role'  => 'alert',
    ],
];

$variant = $variants[$type] ?? $variants['error'];
?>
<div role="<?= $variant['role'] ?>" class="flash fade-up <?= $variant['class'] ?>">
    <span class="flash__icon"><?= $variant['icon'] ?></span>
    <span class="flash__msg"><?= htmlspecialchars((string) $flashData['msg'], ENT_QUOTES, 'UTF-8') ?></span>
</div>
