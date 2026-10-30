<style>
    :root { --purple: #5c4be0; --purple-dark: #4938be; --purple-light: #f0edff; --ink: #29263d; --muted: #656179; --border: #e5e2ef; --bg: #f6f7fb; }
    * { box-sizing: border-box; }
    [hidden] { display: none !important; }
    body { margin: 0; background: var(--bg); color: var(--ink); font: 14px/1.6 Arial, sans-serif; }
    a { color: var(--purple); text-decoration: none; } a:hover { text-decoration: underline; }
    button, input, select, textarea { font: inherit; }
    :focus-visible { outline: 3px solid #b9aaff; outline-offset: 3px; }
    .skip-link { position: fixed; top: -80px; left: 16px; z-index: 10; padding: 12px; background: white; border: 2px solid var(--purple); border-radius: 8px; } .skip-link:focus { top: 12px; }
    .layout { display: flex; min-height: 100vh; }
    .sidebar { width: 254px; flex-shrink: 0; padding: 28px 18px; color: white; background: linear-gradient(180deg, #7160ef, #5c4be0); }
    .sidebar-inner { position: sticky; top: 28px; }
    .brand-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
    .brand { display: flex; align-items: center; gap: 12px; color: white; } .brand:hover { text-decoration: none; }
    .brand-mark { width: 46px; height: 46px; display: grid; place-items: center; border: 1px solid #ffffff70; border-radius: 14px; font-size: 16px; font-weight: 700; }
    .brand strong { display: block; font-size: 25px; line-height: 1.2; letter-spacing: 1px; } .brand small { color: #f1eeff; }
    .menu-caption { margin: 30px 14px 10px; font-size: 11px; font-weight: 700; letter-spacing: 1.5px; color: #eeeaff; }
    .menu { display: grid; gap: 5px; }
    .menu a { display: flex; align-items: center; gap: 12px; min-height: 46px; padding: 10px 14px; border-radius: 12px; color: white; font-weight: 600; }
    .menu a:hover { background: #ffffff20; text-decoration: none; } .menu a.active { background: white; color: var(--purple); }
    .menu svg { width: 20px; height: 20px; flex-shrink: 0; fill: none; stroke: currentColor; stroke-width: 1.7; stroke-linecap: round; stroke-linejoin: round; }
    .sidebar-note { margin: 26px 14px 14px; color: #f1eeff; font-size: 12px; }
    .logout { width: 100%; text-align: left; background: #ffffff12; color: white; border: 1px solid #ffffff45; border-radius: 12px; padding: 10px 14px; cursor: pointer; } .logout:hover { background: #ffffff25; }
    .nav-toggle { display: none; color: white; border: 1px solid #ffffff70; background: transparent; border-radius: 8px; padding: 8px 12px; cursor: pointer; }
    .content { flex: 1; min-width: 0; padding: 28px 36px 40px; max-width: 1700px; }
    .topbar { display: flex; justify-content: space-between; align-items: center; gap: 16px; margin-bottom: 26px; }
    .eyebrow { font-size: 11px; font-weight: 700; letter-spacing: 1.6px; color: var(--muted); text-transform: uppercase; }
    .profile-mini { display: flex; align-items: center; gap: 10px; background: white; border: 1px solid var(--border); padding: 8px 14px 8px 8px; border-radius: 40px; color: var(--ink); min-width: 0; }
    .profile-mini:hover { text-decoration: none; background: var(--purple-light); }
    .profile-mini strong { display: block; overflow-wrap: anywhere; line-height: 1.3; } .profile-mini small { display: block; color: var(--muted); font-size: 11px; }
    .avatar { display: grid; place-items: center; width: 36px; height: 36px; flex-shrink: 0; border-radius: 50%; background: var(--purple-light); color: var(--purple); font-weight: 700; }
    .page-heading { display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px; margin-bottom: 24px; }
    h1, h2, h3, p { margin-top: 0; } h1 { font-size: clamp(24px, 3vw, 32px); line-height: 1.25; margin-bottom: 8px; letter-spacing: -.6px; }
    h2 { font-size: 18px; line-height: 1.4; margin-bottom: 8px; } h3 { font-size: 15px; margin-bottom: 8px; } p { margin-bottom: 12px; }
    .page-heading p { margin-bottom: 0; color: var(--muted); max-width: 680px; }
    .card { background: white; border: 1px solid var(--border); border-radius: 16px; padding: 22px; margin-bottom: 22px; box-shadow: 0 4px 20px #29263d03; }
    .card-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; flex-wrap: wrap; margin-bottom: 18px; }
    .card-header h2 { margin-bottom: 2px; } .card-header p { margin-bottom: 0; }
    .muted, .help { color: var(--muted); } .help { font-size: 12px; margin: 6px 0 0; } .small { font-size: 12px; } .text-break { overflow-wrap: anywhere; } .nowrap { white-space: nowrap; }
    .grid { display: grid; gap: 18px; } .grid-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px; margin-bottom: 22px; }
    .stat { background: white; border: 1px solid var(--border); border-radius: 16px; padding: 20px; }
    .stat .label { color: var(--muted); font-size: 12px; font-weight: 600; } .stat .value { display: block; margin: 5px 0; font-size: 30px; line-height: 1.25; font-weight: 700; color: var(--purple); } .stat small { color: var(--muted); }
    .btn { display: inline-flex; align-items: center; justify-content: center; min-height: 42px; gap: 6px; padding: 9px 16px; border: 1px solid transparent; border-radius: 10px; font-weight: 600; background: var(--purple); color: white; cursor: pointer; line-height: 1.4; text-align: center; }
    .btn:hover { background: var(--purple-dark); text-decoration: none; } .btn-secondary { background: white; color: var(--purple); border-color: var(--border); } .btn-secondary:hover { background: var(--purple-light); }
    .btn-warning { background: #fff7e8; color: #8a4d08; border-color: #f2d7a6; } .btn-warning:hover { background: #ffedcc; }
    .btn-small { min-height: 34px; font-size: 12px; padding: 7px 10px; } .btn:disabled { opacity: .5; cursor: not-allowed; }
    .actions { display: flex; align-items: center; flex-wrap: wrap; gap: 8px; } .actions form { margin: 0; }
    .form-actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--border); } .form-card { max-width: 940px; }
    .field { margin-bottom: 18px; min-width: 0; } .field label, .field-label { display: block; margin-bottom: 6px; font-size: 13px; font-weight: 600; }
    input:not([type=checkbox]):not([type=hidden]), select, textarea { width: 100%; min-height: 43px; padding: 10px 12px; background: white; color: var(--ink); border: 1px solid #cec9df; border-radius: 8px; }
    input[readonly] { background: #f5f4f9 !important; color: var(--muted) !important; } textarea { min-height: 110px; resize: vertical; }
    input[type=checkbox] { width: 17px; height: 17px; flex-shrink: 0; accent-color: var(--purple); }
    .error { color: #a52a3b; font-size: 12px; margin: 5px 0 0; } [aria-invalid=true] { border-color: #b33648 !important; }
    fieldset { min-width: 0; border: 0; padding: 0; margin: 0 0 18px; } legend { font-size: 13px; font-weight: 600; padding: 0; margin-bottom: 8px; }
    .check, .field label.check { display: flex; align-items: flex-start; gap: 9px; padding: 10px 12px; border: 1px solid var(--border); border-radius: 9px; cursor: pointer; font-weight: 400; }
    .check input { margin: 3px 0 0; } .check:hover { background: var(--purple-light); } .check small { display: block; color: var(--muted); font-size: 12px; }
    .check-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; } .check-list { max-height: 360px; overflow-y: auto; padding: 3px; }
    .filter-form { display: flex; align-items: flex-end; flex-wrap: wrap; gap: 12px; } .filter-form .field { flex: 1 1 160px; margin-bottom: 0; } .filter-form .field-search { flex: 2 1 230px; }
    .alert { padding: 14px 18px; border-radius: 12px; border: 1px solid; margin-bottom: 20px; overflow-wrap: anywhere; }
    .alert-success { background: #edf9f2; color: #23613f; border-color: #bfe5cc; } .alert-error { background: #fff0f2; color: #96293b; border-color: #efc0c8; }
    .alert-info { background: var(--purple-light); color: #514198; border-color: #dcd4ff; } .alert-warning { background: #fff8eb; color: #855111; border-color: #f0d9b4; }
    .alert p:last-child { margin-bottom: 0; } .alert ul { margin: 6px 0 0; padding-left: 20px; }
    .badge { display: inline-block; border-radius: 20px; padding: 3px 9px; font-size: 11px; font-weight: 600; background: var(--purple-light); color: var(--purple); white-space: nowrap; }
    .badge-success { background: #e8f5ed; color: #24603e; } .badge-muted { background: #f0eff4; color: #635e72; } .badge-warning { background: #fff3dc; color: #8b5315; } .tags { display: flex; flex-wrap: wrap; gap: 5px; }
    .table-wrap { overflow-x: auto; width: 100%; border: 1px solid var(--border); border-radius: 11px; }
    table { width: 100%; border-collapse: collapse; text-align: left; font-size: 13px; }
    th { padding: 12px 14px; color: var(--muted); font-size: 11px; text-transform: uppercase; letter-spacing: .5px; background: #f8f7fc; white-space: nowrap; }
    td { padding: 14px; border-top: 1px solid var(--border); vertical-align: top; } tbody tr:hover { background: #fcfbff; } td strong { display: block; } .number { text-align: right; font-variant-numeric: tabular-nums; }
    .empty { padding: 34px 18px; text-align: center; color: var(--muted); border: 1px dashed #d9d3e9; border-radius: 12px; } .empty h2, .empty h3 { color: var(--ink); } .empty p { max-width: 520px; margin: 0 auto 16px; }
    .pagination { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-top: 18px; } .page-links { display: flex; flex-wrap: wrap; align-items: center; gap: 5px; }
    .page-link { display: inline-flex; align-items: center; justify-content: center; min-width: 36px; min-height: 36px; padding: 6px 10px; border-radius: 8px; border: 1px solid var(--border); background: white; }
    .page-link[aria-current=page] { color: white; background: var(--purple); border-color: var(--purple); } .page-link[aria-disabled=true] { color: #817a90; background: #f3f1f7; }
    dl { margin: 0; } .identity { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 20px; } dt { color: var(--muted); font-size: 12px; margin-bottom: 4px; } dd { margin: 0; font-weight: 600; overflow-wrap: anywhere; }
    details { border: 1px solid var(--border); border-radius: 10px; margin-top: 18px; } summary { padding: 12px 15px; cursor: pointer; color: var(--purple); font-weight: 600; } .details-body { padding: 0 15px 15px; }
    .rekap-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; margin: 16px 0; } .rekap-stat { padding: 12px; background: #f8f7fc; border-radius: 10px; }
    .rekap-stat strong { display: block; color: var(--purple); font-size: 22px; line-height: 1.4; } .rekap-stat span { color: var(--muted); font-size: 12px; } .footer { margin-top: 30px; color: var(--muted); font-size: 11px; }
    @media (max-width: 1100px) { .sidebar { width: 226px; padding: 24px 14px; } .content { padding: 24px; } .stats { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 760px) {
        .layout { display: block; } .sidebar { width: 100%; padding: 16px; } .sidebar-inner { position: static; }
        .brand-mark { width: 38px; height: 38px; } .brand strong { font-size: 22px; } .js .nav-toggle { display: block; } .js .sidebar:not(.is-open) .sidebar-content { display: none; }
        .menu-caption { margin-top: 20px; } .menu { grid-template-columns: repeat(2, minmax(0, 1fr)); } .menu a { padding: 10px; font-size: 13px; } .sidebar-note { margin-top: 14px; }
        .content { padding: 20px 16px 30px; } .topbar { margin-bottom: 22px; } .topbar .eyebrow { font-size: 10px; } .profile-mini { max-width: 62%; } .profile-mini strong { font-size: 12px; }
        .card { padding: 16px; } .grid-2, .check-grid, .identity { grid-template-columns: 1fr; } .stats { gap: 10px; } .stat { padding: 15px; } .stat .value { font-size: 26px; }
        .rekap-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); } .filter-form .field { flex-basis: 45%; } .filter-form .field-search { flex-basis: 100%; } .form-actions .btn { flex: 1; }
    }
    @media print { .sidebar, .topbar, .footer, .filter-form, .actions, .page-heading > .btn { display: none; } .layout { display: block; } .content { padding: 0; max-width: none; } .card { box-shadow: none; break-inside: avoid; } .table-wrap { overflow: visible; } }
</style>
