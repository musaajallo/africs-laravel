<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#093a20">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=jost:400,500,600,600i,700,900&display=swap" rel="stylesheet" />

        <script type="application/ld+json">
            @verbatim
            {
                "@context": "https://schema.org",
                "@type": "Organization",
                "name": "Africs",
                "url": "https://africsinc.com",
                "description": "Africs adds value to every engagement through business, technology, and design solutions."
            }
            @endverbatim
        </script>

        <!-- Apply the saved panel theme before paint to avoid a flash -->
        <script>
            try {
                if (localStorage.getItem('africs.panelTheme') === 'dark') {
                    document.documentElement.dataset.panelTheme = 'dark';
                }
            } catch (e) {}
        </script>

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body>
        @inertia
    </body>
</html>
