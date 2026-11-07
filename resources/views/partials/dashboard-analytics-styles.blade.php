<style>
.dashboard-page .metrics { margin-bottom:24px; }
.dashboard-page .metric-number { margin-bottom:0; }
.dashboard-analytics { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:24px; }
.dashboard-page .analytics-panel { padding:26px; margin:0; min-width:0; background:#fff; border:1px solid #dce5eb; border-radius:12px; }
.dashboard-page .analytics-heading { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:26px; }
.dashboard-page .analytics-heading h2 { margin:0; color:#183540; font-size:20px; }
.analytics-tag { padding:5px 9px; color:#526974; background:#f1f5f7; border-radius:5px; font-size:12px; }
.status-visual { display:flex; align-items:center; gap:24px; }
.status-ring { position:relative; width:180px; flex:0 0 180px; aspect-ratio:1; }
.status-ring svg { display:block; width:100%; height:100%; }
.ring-total { position:absolute; inset:0; display:flex; flex-direction:column; justify-content:center; align-items:center; }
.ring-total strong { font-size:32px; color:#183540; font-variant-numeric:tabular-nums; }
.ring-total span { font-size:12px; color:#526974; }
.dashboard-page .status-legend { width:100%; border-collapse:collapse; font-size:13px; }
.dashboard-page .status-legend th, .dashboard-page .status-legend td { padding:12px 5px; border-bottom:1px solid #e7edf1; background:transparent; text-align:right; font-variant-numeric:tabular-nums; }
.dashboard-page .status-legend th:first-child { text-align:left; }
.dashboard-page .status-legend thead th { color:#526974; font-size:11px; font-weight:600; }
.status-legend a { display:inline-flex; align-items:center; gap:8px; text-decoration:none; color:#183540; }
.status-legend a:hover { text-decoration:underline; }
.legend-dot { display:inline-block; width:9px; height:9px; border-radius:50%; flex-shrink:0; }
.work-chart { display:grid; gap:22px; }
.work-bar-label { display:flex; align-items:baseline; justify-content:space-between; gap:12px; margin-bottom:9px; font-size:14px; color:#183540; }
.work-bar-label strong { font-variant-numeric:tabular-nums; }
.work-bar-label small { color:#526974; font-size:11px; }
.work-track { height:10px; background:#edf2f5; border-radius:5px; overflow:hidden; }
.work-track span { display:block; height:100%; border-radius:5px; }
.dashboard-page .analytics-empty { margin:20px 0 0; color:#526974; font-size:14px; }
.dashboard-page .department-access { margin-top:24px; }
.dashboard-departments { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:14px; }
.dashboard-department { display:flex; justify-content:space-between; align-items:center; gap:14px; padding:16px; border:1px solid #e0e8ed; border-radius:8px; }
.dashboard-department h3 { font-size:15px; margin:0; overflow-wrap:anywhere; }
@media(max-width:1250px) { .status-visual { flex-direction:column; } }
@media(max-width:900px) { .dashboard-analytics { grid-template-columns:1fr; } .status-visual { flex-direction:row; } }
@media(max-width:600px) { .dashboard-page .analytics-panel { padding:20px; } .status-visual { flex-direction:column; } .dashboard-departments { grid-template-columns:1fr; } .work-bar-label { font-size:12px; } }

.dashboard-risk-grid { margin-top:24px; grid-template-columns:repeat(3,minmax(0,1fr)); }
@media(max-width:1100px) { .dashboard-risk-grid { grid-template-columns:1fr; } }
.dashboard-page .risk-population { margin:0 0 24px; color:#526974; font-size:13px; }
.risk-population strong { color:#183540; font-size:28px; margin-right:6px; font-variant-numeric:tabular-nums; }
.risk-distribution { display:grid; gap:22px; }
.risk-share { display:inline-block; min-width:58px; margin-left:14px; text-align:right; }
</style>
