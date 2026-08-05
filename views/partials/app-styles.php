<style>
  /* ── Sidebar scrollbar ── */
  .sidebar-nav::-webkit-scrollbar { width: 4px; }
  .sidebar-nav::-webkit-scrollbar-track { background: transparent; }
  .sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.12); border-radius: 99px; }

  /* ── Fade-up animation ── */
  @keyframes fadeUp {
    from { opacity: 0; transform: translateY(10px); }
    to   { opacity: 1; transform: translateY(0); }
  }
  .fade-up { animation: fadeUp 0.35s ease forwards; }
  .delay-1 { animation-delay: 0.04s; opacity: 0; }
  .delay-2 { animation-delay: 0.08s; opacity: 0; }
  .delay-3 { animation-delay: 0.12s; opacity: 0; }
  .delay-4 { animation-delay: 0.16s; opacity: 0; }
  .delay-5 { animation-delay: 0.20s; opacity: 0; }

  /* ── Data tables ── */
  .data-table th { font-weight: 500; font-size: 10px; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280; }
  .data-table td { font-size: 13px; color: #374151; }
  .data-table tr:hover td { background: #f0fdf4; }

  /* ── Badges ── */
  .badge { display: inline-flex; align-items: center; padding: 2px 10px; border-radius: 999px; font-size: 11px; font-weight: 500; }
  .badge-utama  { background: #dcfce7; color: #166534; }
  .badge-madya  { background: #fef3c7; color: #92400e; }
  .badge-pemula { background: #dbeafe; color: #1e40af; }

  /* ── Global scrollbar ── */
  ::-webkit-scrollbar { width: 5px; }
  ::-webkit-scrollbar-track { background: transparent; }
  ::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 99px; }

  /* ── Legend dots ── */
  .legend-dot { width: 10px; height: 10px; border-radius: 2px; flex-shrink: 0; }

  /* ── Live badge pulse ── */
  @keyframes pulse-dot {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.4; }
  }
  .live-dot { animation: pulse-dot 2s ease-in-out infinite; }

  /* ── Stat card (generic reusable) ── */
  .stat-card {
    background: white;
    border-radius: 0.75rem;
    border: 1px solid #f3f4f6;
  }
</style>
