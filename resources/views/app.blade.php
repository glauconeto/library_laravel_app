<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title inertia>{{ config('app.name', 'Library') }}</title>

        @routes
        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/app.tsx'])
    </head>
    <body class="font-sans antialiased">
        <script>
            // Detecta e aplica o tema antes do React carregar (evita flash)
            (function() {
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                const savedTheme = localStorage.getItem('theme');
                
                let isDark = false;
                if (savedTheme === 'dark') {
                    isDark = true;
                } else if (savedTheme === 'light') {
                    isDark = false;
                } else {
                    // system ou não definido - segue o sistema
                    isDark = prefersDark;
                }
                
                if (isDark) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            })();
        </script>
        @inertia
    </body>
</html>
