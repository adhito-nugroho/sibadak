/**
 * SIBADAK — shared UI behaviors
 * sidebar collapse, data-table enhancements (checkbox / empty / sort / year / title), global search
 */
(function () {
  'use strict';

  var STORAGE_KEY = 'sibadak_sidebar_collapsed';
  var CURRENT_YEAR = new Date().getFullYear();
  var SORTABLE_KEYS = [
    'nama', 'sasaran', 'unit', 'tahun', 'anggota', 'pelaksana',
    'register', 'kelas', 'kps', 'jumlah', 'luas', 'lokasi'
  ];

  function applySidebarState(collapsed) {
    document.body.classList.toggle('sidebar-collapsed', collapsed);
    var btn = document.getElementById('sidebar-toggle');
    if (btn) {
      var icon = btn.querySelector('i');
      if (icon) {
        icon.className = collapsed
          ? 'ti ti-layout-sidebar-left-expand text-base leading-none'
          : 'ti ti-layout-sidebar-left-collapse text-base leading-none';
      }
      btn.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
      btn.title = collapsed ? 'Perluas sidebar' : 'Ciutkan sidebar';
    }
  }

  function initSidebar() {
    var saved = false;
    try {
      saved = localStorage.getItem(STORAGE_KEY) === '1';
    } catch (e) { /* ignore */ }

    applySidebarState(saved);

    var btn = document.getElementById('sidebar-toggle');
    if (btn) {
      btn.addEventListener('click', function () {
        var next = !document.body.classList.contains('sidebar-collapsed');
        applySidebarState(next);
        try {
          localStorage.setItem(STORAGE_KEY, next ? '1' : '0');
        } catch (e) { /* ignore */ }
      });
    }
  }

  function headerLabel(th) {
    var clone = th.cloneNode(true);
    var icons = clone.querySelectorAll('i, .sort-icon');
    for (var i = 0; i < icons.length; i++) icons[i].remove();
    return (clone.textContent || '').replace(/\s+/g, ' ').trim();
  }

  function isSortableHeader(label) {
    if (!label) return false;
    var t = label.toLowerCase();
    if (t === 'aksi' || t.indexOf('aksi') === 0) return false;
    for (var i = 0; i < SORTABLE_KEYS.length; i++) {
      var key = SORTABLE_KEYS[i];
      if (t === key || t.indexOf(key + ' ') === 0 || t.indexOf(' ' + key) !== -1) {
        // Skip long "total …" metric headers except plain Unit / Tahun / Jumlah / Luas
        if (t.indexOf('total') === 0 && key !== 'unit' && key !== 'tahun') continue;
        return true;
      }
    }
    return false;
  }

  function isYearHeader(label) {
    var t = (label || '').toLowerCase();
    return t === 'tahun' || t.indexOf('tahun ') === 0;
  }

  function getRowChecks(table) {
    return Array.prototype.slice.call(
      table.querySelectorAll('tbody input.row-check')
    );
  }

  function getMaster(table) {
    return table.querySelector('thead input.check-all, thead #check-all');
  }

  function syncRowSelected(cb) {
    var tr = cb.closest('tr');
    if (tr) tr.classList.toggle('is-selected', cb.checked);
  }

  function updateBulkBar(table) {
    var bar = document.getElementById('bulk-bar');
    var countEl = document.getElementById('bulk-bar-count');
    if (!bar) return;

    var rows = Array.prototype.slice.call(
      document.querySelectorAll('table.data-table tbody input.row-check')
    );
    var checked = rows.filter(function (cb) { return cb.checked; }).length;
    var visible = checked > 0;
    bar.classList.toggle('is-visible', visible);
    if (visible) bar.removeAttribute('hidden');
    else bar.setAttribute('hidden', '');
    if (countEl) countEl.textContent = checked + ' dipilih';
  }

  function getSelectedIds() {
    return Array.prototype.slice.call(
      document.querySelectorAll('table.data-table tbody input.row-check')
    )
      .filter(function (cb) { return cb.checked; })
      .map(function (cb) { return cb.value; })
      .filter(Boolean);
  }

  function clearAllSelections() {
    document.querySelectorAll('table.data-table').forEach(function (table) {
      var master = getMaster(table);
      if (master) {
        master.checked = false;
        master.indeterminate = false;
      }
      getRowChecks(table).forEach(function (cb) {
        cb.checked = false;
        syncRowSelected(cb);
      });
    });
    updateBulkBar();
  }

  function ensureBulkBar(table) {
    var existing = document.getElementById('bulk-bar');
    if (existing) return existing;

    var host = table.closest('.stat-card') || table.parentElement;
    var bar = document.createElement('div');
    bar.id = 'bulk-bar';
    bar.className = 'bulk-bar';
    bar.setAttribute('role', 'status');
    bar.setAttribute('aria-live', 'polite');
    bar.setAttribute('hidden', '');
    bar.innerHTML =
      '<span id="bulk-bar-count" class="bulk-bar-count">0 dipilih</span>'
      + '<div class="bulk-bar-actions">'
      + '<button type="button" id="bulk-export" class="bulk-btn bulk-btn-export">'
      + '<i class="ti ti-download text-sm"></i> Export terpilih</button>'
      + '<button type="button" id="bulk-delete" class="bulk-btn bulk-btn-delete">'
      + '<i class="ti ti-trash text-sm"></i> Hapus terpilih</button>'
      + '<button type="button" id="bulk-clear" class="bulk-btn bulk-btn-clear">Batal</button>'
      + '</div>';

    if (host && host.parentNode) {
      host.parentNode.insertBefore(bar, host);
    } else {
      document.body.insertBefore(bar, document.body.firstChild);
    }
    return bar;
  }

  function resolveBulkUrl() {
    var bar = document.getElementById('bulk-bar');
    var custom = bar && bar.getAttribute('data-bulk-url');
    if (custom) return custom;
    var path = (window.location.pathname || '').replace(/\/+$/, '');
    if (!path) return '';
    return path + '/bulk';
  }

  function submitBulkAction(action) {
    var ids = getSelectedIds();
    if (ids.length === 0) return;
    var url = resolveBulkUrl();
    var token = window.CSRF_TOKEN || '';
    if (!url || !token) {
      window.alert('Bulk action belum siap di halaman ini.');
      return;
    }

    var form = document.createElement('form');
    form.method = 'POST';
    form.action = url;
    form.style.display = 'none';

    var csrf = document.createElement('input');
    csrf.type = 'hidden';
    csrf.name = 'csrf_token';
    csrf.value = token;
    form.appendChild(csrf);

    var act = document.createElement('input');
    act.type = 'hidden';
    act.name = 'action';
    act.value = action;
    form.appendChild(act);

    ids.forEach(function (id) {
      var input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'ids[]';
      input.value = id;
      form.appendChild(input);
    });

    document.body.appendChild(form);
    form.submit();
  }

  function moduleSupportsDeactivate() {
    var path = (window.location.pathname || '').replace(/\/+$/, '');
    return /(?:^|\/)kth$/.test(path);
  }

  function bindBulkActions() {
    var clearBtn = document.getElementById('bulk-clear');
    if (clearBtn && !clearBtn.getAttribute('data-bound')) {
      clearBtn.setAttribute('data-bound', '1');
      clearBtn.addEventListener('click', clearAllSelections);
    }

    var exportBtn = document.getElementById('bulk-export');
    if (exportBtn && !exportBtn.getAttribute('data-bound')) {
      exportBtn.setAttribute('data-bound', '1');
      exportBtn.addEventListener('click', function () {
        var ids = getSelectedIds();
        if (ids.length === 0) return;
        submitBulkAction('export');
      });
    }

    var deleteBtn = document.getElementById('bulk-delete');
    if (deleteBtn) {
      if (!moduleSupportsDeactivate()) {
        deleteBtn.hidden = true;
      } else if (!deleteBtn.getAttribute('data-bound')) {
        deleteBtn.setAttribute('data-bound', '1');
        deleteBtn.addEventListener('click', function () {
          var ids = getSelectedIds();
          if (ids.length === 0) return;
          if (!window.confirm('Nonaktifkan ' + ids.length + ' KTH terpilih?')) {
            return;
          }
          submitBulkAction('deactivate');
        });
      }
    }
  }

  function extractRowId(tr) {
    var marked = tr.querySelector('[data-row-id]');
    if (marked) return marked.getAttribute('data-row-id') || '';
    var existing = tr.querySelector('input.row-check');
    if (existing && existing.value) return existing.value;
    var links = tr.querySelectorAll('a[href]');
    for (var i = 0; i < links.length; i++) {
      var href = links[i].getAttribute('href') || '';
      var m = href.match(/\/(\d+)(?:\/edit)?\/?$/);
      if (m) return m[1];
    }
    return '';
  }

  function ensureCheckColumn(table) {
    var theadRow = table.querySelector('thead tr');
    if (!theadRow) return;
    if (theadRow.querySelector('.col-check, #check-all, .check-all')) return;

    var th = document.createElement('th');
    th.className = 'col-check';
    th.innerHTML = '<input type="checkbox" class="check-all" title="Pilih semua di halaman ini" aria-label="Pilih semua">';
    theadRow.insertBefore(th, theadRow.firstChild);

    var bodyRows = table.querySelectorAll('tbody tr');
    for (var i = 0; i < bodyRows.length; i++) {
      var tr = bodyRows[i];
      var emptyCell = tr.querySelector('td.cell-empty, td[colspan]');
      if (emptyCell) {
        var span = parseInt(emptyCell.getAttribute('colspan') || '1', 10);
        emptyCell.setAttribute('colspan', String(span + 1));
        continue;
      }
      var rowId = extractRowId(tr);
      var firstData = tr.querySelector('td');
      var label = firstData ? (firstData.getAttribute('title') || firstData.textContent || 'baris').trim() : 'baris';
      var td = document.createElement('td');
      td.className = 'col-check';
      var cb = document.createElement('input');
      cb.type = 'checkbox';
      cb.className = 'row-check';
      cb.name = 'ids[]';
      cb.setAttribute('aria-label', 'Pilih ' + label.slice(0, 80));
      if (rowId) cb.value = rowId;
      td.appendChild(cb);
      tr.insertBefore(td, tr.firstChild);
    }
  }

  function enhanceSortHeaders(table) {
    var heads = table.querySelectorAll('thead th');
    for (var i = 0; i < heads.length; i++) {
      var th = heads[i];
      if (th.classList.contains('col-check') || th.classList.contains('col-aksi')) continue;
      var label = headerLabel(th);
      if (!isSortableHeader(label)) continue;
      th.classList.add('th-sortable');
      if (!th.querySelector('.sort-icon')) {
        var icon = document.createElement('i');
        icon.className = 'ti ti-selector sort-icon';
        icon.setAttribute('aria-hidden', 'true');
        th.appendChild(document.createTextNode(' '));
        th.appendChild(icon);
      }
    }
  }

  function wrapEmptyHints(table) {
    var cells = table.querySelectorAll('tbody td');
    for (var i = 0; i < cells.length; i++) {
      var td = cells[i];
      if (td.classList.contains('col-check') || td.classList.contains('col-aksi') || td.classList.contains('cell-empty')) continue;
      if (td.querySelector('.num-empty-hint, .badge, .btn-icon, a, input, button')) continue;

      var text = (td.textContent || '').replace(/\s+/g, ' ').trim();
      if (text === '—' || text === '-' || text === '–' || text === '') {
        if (td.querySelector('.num-empty-hint')) continue;
        td.innerHTML = '';
        var span = document.createElement('span');
        span.className = 'num-empty-hint';
        span.textContent = '—';
        span.title = 'Kosong';
        td.appendChild(span);
      }
    }
  }

  function enhanceTitles(table) {
    var cells = table.querySelectorAll('tbody td');
    for (var i = 0; i < cells.length; i++) {
      var td = cells[i];
      if (td.classList.contains('col-check') || td.classList.contains('col-aksi') || td.classList.contains('cell-empty')) continue;
      if (td.getAttribute('title')) continue;
      if (td.querySelector('.btn-icon, input, button, a.btn-icon')) continue;
      if (td.querySelector('.num-empty-hint')) continue;

      var text = (td.textContent || '').replace(/\s+/g, ' ').trim();
      if (!text || text === '—') continue;
      td.setAttribute('title', text);
    }
  }

  function enhanceYearBadges(table) {
    var heads = table.querySelectorAll('thead th');
    var yearIdx = -1;
    for (var i = 0; i < heads.length; i++) {
      if (isYearHeader(headerLabel(heads[i]))) {
        yearIdx = i;
        break;
      }
    }
    if (yearIdx < 0) return;

    var rows = table.querySelectorAll('tbody tr');
    for (var r = 0; r < rows.length; r++) {
      var cells = rows[r].children;
      if (!cells[yearIdx] || cells[yearIdx].classList.contains('cell-empty')) continue;
      var td = cells[yearIdx];
      if (td.querySelector('.badge-tahun')) continue;

      var raw = (td.textContent || '').replace(/\s+/g, ' ').trim();
      var m = raw.match(/^(\d{4})$/);
      if (!m) {
        // Allow nested num-active wrapping a bare year
        var inner = td.querySelector('.num-active');
        if (inner) {
          var ir = (inner.textContent || '').trim();
          m = ir.match(/^(\d{4})$/);
          if (!m) continue;
          var y1 = parseInt(m[1], 10);
          var badge1 = document.createElement('span');
          badge1.className = 'badge-tahun ' + (y1 === CURRENT_YEAR ? 'badge-tahun-current' : 'badge-tahun-past');
          badge1.textContent = String(y1);
          td.innerHTML = '';
          td.appendChild(badge1);
        }
        continue;
      }

      var y = parseInt(m[1], 10);
      var badge = document.createElement('span');
      badge.className = 'badge-tahun ' + (y === CURRENT_YEAR ? 'badge-tahun-current' : 'badge-tahun-past');
      badge.textContent = String(y);
      td.innerHTML = '';
      td.appendChild(badge);
    }
  }

  function bindTableSelection(table) {
    if (table.getAttribute('data-select-bound') === '1') return;
    table.setAttribute('data-select-bound', '1');

    var master = getMaster(table);
    if (master) {
      master.classList.add('check-all');
      master.addEventListener('change', function () {
        getRowChecks(table).forEach(function (cb) {
          cb.checked = master.checked;
          syncRowSelected(cb);
        });
        master.indeterminate = false;
        updateBulkBar(table);
      });
    }

    table.addEventListener('change', function (e) {
      var t = e.target;
      if (!t || !t.classList || !t.classList.contains('row-check')) return;
      syncRowSelected(t);
      syncMasterState(table);
    });

    getRowChecks(table).forEach(syncRowSelected);
  }

  function syncMasterState(table) {
    var master = getMaster(table);
    if (!master) return;
    var rows = getRowChecks(table);
    var checked = rows.filter(function (cb) { return cb.checked; }).length;
    master.checked = rows.length > 0 && checked === rows.length;
    master.indeterminate = checked > 0 && checked < rows.length;
    updateBulkBar(table);
  }

  function initDataTables() {
    var tables = document.querySelectorAll('table.data-table');
    for (var i = 0; i < tables.length; i++) {
      var table = tables[i];
      ensureBulkBar(table);
      ensureCheckColumn(table);
      enhanceSortHeaders(table);
      wrapEmptyHints(table);
      enhanceYearBadges(table);
      enhanceTitles(table);
      bindTableSelection(table);
    }
    bindBulkActions();
  }

  function initGlobalSearch() {
    var form = document.getElementById('global-search-form');
    var scope = document.getElementById('global-search-scope');
    if (!form || !scope || !window.SIBADAK_SEARCH_SCOPES) return;

    var sync = function () {
      var path = window.SIBADAK_SEARCH_SCOPES[scope.value];
      if (path) form.action = path;
    };
    scope.addEventListener('change', sync);
    sync();
  }

  function boot() {
    initSidebar();
    initDataTables();
    initGlobalSearch();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
