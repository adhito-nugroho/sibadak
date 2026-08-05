<?php
/** @var list<array{id:int,nama:string,urutan:int}> $hhkKomoditas */
/** @var list<array{id:int,nama:string,satuan:string,urutan:int}> $hhbkKomoditas */
$bulanList = ['1'=>'Januari','2'=>'Februari','3'=>'Maret','4'=>'April','5'=>'Mei','6'=>'Juni',
              '7'=>'Juli','8'=>'Agustus','9'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'];
$canMut = in_array(user_role(), ['admin', 'operator'], true);
?>
<div class="space-y-5 fade-up">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h2 class="text-sm font-semibold text-gray-800">Laporan Rekonsiliasi HHK & HHBK</h2>
            <p class="text-[10px] text-gray-400 mt-0.5">Berita Acara + Lampiran Rekapitulasi — Export Excel & PDF</p>
        </div>
        <?php if ($canMut): ?>
        <div class="flex gap-2">
            <a href="<?= htmlspecialchars(APP_URL . '/laporan/komoditas-hhk', ENT_QUOTES, 'UTF-8') ?>" class="flex items-center gap-1.5 border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 text-xs font-medium px-3 py-2 rounded-lg transition-colors">
                <i class="ti ti-settings text-sm leading-none"></i> Komoditas HHK
            </a>
            <a href="<?= htmlspecialchars(APP_URL . '/laporan/komoditas-hhbk', ENT_QUOTES, 'UTF-8') ?>" class="flex items-center gap-1.5 border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 text-xs font-medium px-3 py-2 rounded-lg transition-colors">
                <i class="ti ti-settings text-sm leading-none"></i> Komoditas HHBK
            </a>
        </div>
        <?php endif; ?>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Card HHK -->
        <div class="bg-white rounded-xl border border-gray-100 p-4">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-8 h-8 rounded-lg bg-forest-50 flex items-center justify-center">
                    <i class="ti ti-trees text-forest-700 text-base leading-none"></i>
                </div>
                <div>
                    <h3 class="text-xs font-semibold text-gray-800">Laporan HHK</h3>
                    <p class="text-[10px] text-gray-400">Hasil Hutan Kayu</p>
                </div>
            </div>
            <form method="get" action="<?= htmlspecialchars(APP_URL . '/laporan/preview', ENT_QUOTES, 'UTF-8') ?>" class="space-y-3">
                <input type="hidden" name="jenis" value="hhk">
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wide mb-1">Bulan</label>
                        <select name="bulan" class="w-full text-xs px-3 py-2 rounded-lg border border-gray-200 outline-none">
                            <?php foreach ($bulanList as $b => $n): ?><option value="<?= $b ?>" <?= (int)date('n') === (int)$b ? 'selected' : '' ?>><?= $n ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wide mb-1">Tahun</label>
                        <select name="tahun" class="w-full text-xs px-3 py-2 rounded-lg border border-gray-200 outline-none">
                            <?php foreach ($yearOptions as $y): ?><option value="<?= $y ?>" <?= $y === (int)date('Y') ? 'selected' : '' ?>><?= $y ?></option><?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wide mb-1">Tanggal Penandatanganan</label>
                    <input type="date" name="tanggal_ttd" value="<?= date('Y-m-d') ?>" class="w-full text-xs px-3 py-2 rounded-lg border border-gray-200 outline-none">
                </div>
                <div class="flex gap-2 pt-1">
                    <button type="submit" formaction="<?= htmlspecialchars(APP_URL . '/laporan/preview', ENT_QUOTES, 'UTF-8') ?>" class="flex-1 flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg border border-gray-200 text-gray-700 text-xs font-medium hover:bg-gray-50">
                        <i class="ti ti-eye text-sm leading-none"></i> Preview
                    </button>
                    <button type="submit" formaction="<?= htmlspecialchars(APP_URL . '/laporan/export-excel', ENT_QUOTES, 'UTF-8') ?>" class="flex-1 flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg bg-green-700 text-white text-xs font-medium hover:bg-green-800">
                        <i class="ti ti-file-spreadsheet text-sm leading-none"></i> Excel
                    </button>
                    <button type="submit" formaction="<?= htmlspecialchars(APP_URL . '/laporan/export-pdf', ENT_QUOTES, 'UTF-8') ?>" class="flex-1 flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg bg-red-600 text-white text-xs font-medium hover:bg-red-700">
                        <i class="ti ti-file-type-pdf text-sm leading-none"></i> PDF
                    </button>
                </div>
            </form>
            <div class="mt-3 pt-3 border-t border-gray-100">
                <p class="text-[10px] text-gray-400"><?= count($hhkKomoditas) ?> jenis komoditas terdaftar</p>
            </div>
        </div>

        <!-- Card HHBK -->
        <div class="bg-white rounded-xl border border-gray-100 p-4">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center">
                    <i class="ti ti-leaf-2 text-amber-700 text-base leading-none"></i>
                </div>
                <div>
                    <h3 class="text-xs font-semibold text-gray-800">Laporan HHBK</h3>
                    <p class="text-[10px] text-gray-400">Hasil Hutan Bukan Kayu</p>
                </div>
            </div>
            <form method="get" action="<?= htmlspecialchars(APP_URL . '/laporan/preview', ENT_QUOTES, 'UTF-8') ?>" class="space-y-3">
                <input type="hidden" name="jenis" value="hhbk">
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wide mb-1">Bulan</label>
                        <select name="bulan" class="w-full text-xs px-3 py-2 rounded-lg border border-gray-200 outline-none">
                            <?php foreach ($bulanList as $b => $n): ?><option value="<?= $b ?>" <?= (int)date('n') === (int)$b ? 'selected' : '' ?>><?= $n ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wide mb-1">Tahun</label>
                        <select name="tahun" class="w-full text-xs px-3 py-2 rounded-lg border border-gray-200 outline-none">
                            <?php foreach ($yearOptions as $y): ?><option value="<?= $y ?>" <?= $y === (int)date('Y') ? 'selected' : '' ?>><?= $y ?></option><?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wide mb-1">Tanggal Penandatanganan</label>
                    <input type="date" name="tanggal_ttd" value="<?= date('Y-m-d') ?>" class="w-full text-xs px-3 py-2 rounded-lg border border-gray-200 outline-none">
                </div>
                <div class="flex gap-2 pt-1">
                    <button type="submit" formaction="<?= htmlspecialchars(APP_URL . '/laporan/preview', ENT_QUOTES, 'UTF-8') ?>" class="flex-1 flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg border border-gray-200 text-gray-700 text-xs font-medium hover:bg-gray-50">
                        <i class="ti ti-eye text-sm leading-none"></i> Preview
                    </button>
                    <button type="submit" formaction="<?= htmlspecialchars(APP_URL . '/laporan/export-excel', ENT_QUOTES, 'UTF-8') ?>" class="flex-1 flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg bg-green-700 text-white text-xs font-medium hover:bg-green-800">
                        <i class="ti ti-file-spreadsheet text-sm leading-none"></i> Excel
                    </button>
                    <button type="submit" formaction="<?= htmlspecialchars(APP_URL . '/laporan/export-pdf', ENT_QUOTES, 'UTF-8') ?>" class="flex-1 flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg bg-red-600 text-white text-xs font-medium hover:bg-red-700">
                        <i class="ti ti-file-type-pdf text-sm leading-none"></i> PDF
                    </button>
                </div>
            </form>
            <div class="mt-3 pt-3 border-t border-gray-100">
                <p class="text-[10px] text-gray-400"><?= count($hhbkKomoditas) ?> jenis komoditas terdaftar</p>
            </div>
        </div>
    </div>
</div>
