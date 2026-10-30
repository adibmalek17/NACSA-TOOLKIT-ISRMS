<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Account security | NACSA Toolkit</title>
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

        /* Account settings and user administration */
        .settings-panel { margin-bottom: 24px; padding: 28px; border: 1px solid var(--border); border-radius: 14px; background: white; min-width: 0; }
        .settings-panel h2 { font-size: 19px; margin: 0 0 16px; color: var(--text); }
        .settings-panel h3 { margin: 26px 0 12px; font-size: 16px; color: var(--text); }
        .settings-panel h4 { margin: 24px 0 8px; font-size: 13px; color: var(--text); }
        .settings-panel p { color: #526974; }
        .settings-panel button { padding: 11px 18px; border: 1px solid transparent; border-radius: 8px; background: var(--teal); color: white; cursor: pointer; font: inherit; font-weight: 600; }
        .settings-panel button:hover { background: var(--teal-dark); }
        .settings-panel .danger, .settings-panel .remove { background: #fff2ef; border-color: #e7bbb4; color: #942f25; }
        .settings-panel .danger:hover, .settings-panel .remove:hover { background: #fce1db; }
        .submit { margin-top: 20px; }
        .section-heading { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 20px; }
        .section-heading h2 { margin: 0; }
        .state-badge { display: inline-block; padding: 4px 11px; border: 1px solid #c5e2d4; border-radius: 20px; background: #e8f6ef; color: #21634f; font-size: 12px; font-weight: 600; }
        .state-badge.pending { border-color: #edd8a7; background: #fff5df; color: #805600; }
        .notice { padding: 16px 18px; margin-bottom: 24px; background: #fff8e9; border: 1px solid #edd8a7; border-radius: 9px; color: #73520b; }
        .confirmation-form { max-width: 380px; }
        .confirmation-form input { font-size: 20px; letter-spacing: 5px; }
        .qr { width: fit-content; max-width: 100%; overflow: auto; padding: 16px; margin: 24px 0; border: 1px solid var(--border); border-radius: 10px; background: white; }
        /* QR artwork must not inherit the navigation icons' stroke/fill rules. */
        .qr svg { display: block; width: 200px; height: 200px; max-width: 100%; fill: #000; stroke: none; stroke-width: 0; }
        .codes { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; max-width: 620px; padding: 18px; margin: 20px 0; border: 1px solid var(--border); border-radius: 9px; background: #f5f8fa; }
        .codes code { font-family: Consolas, monospace; font-size: 14px; color: #193540; overflow-wrap: anywhere; }
        .danger-panel { border-color: #edc5bf; }
        .managed-account { margin-top: 20px; padding: 24px; border: 1px solid var(--border); border-radius: 10px; }
        .managed-account h3 { margin-top: 0; font-size: 18px; }
        .managed-account p { overflow-wrap: anywhere; }
        .row { display: flex; gap: 16px; justify-content: space-between; align-items: center; flex-wrap: wrap; padding: 14px 0; border-bottom: 1px solid var(--border); }
        .row span { overflow-wrap: anywhere; min-width: 0; }
        .row form { margin: 0; flex-shrink: 0; }
        .row .remove { padding: 8px 12px; font-size: 12px; }
        .managed-account details { margin-top: 20px; border: 1px solid var(--border); border-radius: 8px; padding: 16px; background: #f8fafb; }
        .managed-account summary { margin: 0; cursor: pointer; color: #06665f; font-weight: 600; }
        .managed-account details[open] summary { margin-bottom: 16px; }
        .settings-panel input:focus-visible, .settings-panel select:focus-visible { outline: 3px solid #39bdb0; outline-offset: 2px; }
        @media (max-width: 760px) {
            .settings-panel { padding: 20px; }
            .managed-account { padding: 18px; }
            .row { align-items: flex-start; flex-direction: column; }
        }
        @media (max-width: 420px) { .codes { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
@php
    $signedInUser = auth()->user();
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

<a class="skip-link" href="#main-content">Skip to main content</a>

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

        <a class="nav-link" href="{{ route('assessments.index') }}">
            <svg aria-hidden="true"><use href="#icon-document"/></svg>
            Assessments
        </a>

        <a class="nav-link active" href="{{ route('account.security') }}" aria-current="page">
            <svg aria-hidden="true"><use href="#icon-lock"/></svg>
            Account security
        </a>

        @if ($signedInUser->is_system_admin)
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
    <strong>Account security</strong>
</div>

    <div class="topbar-actions">
        <div class="account">
            <span class="avatar" aria-hidden="true">
                {{ mb_strtoupper(mb_substr($signedInUser->name, 0, 1)) }}
            </span>

            <div>
                <span class="account-name">{{ $signedInUser->name }}</span>
                <span class="account-email">{{ $signedInUser->email }}</span>
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
        <div class="page-heading"><div>
            <p class="eyebrow">Your risk workspace</p>
            <h1>Account security</h1>
            <p>Manage two-factor authentication and recovery codes for your account.</p>
        </div></div>
    @if (! config('fortify.mfa_enabled', true))
        <section class="settings-panel">
            <div class="section-heading">
                <h2>Two-factor authentication</h2>
                <span class="state-badge pending">Temporarily disabled</span>
            </div>
            <p>Two-factor authentication is turned off for local development.</p>
            <p>Setup, recovery codes and MFA management are unavailable while it is disabled. Your existing enrollment is retained.</p>
        </section>
    @else
    @if (! $user->two_factor_secret || ! $user->two_factor_confirmed_at)
        <div class="notice" role="status">
            Two-factor authentication is required.
            Complete setup below before accessing the toolkit.
        </div>
    @endif

    @if (session('status') === 'two-factor-authentication-confirmed')
        <div class="success" role="status">
            Two-factor authentication is now enabled. Save your recovery codes below.
        </div>
    @elseif (session('status') === 'two-factor-authentication-disabled')
        <div class="notice" role="status">Two-factor authentication has been disabled.</div>
    @elseif (session('status') === 'recovery-codes-generated')
        <div class="success" role="status">
            New recovery codes have been generated. Replace your previously saved codes with these.
        </div>
    @endif

    @foreach ($errors->getBags() as $errorBag)
        @if ($errorBag->any())
            <div class="errors" role="alert">
                <ul>
                    @foreach ($errorBag->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    @endforeach

    @if (! $user->two_factor_secret)
        <section class="settings-panel">
            <div class="section-heading"><h2>Two-factor authentication</h2><span class="state-badge pending">Not enabled</span></div>
            <p>Protect your account with your password and a code from an authenticator app.</p>
            <form method="POST" action="{{ url('/user/two-factor-authentication') }}">
                @csrf
                <button type="submit">Set up two-factor authentication</button>
            </form>
        </section>
    @elseif (! $user->two_factor_confirmed_at)
        <section class="settings-panel">
            <div class="section-heading"><h2>Finish setup</h2><span class="state-badge pending">Confirmation needed</span></div>
            <p>Scan this QR code using your authenticator app. Then enter the six-digit code shown in the app.</p>
            <div class="qr">{!! $user->twoFactorQrCodeSvg() !!}</div>
            <p>Two-factor authentication is not active until you successfully confirm a code.</p>
            <form method="POST" action="{{ url('/user/confirmed-two-factor-authentication') }}" class="confirmation-form">
                @csrf
                <label for="code">Authentication code</label>
                <input id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" minlength="6" maxlength="6" required>
                <button type="submit" class="submit">Confirm and enable</button>
            </form>
        </section>
        <section class="settings-panel">
            <h2>Cancel setup</h2>
            <form method="POST" action="{{ url('/user/two-factor-authentication') }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="danger">Cancel setup</button>
            </form>
        </section>
    @else
        <section class="settings-panel">
            <div class="section-heading"><h2>Two-factor authentication</h2><span class="state-badge">Enabled</span></div>
            <p>When you next sign in with your password, you will also be asked for an authentication code.</p>
            <h3>Recovery codes</h3>
            <p>Save these somewhere private before signing out. They allow you to sign in if you lose access to your authenticator. Each code can be used once.</p>
            <div class="codes">
                @foreach ($user->recoveryCodes() as $recoveryCode)
                    <div><code>{{ $recoveryCode }}</code></div>
                @endforeach
            </div>
            <p>Generating new codes invalidates your previous recovery codes.</p>
            <form method="POST" action="{{ url('/user/two-factor-recovery-codes') }}">
                @csrf
                <button type="submit">Generate new recovery codes</button>
            </form>
        </section>
        <section class="settings-panel danger-panel">
            <h2>Disable two-factor authentication</h2>
            <p>This removes the authentication-code requirement from your account.</p>
            <form method="POST" action="{{ url('/user/two-factor-authentication') }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="danger">Disable two-factor authentication</button>
            </form>
        </section>
    @endif
    @endif
        <footer class="page-footer"><span>NACSA Toolkit · Risk assessment workspace</span></footer>
    </main>
</div>
</body>
</html>
