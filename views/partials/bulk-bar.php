<?php
/**
 * Shared bulk-action bar for data tables.
 * Hidden until ≥1 row checkbox is selected (toggled by assets/js/app-ui.js).
 */
?>
<div id="bulk-bar" class="bulk-bar" role="status" aria-live="polite" hidden>
    <span id="bulk-bar-count" class="bulk-bar-count">0 dipilih</span>
    <div class="bulk-bar-actions">
        <button type="button" id="bulk-export" class="bulk-btn bulk-btn-export">
            <i class="ti ti-download text-sm"></i> Export terpilih
        </button>
        <button type="button" id="bulk-delete" class="bulk-btn bulk-btn-delete">
            <i class="ti ti-trash text-sm"></i> Hapus terpilih
        </button>
        <button type="button" id="bulk-clear" class="bulk-btn bulk-btn-clear">Batal</button>
    </div>
</div>
