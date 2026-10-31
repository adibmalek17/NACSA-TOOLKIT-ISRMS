<style>
:root { --navy:#14263d; --navy-light:#203952; --teal:#2457c5; --teal-dark:#1d46a0; --text:#1e293b; --muted:#526176; --border:#dce3ec; --background:#f4f6f9; --nav-width:248px; --nav-speed:240ms; }
html.nav-collapsed { --nav-width:68px; }
body { background:var(--background); color:var(--text); }
.sidebar, body.sidebar-closed .sidebar { display:flex; position:fixed; inset:0 auto 0 0; width:var(--nav-width); height:100dvh; padding:16px 10px; overflow-x:hidden; overflow-y:auto; z-index:40; background:var(--navy); transition:width var(--nav-speed) cubic-bezier(.2,.8,.2,1); }
.workspace, body.sidebar-closed .workspace { margin-left:var(--nav-width); transition:margin-left var(--nav-speed) cubic-bezier(.2,.8,.2,1); }
.sidebar-header { display:flex; align-items:center; justify-content:space-between; gap:4px; min-height:44px; }
.sidebar .brand { gap:8px; padding:0 2px; min-width:0; white-space:nowrap; }
.sidebar .brand strong { font-size:16px; }
.sidebar .brand small { font-size:8px; letter-spacing:1.5px; }
.sidebar .brand-mark { width:32px; height:36px; border-color:#547190; color:#bcd2ff; background:#203952; }
.sidebar .sidebar-toggle { flex:0 0 40px; width:40px; height:40px; min-height:40px; display:grid; place-items:center; padding:8px; color:#d7e2ef; border:0; border-radius:8px; background:transparent; cursor:pointer; }
.sidebar .sidebar-toggle:hover { background:#2b435d; color:#fff; }
.sidebar-toggle svg { width:22px; height:22px; stroke-width:1.7; }
.sidebar .sidebar-navigation { display:grid; margin-top:32px; gap:8px; }
.sidebar .nav-link { height:46px; padding:12px; gap:12px; overflow:hidden; white-space:nowrap; color:#d4dfed; }
.sidebar .nav-link.active { background:#2457c5; color:#fff; box-shadow:none; }
.sidebar .nav-link:hover { background:#203952; color:#fff; }
.sidebar .nav-link.active:hover { background:#1d46a0; }
.nav-link-label { opacity:1; transition:opacity 140ms ease; }
html.nav-collapsed .sidebar .brand { display:none; }
html.nav-collapsed .sidebar-header { justify-content:center; }
html.nav-collapsed .nav-link-label { opacity:0; width:0; overflow:hidden; }
html.nav-collapsed .sidebar .nav-link { gap:0; justify-content:center; }
html.nav-collapsed .sidebar .sidebar-navigation { margin-top:32px; }
.sidebar-backdrop { display:none; }
a:focus-visible,button:focus-visible,input:focus-visible,select:focus-visible,textarea:focus-visible { outline-color:#6996ef; }
.avatar { background:#eaf0fc; color:#2457c5; }
.metric-icon:not(.amber):not(.teal) { background:#eaf0fc; color:#2457c5; }
.toolkit-content h1,.toolkit-content h2,.toolkit-content h3 { color:#1e293b; }
.assessment-workspace .assessment-nav a[aria-current="page"] { background:#2457c5; }
@media(max-width:760px) {
    :root { --nav-width:248px; }
    html.nav-collapsed { --nav-width:60px; }
    .sidebar,body.sidebar-closed .sidebar { padding:16px 6px; }
    .workspace,body.sidebar-closed .workspace { margin-left:60px; }
    .topbar-heading { width:auto; }
    html:not(.nav-collapsed) .sidebar-backdrop { display:block; position:fixed; inset:0; z-index:35; padding:0; border:0; border-radius:0; background:rgba(15,23,42,.3); }
}
@media(prefers-reduced-motion:reduce) { .sidebar,.workspace,.nav-link-label { transition:none !important; } }
@media print { .sidebar,body.sidebar-closed .sidebar,.sidebar-backdrop { display:none !important; } .workspace,body.sidebar-closed .workspace { margin-left:0; } }

.sidebar .brand strong { font-size:12px; letter-spacing:.3px; }
.sidebar .brand small { font-size:10px; letter-spacing:2px; margin-top:3px; }
.asset-list-page .asset-panel,.assessment-list-page .assessment-panel { padding:28px; border:1px solid #dce3ec; border-radius:12px; }
.asset-list-page .grid,.assessment-list-page #assessment-form > .grid { gap:20px 24px; }
.asset-list-page input,.asset-list-page select,.assessment-list-page input,.assessment-list-page select { min-height:44px; padding:10px 12px; border-radius:7px; }
.asset-list-page textarea,.assessment-list-page textarea { min-height:96px; border-radius:7px; }
.assessment-list-page #assessment-form h3 { padding-top:24px; border-top:1px solid #e3e9f0; margin-top:28px; }
.assessment-list-page .excel-upload { padding:18px; margin:24px 0; border:1px dashed #b5c5df; border-radius:8px; background:#f7f9fd; }
.assessment-list-page .excel-upload legend { font-weight:600; color:#2457c5; }
.assessment-list-page .assessment-actions { flex-wrap:nowrap !important; }
.assessment-list-page .assessment-actions form > button { margin-top:0; }
.assessment-list-page .assessment-actions .edit-action,.assessment-list-page .assessment-actions .danger { min-height:38px; padding:7px 12px; display:inline-flex; align-items:center; justify-content:center; }
.assessment-list-page .asset-assessment-link { color:#2457c5; text-decoration:none; }
.assessment-list-page .asset-assessment-link:hover { text-decoration:underline; }
.assessment-list-page td { vertical-align:middle; }
.assessment-list-page .risk-badge { margin-top:5px; }
@media(max-width:650px) { .asset-list-page .asset-panel,.assessment-list-page .assessment-panel { padding:18px; } }
</style>
