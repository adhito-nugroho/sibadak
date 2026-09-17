<style>
  /* ── Sidebar scrollbar ── */
  .sidebar-nav::-webkit-scrollbar { width: 4px; }
  .sidebar-nav::-webkit-scrollbar-track { background: transparent; }
  .sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.12); border-radius: 99px; }

  /* ── Fade-up ── */
  @keyframes fadeUp {
    from { opacity: 0; transform: translateY(8px); }
    to   { opacity: 1; transform: translateY(0); }
  }
  .fade-up { animation: fadeUp 0.3s ease forwards; }
  .delay-1 { animation-delay: 0.03s; opacity: 0; }
  .delay-2 { animation-delay: 0.06s; opacity: 0; }
  .delay-3 { animation-delay: 0.09s; opacity: 0; }

  /* ── Sidebar collapse ── */
  .app-sidebar {
    width: var(--sidebar-width, 14rem);
    transition: width 0.2s ease;
  }
  .app-sidebar .nav-label,
  .app-sidebar .brand-text,
  .app-sidebar .user-meta,
  .app-sidebar .nav-group-label {
    transition: opacity 0.15s ease;
  }
  body.sidebar-collapsed .app-sidebar { width: var(--sidebar-width-collapsed, 4rem); }
  body.sidebar-collapsed .app-sidebar .nav-label,
  body.sidebar-collapsed .app-sidebar .brand-text,
  body.sidebar-collapsed .app-sidebar .user-meta,
  body.sidebar-collapsed .app-sidebar .nav-group-label {
    opacity: 0; width: 0; overflow: hidden; white-space: nowrap; pointer-events: none;
  }
  body.sidebar-collapsed .app-sidebar .nav-link {
    justify-content: center; padding-left: 0.5rem; padding-right: 0.5rem; gap: 0;
  }
  body.sidebar-collapsed .app-sidebar .brand-row,
  body.sidebar-collapsed .app-sidebar .user-row { justify-content: center; }
  body.sidebar-collapsed .app-sidebar .user-row .logout-btn { display: none; }
  .app-main {
    margin-left: var(--sidebar-width, 14rem);
    transition: margin-left 0.2s ease;
  }
  body.sidebar-collapsed .app-main { margin-left: var(--sidebar-width-collapsed, 4rem); }

  .nav-group-label { margin-top: 1.25rem; }
  .nav-group-label:first-child { margin-top: 0.25rem; }

  a.nav-link.is-active {
    box-shadow: inset 3px 0 0 #3AA870;
  }

  /* ── Page toolbar (meta + CTA) ── */
  .page-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    margin-bottom: 0.85rem;
  }
  .page-toolbar-meta {
    font-size: 0.75rem;
    color: #6b7280;
    line-height: 1.3;
  }
  .page-toolbar-meta strong {
    color: #1f2937;
    font-weight: 600;
  }

  /* ── Filter bar: compact flex, no sparse grid gaps ── */
  .filter-bar {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-end;
    gap: 0.65rem 0.75rem;
    padding: 0.75rem 0.9rem;
    margin-bottom: 0.85rem;
    background: #fff;
    border: 1px solid #e8ece9;
    border-radius: 0.75rem;
    box-shadow: var(--shadow-card, 0 1px 2px rgba(0,0,0,0.04));
  }
  .filter-field {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
    min-width: 0;
  }
  .filter-field label {
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #6b7280;
  }
  .filter-field select,
  .filter-field input[type="text"],
  .filter-field input[type="search"] {
    height: 2rem;
    font-size: 12.5px;
    padding: 0 0.65rem;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    background: #fff;
    outline: none;
    color: #1f2937;
  }
  .filter-field select:focus,
  .filter-field input:focus {
    border-color: #86efac;
    box-shadow: 0 0 0 2px rgba(34, 197, 94, 0.12);
  }
  .filter-field-kab { width: 10.5rem; }
  .filter-field-kelas { width: 7.5rem; }
  .filter-field-sm { width: 6.75rem; }
  .filter-field-md { width: 9.5rem; }
  .filter-field-lg { width: 14rem; }
  .filter-field-q {
    flex: 1 1 14rem;
    min-width: 12rem;
  }
  .filter-field-q input {
    background: #f8faf8 !important;
    border-color: #e5e7eb !important;
  }
  .filter-actions {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    margin-left: auto;
  }
  .filter-actions .btn-apply {
    height: 2rem;
    padding: 0 0.9rem;
    border-radius: 0.5rem;
    background: #1A6B3A;
    color: #fff;
    font-size: 12.5px;
    font-weight: 600;
    border: 0;
    cursor: pointer;
  }
  .filter-actions .btn-apply:hover { background: #155730; }
  .filter-actions .btn-reset {
    height: 2rem;
    padding: 0 0.75rem;
    border-radius: 0.5rem;
    border: 1px solid #e5e7eb;
    background: #fff;
    color: #4b5563;
    font-size: 12.5px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
  }
  .filter-actions .btn-reset:hover { background: #f9fafb; }

  /* Legacy alias */
  .filter-search-input { /* kept for other pages */ }

  /* ── Data tables ── */
  .data-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    table-layout: fixed;
  }
  .data-table th {
    font-weight: 600;
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #6b7280;
    padding: 0.4rem 0.55rem;
    white-space: nowrap;
    background: #f3f6f4;
    border-bottom: 1px solid #e5ebe7;
  }
  .data-table td {
    font-size: 12.5px;
    color: #374151;
    padding: 0.3rem 0.55rem;
    line-height: 1.25;
    vertical-align: middle;
    border-bottom: 1px solid #f0f2f0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }
  .data-table tbody tr:nth-child(odd) td { background: #fff; }
  .data-table tbody tr:nth-child(even) td { background: #f9fafb; }
  .data-table tbody tr:hover td { background: #f0fdf4; }
  .data-table tbody tr.is-selected td { background: #ecfdf5; }
  .data-table tbody tr.is-selected:hover td { background: #dcfce7; }

  /* Sticky identity columns so name stays visible */
  .data-table .col-check {
    width: 2rem;
    text-align: center;
    padding-left: 0.45rem !important;
    padding-right: 0.25rem !important;
  }
  .data-table th.col-check,
  .data-table td.col-check {
    position: sticky;
    left: 0;
    z-index: 2;
    background: #f3f6f4;
  }
  .data-table tbody tr:nth-child(odd) td.col-check { background: #fff; }
  .data-table tbody tr:nth-child(even) td.col-check { background: #f9fafb; }
  .data-table tbody tr:hover td.col-check { background: #f0fdf4; }
  .data-table tbody tr.is-selected td.col-check { background: #ecfdf5; }

  .data-table .col-name { width: 18%; min-width: 9rem; }
  .data-table th.col-name,
  .data-table td.col-name {
    position: sticky;
    left: 2rem;
    z-index: 2;
    background: #f3f6f4;
    box-shadow: 4px 0 8px -6px rgba(0,0,0,0.12);
  }
  .data-table td.col-name {
    font-weight: 600;
    color: #1f2937;
    background: #fff;
  }
  .data-table tbody tr:nth-child(odd) td.col-name { background: #fff; }
  .data-table tbody tr:nth-child(even) td.col-name { background: #f9fafb; }
  .data-table tbody tr:hover td.col-name { background: #f0fdf4; }
  .data-table tbody tr.is-selected td.col-name { background: #ecfdf5; }

  .data-table .col-register { width: 22%; }
  .data-table .col-kab { width: 12%; }
  .data-table .col-kec { width: 12%; }
  .data-table .col-kelas { width: 7rem; }
  .data-table .col-anggota { width: 5.5rem; text-align: right; }
  .data-table .col-aksi { width: 4.75rem; text-align: right; }

  .data-table .mono-clip {
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    font-size: 11px;
    color: #6b7280;
  }

  .data-table .th-sortable {
    cursor: pointer;
    user-select: none;
    transition: background 0.12s ease, color 0.12s ease;
  }
  .data-table .th-sortable:hover {
    background: #e5ebe7;
    color: #374151;
  }
  .data-table .th-sortable .sort-icon {
    display: inline-flex;
    margin-left: 0.2rem;
    opacity: 0.35;
    vertical-align: middle;
    font-size: 0.75rem;
    line-height: 1;
  }
  .data-table .th-sortable:hover .sort-icon { opacity: 0.9; color: #1A6B3A; }

  .data-table .row-check,
  .data-table .check-all {
    width: 0.85rem; height: 0.85rem;
    accent-color: #1A6B3A; cursor: pointer;
  }

  /* Numbers */
  .num-active {
    color: #1f2937;
    font-weight: 600;
    font-variant-numeric: tabular-nums;
  }
  .num-empty-hint {
    display: inline-flex;
    align-items: center;
    font-size: 11px;
    font-weight: 400;
    font-style: italic;
    color: #d1d5db;
    font-variant-numeric: tabular-nums;
    letter-spacing: 0.02em;
  }

  /* Year badge (current vs past) */
  .badge-tahun {
    display: inline-flex;
    align-items: center;
    padding: 1px 7px;
    border-radius: 999px;
    font-size: 10.5px;
    font-weight: 600;
    font-variant-numeric: tabular-nums;
    letter-spacing: 0.01em;
    border: 1px solid transparent;
    line-height: 1.35;
  }
  .badge-tahun-current {
    background: #dcfce7;
    color: #166534;
    border-color: #86efac;
  }
  .badge-tahun-past {
    background: #f3f4f6;
    color: #6b7280;
    border-color: #e5e7eb;
  }

  /* Action icons — always visible chrome */
  .btn-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 1.65rem;
    height: 1.65rem;
    border-radius: 0.4rem;
    color: #4b5563;
    background: #fff;
    border: 1px solid #e5e7eb;
    transition: background 0.12s ease, color 0.12s ease, border-color 0.12s ease;
  }
  .btn-icon:hover {
    background: #f0fdf4;
    color: #1A6B3A;
    border-color: #86efac;
  }
  .btn-icon.btn-icon-muted:hover {
    background: #f3f4f6;
    color: #111827;
    border-color: #d1d5db;
  }
  .btn-icon i { font-size: 0.85rem; line-height: 1; }

  /* ── Badges — strong semantic colors (hardcoded fallbacks) ── */
  .badge {
    display: inline-flex;
    align-items: center;
    padding: 2px 8px;
    border-radius: 999px;
    font-size: 10.5px;
    font-weight: 600;
    letter-spacing: 0.01em;
    border: 1px solid transparent;
    line-height: 1.3;
  }
  .badge-utama {
    background: #fef3c7 !important;
    color: #92400e !important;
    border-color: #f59e0b !important;
  }
  .badge-madya {
    background: #dcfce7 !important;
    color: #166534 !important;
    border-color: #4ade80 !important;
  }
  .badge-pemula {
    background: #e2e8f0 !important;
    color: #334155 !important;
    border-color: #94a3b8 !important;
  }

  /* Status generik */
  .badge-aktif, .badge-disetujui, .badge-final, .badge-selesai, .badge-sudah {
    background: #dcfce7 !important; color: #166534 !important; border-color: #86efac !important;
  }
  .badge-nonaktif, .badge-ditolak, .badge-belum {
    background: #f3f4f6 !important; color: #6b7280 !important; border-color: #d1d5db !important;
  }
  .badge-draft, .badge-proses, .badge-diajukan {
    background: #e0f2fe !important; color: #075985 !important; border-color: #7dd3fc !important;
  }

  /* Skema KPS */
  .badge-skema-hkm { background: #f3e8ff !important; color: #6b21a8 !important; border-color: #d8b4fe !important; }
  .badge-skema-hd { background: #ffedd5 !important; color: #9a3412 !important; border-color: #fdba74 !important; }
  .badge-skema-htr { background: #ede9fe !important; color: #5b21b6 !important; border-color: #c4b5fd !important; }
  .badge-skema-kulin { background: #dcfce7 !important; color: #166534 !important; border-color: #86efac !important; }
  .badge-skema-iphps { background: #ccfbf1 !important; color: #115e59 !important; border-color: #5eead4 !important; }

  .data-table td.cell-empty {
    white-space: normal !important;
    overflow: visible !important;
    text-overflow: unset !important;
  }
  .data-table td.cell-stack {
    white-space: normal !important;
    line-height: 1.25;
  }
  .data-table td.cell-stack .sub {
    display: block;
    font-size: 10px;
    color: #9ca3af;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    margin-top: 1px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }
  .pager {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
    padding: 0.5rem 0.75rem;
    border-top: 1px solid #f0f2f0;
    background: rgba(249, 250, 251, 0.7);
  }
  .pager-meta { font-size: 0.75rem; color: #6b7280; }
  .pager-links { display: flex; gap: 0.4rem; }
  .pager-links a {
    font-size: 0.75rem;
    padding: 0.2rem 0.6rem;
    border-radius: 0.375rem;
    border: 1px solid #e5e7eb;
    background: #fff;
    color: #4b5563;
    text-decoration: none;
  }
  .pager-links a:hover { background: #f9fafb; }

  /* ── Global topbar search ── */
  .global-search {
    display: flex;
    align-items: center;
    background: #fff;
    border: 1px solid #d1e0d6;
    border-radius: 0.5rem;
    overflow: hidden;
    height: 2rem;
    min-width: 15.5rem;
  }
  .global-search select {
    border: 0;
    border-right: 1px solid #d1e0d6;
    background: #f0f7f2;
    font-size: 11px;
    font-weight: 600;
    color: #166534;
    padding: 0 0.45rem;
    height: 100%;
    outline: none;
    max-width: 4.5rem;
    cursor: pointer;
  }
  .global-search input {
    border: 0; flex: 1; font-size: 12px;
    padding: 0 0.55rem; outline: none; min-width: 0; height: 100%;
    background: transparent;
  }
  .global-search .gs-icon {
    color: #3AA870; padding-left: 0.5rem; font-size: 0.9rem;
  }

  /* ── Empty state ── */
  .empty-state {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    padding: 2.5rem 1.5rem; text-align: center; color: #6b7280;
  }
  .empty-state-icon {
    width: 3rem; height: 3rem; border-radius: 999px; background: #f0fdf4;
    color: #3AA870; display: flex; align-items: center; justify-content: center;
    margin-bottom: 0.75rem; font-size: 1.35rem;
  }
  .empty-state-title { font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 0.25rem; }
  .empty-state-desc { font-size: 0.75rem; color: #9ca3af; max-width: 18rem; line-height: 1.45; }

  ::-webkit-scrollbar { width: 5px; height: 5px; }
  ::-webkit-scrollbar-track { background: transparent; }
  ::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 99px; }

  .legend-dot { width: 10px; height: 10px; border-radius: 2px; flex-shrink: 0; }
  @keyframes pulse-dot { 0%, 100% { opacity: 1; } 50% { opacity: 0.4; } }
  .live-dot { animation: pulse-dot 2s ease-in-out infinite; }

  .stat-card {
    background: white;
    border-radius: 0.75rem;
    border: 1px solid #e8ece9;
    box-shadow: var(--shadow-card, 0 1px 2px rgba(0,0,0,0.04));
  }

  .bulk-bar {
    display: none; align-items: center; justify-content: space-between;
    gap: 0.75rem; flex-wrap: wrap; padding: 0.5rem 0.85rem;
    background: #f0fdf4; border: 1px solid rgba(26, 107, 58, 0.22);
    border-radius: 0.5rem; margin-bottom: 0.65rem;
    box-shadow: 0 1px 2px rgba(26, 107, 58, 0.06);
  }
  .bulk-bar.is-visible { display: flex; }
  .bulk-bar[hidden] { display: none !important; }
  .bulk-bar.is-visible[hidden] { display: flex !important; }
  .bulk-bar-count { font-size: 0.75rem; color: #166534; font-weight: 600; }
  .bulk-bar-actions { display: flex; flex-wrap: wrap; gap: 0.35rem; }
  .bulk-btn {
    display: inline-flex; align-items: center; gap: 0.3rem;
    font-size: 0.75rem; font-weight: 500; padding: 0.32rem 0.7rem;
    border-radius: 0.4rem; border: 1px solid transparent; cursor: pointer;
    background: #fff; transition: background 0.12s ease, border-color 0.12s ease;
  }
  .bulk-btn-export { color: #166534; border-color: rgba(26, 107, 58, 0.25); }
  .bulk-btn-export:hover { background: #ecfdf5; }
  .bulk-btn-delete { color: #b91c1c; border-color: #fecaca; }
  .bulk-btn-delete:hover { background: #fef2f2; }
  .bulk-btn-clear { background: transparent; color: #6b7280; border-color: #e5e7eb; }
  .bulk-btn-clear:hover { background: #f9fafb; }

  .btn-primary-add {
    display: inline-flex; align-items: center; gap: 0.35rem;
    height: 2rem; padding: 0 0.85rem;
    background: #1A6B3A; color: #fff; font-size: 12.5px; font-weight: 600;
    border-radius: 0.5rem; text-decoration: none;
    box-shadow: 0 1px 2px rgba(26, 107, 58, 0.2);
  }
  .btn-primary-add:hover { background: #155730; color: #fff; }

  @media (max-width: 768px) {
    .global-search { min-width: 0; width: 100%; }
    body.sidebar-collapsed .app-sidebar,
    .app-sidebar { width: var(--sidebar-width, 14rem); }
    body.sidebar-collapsed .app-main,
    .app-main { margin-left: 0; }
    .data-table th.col-check,
    .data-table td.col-check,
    .data-table th.col-name,
    .data-table td.col-name {
      position: static; box-shadow: none;
    }
  }
</style>
