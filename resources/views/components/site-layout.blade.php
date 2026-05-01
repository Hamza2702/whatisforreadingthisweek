<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookworms</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400;600&family=Fredoka+One&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="/images/favicon.ico">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body { background-color: #fdfbf7; }
        .font-system { font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; }
    </style>
</head>
<body class="font-sans text-[#755f54] antialiased min-h-screen flex flex-col overflow-x-hidden">
    <!-- NAVBAR -->
    <nav class="w-full py-6 px-6 md:px-12 lg:px-16 xl:px-24 flex justify-between items-center sticky top-0 z-50 bg-[#fdfbf7]">
        <a href="/" class="flex items-center gap-3">
            <img src="{{ asset('images/logo.png') }}" alt="Bookworms" class="h-10 md:h-12 drop-shadow-sm" draggable="false">
            <span class="text-2xl md:text-3xl font-display font-black tracking-tight text-[#e87a90]">Bookworms</span>
        </a>
        <!-- start reading -->
        <div class="hidden md:flex items-center gap-4 md:gap-6">
            @if (Auth::check() && (Auth::user()->role === 'teacher' || Auth::user()->role === 'headteacher'))
                <a href="{{ route('teacher.index') }}" class="bg-[#755f54] hover:bg-[#5c4a41] text-white text-sm md:text-base font-bold px-5 md:px-7 py-2.5 rounded-full shadow-sm transition-all transform hover:scale-105">
                    Start Reading
                </a>
            @else
                <a href="/login" class="bg-[#755f54] hover:bg-[#5c4a41] text-white text-sm md:text-base font-bold px-5 md:px-7 py-2.5 rounded-full shadow-sm transition-all transform hover:scale-105">
                    Start Reading
                </a>
            @endif
        </div>

        <!-- mobile hamburger button -->
        <button id="mobile-nav-button" class="md:hidden p-2 focus:outline-none z-50 relative">
            <div class="w-6 h-0.5 bg-[#755f54] mb-1.5 transition-transform duration-300 origin-center" id="bar1"></div>
            <div class="w-6 h-0.5 bg-[#755f54] mb-1.5 transition-opacity duration-300" id="bar2"></div>
            <div class="w-6 h-0.5 bg-[#755f54] transition-transform duration-300 origin-center" id="bar3"></div>
        </button>
    </nav>

    <!-- MOBILE -->
    <div id="mobile-menu-panel" class="md:hidden fixed top-0 right-0 w-64 h-full bg-[#fdfbf7] shadow-2xl z-40 transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col border-l border-[#755f5420] pt-24">
        <div class="flex flex-col p-6 space-y-6 items-end">
            <a href="/login" class="text-xl text-[#755f54] font-bold hover:text-orange-500">Login</a>
            <a href="/login" class="text-xl bg-[#755f54] text-white font-bold px-6 py-2 rounded-full text-center w-full">Start Reading</a>
        </div>
    </div>

    <!-- Page content -->
    <main class="flex-grow w-full">
        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="py-10 text-center border-t border-[#755f5420] mt-auto bg-white w-full">
        <p class="text-xs font-bold text-[#755f54]/60 tracking-[0.2em]">
            &copy; {{ date('Y') }} BOOKWORMS
        </p>
    </footer>

    <!-- MOBILE HAMBURGER SCRIPT -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const mobileBtn = document.getElementById('mobile-nav-button');
            const mobileMenu = document.getElementById('mobile-menu-panel');
            const bar1 = document.getElementById('bar1');
            const bar2 = document.getElementById('bar2');
            const bar3 = document.getElementById('bar3');

            if(mobileBtn) {
                mobileBtn.addEventListener('click', () => {
                    mobileMenu.classList.toggle('translate-x-full');
                    bar1.classList.toggle('translate-y-2');
                    bar1.classList.toggle('rotate-45');
                    bar2.classList.toggle('opacity-0');
                    bar3.classList.toggle('-translate-y-2');
                    bar3.classList.toggle('-rotate-45');
                });
            }
        });
    </script>
</body>
</html>