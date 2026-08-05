<?php
/** @var array $dashboard */
$s = $dashboard['stats'];
$d = $dashboard['delta'] ?? ['kth' => 0, 'kps' => 0, 'anggota' => 0, 'rhl_ha' => 0.0];
$kthKab = $dashboard['kth_per_kab'];
$kpsSkema = $dashboard['kps_per_skema'];
$kthKelas = $dashboard['kth_per_kelas'];
$rhlKeg = $dashboard['rhl_per_kegiatan'];
$kthBaru = $dashboard['kth_terbaru'];
$kpsBaru = $dashboard['kps_terbaru'];
$err = $dashboard['error'];

$currentKabupaten = isset($_GET['kabupaten']) ? (int) $_GET['kabupaten'] : 0;
$currentTahun = isset($_GET['tahun']) ? (int) $_GET['tahun'] : 0;

$totalKpsSkema = 0;
foreach ($kpsSkema as $row) {
    $totalKpsSkema += $row['jumlah'];
}
?>

<?php if ($err !== null): ?>
<div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-5">
    <p class="text-xs text-red-700 font-medium"><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></p>
</div>
<?php endif; ?>

<!-- Filter bar -->
<div class="flex items-center gap-2 mb-4 flex-wrap fade-up">
    <span class="text-xs text-gray-500">Filter:</span>
    <a href="?kabupaten=0<?= $currentTahun ? '&tahun=' . $currentTahun : '' ?>"
       class="text-xs px-3 py-1.5 rounded-full border transition-colors <?= $currentKabupaten === 0 ? 'bg-forest-800 text-white border-forest-800' : 'border-gray-200 bg-white text-gray-600 hover:border-gray-300' ?>">Semua</a>
    <a href="?kabupaten=1<?= $currentTahun ? '&tahun=' . $currentTahun : '' ?>"
       class="text-xs px-3 py-1.5 rounded-full border transition-colors <?= $currentKabupaten === 1 ? 'bg-forest-800 text-white border-forest-800' : 'border-gray-200 bg-white text-gray-600 hover:border-gray-300' ?>">Bojonegoro</a>
    <a href="?kabupaten=2<?= $currentTahun ? '&tahun=' . $currentTahun : '' ?>"
       class="text-xs px-3 py-1.5 rounded-full border transition-colors <?= $currentKabupaten === 2 ? 'bg-forest-800 text-white border-forest-800' : 'border-gray-200 bg-white text-gray-600 hover:border-gray-300' ?>">Tuban</a>
    <a href="?kabupaten=3<?= $currentTahun ? '&tahun=' . $currentTahun : '' ?>"
       class="text-xs px-3 py-1.5 rounded-full border transition-colors <?= $currentKabupaten === 3 ? 'bg-forest-800 text-white border-forest-800' : 'border-gray-200 bg-white text-gray-600 hover:border-gray-300' ?>">Lamongan</a>
    <a href="?kabupaten=4<?= $currentTahun ? '&tahun=' . $currentTahun : '' ?>"
       class="text-xs px-3 py-1.5 rounded-full border transition-colors <?= $currentKabupaten === 4 ? 'bg-forest-800 text-white border-forest-800' : 'border-gray-200 bg-white text-gray-600 hover:border-gray-300' ?>">Gresik</a>
    <div class="ml-auto flex items-center gap-2">
        <span class="text-xs text-gray-500">Periode:</span>
        <select onchange="window.location.href='?kabupaten=<?= $currentKabupaten ?>&tahun='+this.value"
                class="text-xs border border-gray-200 rounded-md px-2 py-1.5 bg-white text-gray-700 focus:outline-none focus:ring-1 focus:ring-forest-500">
            <option value="">Semua Tahun</option>
            <?php for ($y = (int) date('Y'); $y >= 2020; $y--): ?>
            <option value="<?= $y ?>" <?= $currentTahun === $y ? 'selected' : '' ?>>Tahun <?= $y ?></option>
            <?php endfor; ?>
        </select>
    </div>
</div>

<!-- Stat cards -->
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-3 mb-5">
    <!-- Card 1: KTH -->
    <div class="bg-white rounded-xl border border-gray-100 p-4 relative overflow-hidden fade-up delay-1">
        <div class="absolute top-0 left-0 right-0 h-[3px] rounded-t-xl" style="background:var(--color-primary)"></div>
        <div class="absolute top-3 right-3 flex items-center gap-1 text-[10px] font-medium text-forest-600">
            <span class="w-1.5 h-1.5 rounded-full bg-forest-500 live-dot"></span> Live
        </div>
        <div class="w-9 h-9 rounded-lg bg-green-50 flex items-center justify-center mb-3 mt-1">
            <i class="ti ti-building-community text-forest-700 text-lg leading-none"></i>
        </div>
        <p class="text-2xl font-semibold text-gray-900 leading-none"><?= format_id($s['kth']) ?></p>
        <p class="text-xs font-medium text-gray-700 mt-1.5">Total KTH Terdaftar</p>
        <p class="text-[10px] text-gray-400 mt-0.5">Aktif · 4 Kabupaten</p>
        <?php if ($d['kth'] != 0): ?>
        <p class="flex items-center gap-1 text-[10px] mt-2 <?= $d['kth'] > 0 ? 'text-green-600' : 'text-red-500' ?>">
            <i class="ti <?= $d['kth'] > 0 ? 'ti-trending-up' : 'ti-trending-down' ?> text-xs"></i>
            <?= $d['kth'] > 0 ? '+' : '' ?><?= $d['kth'] ?> KTH dari tahun lalu
        </p>
        <?php endif; ?>
    </div>

    <!-- Card 2: KPS -->
    <div class="bg-white rounded-xl border border-gray-100 p-4 relative overflow-hidden fade-up delay-2">
        <div class="absolute top-0 left-0 right-0 h-[3px] rounded-t-xl" style="background:var(--color-primary)"></div>
        <div class="absolute top-3 right-3 flex items-center gap-1 text-[10px] font-medium text-forest-600">
            <span class="w-1.5 h-1.5 rounded-full bg-forest-500 live-dot"></span> Live
        </div>
        <div class="w-9 h-9 rounded-lg bg-amber-50 flex items-center justify-center mb-3 mt-1">
            <i class="ti ti-trees text-amber-700 text-lg leading-none"></i>
        </div>
        <p class="text-2xl font-semibold text-gray-900 leading-none"><?= format_id($s['kps']) ?></p>
        <p class="text-xs font-medium text-gray-700 mt-1.5">Kelompok Perhutanan Sosial</p>
        <p class="text-[10px] text-gray-400 mt-0.5">HKm · HD · HTR · Kulin KK · IPHPS</p>
        <?php if ($d['kps'] != 0): ?>
        <p class="flex items-center gap-1 text-[10px] mt-2 <?= $d['kps'] > 0 ? 'text-green-600' : 'text-red-500' ?>">
            <i class="ti <?= $d['kps'] > 0 ? 'ti-trending-up' : 'ti-trending-down' ?> text-xs"></i>
            <?= $d['kps'] > 0 ? '+' : '' ?><?= $d['kps'] ?> kelompok tahun ini
        </p>
        <?php endif; ?>
    </div>

    <!-- Card 3: Anggota -->
    <div class="bg-white rounded-xl border border-gray-100 p-4 relative overflow-hidden fade-up delay-3">
        <div class="absolute top-0 left-0 right-0 h-[3px] rounded-t-xl" style="background:var(--color-primary)"></div>
        <div class="absolute top-3 right-3 flex items-center gap-1 text-[10px] font-medium text-forest-600">
            <span class="w-1.5 h-1.5 rounded-full bg-forest-500 live-dot"></span> Live
        </div>
        <div class="w-9 h-9 rounded-lg bg-blue-50 flex items-center justify-center mb-3 mt-1">
            <i class="ti ti-users text-blue-700 text-lg leading-none"></i>
        </div>
        <p class="text-2xl font-semibold text-gray-900 leading-none"><?= format_id($s['anggota']) ?></p>
        <p class="text-xs font-medium text-gray-700 mt-1.5">Total Anggota KTH</p>
        <p class="text-[10px] text-gray-400 mt-0.5">Σ jumlah anggota per KTH</p>
        <?php if ($d['anggota'] != 0): ?>
        <p class="flex items-center gap-1 text-[10px] mt-2 <?= $d['anggota'] > 0 ? 'text-green-600' : 'text-red-500' ?>">
            <i class="ti <?= $d['anggota'] > 0 ? 'ti-trending-up' : 'ti-trending-down' ?> text-xs"></i>
            <?= $d['anggota'] > 0 ? '+' : '' ?><?= format_id($d['anggota']) ?> anggota baru
        </p>
        <?php endif; ?>
    </div>

    <!-- Card 4: RHL -->
    <div class="bg-white rounded-xl border border-gray-100 p-4 relative overflow-hidden fade-up delay-4">
        <div class="absolute top-0 left-0 right-0 h-[3px] rounded-t-xl" style="background:var(--color-primary)"></div>
        <div class="absolute top-3 right-3 flex items-center gap-1 text-[10px] font-medium text-forest-600">
            <span class="w-1.5 h-1.5 rounded-full bg-forest-500 live-dot"></span> Live
        </div>
        <div class="w-9 h-9 rounded-lg bg-cyan-50 flex items-center justify-center mb-3 mt-1">
            <i class="ti ti-map-2 text-cyan-700 text-lg leading-none"></i>
        </div>
        <p class="leading-none">
            <span class="text-2xl font-semibold text-gray-900"><?= format_id($s['rhl_ha'], 1) ?></span>
            <span class="text-sm text-gray-400 font-normal"> Ha</span>
        </p>
        <p class="text-xs font-medium text-gray-700 mt-1.5">Luas RHL</p>
        <p class="text-[10px] text-gray-400 mt-0.5">Kumulatif semua tahun</p>
        <?php if ($d['rhl_ha'] != 0): ?>
        <p class="flex items-center gap-1 text-[10px] mt-2 <?= $d['rhl_ha'] > 0 ? 'text-green-600' : 'text-red-500' ?>">
            <i class="ti <?= $d['rhl_ha'] > 0 ? 'ti-trending-up' : 'ti-trending-down' ?> text-xs"></i>
            <?= $d['rhl_ha'] > 0 ? '+' : '' ?><?= format_id($d['rhl_ha'], 1) ?> Ha tahun ini
        </p>
        <?php endif; ?>
    </div>
</div>

<!-- Charts row -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-5">
    <!-- Bar chart: KTH per Kabupaten -->
    <div class="bg-white rounded-xl border border-gray-100 p-4 lg:col-span-2 fade-up delay-2">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-sm font-semibold text-gray-800">Distribusi KTH per Kabupaten</h2>
                <p class="text-[10px] text-gray-400 mt-0.5">Berdasarkan kelas kelompok</p>
            </div>
        </div>
        <?php if ($kthKab === []): ?>
            <p class="text-xs text-gray-500 py-8 text-center">Belum ada data KTH.</p>
        <?php else: ?>
        <div class="h-48">
            <canvas id="kthBarChart"></canvas>
        </div>
        <div class="flex items-center gap-4 mt-3">
            <span class="flex items-center gap-1.5 text-xs text-gray-600"><span class="legend-dot" style="background:#1A6B3A"></span> Utama</span>
            <span class="flex items-center gap-1.5 text-xs text-gray-600"><span class="legend-dot" style="background:#E07B3A"></span> Madya</span>
            <span class="flex items-center gap-1.5 text-xs text-gray-600"><span class="legend-dot" style="background:#4A90D9"></span> Pemula</span>
        </div>
        <?php endif; ?>
    </div>

    <!-- Donut chart: Skema KPS -->
    <div class="bg-white rounded-xl border border-gray-100 p-4 fade-up delay-3">
        <h2 class="text-sm font-semibold text-gray-800">Skema KPS</h2>
        <p class="text-[10px] text-gray-400 mt-0.5 mb-4">Distribusi per jenis skema</p>
        <?php if ($kpsSkema === []): ?>
            <p class="text-xs text-gray-500 text-center py-8">Belum ada data KPS.</p>
        <?php else: ?>
        <div class="flex justify-center mb-4">
            <div class="relative w-[140px] h-[140px]">
                <canvas id="kpsDonutChart"></canvas>
                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                    <span class="text-2xl font-semibold text-gray-900"><?= format_id($totalKpsSkema) ?></span>
                    <span class="text-[10px] text-gray-400">Total</span>
                </div>
            </div>
        </div>
        <div class="space-y-1.5">
            <?php foreach ($kpsSkema as $row): ?>
            <div class="flex justify-between items-center text-xs py-0.5">
                <span class="flex items-center gap-1.5 text-gray-600">
                    <span class="w-2 h-2 rounded-full flex-shrink-0" style="background:<?= htmlspecialchars($row['warna'], ENT_QUOTES, 'UTF-8') ?>"></span>
                    <?= htmlspecialchars($row['skema'], ENT_QUOTES, 'UTF-8') ?>
                </span>
                <span class="text-gray-700 font-medium"><?= format_id($row['jumlah']) ?> <span class="text-gray-400 font-normal">(<?= format_id($row['pct'], 0) ?>%)</span></span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Second row: Kelas KTH + RHL Kegiatan -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-5">
    <!-- Kelas KTH -->
    <div class="bg-white rounded-xl border border-gray-100 p-4 fade-up delay-2">
        <h2 class="text-sm font-semibold text-gray-800 mb-1">Kelas KTH</h2>
        <p class="text-[10px] text-gray-400 mb-4">Agregasi per kelas</p>
        <?php if ($kthKelas === []): ?>
            <p class="text-xs text-gray-500">—</p>
        <?php else: ?>
        <?php
        $kelasColors = ['Utama' => '#1A6B3A', 'Madya' => '#E07B3A', 'Pemula' => '#4A90D9'];
        ?>
        <div class="space-y-3">
            <?php foreach ($kthKelas as $row): ?>
            <?php $color = $kelasColors[$row['kelas']] ?? '#6b7280'; ?>
            <div>
                <div class="flex justify-between mb-1 text-xs">
                    <span class="font-medium text-gray-600"><?= htmlspecialchars($row['kelas'], ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="font-medium text-gray-700"><?= format_id($row['jumlah_kth']) ?> KTH</span>
                </div>
                <div class="h-1.5 rounded-full bg-gray-100 overflow-hidden">
                    <div class="h-full rounded-full" style="width:<?= min(100, (float) $row['pct_kth']) ?>%;background:<?= $color ?>"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Kegiatan RHL -->
    <div class="bg-white rounded-xl border border-gray-100 p-4 lg:col-span-2 fade-up delay-3">
        <h2 class="text-sm font-semibold text-gray-800 mb-1">Kegiatan RHL</h2>
        <p class="text-[10px] text-gray-400 mb-4">Luas (Ha) kumulatif per kegiatan</p>
        <?php if ($rhlKeg === []): ?>
            <p class="text-xs text-gray-500">Belum ada data RHL.</p>
        <?php else: ?>
        <div class="space-y-2.5 max-h-72 overflow-y-auto pr-1">
            <?php foreach ($rhlKeg as $row): ?>
            <div>
                <div class="flex justify-between mb-1 text-xs gap-2">
                    <span class="text-gray-600 truncate"><?= htmlspecialchars($row['kegiatan'], ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="font-medium text-gray-700 flex-shrink-0"><?= format_id($row['total_luas_ha'], 1) ?> Ha</span>
                </div>
                <div class="h-2 rounded-full bg-gray-100 overflow-hidden">
                    <div class="h-full rounded-full" style="width:<?= min(100, (float) $row['pct']) ?>%;background:linear-gradient(90deg,#1A6B3A,#3AA870)"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Tables row -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    <!-- KTH Terdaftar -->
    <div class="bg-white rounded-xl border border-gray-100 overflow-hidden lg:col-span-2 fade-up delay-2">
        <div class="flex items-center justify-between px-4 pt-4 pb-3">
            <h2 class="text-sm font-semibold text-gray-800">KTH Terdaftar</h2>
            <a href="<?= htmlspecialchars(APP_URL . '/kth', ENT_QUOTES, 'UTF-8') ?>" class="text-xs text-forest-600 font-medium hover:underline">Lihat semua →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="data-table w-full">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="px-4 py-2.5 text-left">Nama KTH</th>
                        <th class="px-4 py-2.5 text-left">Kabupaten</th>
                        <th class="px-4 py-2.5 text-left">Kecamatan</th>
                        <th class="px-4 py-2.5 text-left">Kelas</th>
                        <th class="px-4 py-2.5 text-right">Anggota</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php if ($kthBaru === []): ?>
                    <tr><td colspan="5" class="px-4 py-8 text-center text-xs text-gray-500">Tidak ada data.</td></tr>
                    <?php else: foreach ($kthBaru as $r): ?>
                    <tr class="hover:bg-forest-50/50 transition-colors">
                        <td class="px-4 py-2.5 text-xs font-medium text-gray-800"><?= htmlspecialchars($r['nama'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="px-4 py-2.5 text-xs text-gray-600"><?= htmlspecialchars($r['kabupaten'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="px-4 py-2.5 text-xs text-gray-600"><?= htmlspecialchars($r['kecamatan'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="px-4 py-2.5">
                            <?php $bc = match ($r['kelas']) { 'Utama' => 'badge-utama', 'Madya' => 'badge-madya', default => 'badge-pemula' }; ?>
                            <span class="badge <?= $bc ?>"><?= htmlspecialchars($r['kelas'], ENT_QUOTES, 'UTF-8') ?></span>
                        </td>
                        <td class="px-4 py-2.5 text-right text-xs font-medium text-gray-700"><?= format_id((int) $r['jumlah_anggota']) ?></td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- KPS Terbaru -->
    <div class="bg-white rounded-xl border border-gray-100 p-4 fade-up delay-3">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-sm font-semibold text-gray-800">KPS Terbaru</h2>
            <a href="<?= htmlspecialchars(APP_URL . '/kps', ENT_QUOTES, 'UTF-8') ?>" class="text-xs text-forest-600 font-medium hover:underline">Semua →</a>
        </div>
        <?php if ($kpsBaru === []): ?>
            <p class="text-xs text-gray-500">Tidak ada data.</p>
        <?php else: ?>
        <?php
        $skemaColors = ['HKm' => '#7B5EA7', 'HD' => '#E07B3A', 'HTR' => '#4A90D9', 'Kulin KK' => '#1A6B3A', 'IPHPS' => '#17A77E'];
        ?>
        <div class="space-y-2">
            <?php foreach ($kpsBaru as $r): ?>
            <?php $dot = $skemaColors[$r['skema']] ?? '#6b7280'; ?>
            <div class="flex items-start gap-2.5 p-2.5 rounded-lg hover:bg-gray-50 transition-colors">
                <div class="w-7 h-7 rounded-md flex items-center justify-center flex-shrink-0 text-white text-[10px] font-semibold" style="background:<?= htmlspecialchars($dot, ENT_QUOTES, 'UTF-8') ?>">
                    <?= htmlspecialchars(mb_strtoupper(mb_substr($r['skema'], 0, 2)), ENT_QUOTES, 'UTF-8') ?>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-medium text-gray-800 truncate"><?= htmlspecialchars($r['nama'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p class="text-[10px] text-gray-400"><?= htmlspecialchars($r['kecamatan'], ENT_QUOTES, 'UTF-8') ?><?php if ($r['luas'] !== null): ?> · <?= format_id($r['luas'], 1) ?> Ha<?php endif; ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Footer -->
<div class="text-center py-4 text-[10px] text-gray-400 leading-relaxed">
    <span class="font-medium text-gray-500"><?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?></span> v1.0 · <?= htmlspecialchars(APP_TAGLINE, ENT_QUOTES, 'UTF-8') ?>
    <br>CDK Wilayah Bojonegoro · Dinas Kehutanan Provinsi Jawa Timur · <?= (int) date('Y') ?>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Global Chart.js defaults
    Chart.defaults.font.family = "'Inter', 'Segoe UI', sans-serif";
    Chart.defaults.font.size = 13;
    Chart.defaults.color = '#4A6357';
    Chart.defaults.plugins.legend.labels.usePointStyle = true;
    Chart.defaults.plugins.tooltip.backgroundColor = '#1A3A28';
    Chart.defaults.plugins.tooltip.titleColor = '#FFFFFF';
    Chart.defaults.plugins.tooltip.bodyColor = '#E8F5EC';
    Chart.defaults.plugins.tooltip.padding = 10;
    Chart.defaults.plugins.tooltip.cornerRadius = 6;
    Chart.defaults.scale.grid.color = 'rgba(26, 107, 58, 0.08)';

    <?php
    $chartLabels = [];
    $chartDataPemula = [];
    $chartDataMadya = [];
    $chartDataUtama = [];
    foreach ($kthKab as $k) {
        $chartLabels[] = $k['kode'];
        $chartDataPemula[] = $k['pemula'];
        $chartDataMadya[] = $k['madya'];
        $chartDataUtama[] = $k['utama'];
    }
    ?>
    const kthBarCtx = document.getElementById('kthBarChart');
    if (kthBarCtx) {
        new Chart(kthBarCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($chartLabels) ?>,
                datasets: [
                    { label: 'Utama', data: <?= json_encode($chartDataUtama) ?>, backgroundColor: '#1A6B3A', hoverBackgroundColor: '#155730', borderRadius: 2, borderSkipped: false },
                    { label: 'Madya', data: <?= json_encode($chartDataMadya) ?>, backgroundColor: '#E07B3A', hoverBackgroundColor: '#C4622A', borderRadius: 2, borderSkipped: false },
                    { label: 'Pemula', data: <?= json_encode($chartDataPemula) ?>, backgroundColor: '#4A90D9', hoverBackgroundColor: '#2F6FA8', borderRadius: 2, borderSkipped: false }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: { stacked: true, grid: { display: false } },
                    y: { stacked: true, grid: { color: 'rgba(26, 107, 58, 0.08)' }, ticks: { font: { size: 10 } } }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            footer: function(items) {
                                let total = 0;
                                items.forEach(function(item) { total += item.parsed.y; });
                                return 'Total: ' + total + ' KTH';
                            }
                        }
                    }
                }
            }
        });
    }

    <?php
    $kpsLabels = [];
    $kpsData = [];
    foreach ($kpsSkema as $row) {
        $kpsLabels[] = $row['skema'];
        $kpsData[] = $row['jumlah'];
    }
    // Colorblind-safe donut palette mapped by skema
    $kpsColorMap = ['HKm' => '#7B5EA7', 'HD' => '#E07B3A', 'HTR' => '#4A90D9', 'Kulin KK' => '#1A6B3A', 'IPHPS' => '#17A77E'];
    $kpsColors = [];
    foreach ($kpsSkema as $row) {
        $kpsColors[] = $kpsColorMap[$row['skema']] ?? '#8AA396';
    }
    ?>
    const kpsDonutCtx = document.getElementById('kpsDonutChart');
    if (kpsDonutCtx) {
        new Chart(kpsDonutCtx, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($kpsLabels) ?>,
                datasets: [{ data: <?= json_encode($kpsData) ?>, backgroundColor: <?= json_encode($kpsColors) ?>, borderWidth: 0, hoverOffset: 4 }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const pct = ((context.parsed / total) * 100).toFixed(1);
                                return ' ' + context.label + ': ' + context.parsed + ' (' + pct + '%)';
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
