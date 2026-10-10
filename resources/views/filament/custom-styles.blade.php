<style>
    /* 1. Global Font & Panel Base */
    body.fi-body {
        background-color: #f8fafc !important;
        font-family: 'Nunito', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
        -webkit-font-smoothing: antialiased !important;
    }

    /* 2. Login Page Container & Modern Card ala Skomda */
    .fi-simple-layout {
        min-height: 100vh !important;
        background: radial-gradient(circle at 50% 10%, #f1f5f9 0%, #f8fafc 100%) !important;
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 2rem 1rem !important;
    }

    .fi-simple-main-ctn {
        width: 100% !important;
        max-width: 440px !important;
        margin: 0 auto !important;
    }

    .fi-simple-main {
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 1.5rem !important; /* 24px */
        padding: 2.25rem 2rem !important;
        box-shadow: 0 20px 25px -5px rgba(15, 23, 42, 0.05), 0 8px 10px -6px rgba(15, 23, 42, 0.03) !important;
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
        color: #475569 !important;
        margin-bottom: 0.35rem !important;
        display: block !important;
    }

    .fi-fo-field-label-required-mark {
        color: #ef4444 !important;
        margin-left: 0.2rem !important;
        font-weight: 900 !important;
    }

    /* 4. Input Wrapper & Field Reset */
    .fi-input-wrp {
        display: flex !important;
        align-items: center !important;
        position: relative !important;
        width: 100% !important;
        min-height: 46px !important;
        height: 46px !important;
        border-radius: 0.75rem !important; /* 12px */
        border: 1.5px solid #e2e8f0 !important;
        background-color: #f8fafc !important;
        transition: all 0.15s ease-in-out !important;
        box-shadow: none !important;
        overflow: hidden !important;
        box-sizing: border-box !important;
    }

    .fi-input-wrp:focus-within {
        border-color: #d97706 !important;
        background-color: #ffffff !important;
        box-shadow: 0 0 0 3px rgba(217, 119, 6, 0.15) !important;
    }

    .fi-input-wrp-content-ctn {
        display: flex !important;
        flex: 1 1 0% !important;
        width: 100% !important;
        height: 100% !important;
        align-items: center !important;
    }

    /* Reset mutlak untuk tag input agar tidak bertumpuk atau keluar batas */
    .fi-input-wrp input,
    input.fi-input {
        display: block !important;
        width: 100% !important;
        height: 100% !important;
        background: transparent !important;
        background-color: transparent !important;
        border: none !important;
        border-width: 0 !important;
        outline: none !important;
        box-shadow: none !important;
        padding: 0.65rem 1rem !important;
        font-size: 0.875rem !important;
        font-weight: 600 !important;
        color: #0f172a !important;
        box-sizing: border-box !important;
        -webkit-appearance: none !important;
        appearance: none !important;
    }

    .fi-input-wrp input::placeholder,
    input.fi-input::placeholder {
        color: #94a3b8 !important;
        font-weight: 500 !important;
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
        color: #94a3b8 !important;
        cursor: pointer !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.3rem !important;
        border-radius: 0.5rem !important;
        transition: color 0.15s, background-color 0.15s !important;
    }

    /* Pastikan tombol mata yang di-hide Alpine / x-show benar-benar tersembunyi */
    .fi-input-wrp-actions button[x-cloak],
    .fi-input-wrp-actions button[style*="display: none"],
    .fi-input-wrp-actions button.hidden {
        display: none !important;
    }

    .fi-input-wrp-actions button:hover,
    .fi-icon-btn:hover {
        color: #475569 !important;
        background-color: #f1f5f9 !important;
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
        border: 1.5px solid #cbd5e1 !important;
        background-color: #ffffff !important;
        cursor: pointer !important;
        accent-color: #d97706 !important;
        margin: 0 !important;
        vertical-align: middle !important;
    }

    label[for="form.remember"] .fi-fo-field-label-content {
        font-size: 0.8125rem !important;
        color: #475569 !important;
        font-weight: 600 !important;
        text-transform: none !important;
        letter-spacing: normal !important;
        margin: 0 !important;
    }

    /* 7. Action Button: Tombol Masuk Biasa & Sederhana (Tanpa Animasi Berlebihan) */
    .fi-sc-actions,
    #content\.form-actions,
    .fi-ac.fi-width-full {
        width: 100% !important;
        margin-top: 1.25rem !important;
        display: block !important;
    }

    form button[type="submit"],
    .fi-ac-btn-action,
    .fi-btn.fi-color-primary,
    .fi-btn-primary {
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
        transition: background-color 0.15s ease !important; /* Transisi warna wajar tanpa loncat */
        transform: none !important;
        box-sizing: border-box !important;
    }

    form button[type="submit"]:hover,
    .fi-ac-btn-action:hover {
        background-color: #b45309 !important; /* Sedikit lebih gelap saat di-hover */
        transform: none !important;
        filter: none !important;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
    }

    form button[type="submit"]:active,
    .fi-ac-btn-action:active {
        background-color: #92400e !important;
        transform: none !important;
    }

    form button[type="submit"] span,
    .fi-ac-btn-action span {
        color: #ffffff !important;
        font-weight: 700 !important;
        font-size: 0.9375rem !important;
        display: inline-block !important;
    }

    form button[type="submit"] svg {
        color: #ffffff !important;
        width: 20px !important;
        height: 20px !important;
    }

    /* 8. Topbar & Header Admin */
    .fi-topbar {
        background-color: #ffffff !important;
        border-bottom: 1px solid #e2e8f0 !important;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.03) !important;
    }

    /* 9. Sidebar Admin */
    aside.fi-sidebar {
        background-color: #ffffff !important;
        border-right: 1px solid #e2e8f0 !important;
        box-shadow: none !important;
    }

    .fi-sidebar-item-btn {
        border-radius: 0.75rem !important;
        font-weight: 700 !important;
        transition: all 0.15s ease-in-out !important;
    }

    .fi-sidebar-item-btn:hover {
        background-color: #f1f5f9 !important;
    }

    .fi-sidebar-group-label {
        font-size: 0.68rem !important;
        text-transform: uppercase !important;
        letter-spacing: 0.06em !important;
        font-weight: 800 !important;
        color: #94a3b8 !important;
    }

    /* 10. Kartu Dashboard & Widget */
    .fi-section,
    .fi-wi-stats-overview-stat,
    .fi-ta-ctn {
        border-radius: 1.25rem !important;
        border: 1px solid #e2e8f0 !important;
        background-color: #ffffff !important;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.02) !important;
    }
</style>
