<style>

        :root {
            --navy: #102d39;
            --navy-light: #1c414e;
            --teal: #087f78;
            --teal-dark: #06665f;
            --text: #193540;
            --muted: #677c86;
            --border: #e1e9ed;
            --background: #f3f6f8;
            --white: #ffffff;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            background: var(--background);
            color: var(--text);
            font-family: "Segoe UI", Arial, sans-serif;
            font-size: 14px;
            line-height: 1.6;
        }

        a { color: var(--teal); }

        button { font: inherit; }

        a, button {
            -webkit-tap-highlight-color: transparent;
        }

        a:focus-visible,
        button:focus-visible,
        summary:focus-visible {
            outline: 3px solid #39bdb0;
            outline-offset: 4px;
        }

        h1, h2, h3, p { margin-top: 0; }

        h1, h2, h3 { line-height: 1.3; }

        svg {
            display: block;
            width: 21px;
            height: 21px;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.8;
            stroke-linecap: round;
            stroke-linejoin: round;
            flex-shrink: 0;
        }

        .skip-link {
            position: fixed;
            top: -100px;
            left: 16px;
            z-index: 100;
            padding: 12px 18px;
            background: white;
        }

        .skip-link:focus { top: 16px; }

        /* Sidebar */

        .sidebar {
            position: fixed;
            inset: 0 auto 0 0;
            width: 248px;
            display: flex;
            flex-direction: column;
            padding: 30px 20px 22px;
            background: var(--navy);
            color: white;
            overflow-y: auto;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0 10px;
            color: white;
            text-decoration: none;
        }

        .brand-mark {
            display: grid;
            place-items: center;
            width: 42px;
            height: 46px;
            border: 1px solid #52858b;
            border-radius: 12px;
            background: var(--navy-light);
            color: #7ce0cd;
        }

        .brand strong {
            display: block;
            font-size: 19px;
            letter-spacing: 1px;
        }

        .brand small {
            display: block;
            color: #a8c0c9;
            font-size: 10px;
            letter-spacing: 2.4px;
            text-transform: uppercase;
        }

        .nav-label {
            margin: 38px 12px 12px;
            color: #a8bec7;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1.8px;
            text-transform: uppercase;
        }

        .nav-links {
            display: grid;
            gap: 7px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 14px;
            border-radius: 9px;
            color: #c6d6dc;
            text-decoration: none;
            font-weight: 500;
        }

        .nav-link:hover {
            background: var(--navy-light);
            color: white;
        }

        .nav-link.active {
            background: #20534f;
            color: #c2f6e9;
            box-shadow: inset 3px 0 #6bd9bd;
        }

        .sidebar-bottom {
            margin-top: auto;
            padding-top: 34px;
        }

        .workspace-note {
            margin: 0 10px 20px;
            padding-top: 20px;
            border-top: 1px solid #31505c;
            color: #adc3cb;
            font-size: 12px;
        }

        .workspace-note strong {
            display: block;
            margin-bottom: 4px;
            color: #e1edf0;
            font-weight: 600;
        }

        .signout {
            display: flex;
            align-items: center;
            gap: 12px;
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #3c5b67;
            border-radius: 9px;
            background: transparent;
            color: #e2edf1;
            text-align: left;
            cursor: pointer;
        }

        .signout:hover { background: var(--navy-light); }

        /* Main shell */

        .workspace { margin-left: 248px; }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            min-height: 88px;
            padding: 18px 40px;
            border-bottom: 1px solid var(--border);
            background: var(--white);
        }

        .breadcrumb {
            color: var(--muted);
            font-size: 13px;
        }

        .breadcrumb strong {
            color: var(--text);
            font-weight: 600;
        }

        .account {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .avatar {
            display: grid;
            place-items: center;
            flex-shrink: 0;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e7f2ef;
            color: var(--teal-dark);
            font-weight: 700;
        }

        .account-name {
            display: block;
            max-width: 250px;
            overflow-wrap: anywhere;
            font-size: 13px;
            font-weight: 600;
        }

        .account-email {
            display: block;
            max-width: 250px;
            overflow-wrap: anywhere;
            color: var(--muted);
            font-size: 11px;
        }

        main {
            max-width: 1480px;
            margin: auto;
            padding: 36px 40px 24px;
        }

        .page-heading {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 24px;
            margin-bottom: 28px;
        }

        .eyebrow {
            margin-bottom: 8px;
            color: var(--teal);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        h1 {
            margin-bottom: 10px;
            font-size: clamp(25px, 3vw, 34px);
            font-weight: 650;
            letter-spacing: -1px;
        }

        .page-heading p:last-child {
            margin-bottom: 0;
            color: var(--muted);
        }

        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            flex-shrink: 0;
            padding: 12px 18px;
            border: 1px solid var(--teal);
            border-radius: 8px;
            background: var(--teal);
            color: white;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
        }

        .button:hover {
            border-color: var(--teal-dark);
            background: var(--teal-dark);
        }

        .empty-notice {
            margin-bottom: 24px;
            padding: 16px 20px;
            border: 1px solid #e5d6ac;
            border-radius: 10px;
            background: #fff8e8;
            color: #765b23;
        }

        /* Summary cards */

        .metrics {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 18px;
            margin-bottom: 28px;
        }

        .metric {
            display: block;
            min-width: 0;
            padding: 22px;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: var(--white);
            color: var(--text);
            text-decoration: none;
            box-shadow: 0 3px 12px #173d4903;
        }

        .metric:hover { border-color: #92bdb6; }

        .metric-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
        }

        .metric h2 {
            margin: 0;
            font-size: 13px;
            font-weight: 600;
        }

        .metric-icon {
            display: grid;
            place-items: center;
            flex-shrink: 0;
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: #edf3f6;
            color: #506c7d;
        }

        .metric-icon.teal {
            background: #e5f3ee;
            color: #137c64;
        }

        .metric-icon.amber {
            background: #fff3d9;
            color: #a17217;
        }

        .metric-number {
            margin: 18px 0 3px;
            font-size: 38px;
            font-weight: 650;
            line-height: 1.2;
            letter-spacing: -1.4px;
            overflow-wrap: anywhere;
        }

        .metric-caption {
            margin: 0;
            color: var(--muted);
            font-size: 12px;
        }

        /* Content panels */

        .content-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.35fr) minmax(0, 1fr);
            gap: 24px;
            align-items: start;
        }

        .panel {
            min-width: 0;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: var(--white);
            overflow: hidden;
        }

        .panel-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            padding: 24px 24px 18px;
        }

        .panel-heading h2 {
            margin: 0 0 5px;
            font-size: 17px;
            font-weight: 650;
            letter-spacing: -0.3px;
        }

        .panel-heading p {
            margin: 0;
            color: var(--muted);
            font-size: 12px;
        }

        .small-label {
            flex-shrink: 0;
            padding: 4px 9px;
            border: 1px solid var(--border);
            border-radius: 6px;
            color: var(--muted);
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }

        .task-list { padding: 0 24px; }

        .task {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 20px 0;
            border-top: 1px solid #edf1f3;
        }

        .task-symbol {
            display: grid;
            place-items: center;
            flex-shrink: 0;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: #fff4e2;
            color: #9e742b;
        }

        .task-symbol.blue {
            background: #edf2f9;
            color: #506e9d;
        }

        .task-symbol.teal {
            background: #e8f4f0;
            color: #277f6d;
        }

        .task-body { flex: 1; min-width: 0; }

        .task h3 {
            margin: 0 0 4px;
            font-size: 13px;
            font-weight: 600;
        }

        .task p {
            margin: 0;
            color: var(--muted);
            font-size: 12px;
        }

        .task-count {
            min-width: 34px;
            font-size: 23px;
            font-weight: 650;
            text-align: right;
        }

        .panel-footer {
            padding: 15px 24px;
            border-top: 1px solid var(--border);
            background: #fbfcfd;
        }

        .text-link {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
        }

        .text-link:hover { text-decoration: underline; }

        .departments { padding: 0 24px 8px; }

        .department {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 18px 0;
            border-top: 1px solid #edf1f3;
        }

        .department h3 {
            margin: 0 0 5px;
            font-size: 13px;
            font-weight: 600;
            overflow-wrap: anywhere;
        }

        .department p {
            margin: 0;
            color: var(--muted);
            font-size: 11px;
        }

        .role-badge {
            flex-shrink: 0;
            padding: 4px 10px;
            border: 1px solid #cfe4dd;
            border-radius: 20px;
            background: #edf7f3;
            color: #28735d;
            font-size: 10px;
            font-weight: 700;
        }

        .access-note {
            margin: 4px 24px 24px;
            padding: 14px;
            border-radius: 8px;
            background: #f3f6f8;
            color: var(--muted);
            font-size: 12px;
        }

        .count-details {
            margin-top: 24px;
            padding: 16px 20px;
            border: 1px solid var(--border);
            border-radius: 10px;
            color: var(--muted);
            font-size: 12px;
        }

        .count-details summary {
            cursor: pointer;
            color: #46616d;
            font-weight: 600;
        }

        .count-details p { margin: 12px 0 0; }

        .page-footer {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            margin-top: 28px;
            color: var(--muted);
            font-size: 11px;
        }

        @media (min-width: 1600px) {
            main { padding-top: 48px; }
            .metrics { gap: 24px; }
            .metric { padding: 28px; }
        }

        @media (max-width: 1200px) {
            .sidebar { width: 220px; }
            .workspace { margin-left: 220px; }
            main { padding: 28px 24px 20px; }
            .topbar { padding: 18px 24px; }
            .metrics { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .content-grid { grid-template-columns: 1fr; }
        }

        @media (max-width: 760px) {
            .sidebar {
                position: static;
                width: auto;
                padding: 20px;
            }

            .brand { padding: 0; }
            .nav-label { margin: 22px 0 10px; }
            .nav-links { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .nav-link { padding: 10px; font-size: 12px; }
            .sidebar-bottom { padding-top: 18px; }
            .workspace-note { display: none; }
            .signout { width: auto; padding: 8px 12px; font-size: 12px; }
            .workspace { margin-left: 0; }
            .topbar { min-height: 74px; padding: 15px 20px; }
            .breadcrumb { display: block; }
            main { padding: 24px 16px; }
            .page-heading { align-items: flex-start; flex-direction: column; }
            .metrics { gap: 12px; }
            .metric { padding: 16px; }
            .metric-top { align-items: flex-start; }
            .metric-icon { width: 30px; height: 30px; }
            .metric-icon svg { width: 17px; height: 17px; }
            .metric-number { font-size: 32px; }
            .panel-heading { padding: 20px 18px 16px; }
            .task-list, .departments { padding-left: 18px; padding-right: 18px; }
            .access-note { margin-left: 18px; margin-right: 18px; }
            .page-footer { flex-direction: column; gap: 4px; }
        }

        @media (max-width: 380px) {
            .metrics, .nav-links { grid-template-columns: 1fr; }
            .department { align-items: flex-start; flex-direction: column; gap: 8px; }
        }

        @media print {
            .sidebar, .topbar, .button, .page-footer { display: none; }
            .workspace { margin-left: 0; }
            main { max-width: none; padding: 0; }
            body { background: white; }
            .panel, .metric { break-inside: avoid; }
        }

        .sidebar-navigation {
    margin-top: 38px;
    gap: 10px;
}

.topbar-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    flex-wrap: wrap;
    gap: 20px;
    margin-left: auto;
    min-width: 0;
}

.topbar-signout-form {
    margin: 0;
    padding-left: 20px;
    border-left: 1px solid var(--border);
}

.topbar-signout {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    padding: 10px 14px;
    border: 1px solid var(--border);
    border-radius: 8px;
    background: white;
    color: var(--text);
    font: inherit;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
}

.topbar-signout:hover {
    background: #fff1ef;
    border-color: #edc5bf;
    color: #a13024;
}

.topbar-signout svg {
    width: 18px;
    height: 18px;
}

@media (max-width: 760px) {
    .sidebar-navigation {
        margin-top: 22px;
    }

    .topbar-actions {
        width: 100%;
        justify-content: space-between;
        gap: 14px;
    }

    .topbar-signout-form {
        padding-left: 0;
        border-left: none;
    }
}

/* Stronger contrast for headings and account details */
.page-heading h1,
.panel-heading h2,
.department h3,
.task h3,
.task-count,
.account-name {
    color: #193540;
}

.account-email,
.page-heading p:last-child,
.metric-caption,
.panel-heading p,
.task p,
.department p,
.access-note,
.small-label {
    color: #526974;
}

.avatar {
    color: #06665f;
}

.topbar-signout {
    color: #193540;
    background: #ffffff;
    border-color: #b8c9d1;
}

.topbar-signout span {
    color: inherit;
}

.metric-icon {
    color: #506c7d;
}

.task-symbol {
    color: #916319;
}

.role-badge {
    color: #21634f;
}
    
/* Shared interface rules: every page uses the same controls and spacing. */
:root { --muted: #526974; --control-border: #b8c9d1; }
[hidden], input[type="hidden"] { display: none !important; }
h1,h2,h3,h4,p { overflow-wrap: anywhere; }
h2 { font-size: 19px; margin-bottom: 18px; }
h3 { font-size: 16px; margin-bottom: 12px; }
h4 { font-size: 14px; margin-bottom: 10px; }
.topbar { flex-wrap: wrap; }
.page-label { color: var(--text); font-size: 14px; }
.toolkit-content > section, .overview-columns > section {
    padding: 28px; margin-bottom: 24px; background: white;
    border: 1px solid var(--border); border-radius: 12px; min-width: 0;
}
.toolkit-content article { padding: 22px; margin-top: 20px; border: 1px solid var(--border); border-radius: 10px; background: white; }
.toolkit-content article h3:first-child { margin-top: 0; }
.toolkit-content > section > :last-child, article > :last-child { margin-bottom: 0; }
.toolkit-content .content-grid > .panel { margin-bottom: 0; }
.grid { display: grid; grid-template-columns: repeat(2,minmax(0,1fr)); gap: 20px 24px; }
label { display: block; font-size: 13px; font-weight: 600; color: var(--text); }
input,select,textarea { display: block; width: 100%; min-width: 0; margin-top: 8px; padding: 11px 14px; border: 1px solid var(--control-border); border-radius: 8px; background: white; color: var(--text); font: inherit; font-weight: 400; line-height: 1.5; }
input:not([type="checkbox"]):not([type="hidden"]),select { min-height: 44px; }
input[type="checkbox"],input[type="radio"] { display: inline-block; width: auto; min-height: 0; margin: 0 6px 0 0; accent-color: var(--teal); }
textarea { min-height: 110px; resize: vertical; }
form > label + label { margin-top: 20px; }
form > p { margin: 20px 0; }
input:focus-visible,select:focus-visible,textarea:focus-visible,summary:focus-visible,.table-wrap:focus-visible { outline: 3px solid #39bdb0; outline-offset: 3px; }
button,.button,.actions > a,.filter-actions > a,.pagination > a,.edit-action {
    display: inline-flex; align-items: center; justify-content: center; gap: 8px;
    min-height: 44px; margin: 0; padding: 10px 18px; border: 1px solid var(--teal);
    border-radius: 8px; background: var(--teal); color: white; font: inherit;
    font-size: 13px; font-weight: 600; line-height: 1.5; text-decoration: none; cursor: pointer;
}
button:hover,.button:hover { background: var(--teal-dark); border-color: var(--teal-dark); }
button:disabled { opacity: .65; cursor: wait; }
.actions,.filter-actions,.toolbar { display: flex; flex-wrap: wrap; gap: 12px; align-items: center; margin-top: 24px; }
.toolbar { margin: 0 0 24px; }
.actions > a,.filter-actions > a,.filter-actions button:last-child,.pagination > a,.edit-action,.topbar-signout {
    background: white; color: var(--text); border-color: var(--control-border); margin-left: 0;
}
.actions > a:hover,.filter-actions > a:hover,.filter-actions button:last-child:hover,.pagination > a:hover,.edit-action:hover { background: #edf2f5; border-color: #8da5b1; color: var(--text); }
button.danger,button.remove,button.archive-button { background: #fff2ef; color: #942f25; border-color: #e7bbb4; }
button.danger:hover,button.remove:hover,button.archive-button:hover { background: #fce1db; border-color: #d89a90; }
.submit,form > label + button { margin-top: 20px; }
.assessment-actions,.asset-actions { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }
.assessment-actions form,.asset-actions form { margin: 0; }
.assessment-actions button,.assessment-actions a,.asset-actions button,.asset-actions a { min-height: 36px; padding: 7px 12px; font-size: 12px; }
.asset-actions a { display: inline-flex; border: 1px solid var(--control-border); border-radius: 8px; background: white; color: var(--text); font-weight: 600; text-decoration: none; }
.filter-form { padding: 20px; margin-top: 24px; border: 1px solid var(--border); border-radius: 10px; background: #f7f9fa; }
.results-count { margin: 22px 0 14px; }
.table-wrap { width: 100%; overflow-x: auto; border: 1px solid var(--border); border-radius: 9px; }
table { width: 100%; border-collapse: collapse; text-align: left; }
th,td { padding: 14px 16px; border-bottom: 1px solid var(--border); vertical-align: top; overflow-wrap: anywhere; }
thead th { background: #f3f6f8; color: #405a66; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; }
tbody th { font-size: 13px; font-weight: 600; }
td { font-size: 13px; }
tbody tr:last-child > * { border-bottom: 0; }
tbody tr:hover { background: #fafcfc; }
.assessment-list-page table { min-width: 1050px; }
.asset-list-page table { min-width: 920px; }
.assessment-list-page td:last-child { min-width: 150px; }
.asset-assessment-link { color: #06665f; text-decoration: underline; text-underline-offset: 3px; }
.asset-code { color: #06665f; font-weight: 700; }
.empty-assets { text-align: center; padding: 40px 20px; }
.empty-assets strong,.empty-assets span { display: block; }
.pagination { display: flex; flex-wrap: wrap; align-items: center; gap: 14px; margin-top: 20px; }
.pagination > a { min-height: 36px; padding: 7px 12px; }
.pagination span,.hint,.section-intro,.read-only,.note { color: var(--muted); }
.success,.errors,.notice,.overview-notice,.empty-notice { padding: 16px 18px; margin: 0 0 24px; border: 1px solid #d4dfe5; border-radius: 9px; background: #edf2f5; color: #405a66; }
.success { background: #e8f6ef; color: #21634f; border-color: #c5e2d4; }
.errors { background: #fff1ef; color: #942f25; border-color: #edc5bf; }
.errors ul { margin: 0; padding-left: 20px; }
.notice > :last-child { margin-bottom: 0; }
.text,.comment { white-space: pre-wrap; overflow-wrap: anywhere; line-height: 1.7; }
dl { margin: 0; } dt { font-weight: 600; font-size: 12px; color: var(--muted); } dd { margin: 6px 0 0; line-height: 1.6; overflow-wrap: anywhere; }
.action-code { display: block; font-size: 12px; color: var(--muted); margin-top: 5px; overflow-wrap: anywhere; }
.result { padding: 22px; margin-bottom: 24px; background: #f3f6f8; border: 1px solid #d4dfe5; border-radius: 8px; }
.result p { margin: 6px 0; } .score { font-size: 36px; font-weight: 700; color: var(--text); }
.overview-columns { display: grid; grid-template-columns: repeat(2,minmax(0,1fr)); gap: 24px; align-items: start; }
.assessment-nav { display: flex; flex-wrap: wrap; gap: 6px; padding: 6px; margin-bottom: 28px; border: 1px solid var(--border); border-radius: 10px; background: white; }
.assessment-nav a { padding: 10px 16px; border-radius: 6px; color: var(--muted); font-weight: 600; text-decoration: none; }
.assessment-nav a:hover,.assessment-nav a[aria-current="page"] { background: #e9eff2; color: var(--text); }
.section-heading,.register-heading { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 20px; }
.section-heading h2,.register-heading h2 { margin: 0; }
.status-badge,.state-badge,.register-count,.category-badge { display: inline-block; padding: 4px 11px; border: 1px solid #d4dfe5; border-radius: 20px; background: #f1f5f8; color: #405a66; font-size: 12px; font-weight: 600; }
.status-verified { background: #e8f6ef; color: #21634f; border-color: #c5e2d4; }
.status-submitted,.state-badge.pending { background: #fff5df; color: #805600; border-color: #edd8a7; }
.status-returned { background: #fff0ec; color: #943c25; border-color: #efc9bc; }
.excel-upload { min-width: 0; margin: 26px 0; padding: 20px; border: 1px solid #cbd8dd; border-radius: 10px; background: #f7f9fa; }
.excel-upload legend { padding: 0 8px; font-weight: 600; }
#excel-fill-note { margin-top: 10px; font-size: 12px; }
#excel-fill-status,#excel-fill-errors { margin: 16px 0 0; }
input::file-selector-button { padding: 7px 10px; margin-right: 12px; border: 1px solid var(--control-border); border-radius: 5px; background: #edf2f5; color: var(--text); cursor: pointer; }
#usim-fields { margin-top: 24px; }
#score-hint { padding: 12px 16px; border-left: 3px solid #bccbd3; background: #f3f6f8; }
.qr { width: fit-content; max-width: 100%; overflow: auto; padding: 16px; margin: 24px 0; background: white; border: 1px solid var(--border); border-radius: 10px; }
.qr svg { display: block; width: 200px; height: 200px; max-width: 100%; fill: #000; stroke: none; stroke-width: 0; }
.codes { display: grid; grid-template-columns: repeat(2,minmax(0,1fr)); gap: 10px; max-width: 620px; margin: 20px 0; padding: 18px; background: #f5f8fa; border: 1px solid var(--border); border-radius: 9px; }
.codes code { color: var(--text); font-size: 14px; overflow-wrap: anywhere; }
.confirmation-form { max-width: 380px; }
.row { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; padding: 14px 0; border-bottom: 1px solid var(--border); }
.row form { margin: 0; } .row span { overflow-wrap: anywhere; min-width: 0; }
.managed-account details,.guest-page details { margin-top: 20px; padding: 16px; border: 1px solid var(--border); border-radius: 8px; background: #f8fafb; }
summary { cursor: pointer; font-weight: 600; color: var(--text); }
.managed-account summary { margin: 0; } details[open] > summary { margin-bottom: 16px; }
.guest-page { padding: 48px 20px; background: #f3f6f8; }
.guest-page main { max-width: 540px; margin: 0 auto; padding: 36px; border: 1px solid var(--border); border-radius: 14px; background: white; }
.guest-page .brand { display: block; padding: 0; margin-bottom: 24px; color: var(--teal-dark); font-size: 14px; font-weight: 700; letter-spacing: 1.5px; }
.guest-page h1 { font-size: 28px; }
.guest-page form > label { margin-top: 20px; }
.guest-page form > button { width: 100%; margin-top: 24px; }
.guest-page .note { font-size: 12px; margin-top: 24px; }
@media (max-width:1100px) { .overview-columns { grid-template-columns: 1fr; gap: 0; } }
@media (max-width:760px) {
    .toolkit-content > section,.overview-columns > section { padding: 20px; }
    .toolkit-content article { padding: 18px; }
    .grid { grid-template-columns: 1fr; gap: 18px; }
    .row { align-items: flex-start; }
    .assessment-nav a { padding: 9px 12px; }
    .guest-page { padding: 24px 12px; }.guest-page main { padding: 24px; }
}
@media (max-width:480px) {
    .filter-actions > a,.filter-actions > button { flex: 1 1 100%; }
    .codes { grid-template-columns: 1fr; }
}
@media print {
    .sidebar,.topbar,.skip-link,.assessment-nav,.toolbar,.page-footer,.back-link { display: none !important; }
    .workspace { margin: 0; } main.toolkit-content { max-width: none; padding: 0; }
    body { background: white; color: black; font-size: 10pt; }
    .report-page section,.report-page article { padding: 12px 0; border: 0; border-radius: 0; }
    .report-page article { break-inside: avoid; }
    .table-wrap { overflow: visible; } table { min-width: 0 !important; }
    thead { display: table-header-group; } tr { break-inside: avoid; }
}

.filter-actions > button,.filter-actions > a { min-width: 140px; }
/* Collapsible navigation shared by every signed-in page. */
.topbar-heading { display:flex; align-items:center; gap:14px; }
.sidebar-toggle { width:44px; height:44px; padding:10px; background:white; color:var(--text); border:1px solid var(--control-border); flex-shrink:0; }
.sidebar-toggle:hover { background:#edf2f5; color:var(--text); border-color:#8da5b1; }
.sidebar { z-index:20; }
body.sidebar-closed .sidebar { display:none; }
body.sidebar-closed .workspace { margin-left:0; }
@media (max-width:760px) {
    .sidebar { position:static; width:100%; }
    .topbar-heading { width:100%; }
    .workspace { margin-left:0; }
}
@media print { .sidebar-toggle { display:none !important; } }

/* Risk bands retain their text labels for accessibility. */
.risk-badge { display:inline-flex; align-items:center; padding:6px 12px; border:1px solid; border-radius:6px; font-size:14px; line-height:1.4; font-weight:600; white-space:nowrap; }
.risk-badge.risk-high, .result.risk-high { background:#fff1f2; color:#991b1b; border-color:#f5b6bd; }
.risk-badge.risk-medium, .result.risk-medium { background:#fff8d6; color:#755600; border-color:#e8cd63; }
.risk-badge.risk-low, .result.risk-low { background:#eaf7ee; color:#166534; border-color:#a4d7b2; }
.risk-badge.risk-neutral, .result.risk-neutral { background:#f1f5f9; color:#475569; border-color:#cbd5e1; }
.result[class*="risk-"] { border:1px solid; border-left-width:5px; }
.result[class*="risk-"] .score { color:inherit; }
.risk-plan { display:block; margin-top:5px; color:#526974; font-weight:400; }
.risk-score { font-weight:700; font-variant-numeric:tabular-nums; }

/* Assessment workspace */
.assessment-workspace .page-heading { margin-bottom:24px; }
.assessment-workspace .assessment-nav { padding:6px; gap:6px; border-radius:10px; margin-bottom:24px; }
.assessment-workspace .assessment-nav a { border-radius:6px; padding:12px 20px; font-weight:600; }
.assessment-workspace .assessment-nav a[aria-current="page"] { background:#123641; color:#fff; }
.assessment-workspace section { border:1px solid #dce5eb; border-radius:12px; margin-top:24px; padding:28px; background:#fff; }
.assessment-workspace h2 { font-size:22px; margin:0 0 20px; }
.assessment-workspace h3 { font-size:18px; margin:24px 0 14px; }
.assessment-workspace h4 { margin:22px 0 10px; }
.assessment-workspace table { font-variant-numeric:tabular-nums; }
.assessment-workspace th, .assessment-workspace td { padding:16px 18px; vertical-align:middle; }
.assessment-workspace .table-wrap + h3 { margin-top:30px; }
.assessment-workspace form > button { margin-top:20px; }
.assessment-context { display:flex; flex-wrap:wrap; gap:24px 48px; padding:20px 24px; background:#edf3f5; border:1px solid #dce5eb; border-radius:10px; margin:20px 0; }
.assessment-context div { min-width:110px; }
.assessment-context span { display:block; color:#526974; font-size:13px; margin-bottom:6px; }
.assessment-context strong { font-size:15px; color:#183540; }
.panel-heading { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:16px; margin-bottom:20px; }
.panel-heading h2, .panel-heading h3, .panel-heading p { margin:0; }
.panel-heading .eyebrow { margin-bottom:8px; }
.assessment-workspace .treatment-card { margin-top:20px; padding:24px; border:1px solid #dce5eb; border-radius:10px; background:#fff; }
.assessment-workspace .treatment-card .decision-panel { background:#f7fafb; margin-top:24px; }
.verification-badge { padding:7px 12px; border-radius:6px; font-weight:600; font-size:13px; border:1px solid; }
.verification-complete { background:#eaf7ee; color:#166534; border-color:#a4d7b2; }
.verification-pending { background:#fff8d6; color:#755600; border-color:#e8cd63; }
.verification-neutral { background:#edf2f7; color:#475569; border-color:#cbd5e1; }
.assessment-workspace .secondary-action { display:inline-flex; align-items:center; justify-content:center; padding:10px 16px; min-height:42px; border:1px solid #b9cbd3; border-radius:6px; text-decoration:none; color:#183540; background:#fff; font-weight:600; }
.assessment-workspace .secondary-action:hover { background:#edf3f5; }
.assessment-empty { padding:24px; background:#f7fafb; border:1px dashed #bccbd3; border-radius:8px; color:#526974; }
.assessment-workspace .report-actions { display:flex; justify-content:flex-end; margin:20px 0; }
@media(max-width:700px) { .assessment-workspace section, .assessment-workspace .treatment-card { padding:18px; } .assessment-context { gap:16px; } .assessment-workspace .assessment-nav { overflow-x:auto; flex-wrap:nowrap; } .assessment-nav a { white-space:nowrap; } }
@media print { .report-actions { display:none !important; } .assessment-workspace section { break-inside:auto; } .assessment-workspace tr { break-inside:avoid; } }

.plan-metadata { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:20px; margin:20px 0; padding:20px 0; border-top:1px solid #e2e9ed; border-bottom:1px solid #e2e9ed; }
.plan-metadata dt { color:#526974; font-size:13px; font-weight:500; }
.plan-metadata dd { margin:7px 0 0; font-weight:600; overflow-wrap:anywhere; }
.plan-actions { display:flex; flex-wrap:wrap; gap:10px; margin-top:24px; }
@media(max-width:900px) { .plan-metadata { grid-template-columns:repeat(2,minmax(0,1fr)); } }
@media(max-width:500px) { .plan-metadata { grid-template-columns:1fr; } }
@media print { #export-excel { display:none !important; } }

.report-detail-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:24px; align-items:start; }
.report-detail-grid > section { min-width:0; }
.report-details-table { table-layout:fixed; width:100%; }
.report-details-table th:first-child { width:36%; }
.report-details-table td, .report-details-table th { overflow-wrap:anywhere; }
.report-value { white-space:pre-wrap; }
.current-review-decision { margin-top:20px; padding:16px 20px; border-left:3px solid #087c72; background:#f4f8f9; }
@media(max-width:1100px) { .report-detail-grid { grid-template-columns:1fr; gap:0; } }
@media print { .report-detail-grid { display:block; } }
</style>
