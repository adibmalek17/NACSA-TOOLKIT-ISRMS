<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Risk Assessments | NACSA Toolkit</title>
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
    
        /* Asset register */
        :root { --muted: #526974; }
        .asset-panel { margin-bottom: 24px; padding: 28px; border: 1px solid var(--border); border-radius: 14px; background: white; min-width: 0; }
        .asset-panel h2 { margin: 0 0 8px; font-size: 19px; color: var(--text); }
        .section-intro { margin: 0 0 24px; color: var(--muted); }
        .grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 20px 24px; }
        label { display: block; font-size: 13px; font-weight: 600; color: var(--text); }
        input, select, textarea { display: block; width: 100%; min-width: 0; margin-top: 8px; padding: 12px 14px; border: 1px solid #b8c9d1; border-radius: 8px; background: #fff; color: var(--text); font: inherit; font-weight: 400; }
        textarea { min-height: 100px; resize: vertical; }
        input:focus-visible, select:focus-visible, textarea:focus-visible, .table-wrap:focus-visible { outline: 3px solid #39bdb0; outline-offset: 2px; }
        .asset-panel form > p { margin: 20px 0; }
        .save-asset { border: 0; cursor: pointer; }
        .success, .errors { padding: 16px 18px; border-radius: 8px; margin-bottom: 24px; }
        .success { background: #e8f6ef; color: #21634f; border: 1px solid #c5e2d4; }
        .errors { background: #fff1ef; color: #942f25; border: 1px solid #edc5bf; }
        .errors ul { margin: 0; padding-left: 20px; }
        .register-heading { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; }
        .register-count { padding: 5px 12px; border-radius: 20px; background: #e8f4f1; color: #21634f; font-size: 12px; font-weight: 600; white-space: nowrap; }
        .table-wrap { overflow-x: auto; border: 1px solid var(--border); border-radius: 9px; }
        table { width: 100%; min-width: 930px; border-collapse: collapse; text-align: left; }
        th, td { padding: 16px; border-bottom: 1px solid var(--border); vertical-align: top; }
        th { background: #f3f6f8; color: #405a66; font-size: 11px; font-weight: 700; letter-spacing: .5px; text-transform: uppercase; }
        td { font-size: 13px; overflow-wrap: anywhere; max-width: 250px; }
        tbody tr:last-child td { border-bottom: 0; }
        tbody tr:hover { background: #fafcfc; }
        .asset-code { font-weight: 700; color: var(--teal-dark); }
        .category-badge { display: inline-block; padding: 3px 9px; background: #edf2f5; border-radius: 5px; color: #405a66; font-size: 12px; }
        .asset-actions { display: flex; align-items: center; gap: 8px; white-space: nowrap; }
        .asset-actions form { margin: 0; }
        .asset-actions a, .archive-button { display: inline-flex; align-items: center; justify-content: center; padding: 7px 11px; border-radius: 6px; font: inherit; font-size: 12px; font-weight: 600; cursor: pointer; }
        .asset-actions a { border: 1px solid #b8d6cf; color: #06665f; background: #f0f8f5; text-decoration: none; }
        .asset-actions a:hover { background: #e0f0ea; }
        .archive-button { border: 1px solid #edc5bf; background: #fff5f3; color: #942f25; }
        .archive-button:hover { background: #fce5df; }
        .read-only { color: var(--muted); font-size: 12px; }
        .empty-assets { text-align: center; padding: 44px 20px; }
        .empty-assets strong, .empty-assets span { display: block; }
        .empty-assets span { margin-top: 6px; color: var(--muted); }
        .pagination { display: flex; flex-wrap: wrap; align-items: center; gap: 16px; margin-top: 20px; font-size: 13px; }
        .pagination a { padding: 7px 13px; border: 1px solid var(--border); border-radius: 6px; text-decoration: none; font-weight: 600; }
        .pagination a:hover { background: #edf7f3; }
        .pagination span { color: var(--muted); }
        @media (max-width: 760px) {
            .topbar { flex-wrap: wrap; }
            .asset-panel { padding: 20px; }
            .grid { grid-template-columns: 1fr; gap: 18px; }
            .account { flex: 1; }
            .register-heading { flex-wrap: wrap; margin-bottom: 16px; }
            .register-heading .section-intro { margin-bottom: 0; }
        }

        /* Assessment forms and results */
        .assessment-panel { padding: 28px; margin-bottom: 24px; border: 1px solid var(--border); border-radius: 14px; background: white; min-width: 0; }
        .assessment-panel h2 { margin: 0 0 10px; color: var(--text); font-size: 19px; }
        .assessment-panel h3 { margin: 28px 0 10px; font-size: 16px; }
        .assessment-panel button { display: inline-flex; align-items: center; justify-content: center; padding: 11px 18px; border: 1px solid transparent; border-radius: 8px; background: var(--teal); color: white; font: inherit; font-weight: 600; cursor: pointer; }
        .assessment-panel button:hover { background: var(--teal-dark); }
        .assessment-panel button:disabled { opacity: .65; cursor: wait; }
        .assessment-panel input[type="hidden"] { display: none; }
        .assessment-panel input[type="checkbox"] { display: inline-block; width: auto; margin: 0 6px 0 0; accent-color: var(--teal); }
        .assessment-panel form > p { margin: 20px 0; }
        .hint { color: var(--muted); line-height: 1.65; }
        .excel-upload { min-width: 0; margin: 26px 0; padding: 20px; border: 1px solid #b8d6cf; border-radius: 10px; background: #f3faf7; }
        .excel-upload legend { padding: 0 8px; color: #21634f; font-weight: 600; }
        .excel-upload p { margin-bottom: 14px; }
        .excel-upload input[type="file"] { background: white; font-size: 13px; }
        .excel-upload input::file-selector-button { margin-right: 12px; padding: 7px 10px; border: 1px solid #b8d6cf; border-radius: 5px; background: #edf7f3; color: #06665f; cursor: pointer; }
        #excel-fill-note { margin-top: 10px; font-size: 12px; }
        #excel-fill-status { margin: 14px 0 0; color: #21634f; }
        #excel-fill-errors { margin: 16px 0 0; }
        #assessment-form > .grid:first-child { margin-top: 24px; }
        #score-hint { padding: 12px 16px; border-left: 3px solid var(--teal); background: #f3f6f8; border-radius: 0 6px 6px 0; }
        .filter-form { padding: 20px; margin-top: 24px; background: #f7f9fa; border: 1px solid var(--border); border-radius: 10px; }
        .filter-actions { display: flex; flex-wrap: wrap; align-items: center; gap: 16px; margin-top: 20px; }
        .filter-actions button:last-child { margin-left: auto; background: white; color: var(--teal-dark); border-color: #b8d6cf; }
        .filter-actions button:last-child:hover { background: #e8f4ef; }
        .results-count { margin: 22px 0 14px; font-size: 13px; }
        .assessment-panel table { min-width: 1180px; }
        .assessment-panel td { max-width: 240px; }
        .assessment-panel td:nth-child(4), .assessment-panel td:nth-child(5) { white-space: nowrap; }
        .assessment-panel td:nth-child(6) { font-weight: 700; }
        .assessment-panel td:last-child { min-width: 220px; }
        .assessment-panel td:last-child form { white-space: normal; }
        .assessment-panel td:last-child label { line-height: 1.5; }
        .assessment-panel td:last-child button { margin-top: 10px; }
        .status-badge { display: inline-block; padding: 4px 10px; border: 1px solid #d4dfe5; border-radius: 20px; background: #f1f5f8; color: #405a66; font-size: 11px; font-weight: 700; white-space: nowrap; }
        .status-verified { background: #e8f6ef; color: #21634f; border-color: #c5e2d4; }
        .status-submitted { background: #fff5df; color: #805600; border-color: #edd8a7; }
        .status-returned { background: #fff0ec; color: #943c25; border-color: #efc9bc; }
        [hidden] { display: none !important; }
        @media (max-width: 760px) {
            .assessment-panel { padding: 20px; }
            .excel-upload, .filter-form { padding: 16px; }
            .filter-actions button:last-child { margin-left: 0; }
        }
    </style>
</head>
<body>
@php
    $user = auth()->user();
@endphp
<svg xmlns="http://www.w3.org/2000/svg"
     aria-hidden="true"
     style="position:absolute;width:0;height:0;overflow:hidden">
    <defs>
        <symbol id="icon-shield" viewBox="0 0 24 24">
            <path d="M12 3 4.5 6v6c0 4 3 7 7.5 9 4.5-2 7.5-5 7.5-9V6L12 3Z"/>
            <path d="m8.5 12 2.3 2.3 4.7-5"/>
        </symbol>
        <symbol id="icon-dashboard" viewBox="0 0 24 24">
            <rect x="3" y="3" width="7" height="7" rx="1.5"/>
            <rect x="14" y="3" width="7" height="7" rx="1.5"/>
            <rect x="3" y="14" width="7" height="7" rx="1.5"/>
            <rect x="14" y="14" width="7" height="7" rx="1.5"/>
        </symbol>
        <symbol id="icon-assets" viewBox="0 0 24 24">
            <rect x="3" y="4" width="18" height="12" rx="2"/>
            <path d="M8 21h8M12 16v5"/>
        </symbol>
        <symbol id="icon-document" viewBox="0 0 24 24">
            <path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9l-6-6Z"/>
            <path d="M14 3v6h6M8 13h8M8 17h5"/>
        </symbol>
        <symbol id="icon-clock" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="9"/>
            <path d="M12 7v5l3 2"/>
        </symbol>
        <symbol id="icon-check" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="9"/>
            <path d="m8 12 3 3 5-6"/>
        </symbol>
        <symbol id="icon-users" viewBox="0 0 24 24">
            <circle cx="9" cy="8" r="3"/>
            <path d="M3 21v-2a6 6 0 0 1 12 0v2M16 5a3 3 0 0 1 0 6M18 15a5 5 0 0 1 3 4v2"/>
        </symbol>
        <symbol id="icon-lock" viewBox="0 0 24 24">
            <rect x="5" y="10" width="14" height="11" rx="2"/>
            <path d="M8 10V7a4 4 0 0 1 8 0v3M12 14v3"/>
        </symbol>
        <symbol id="icon-arrow" viewBox="0 0 24 24">
            <path d="M5 12h14m-5-5 5 5-5 5"/>
        </symbol>
        <symbol id="icon-return" viewBox="0 0 24 24">
            <path d="m8 4-5 5 5 5M3 9h11a6 6 0 0 1 0 12h-3"/>
        </symbol>
        <symbol id="icon-signout" viewBox="0 0 24 24">
            <path d="M9 4H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h4M10 12h11m-4-4 4 4-4 4"/>
        </symbol>
    </defs>
</svg>

<a class="skip-link" href="#main-content">Skip to assessments</a>

<aside class="sidebar">
    <a class="brand" href="{{ route('dashboard') }}" aria-label="NACSA Toolkit home">
        <span class="brand-mark">
            <svg aria-hidden="true"><use href="#icon-shield"/></svg>
        </span>
        <span>
            <strong>NACSA</strong>
            <small>Risk toolkit</small>
        </span>
    </a>

    <nav class="nav-links sidebar-navigation" aria-label="Main navigation">
        <a class="nav-link" href="{{ route('dashboard') }}">
            <svg aria-hidden="true"><use href="#icon-dashboard"/></svg>
            Dashboard
        </a>

        <a class="nav-link" href="{{ route('assets.index') }}">
            <svg aria-hidden="true"><use href="#icon-assets"/></svg>
            Asset register
        </a>

        <a class="nav-link active" href="{{ route('assessments.index') }}" aria-current="page">
            <svg aria-hidden="true"><use href="#icon-document"/></svg>
            Assessments
        </a>

        <a class="nav-link" href="{{ route('account.security') }}">
            <svg aria-hidden="true"><use href="#icon-lock"/></svg>
            Account security
        </a>

        @if ($user->is_system_admin)
            <a class="nav-link" href="{{ route('admin.users.index') }}">
                <svg aria-hidden="true"><use href="#icon-users"/></svg>
                User management
            </a>
        @endif
    </nav>
</aside>

<div class="workspace">
    <header class="topbar">
    <div class="breadcrumb">
    <strong>Assessments</strong>
</div>

    <div class="topbar-actions">
        <div class="account">
            <span class="avatar" aria-hidden="true">
                {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
            </span>

            <div>
                <span class="account-name">{{ $user->name }}</span>
                <span class="account-email">{{ $user->email }}</span>
            </div>
        </div>

        <form method="POST"
              action="{{ route('logout') }}"
              class="topbar-signout-form">
            @csrf

            <button class="topbar-signout" type="submit">
                <svg aria-hidden="true"><use href="#icon-signout"/></svg>
                <span>Sign out</span>
            </button>
        </form>
    </div>
</header>

    <main id="main-content">
        <div class="page-heading">
            <div>
                <p class="eyebrow">Your risk workspace</p>
                <h1>Risk assessments</h1>
                <p>Create and review assessments for your assigned departments.</p>
            </div>
            <a class="button" href="#saved-assessments">Saved assessments <svg aria-hidden="true"><use href="#icon-arrow"/></svg></a>
        </div>
    <section class="assessment-panel">
        @if (session('success'))
            <div class="success" role="status">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="errors" role="alert">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($canCreate && $assets->isNotEmpty())
            <h2 id="create-assessment">Create an assessment</h2>

            <form id="assessment-form" method="POST" action="{{ route('assessments.store') }}"
                  data-excel-fill-url="{{ route('assessments.import.fill') }}"
                  data-excel-template-url="{{ route('assessments.import.template') }}">
                @csrf

                <div class="grid">
                    <label>
                        Asset
                        <select name="risk_asset_id" required>
                            <option value="">Select an asset</option>
                            @foreach ($assets as $asset)
                                <option
                                    value="{{ $asset->id }}"
                                    @selected(old('risk_asset_id') == $asset->id)
                                >
                                    {{ $asset->code }} — {{ $asset->name }}
                                    ({{ $asset->department_name }})
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <label>
                        Scoring profile
                        <select id="profile" name="profile" required>
                            @foreach ($profiles as $profile)
                                <option
                                    value="{{ $profile }}"
                                    @selected(
                                        old('profile', 'nacsa-3-2024') === $profile
                                    )
                                >
                                    {{ $profile }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                </div>

                <fieldset class="excel-upload">
                    <legend>Fill from Excel — optional</legend>
                    <p class="hint">Choose your asset and scoring profile above, then select an Excel file. You can edit all values before saving.</p>
                    <p><a id="excel-template-link" href="{{ route('assessments.import.template', ['profile' => old('profile', 'nacsa-3-2024')]) }}">Download example for selected profile</a></p>
                    <label for="assessment-workbook">Excel file (.xlsx, up to 2 MB)</label>
                    <input id="assessment-workbook" type="file" accept=".xlsx" aria-describedby="excel-fill-note">
                    <p class="hint" id="excel-fill-note">Selecting a file fills one matching assessment row. It replaces the values below, including blank cells.</p>
                    <button id="excel-fill-button" type="button">Fill form from Excel</button>
                    <p id="excel-fill-status" role="status" aria-live="polite" hidden></p>
                    <div id="excel-fill-errors" class="errors" role="alert" hidden></div>
                    <noscript><p>Enable JavaScript to fill from Excel. You can still enter the assessment manually.</p></noscript>
                </fieldset>

                <div class="grid">
                    <label>
                        Assessment date
                        <input
                            type="date"
                            name="assessed_on"
                            value="{{ old('assessed_on', now()->toDateString()) }}"
                            required
                        >
                    </label>

                    <label>
                        Risk owner
                        <input
                            name="risk_owner"
                            value="{{ old('risk_owner') }}"
                            maxlength="180"
                            required
                        >
                    </label>

                    <label>
                        Threat
                        <textarea
                            name="threat"
                            maxlength="5000"
                            required
                        >{{ old('threat') }}</textarea>
                    </label>

                    <label>
                        Vulnerability
                        <textarea
                            name="vulnerability"
                            maxlength="5000"
                            required
                        >{{ old('vulnerability') }}</textarea>
                    </label>
                </div>

                <p>
                    <label>
                        Existing controls — optional
                        <textarea
                            name="existing_controls"
                            maxlength="5000"
                        >{{ old('existing_controls') }}</textarea>
                    </label>
                </p>

                <h3>Scoring inputs</h3>

                <p class="hint" id="score-hint">
                    Use 1–3 for nacsa-3-2024 and usim-2026.
                    Use 1–5 for nacsa-5-2024.
                    NACSA profiles use Loss; USIM uses Operations,
                    Financial and Reputation impacts.
                </p>

                <div class="grid">
                    @foreach ([
                        'c' => 'Confidentiality',
                        'i' => 'Integrity',
                        'a' => 'Availability',
                        'likelihood' => 'Likelihood',
                    ] as $key => $label)
                        <label>
                            {{ $label }}
                            <input
                                class="score-input"
                                type="number"
                                name="{{ $key }}"
                                value="{{ old($key, 1) }}"
                                min="1"
                                max="5"
                                step="1"
                                required
                            >
                        </label>
                    @endforeach

                    <label id="loss-field">
                        Loss
                        <input
                            class="score-input"
                            type="number"
                            name="loss"
                            value="{{ old('loss', 1) }}"
                            min="1"
                            max="5"
                            step="1"
                        >
                    </label>
                </div>

                <div id="usim-fields">
                    <h3>USIM impact inputs</h3>

                    <div class="grid">
                        @foreach ([
                            'operations' => 'Operations impact',
                            'financial' => 'Financial impact',
                            'reputation' => 'Reputation impact',
                        ] as $key => $label)
                            <label>
                                {{ $label }}
                                <input
                                    type="number"
                                    name="{{ $key }}"
                                    value="{{ old($key, 1) }}"
                                    min="1"
                                    max="3"
                                    step="1"
                                >
                            </label>
                        @endforeach
                    </div>
                </div>

                <p class="hint">
                    Saving calculates the result and creates a draft assessment.
                </p>

                <button type="submit">Calculate and save draft</button>
            </form>
        @elseif ($canCreate)
            <p>
                Register an active asset in your department before creating
                an assessment.
            </p>
            <a href="{{ route('assets.index') }}">Open asset register</a>
        @else
            <p>
                You have view-only access. An Admin or Assessor department
                assignment is required to create assessments.
            </p>
        @endif
    </section>

    <section class="assessment-panel" id="saved-assessments">
        <h2>Saved assessments</h2>

        <form class="filter-form" method="GET" action="{{ route('assessments.index') }}#saved-assessments">
            <div class="grid">
                <label>
                    Search asset code or name
                    <input type="search" name="q"
                           value="{{ $filters['q'] }}"
                           maxlength="180"
                           placeholder="For example: DEV-001 or Test Server">
                </label>

                <label>
                    Assessment status
                    <select name="status">
                        <option value="">All statuses</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}"
                                    @selected($filters['status'] === $status)>
                                {{ ucfirst($status) }}
                            </option>
                        @endforeach
                    </select>
                </label>

                <label>
                    Scoring profile
                    <select name="filter_profile">
                        <option value="">All profiles</option>
                        @foreach ($profiles as $filterProfile)
                            <option value="{{ $filterProfile }}"
                                    @selected($filters['filter_profile'] === $filterProfile)>
                                {{ $filterProfile }}
                            </option>
                        @endforeach
                    </select>
                </label>
            </div>

            <div class="filter-actions">
                <button type="submit">Apply filters</button>
                <a href="{{ route('assessments.index') }}#saved-assessments">Reset filters</a>
                <button type="submit" formaction="{{ route('assessments.export') }}">
                    Export CSV
                </button>
            </div>
        </form>

        <p class="hint results-count" role="status">
            {{ $assessments->total() }} assessment(s) found in your assigned departments.
        </p>

        <div class="table-wrap" role="region" aria-label="Saved assessments table" tabindex="0">
            <table>
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Asset</th>
                        <th scope="col">Department</th>
                        <th scope="col">Date</th>
                        <th scope="col">Profile</th>
                        <th scope="col">Score</th>
                        <th scope="col">Risk level</th>
                        <th scope="col">Status</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($assessments as $assessment)
                        <tr>
                            <td>
                                <a href="{{ route('assessments.show', ['assessment' => $assessment->id]) }}">
                                    View #{{ $assessment->id }}
                                </a>
                            </td>
                            <td>
                                <strong>{{ $assessment->asset_code }}</strong>
                                <br>
                                {{ $assessment->asset_name }}
                            </td>
                            <td>{{ $assessment->department_name }}</td>
                            <td>{{ $assessment->assessed_on }}</td>
                            <td>{{ $assessment->profile }}</td>
                            <td>{{ $assessment->result['score'] ?? '—' }}</td>
                            <td>{{ $assessment->result['label'] ?? '—' }}</td>
                           <td>
    <span class="status-badge status-{{ $assessment->status }}">{{ ucfirst($assessment->status) }}</span>
</td>
<td>
    @include('assessments.lifecycle-actions', [
        'assessment' => $assessment,
    ])
</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                @if ($filters['q'] !== '' || $filters['status'] !== '' || $filters['filter_profile'] !== '')
                                    No assessments match your filters. Try another search or reset the filters.
                                @else
                                    No assessments saved yet.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination">
            @if ($assessments->previousPageUrl())
                <a href="{{ $assessments->previousPageUrl() }}">Previous</a>
            @endif

            <span>
                Page {{ $assessments->currentPage() }}
                of {{ $assessments->lastPage() }}
            </span>

            @if ($assessments->nextPageUrl())
                <a href="{{ $assessments->nextPageUrl() }}">Next</a>
            @endif
        </div>
    </section>
        <footer class="page-footer"><span>NACSA Toolkit · Risk assessment workspace</span><span>Risk assessments</span></footer>
    </main>
</div>

<script>
    const profileSelect = document.getElementById('profile');

    if (profileSelect) {
        function updateScoreFields() {
            const profile = profileSelect.value;
            const isUsim = profile === 'usim-2026';
            const max = profile === 'nacsa-5-2024' ? 5 : 3;

            document.querySelectorAll('.score-input').forEach(input => {
                input.max = String(max);
            });

            const lossField = document.getElementById('loss-field');
            const lossInput = lossField.querySelector('input');

            lossField.hidden = isUsim;
            lossInput.disabled = isUsim;
            lossInput.required = !isUsim;

            const usimFields = document.getElementById('usim-fields');
            usimFields.hidden = !isUsim;

            usimFields.querySelectorAll('input').forEach(input => {
                input.disabled = !isUsim;
                input.required = isUsim;
            });

            document.getElementById('score-hint').textContent =
                `Enter whole numbers from 1 to ${max} for this profile.`;
        }

        profileSelect.addEventListener('change', updateScoreFields);
        updateScoreFields();
    }
</script>
<script src="{{ asset('js/assessment-excel-fill.js') }}" defer></script>
</body>
</html>