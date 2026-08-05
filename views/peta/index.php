<?php
/** @var list<array{id:int,kode:string,nama:string}> $kabupatenList */
?>
<div class="fade-up" style="margin:-1.5rem;margin-top:-1.25rem;">

    <!-- Controls bar -->
    <div class="bg-white border-b border-gray-100 px-4 py-3 flex flex-wrap items-center gap-3">
        <span class="text-xs font-medium text-gray-700">Layer:</span>

        <label class="flex items-center gap-1.5 text-xs text-gray-600 cursor-pointer">
            <input type="checkbox" id="layer_kth" checked class="rounded border-gray-300 text-forest-600 focus:ring-forest-500 w-3.5 h-3.5">
            <span class="w-2.5 h-2.5 rounded-full bg-forest-500"></span> KTH
        </label>
        <label class="flex items-center gap-1.5 text-xs text-gray-600 cursor-pointer">
            <input type="checkbox" id="layer_dpn" checked class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 w-3.5 h-3.5">
            <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span> DPN
        </label>
        <label class="flex items-center gap-1.5 text-xs text-gray-600 cursor-pointer">
            <input type="checkbox" id="layer_gully" checked class="rounded border-gray-300 text-amber-600 focus:ring-amber-500 w-3.5 h-3.5">
            <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> Gully Plug
        </label>
        <label class="flex items-center gap-1.5 text-xs text-gray-600 cursor-pointer">
            <input type="checkbox" id="layer_upsa" checked class="rounded border-gray-300 text-purple-600 focus:ring-purple-500 w-3.5 h-3.5">
            <span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span> UPSA
        </label>
        <label class="flex items-center gap-1.5 text-xs text-gray-600 cursor-pointer">
            <input type="checkbox" id="layer_rhl" checked class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 w-3.5 h-3.5">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span> RHL
        </label>
        <label class="flex items-center gap-1.5 text-xs text-gray-600 cursor-pointer">
            <input type="checkbox" id="layer_kbr" checked class="rounded border-gray-300 text-teal-600 focus:ring-teal-500 w-3.5 h-3.5">
            <span class="w-2.5 h-2.5 rounded-full bg-teal-500"></span> KBR
        </label>
        <label class="flex items-center gap-1.5 text-xs text-gray-600 cursor-pointer">
            <input type="checkbox" id="layer_aep" checked class="rounded border-gray-300 text-rose-600 focus:ring-rose-500 w-3.5 h-3.5">
            <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span> AEP
        </label>

        <div class="ml-auto flex items-center gap-2">
            <span class="text-xs text-gray-500">Kabupaten:</span>
            <select id="filter_kabupaten" class="text-xs border border-gray-200 rounded-md px-2 py-1.5 bg-white text-gray-700 focus:outline-none focus:ring-1 focus:ring-forest-500">
                <option value="0">Semua</option>
                <?php foreach ($kabupatenList as $kb): ?>
                <option value="<?= (int) $kb['id'] ?>"><?= htmlspecialchars($kb['nama'], ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <!-- Map container -->
    <div id="map" style="height:calc(100vh - 180px);min-height:400px;"></div>

    <!-- Empty state overlay -->
    <div id="map_empty" class="hidden absolute inset-0 flex items-center justify-center pointer-events-none" style="top:60px;">
        <div class="bg-white/90 rounded-xl px-6 py-4 text-center shadow-sm">
            <i class="ti ti-map-off text-3xl text-gray-300"></i>
            <p class="text-xs text-gray-500 mt-2">Belum ada data koordinat untuk ditampilkan.</p>
            <p class="text-[10px] text-gray-400 mt-1">Isi koordinat pada data KTH, DPN, Gully Plug, atau UPSA.</p>
        </div>
    </div>
</div>

<!-- Leaflet CSS & JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>

<script>
(function() {
    const baseUrl = <?= json_encode(APP_URL) ?>;

    // Initialize map centered on Bojonegoro area
    const map = L.map('map').setView([-7.15, 111.88], 9);

    // Basemaps
    const osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);

    const esriLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        attribution: '© Esri',
        maxZoom: 18
    });

    L.control.layers({ 'OpenStreetMap': osmLayer, 'Satelit (Esri)': esriLayer }, null, { position: 'topright' }).addTo(map);

    // Layer groups
    const layers = {
        kth: L.markerClusterGroup({ maxClusterRadius: 50 }),
        dpn: L.layerGroup(),
        gully_plug: L.layerGroup(),
        upsa: L.layerGroup(),
        rhl: L.layerGroup(),
        kbr: L.layerGroup(),
        aep: L.layerGroup()
    };

    // Add all to map
    Object.values(layers).forEach(l => l.addTo(map));

    // Icon factories
    function makeIcon(color, iconClass) {
        return L.divIcon({
            html: '<div style="background:' + color + ';width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;border:2px solid white;box-shadow:0 2px 6px rgba(0,0,0,0.3);"><i class="ti ' + iconClass + '" style="color:white;font-size:14px;"></i></div>',
            className: '',
            iconSize: [28, 28],
            iconAnchor: [14, 14],
            popupAnchor: [0, -16]
        });
    }

    const icons = {
        kth: makeIcon('#16a34a', 'ti-leaf'),
        dpn: makeIcon('#2563eb', 'ti-wall'),
        gully_plug: makeIcon('#d97706', 'ti-ripple'),
        upsa: makeIcon('#7c3aed', 'ti-plant'),
        rhl: makeIcon('#059669', 'ti-trees'),
        kbr: makeIcon('#0d9488', 'ti-seeding'),
        aep: makeIcon('#e11d48', 'ti-tool')
    };

    // Popup builders
    function popupKth(p) {
        return '<div class="text-xs"><p class="font-semibold text-gray-800">' + p.nama + '</p>' +
            '<p class="text-gray-500 mt-0.5">' + p.desa + ', ' + p.kecamatan + '</p>' +
            '<p class="text-gray-500">' + p.kabupaten + ' · Kelas ' + p.kelas + '</p>' +
            '<p class="text-gray-500">Anggota: ' + p.anggota + '</p>' +
            '<a href="' + baseUrl + '/kth/' + p.id + '" class="text-green-600 font-medium mt-1 inline-block">Detail →</a></div>';
    }
    function popupDpn(p) {
        return '<div class="text-xs"><p class="font-semibold text-gray-800">DPN: ' + p.nama + '</p>' +
            '<p class="text-gray-500 mt-0.5">' + (p.desa || p.lokasi || '-') + '</p>' +
            '<p class="text-gray-500">Unit: ' + p.jumlah_unit + ' · Tahun ' + p.tahun + '</p></div>';
    }
    function popupGully(p) {
        return '<div class="text-xs"><p class="font-semibold text-gray-800">Gully Plug: ' + p.nama + '</p>' +
            '<p class="text-gray-500 mt-0.5">' + (p.desa || p.lokasi || '-') + '</p>' +
            '<p class="text-gray-500">Unit: ' + p.jumlah_unit + ' · Tahun ' + p.tahun + '</p></div>';
    }
    function popupUpsa(p) {
        return '<div class="text-xs"><p class="font-semibold text-gray-800">UPSA: ' + p.nama + '</p>' +
            '<p class="text-gray-500 mt-0.5">' + (p.desa || p.lokasi || '-') + '</p>' +
            '<p class="text-gray-500">' + (p.jenis_tanaman || '-') + ' · ' + p.jumlah + ' btg</p></div>';
    }
    function popupRhl(p) {
        return '<div class="text-xs"><p class="font-semibold text-gray-800">RHL: ' + p.nama + '</p>' +
            '<p class="text-gray-500 mt-0.5">' + p.kegiatan + '</p>' +
            '<p class="text-gray-500">' + p.kabupaten + ' · Tahun ' + p.tahun + (p.luas_ha ? ' · ' + p.luas_ha + ' Ha' : '') + '</p>' +
            '<a href="' + baseUrl + '/rhl/' + p.id + '" class="text-green-600 font-medium mt-1 inline-block">Detail →</a></div>';
    }
    function popupKbr(p) {
        return '<div class="text-xs"><p class="font-semibold text-gray-800">KBR: ' + p.nama + '</p>' +
            '<p class="text-gray-500 mt-0.5">' + (p.desa || '-') + (p.subdas ? ' · SubDAS: ' + p.subdas : '') + '</p>' +
            '<p class="text-gray-500">' + (p.tahun ? 'Tahun ' + p.tahun : '') + '</p>' +
            '<a href="' + baseUrl + '/kbr/' + p.id + '" class="text-green-600 font-medium mt-1 inline-block">Detail →</a></div>';
    }
    function popupAep(p) {
        return '<div class="text-xs"><p class="font-semibold text-gray-800">AEP: ' + p.nama + '</p>' +
            '<p class="text-gray-500 mt-0.5">' + p.jenis_bantuan + ' (' + p.jumlah + ' unit)</p>' +
            '<p class="text-gray-500">' + p.kabupaten + ' · Tahun ' + p.tahun + '</p>' +
            '<a href="' + baseUrl + '/aep/' + p.id + '" class="text-green-600 font-medium mt-1 inline-block">Detail →</a></div>';
    }

    let totalMarkers = 0;

    async function loadLayer(name) {
        const kabId = document.getElementById('filter_kabupaten').value;
        const url = baseUrl + '/api/peta?layer=' + name + (kabId > 0 ? '&kabupaten_id=' + kabId : '');
        try {
            const res = await fetch(url);
            const geojson = await res.json();
            layers[name].clearLayers();

            geojson.features.forEach(function(f) {
                const coords = f.geometry.coordinates;
                const p = f.properties;
                const marker = L.marker([coords[1], coords[0]], { icon: icons[name] });

                if (name === 'kth') marker.bindPopup(popupKth(p));
                else if (name === 'dpn') marker.bindPopup(popupDpn(p));
                else if (name === 'gully_plug') marker.bindPopup(popupGully(p));
                else if (name === 'upsa') marker.bindPopup(popupUpsa(p));
                else if (name === 'rhl') marker.bindPopup(popupRhl(p));
                else if (name === 'kbr') marker.bindPopup(popupKbr(p));
                else if (name === 'aep') marker.bindPopup(popupAep(p));

                layers[name].addLayer(marker);
            });

            totalMarkers += geojson.features.length;
        } catch(e) { console.error('Failed to load layer:', name, e); }
    }

    async function loadAll() {
        totalMarkers = 0;
        const checks = [
            { id: 'layer_kth', name: 'kth' },
            { id: 'layer_dpn', name: 'dpn' },
            { id: 'layer_gully', name: 'gully_plug' },
            { id: 'layer_upsa', name: 'upsa' },
            { id: 'layer_rhl', name: 'rhl' },
            { id: 'layer_kbr', name: 'kbr' },
            { id: 'layer_aep', name: 'aep' }
        ];

        for (const c of checks) {
            if (document.getElementById(c.id).checked) {
                await loadLayer(c.name);
            } else {
                layers[c.name].clearLayers();
            }
        }

        // Show/hide empty state
        const emptyEl = document.getElementById('map_empty');
        if (totalMarkers === 0) {
            emptyEl.classList.remove('hidden');
        } else {
            emptyEl.classList.add('hidden');
            // Fit bounds to all markers
            const allBounds = [];
            Object.values(layers).forEach(function(lg) {
                if (lg.getLayers && lg.getLayers().length > 0) {
                    lg.eachLayer(function(m) {
                        if (m.getLatLng) allBounds.push(m.getLatLng());
                    });
                }
                // For cluster group
                if (lg.getBounds && lg.getLayers().length > 0) {
                    try { allBounds.push(...lg.getBounds().pad(0.1)); } catch(e) {}
                }
            });
            if (allBounds.length > 0) {
                map.fitBounds(L.latLngBounds(allBounds).pad(0.1));
            }
        }
    }

    // Event listeners
    document.getElementById('filter_kabupaten').addEventListener('change', loadAll);
    ['layer_kth', 'layer_dpn', 'layer_gully', 'layer_upsa', 'layer_rhl', 'layer_kbr', 'layer_aep'].forEach(function(id) {
        document.getElementById(id).addEventListener('change', loadAll);
    });

    // Initial load
    loadAll();
})();
</script>

<style>
    .leaflet-popup-content { margin: 8px 12px; }
    .leaflet-popup-content-wrapper { border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
</style>
