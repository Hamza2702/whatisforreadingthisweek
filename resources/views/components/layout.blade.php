<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ $title ?? 'Bookworms' }}</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400;600&family=Fredoka+One&family=Nunito:wght@300;400;600&display=swap" rel="stylesheet">
  <link rel="icon" type="image/x-icon" href="/images/favicon.ico">
  @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="z-50 bg-background flex flex-col min-h-full overflow-x-hidden">

  <!-- Navigation Bar ============== DESKTOP -->
  <nav class="z-50 sticky top-0 justify-between items-center px-7 desktop-nav hidden lg:flex border-b-4 border-orange-900 p-1 bg-background">
    <div class="flex items-center gap-3">
      <a href="/" class="flex-shrink-0">
        <img src="/images/Logo.png" alt="Logo" class="bookwormLogo max-w-20">
      </a>
      <div class="flex flex-col">
        <h1 class="font-display text-pink-400 text-2xl tracking-wider">BOOKWORMS</h1>
      </div>
    </div>

    <div class="flex items-center space-x-5">
      @if (Auth::check() && Auth::user()->isAdmin())
        <x-nav-link href='/admin/index' class="px-2 text-red-700 font-black"> Dashboard </x-nav-link>
      @elseif (Auth::check() && (Auth::user()->role === 'teacher' || Auth::user()->role === 'headteacher'))
        <x-nav-link href="{{ route('teacher.index') }} " class="px-2 text-red-700 font-black"> Dashboard </x-nav-link>
      @else 
        <x-nav-link href='/dashboard'> Dashboard </x-nav-link>
      @endif
      <x-nav-link href='/explore'> Explore </x-nav-link>
      <x-nav-link href='/assignments'> Assignments </x-nav-link>
      <x-nav-link href='/progress'> Progress </x-nav-link>
      <x-nav-link href='/leaderboard'> Leaderboard </x-nav-link>
      <div class="flex items-center space-x-3">
        @guest
        <x-nav-link href="{{ route('login') }}"> Login </x-nav-link>
        @endguest
        
        @auth
        <div class="group relative flex flex-col justify-start bg-transparent">
          <x-nav-link href='{{ route("user.show", ["id" => Auth::id()]) }}' class="p-0 m-0">
            <a href='{{ route("user.show", ["id" => Auth::id()]) }}'><img src="{{ Auth::user()->pfp ?? '/images/Placeholder.jpeg' }}" alt="Pfp icon" class="h-6 lg:h-8 hover:opacity-75 rounded-full border border-gray-200 bg-white"></a>
          </x-nav-link>
          <div class="group-hover:flex absolute top-[28px] right-0 mt-1 w-max rounded-lg shadow-lg flex-col z-10 py-2 px-4 space-y-2 hidden bg-white border border-gray-100">
            <a href='{{ route("user.show", ["id" => Auth::id()]) }}' class="hover:text-primary w-full text-right text-gray-700">Manage</a>
            <form class="m-0" method="POST" action="/logout">
              @csrf
              <button type="submit" class="hover:text-primary w-full text-right text-gray-700">Log Out</button>
            </form>
          </div>
        </div>
        @endauth
      </div>
    </div>
    <div class="worm hidden">
        <img src="/images/home/wormMovement1.png" alt="Worm" class="h-8">
    </div>
  </nav>

  <!-- Navigation Bar ============== MOBILE -->
  <nav class="lg:hidden z-50 sticky top-0 flex justify-between items-center px-4 h-[76px] border-b-4 border-orange-900 bg-background">
    <div class="flex items-center gap-2">
      <a href="/" class="flex-shrink-0">
        <img src="/images/Logo.png" alt="Logo" class="bookwormLogo w-12 h-auto">
      </a>
      <h1 class="font-display text-pink-400 text-lg tracking-wider font-bold">BOOKWORMS</h1>
    </div>

    <!-- Hamburger button -->
    <button id="mobile-nav-button" class="p-2 focus:outline-none z-50">
      <div class="w-6 h-0.5 bg-primary mb-1.5 transition-transform duration-300 origin-center" id="bar1"></div>
      <div class="w-6 h-0.5 bg-primary mb-1.5 transition-opacity duration-300" id="bar2"></div>
      <div class="w-6 h-0.5 bg-primary transition-transform duration-300 origin-center" id="bar3"></div>
    </button>
  </nav>

  <!-- SIDE PANEL -->
  <div id="mobile-menu-panel" class="lg:hidden fixed top-[76px] right-0 w-64 h-[calc(100vh-76px)] bg-background shadow-2xl z-40 transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col border-l border-gray-200">
    <!-- links -->
    <div class="flex flex-col p-6 space-y-5 items-end flex-grow overflow-y-auto">
      @if (Auth::check() && Auth::user()->isAdmin())
        <a href='/admin/index' class="text-red-700 font-black text-lg"> Dashboard </a>
      @elseif (Auth::check() && (Auth::user()->role === 'teacher' || Auth::user()->role === 'headteacher'))
        <a href="{{ route('teacher.index') }}" class="text-red-700 font-black text-lg"> Dashboard </a>
      @else 
        <a href='/dashboard' class="text-lg text-gray-800 font-medium hover:text-primary"> Dashboard </a>
      @endif
      
      <a href='/explore' class="text-lg text-gray-800 font-medium hover:text-primary"> Explore </a>
      <a href='/assignments' class="text-lg text-gray-800 font-medium hover:text-primary"> Assignments </a>
      <a href='/progress' class="text-lg text-gray-800 font-medium hover:text-primary"> Progress </a>
      <a href='/leaderboard' class="text-lg text-gray-800 font-medium hover:text-primary"> Leaderboard </a>
      
      @guest
      <div class="mt-4 w-full border-t border-gray-200 pt-4 text-right">
        <a href="{{ route('login') }}" class="text-lg text-primary font-bold"> Login </a>
      </div>
      @endguest
    </div>

    <!-- Bottom section -->
    @auth
    <div class="w-full flex flex-col border-t border-secondary p-5 mt-auto">
      <!-- user and pfp -->
      <div class="flex justify-end items-center mb-4">
        <span class="mr-3 font-semibold text-gray-800">
          {{ '@' . (Auth::user()->username ?? Auth::user()->name) }}
        </span>
        <a href='{{ route("user.show", ["id" => Auth::id()]) }}'>
          <img src="{{ Auth::user()->pfp ?? '/images/Placeholder.jpeg' }}" alt="User pfp" class="h-10 w-10 object-cover rounded-full border-2 border-primary bg-white">
        </a>
      </div>
      
      <!-- manage/logout -->
      <div class="flex flex-col space-y-3 items-end w-full border-t border-secondary pt-4">
        <a href='{{ route("user.show", ["id" => Auth::id()]) }}' class="text-gray-600 hover:text-primary font-medium">Manage Account</a>
        <form class="m-0 w-full text-right" method="POST" action="/logout">
          @csrf
          <button type="submit" class="text-gray-600 hover:text-primary font-medium w-full text-right">Log Out</button>
        </form>
      </div>
    </div>
    @endauth
  </div>


  <main class="p-4 text-white flex-1 flex flex-col">
      {{ $slot }}
  </main>

  <!-- FOOTER -->
  <footer class="bg-primary w-full p-6 text-white">
    <div class="flex flex-wrap justify-center space-y-6 md:space-y-0 md:flex-nowrap">
      <div class="w-full md:w-1/3 text-center flex flex-col items-center">
        <ul class="space-y-1">
          <div class="flex justify-between space-x-3"></div>
          <li class="font-semibold">Pages</li>
          <li>
            @if (Auth::check() && Auth::user()->isAdmin())
              <a href='/admin/index' class="hover:underline"> Dashboard </a>
            @elseif (Auth::check() && Auth::user()->role === 'teacher' || Auth::check() && Auth::user()->role === 'headteacher')
              <a href="{{ route('teacher.index') }} " class="hover:underline"> Dashboard </a>
            @else 
              <a href='/dashboard' class="hover:underline text-white"> Dashboard </a>
            @endif
          </li>
          <li><a href="/explore" class="hover:underline text-white">Explore</a></li>
          <li><a href="/assignments" class="hover:underline">Assignments</a></li>
          <li><a href="/progress" class="hover:underline">Progress</a></li>
          <li><a href="/leaderboard" class="hover:underline">Leaderboard</a></li>
          @guest
          <li><a href="/login" class="hover:underline">Login</a></li>
          <li><a href="/register" class="hover:underline">Register</a></li>
          @endguest
          @auth
          <li><a href='{{ route("user.show", ["id" => Auth::id()]) }}' class="hover:underline">Manage Account</a></li>
          <form method="POST" action="/logout">
            @csrf
            <li><button class="hover:underline">Log Out</button></li>
          </form>          
          @endauth
        </ul>
      </div>
      
      <div class="w-full md:w-1/3 text-center flex flex-col items-center">
        <ul class="space-y-1">
          <li class="font-semibold">Legal</li>
          <li><a href="/tmc" class="hover:underline">Terms and Conditions</a></li>
          <li><a href="/pnc" class="hover:underline">Privacy and Cookies</a></li>
          <li><a href="https://www.gov.uk/data-protection" class="hover:underline">GDPA</a></li>
          <li><a href="https://www.ifrs.org/groups/international-sustainability-standards-board/" class="hover:underline">ISSB</a></li>
          <li><a href="https://www.modernslavery.gov.uk/start" class="hover:underline">Modern Slavery Report</a></li>
          <li><a href="https://www.fca.org.uk/" class="hover:underline">UK FCA</a></li>
          <li><a href="/contact" class="hover:underline">Contact Us</a></li>
        </ul>
      </div>
      
      <div class="w-full md:w-1/3 text-center flex flex-col items-center space-y-4">
        <div class="flex flex-col items-center">
          <h3 class="text-base md:text-lg font-semibold">Subscribe to our Newsletter</h3>
          <button class="px-8 py-1 mt-3 bg-text text-white rounded-lg hover:opacity-75">Join</button>
        </div>
      </div>
    </div>

    <div class="text-center py-4 border-t border-gray-200 mt-4">
      <p class="text-xs md:text-base">&copy; 2025 What is for reading this week? All rights reserved.</p>
    </div>
  </footer>

  <script src="{{ asset('js/bookwormLogo.js') }}"></script>
  
  <!-- MOBILE HAMBURGER -->
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const mobileBtn = document.getElementById('mobile-nav-button');
      const mobileMenu = document.getElementById('mobile-menu-panel');
      const bar1 = document.getElementById('bar1');
      const bar2 = document.getElementById('bar2');
      const bar3 = document.getElementById('bar3');

      mobileBtn.addEventListener('click', () => {
        // slide menu in and out
        mobileMenu.classList.toggle('translate-x-full');
        
        // hamburger animation
        bar1.classList.toggle('translate-y-2');
        bar1.classList.toggle('rotate-45');
        bar2.classList.toggle('opacity-0');
        bar3.classList.toggle('-translate-y-2');
        bar3.classList.toggle('-rotate-45');
      });
    });
  </script>
</body>
</html>