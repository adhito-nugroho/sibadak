<?php
$flash = consume_flash();
if ($flash === null) {
    return;
}
$isOk = $flash['type'] === 'success';
$cls = $isOk
    ? 'border-emerald-200 bg-emerald-50/90 text-emerald-900'
    : 'border-red-200 bg-red-50/90 text-red-900';
$icon = $isOk
    ? '<svg class="w-4 h-4 text-emerald-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>'
    : '<svg class="w-4 h-4 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
?>
<div class="fade-up flex gap-3 rounded-2xl border px-4 py-3 text-sm shadow-sm <?= $cls ?>">
    <?= $icon ?>
    <span class="leading-snug"><?= htmlspecialchars($flash['msg'], ENT_QUOTES, 'UTF-8') ?></span>
</div>
