<!-- Google Fonts: Outfit & Red Hat Display -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;700&family=Red+Hat+Display:wght@400;500;700&display=swap" rel="stylesheet">

<style>
    :root {
        --primary-lilac: #743AED;
        --primary-lilac-hover: #5D2CC8;
        --primary-lilac-light: rgba(116, 58, 237, 0.1);
        --secondary-midnight: #05092E;
        --text-body: rgba(5, 9, 46, 0.7);
        --bg-input: #F3F4F6;
        --border-subtle: rgba(0, 9, 51, 0.06);
        --radius-xl: 12px;
        --radius-md: 6px;
    }

    body, .layout-fixed, .login-page, .register-page {
        background-color: #FFFFFF !important;
        font-family: 'Red Hat Display', sans-serif !important;
        color: var(--text-body) !important;
    }

    h1, h2, h3, h4, h5, h6, .brand-text, .title {
        font-family: 'Outfit', sans-serif !important;
        color: var(--secondary-midnight) !important;
        font-weight: 700 !important;
    }

    /* Primary Color Overrides */
    .btn-primary, .bg-custom-blue, .bg-primary, .icheck-primary > input:first-child:checked + label::before {
        background-color: var(--primary-lilac) !important;
        border-color: var(--primary-lilac) !important;
    }

    .btn-primary:hover, .bg-custom-blue:hover {
        background-color: var(--primary-lilac-hover) !important;
        border-color: var(--primary-lilac-hover) !important;
    }

    .text-primary {
        color: var(--primary-lilac) !important;
    }

    /* Form Controls & Input Groups */
    .form-control, .custom-select {
        background-color: var(--bg-input) !important;
        border: 1px solid rgba(0, 9, 51, 0.1) !important;
        border-radius: var(--radius-xl) !important;
        height: 3.5rem !important;
        color: var(--secondary-midnight) !important;
        transition: all 0.2s ease !important;
    }

    .input-group-text {
        background-color: var(--bg-input) !important;
        border: 1px solid rgba(0, 9, 51, 0.1) !important;
        border-left: none !important;
        color: var(--primary-lilac) !important;
        border-top-right-radius: var(--radius-xl) !important;
        border-bottom-right-radius: var(--radius-xl) !important;
        padding: 0 1.25rem !important;
    }

    .form-control:focus {
        background-color: #FFFFFF !important;
        border-color: var(--primary-lilac) !important;
        box-shadow: 0 0 0 2px var(--primary-lilac-light) !important;
        outline: none !important;
    }

    .input-group:focus-within .input-group-text {
        background-color: #FFFFFF !important;
        border-color: var(--primary-lilac) !important;
        border-left: none !important;
    }

    .input-group:focus-within .form-control {
        border-right: none !important;
    }

    /* Scrollbar */
    *::-webkit-scrollbar {
        width: 5px; height: 5px;
    }
    *::-webkit-scrollbar-track { background-color: white; }
    *::-webkit-scrollbar-thumb {
        background-color: var(--primary-lilac);
        border-radius: 100px;
    }
</style>
