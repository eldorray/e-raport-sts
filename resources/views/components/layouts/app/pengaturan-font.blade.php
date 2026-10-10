{{-- Pengaturan font dari halaman Tampilan (ukuran, jenis, warna). Dipakai layout web dan aplikasi HP. --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link
    href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Lato:wght@300;400;700&family=Montserrat:wght@300;400;500;600;700&family=Open+Sans:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap"
    rel="stylesheet">
<script>
    // Apply font size settings
    (function() {
        const fontSize = window.localStorage.getItem('fontSize') || 'normal';
        const sizes = {
            small: '14px',
            normal: '16px',
            large: '18px'
        };
        document.documentElement.style.fontSize = sizes[fontSize] || sizes.normal;
    })();

    // Apply font family settings
    (function() {
        const fontFamily = window.localStorage.getItem('fontFamily') || 'sfpro';
        const fonts = {
            sfpro: '"SF Pro Display", -apple-system, BlinkMacSystemFont, sans-serif',
            inter: '"Inter", sans-serif',
            roboto: '"Roboto", sans-serif',
            opensans: '"Open Sans", sans-serif',
            lato: '"Lato", sans-serif',
            montserrat: '"Montserrat", sans-serif'
        };
        document.documentElement.style.fontFamily = fonts[fontFamily] || fonts.sfpro;
    })();

    // Apply font color settings
    (function() {
        const fontColor = window.localStorage.getItem('fontColor') || 'default';
        const colors = {
            default: {
                light: '#1f2937',
                dark: '#e5e7eb'
            },
            slate: {
                light: '#334155',
                dark: '#cbd5e1'
            },
            zinc: {
                light: '#3f3f46',
                dark: '#d4d4d8'
            },
            neutral: {
                light: '#404040',
                dark: '#d4d4d4'
            },
            stone: {
                light: '#44403c',
                dark: '#d6d3d1'
            },
            warmgray: {
                light: '#78350f',
                dark: '#fcd34d'
            },
            coolgray: {
                light: '#1e3a5f',
                dark: '#93c5fd'
            }
        };
        const selectedColor = colors[fontColor] || colors.default;
        document.documentElement.style.setProperty('--font-color-light', selectedColor.light);
        document.documentElement.style.setProperty('--font-color-dark', selectedColor.dark);
        // Warna pilihan menimpa warna teks bawaan layout (lihat app.css)
        if (fontColor !== 'default') {
            document.documentElement.dataset.warnaFont = fontColor;
        }
    })();
</script>
