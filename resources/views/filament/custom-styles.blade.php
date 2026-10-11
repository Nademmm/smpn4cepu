<style>
    /* 1. Global Font, Panel Base & Minimalist Grey Scrollbar */
    body.fi-body {
        font-family: 'Nunito', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
        -webkit-font-smoothing: antialiased !important;
    }

    html:not(.dark) body.fi-body {
        background-color: #f8fafc !important;
        color: #0f172a !important;
    }

    html.dark body.fi-body {
        background-color: #090d16 !important;
        color: #f8fafc !important;
    }

    /* Minimalist Grey Scrollbars (Light & Dark Ready) */
    html,
    body.fi-body,
    .fi-body *,
    .fi-body *::before,
    .fi-body *::after {
        scrollbar-width: thin !important;
    }

    html:not(.dark),
    html:not(.dark) body.fi-body,
    html:not(.dark) .fi-body * {
        scrollbar-color: #cbd5e1 transparent !important;
    }

    html.dark,
    html.dark body.fi-body,
    html.dark .fi-body * {
        scrollbar-color: #475569 transparent !important;
    }

    /* WebKit Scrollbar (Chrome, Edge, Safari, Opera) */
    .fi-body ::-webkit-scrollbar,
    ::-webkit-scrollbar {
        width: 6px !important;
        height: 6px !important;
    }

    .fi-body ::-webkit-scrollbar-track,
    ::-webkit-scrollbar-track {
        background: transparent !important;
    }

    html:not(.dark) .fi-body ::-webkit-scrollbar-thumb,
    html:not(.dark) ::-webkit-scrollbar-thumb {
        background-color: #cbd5e1 !important;
        border-radius: 9999px !important;
        border: 1px solid transparent !important;
    }

    html:not(.dark) .fi-body ::-webkit-scrollbar-thumb:hover,
    html:not(.dark) ::-webkit-scrollbar-thumb:hover {
        background-color: #94a3b8 !important;
    }

    html.dark .fi-body ::-webkit-scrollbar-thumb,
    html.dark ::-webkit-scrollbar-thumb {
        background-color: #475569 !important;
        border-radius: 9999px !important;
        border: 1px solid transparent !important;
    }

    html.dark .fi-body ::-webkit-scrollbar-thumb:hover,
    html.dark ::-webkit-scrollbar-thumb:hover {
        background-color: #64748b !important;
    }

    .fi-body ::-webkit-scrollbar-corner,
    ::-webkit-scrollbar-corner {
        background: transparent !important;
    }

    /* 2. Login Page Container & Modern Card ala Skomda */
    .fi-simple-layout {
        min-height: 100vh !important;
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 2rem 1rem !important;
    }

    html:not(.dark) .fi-simple-layout {
        background: radial-gradient(circle at 50% 10%, #f1f5f9 0%, #f8fafc 100%) !important;
    }

    html.dark .fi-simple-layout {
        background: radial-gradient(circle at 50% 10%, #0f172a 0%, #020617 100%) !important;
    }

    .fi-simple-main-ctn {
        width: 100% !important;
        max-width: 440px !important;
        margin: 0 auto !important;
    }

    .fi-simple-main {
        border-radius: 1.5rem !important; /* 24px */
        padding: 2.25rem 2rem !important;
        transition: background-color 0.2s, border-color 0.2s !important;
    }

    html:not(.dark) .fi-simple-main {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 20px 25px -5px rgba(15, 23, 42, 0.05), 0 8px 10px -6px rgba(15, 23, 42, 0.03) !important;
    }

    html.dark .fi-simple-main {
        background: #0f172a !important;
        border: 1px solid #1e293b !important;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5) !important;
    }

    /* Sembunyikan default header sederhana Filament agar tidak dobel/tumpang-tindih */
    .fi-simple-header {
        display: none !important;
    }

    /* 3. Form Grid Spacing & Field Labels */
    .fi-sc-has-gap {
        gap: 1.15rem !important;
    }

    .fi-fo-field {
        margin-bottom: 0.25rem !important;
    }

    .fi-fo-field-label,
    .fi-fo-field-label-content,
    .fi-fo-field-wrp-label label {
        font-size: 0.75rem !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.04em !important;
        margin-bottom: 0.35rem !important;
        display: block !important;
    }

    html:not(.dark) .fi-fo-field-label,
    html:not(.dark) .fi-fo-field-label-content,
    html:not(.dark) .fi-fo-field-wrp-label label {
        color: #475569 !important;
    }

    html.dark .fi-fo-field-label,
    html.dark .fi-fo-field-label-content,
    html.dark .fi-fo-field-wrp-label label {
        color: #cbd5e1 !important;
    }

    .fi-fo-field-label-required-mark {
        color: #ef4444 !important;
        margin-left: 0.2rem !important;
        font-weight: 900 !important;
    }

    /* 4. Input Wrapper & Field Reset (Profesional, Clean & Responsif) */
    .fi-input-wrp {
        display: flex !important;
        position: relative !important;
        width: 100% !important;
        min-height: 42px !important;
        height: auto !important; /* Fleksibel untuk text biasa maupun textarea multiline */
        border-radius: 0.75rem !important; /* 12px */
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out, background-color 0.15s ease-in-out !important;
        box-shadow: none !important;
        overflow: visible !important; /* Cegah teks dan placeholder terpotong vertikal */
        box-sizing: border-box !important;
    }

    html:not(.dark) .fi-input-wrp {
        border: 1.5px solid #cbd5e1 !important;
        background-color: #ffffff !important;
    }

    html:not(.dark) .fi-input-wrp:focus-within {
        border-color: #d97706 !important;
        background-color: #ffffff !important;
        box-shadow: 0 0 0 3px rgba(217, 119, 6, 0.15) !important;
    }

    html.dark .fi-input-wrp {
        border: 1.5px solid #334155 !important;
        background-color: #1e293b !important;
    }

    html.dark .fi-input-wrp:focus-within {
        border-color: #f59e0b !important;
        background-color: #0f172a !important;
        box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.2) !important;
    }

    .fi-input-wrp-content-ctn {
        display: flex !important;
        flex: 1 1 0% !important;
        width: 100% !important;
        min-width: 0 !important;
        height: auto !important;
        align-items: center !important;
    }

    /* Single-line Text Input */
    .fi-input-wrp input,
    input.fi-input {
        display: block !important;
        width: 100% !important;
        min-height: 40px !important;
        height: auto !important;
        line-height: 1.5 !important;
        background: transparent !important;
        background-color: transparent !important;
        border: none !important;
        border-width: 0 !important;
        outline: none !important;
        box-shadow: none !important;
        padding: 0.55rem 0.875rem !important;
        font-size: 0.875rem !important;
        font-weight: 500 !important;
        box-sizing: border-box !important;
        -webkit-appearance: none !important;
        appearance: none !important;
    }

    /* Khusus Textarea (Multiline Input Visi, Misi, Deskripsi dll) */
    .fi-fo-textarea-wrp .fi-input-wrp,
    .fi-input-wrp:has(textarea),
    .fi-fo-textarea {
        min-height: 110px !important;
        height: auto !important;
        align-items: stretch !important;
    }

    .fi-fo-textarea textarea,
    .fi-input-wrp textarea,
    textarea.fi-input {
        display: block !important;
        width: 100% !important;
        min-height: 110px !important;
        height: auto !important;
        line-height: 1.6 !important;
        background: transparent !important;
        background-color: transparent !important;
        border: none !important;
        border-width: 0 !important;
        outline: none !important;
        box-shadow: none !important;
        padding: 0.75rem 0.875rem !important;
        font-size: 0.875rem !important;
        font-weight: 500 !important;
        font-family: inherit !important;
        resize: vertical !important;
        box-sizing: border-box !important;
    }

    /* Warna Teks dan Placeholder */
    html:not(.dark) .fi-input-wrp input,
    html:not(.dark) .fi-input-wrp textarea,
    html:not(.dark) input.fi-input,
    html:not(.dark) textarea.fi-input {
        color: #0f172a !important;
    }

    html.dark .fi-input-wrp input,
    html.dark .fi-input-wrp textarea,
    html.dark input.fi-input,
    html.dark textarea.fi-input {
        color: #f8fafc !important;
    }

    html:not(.dark) .fi-input-wrp input::placeholder,
    html:not(.dark) .fi-input-wrp textarea::placeholder,
    html:not(.dark) input.fi-input::placeholder,
    html:not(.dark) textarea.fi-input::placeholder {
        color: #94a3b8 !important;
        font-weight: 400 !important;
    }

    html.dark .fi-input-wrp input::placeholder,
    html.dark .fi-input-wrp textarea::placeholder,
    html.dark input.fi-input::placeholder,
    html.dark textarea.fi-input::placeholder {
        color: #64748b !important;
        font-weight: 400 !important;
    }

    /* Select Input & Options */
    .fi-select-input,
    select.fi-input {
        min-height: 40px !important;
        padding: 0.55rem 0.875rem !important;
        font-size: 0.875rem !important;
        font-weight: 500 !important;
        background: transparent !important;
        border: none !important;
        outline: none !important;
    }

    html:not(.dark) select.fi-input option {
        background-color: #ffffff !important;
        color: #0f172a !important;
    }

    html.dark select.fi-input option {
        background-color: #1e293b !important;
        color: #f8fafc !important;
    }

    /* 5. Password Reveal / Toggle Button */
    .fi-input-wrp-suffix {
        display: flex !important;
        align-items: center !important;
        padding-right: 0.75rem !important;
        background: transparent !important;
    }

    .fi-input-wrp-actions {
        display: flex !important;
        align-items: center !important;
    }

    .fi-input-wrp-actions button,
    .fi-ac-icon-btn-action,
    .fi-icon-btn {
        background: transparent !important;
        border: none !important;
        outline: none !important;
        box-shadow: none !important;
        cursor: pointer !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.3rem !important;
        border-radius: 0.5rem !important;
        transition: color 0.15s, background-color 0.15s !important;
    }

    html:not(.dark) .fi-input-wrp-actions button,
    html:not(.dark) .fi-ac-icon-btn-action,
    html:not(.dark) .fi-icon-btn {
        color: #94a3b8 !important;
    }

    html.dark .fi-input-wrp-actions button,
    html.dark .fi-ac-icon-btn-action,
    html.dark .fi-icon-btn {
        color: #94a3b8 !important;
    }

    .fi-input-wrp-actions button[x-cloak],
    .fi-input-wrp-actions button[style*="display: none"],
    .fi-input-wrp-actions button.hidden {
        display: none !important;
    }

    html:not(.dark) .fi-input-wrp-actions button:hover,
    html:not(.dark) .fi-icon-btn:hover {
        color: #475569 !important;
        background-color: #f1f5f9 !important;
    }

    html.dark .fi-input-wrp-actions button:hover,
    html.dark .fi-icon-btn:hover {
        color: #e2e8f0 !important;
        background-color: #334155 !important;
    }

    .fi-input-wrp-actions button svg,
    .fi-icon-btn svg {
        width: 18px !important;
        height: 18px !important;
        display: block !important;
    }

    /* 6. Checkbox & Remember Me */
    label[for="form.remember"],
    label:has(input[type="checkbox"]) {
        display: inline-flex !important;
        align-items: center !important;
        gap: 0.6rem !important;
        cursor: pointer !important;
        user-select: none !important;
        margin-top: 0.5rem !important;
    }

    input[type="checkbox"].fi-checkbox-input,
    #form\.remember {
        width: 18px !important;
        height: 18px !important;
        min-width: 18px !important;
        min-height: 18px !important;
        border-radius: 0.35rem !important;
        cursor: pointer !important;
        accent-color: #d97706 !important;
        margin: 0 !important;
        vertical-align: middle !important;
    }

    html:not(.dark) input[type="checkbox"].fi-checkbox-input,
    html:not(.dark) #form\.remember {
        border: 1.5px solid #cbd5e1 !important;
        background-color: #ffffff !important;
    }

    html.dark input[type="checkbox"].fi-checkbox-input,
    html.dark #form\.remember {
        border: 1.5px solid #475569 !important;
        background-color: #1e293b !important;
    }

    html:not(.dark) label[for="form.remember"] .fi-fo-field-label-content {
        color: #475569 !important;
    }

    html.dark label[for="form.remember"] .fi-fo-field-label-content {
        color: #cbd5e1 !important;
    }

    label[for="form.remember"] .fi-fo-field-label-content {
        font-size: 0.8125rem !important;
        font-weight: 600 !important;
        text-transform: none !important;
        letter-spacing: normal !important;
        margin: 0 !important;
    }

    /* 7. Action Button: Tombol Masuk Khusus Halaman Login */
    .fi-simple-layout .fi-sc-actions,
    .fi-simple-main .fi-sc-actions,
    .fi-simple-layout #content\.form-actions,
    .fi-simple-layout .fi-ac.fi-width-full {
        width: 100% !important;
        margin-top: 1.25rem !important;
        display: block !important;
    }

    .fi-simple-layout form button[type="submit"],
    .fi-simple-main form button[type="submit"] {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 100% !important;
        min-height: 46px !important;
        height: 46px !important;
        padding: 0.75rem 1.5rem !important;
        border-radius: 0.625rem !important; /* 10px rapi */
        font-weight: 700 !important;
        font-size: 0.9375rem !important;
        color: #ffffff !important;
        background-color: #d97706 !important; /* Amber solid standar yang tenang */
        border: none !important;
        cursor: pointer !important;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
        transition: background-color 0.15s ease !important;
        transform: none !important;
        box-sizing: border-box !important;
    }

    .fi-simple-layout form button[type="submit"]:hover,
    .fi-simple-main form button[type="submit"]:hover {
        background-color: #b45309 !important;
        transform: none !important;
        filter: none !important;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
    }

    .fi-simple-layout form button[type="submit"]:active,
    .fi-simple-main form button[type="submit"]:active {
        background-color: #92400e !important;
        transform: none !important;
    }

    .fi-simple-layout form button[type="submit"] span,
    .fi-simple-main form button[type="submit"] span {
        color: #ffffff !important;
        font-weight: 700 !important;
        font-size: 0.9375rem !important;
        display: inline-block !important;
    }

    .fi-simple-layout form button[type="submit"] svg,
    .fi-simple-main form button[type="submit"] svg {
        color: #ffffff !important;
        width: 20px !important;
        height: 20px !important;
    }

    /* 8. Fix Tombol Logout di Dashboard Panel (AccountWidget) agar Tidak Dobel */
    .fi-account-widget-logout-form {
        display: flex !important;
        align-items: center !important;
    }

    .fi-account-widget-logout-form .fi-icon-btn {
        display: none !important;
    }

    .fi-account-widget-logout-form .fi-btn {
        display: inline-flex !important;
        align-items: center !important;
        gap: 0.35rem !important;
        width: auto !important;
        min-height: 36px !important;
        height: 36px !important;
        padding: 0.4rem 0.85rem !important;
        border-radius: 0.5rem !important;
        font-size: 0.8125rem !important;
        font-weight: 600 !important;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.04) !important;
        transition: all 0.15s ease-in-out !important;
    }

    html:not(.dark) .fi-account-widget-logout-form .fi-btn {
        background-color: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        color: #64748b !important;
    }

    html.dark .fi-account-widget-logout-form .fi-btn {
        background-color: #1e293b !important;
        border: 1px solid #334155 !important;
        color: #cbd5e1 !important;
    }

    html:not(.dark) .fi-account-widget-logout-form .fi-btn:hover {
        background-color: #fef2f2 !important;
        border-color: #fecaca !important;
        color: #dc2626 !important;
    }

    html.dark .fi-account-widget-logout-form .fi-btn:hover {
        background-color: #450a0a !important;
        border-color: #7f1d1d !important;
        color: #fca5a5 !important;
    }

    .fi-account-widget-logout-form .fi-btn svg {
        width: 16px !important;
        height: 16px !important;
        color: currentColor !important;
    }

    /* 9. Topbar & Header Admin (Light & Dark Ready) */
    .fi-topbar {
        transition: background-color 0.2s, border-color 0.2s !important;
    }

    html:not(.dark) .fi-topbar {
        background-color: #ffffff !important;
        border-bottom: 1px solid #e2e8f0 !important;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.03) !important;
    }

    html.dark .fi-topbar {
        background-color: #0f172a !important;
        border-bottom: 1px solid #1e293b !important;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.3) !important;
    }

    /* Hilangkan tombol panah chevron duplikat di topbar agar hanya ada 1 tombol navigasi menu */
    .fi-topbar-collapse-sidebar-btn-ctn {
        display: none !important;
    }

    /* Pastikan satu tombol toggle sidebar (hamburger / close) tampil konsisten di desktop & mobile */
    .fi-topbar-open-sidebar-btn:not([style*="display: none"]),
    .fi-topbar-close-sidebar-btn:not([style*="display: none"]) {
        display: inline-flex !important;
        margin-right: 0.5rem !important;
    }

    .fi-topbar-open-sidebar-btn[style*="display: none"],
    .fi-topbar-close-sidebar-btn[style*="display: none"] {
        display: none !important;
    }

    /* Penataan jarak logo/nama sekolah di samping tombol toggle */
    .fi-topbar-start .fi-logo {
        margin-inline-start: 0.25rem !important;
    }

    /* Tombol icon topbar kontras & transisi halus */
    html:not(.dark) .fi-topbar .fi-icon-btn {
        color: #475569 !important;
    }

    html:not(.dark) .fi-topbar .fi-icon-btn:hover {
        color: #0f172a !important;
        background-color: #f1f5f9 !important;
    }

    html.dark .fi-topbar .fi-icon-btn {
        color: #94a3b8 !important;
    }

    html.dark .fi-topbar .fi-icon-btn:hover {
        color: #f8fafc !important;
        background-color: #1e293b !important;
    }

    /* 10. Sidebar Admin (Light & Dark Ready) */
    aside.fi-sidebar {
        transition: background-color 0.2s, border-color 0.2s !important;
    }

    html:not(.dark) aside.fi-sidebar {
        background-color: #ffffff !important;
        border-right: 1px solid #e2e8f0 !important;
        box-shadow: none !important;
    }

    html.dark aside.fi-sidebar {
        background-color: #0f172a !important;
        border-right: 1px solid #1e293b !important;
        box-shadow: none !important;
    }

    .fi-sidebar-item-btn {
        border-radius: 0.75rem !important;
        font-weight: 700 !important;
        transition: all 0.15s ease-in-out !important;
    }

    html:not(.dark) .fi-sidebar-item-btn:hover {
        background-color: #f1f5f9 !important;
    }

    html.dark .fi-sidebar-item-btn:hover {
        background-color: #1e293b !important;
    }

    .fi-sidebar-group-label {
        font-size: 0.68rem !important;
        text-transform: uppercase !important;
        letter-spacing: 0.06em !important;
        font-weight: 800 !important;
    }

    html:not(.dark) .fi-sidebar-group-label {
        color: #94a3b8 !important;
    }

    html.dark .fi-sidebar-group-label {
        color: #64748b !important;
    }

    /* 11. Kartu Dashboard, Widget & Tabel Data (Light & Dark Ready) */
    .fi-section,
    .fi-wi-stats-overview-stat,
    .fi-ta-ctn {
        border-radius: 1.25rem !important;
        transition: background-color 0.2s, border-color 0.2s !important;
    }

    html:not(.dark) .fi-section,
    html:not(.dark) .fi-wi-stats-overview-stat,
    html:not(.dark) .fi-ta-ctn {
        border: 1px solid #e2e8f0 !important;
        background-color: #ffffff !important;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.02) !important;
    }

    html.dark .fi-section,
    html.dark .fi-wi-stats-overview-stat,
    html.dark .fi-ta-ctn {
        border: 1px solid #1e293b !important;
        background-color: #0f172a !important;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.3) !important;
    }

    /* 12. Helper Styling untuk Elemen Login Kustom */
    html:not(.dark) .fi-login-title {
        color: #0f172a !important;
    }

    html.dark .fi-login-title {
        color: #f8fafc !important;
    }

    html:not(.dark) .fi-login-subtitle {
        color: #64748b !important;
    }

    html.dark .fi-login-subtitle {
        color: #94a3b8 !important;
    }

    html:not(.dark) .fi-login-back-btn {
        color: #64748b !important;
    }

    html.dark .fi-login-back-btn {
        color: #94a3b8 !important;
    }

    html:not(.dark) .fi-login-back-btn:hover {
        color: #0f172a !important;
    }

    html.dark .fi-login-back-btn:hover {
        color: #f8fafc !important;
    }

    html:not(.dark) .fi-login-badge {
        background-color: #f1f5f9 !important;
        color: #475569 !important;
    }

    html.dark .fi-login-badge {
        background-color: #1e293b !important;
        color: #94a3b8 !important;
    }

    html:not(.dark) .fi-login-divider {
        border-color: #f1f5f9 !important;
    }

    html.dark .fi-login-divider {
        border-color: #1e293b !important;
    }
</style>
