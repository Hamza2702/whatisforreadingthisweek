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
  <nav class=" justify-between items-center px-7 desktop-nav hidden lg:flex border-b-2 border-orange-900 p-1 py-4 bg-background">
    <div class="flex items-center gap-3">
      <a href="/" class="flex items-center gap-3">
        <img src="{{ asset('images/logo.png') }}" alt="Bookworms" class="h-10 md:h-12 drop-shadow-sm" draggable="false">
        <span class="text-2xl md:text-3xl font-display font-black tracking-tight text-[#e87a90]">Bookworms</span>
      </a>
    </div>

    <div class="flex items-center space-x-5">
      @if (Auth::check() && Auth::user()->isAdmin())
        <x-nav-link href='/admin/index' class="px-2 text-red-700 font-black"> Dashboard </x-nav-link>
      @elseif (Auth::check() && (Auth::user()->role === 'teacher' || Auth::user()->role === 'headteacher'))
        <x-nav-link href="{{ route('teacher.index') }}" class="px-2 text-red-700 font-black"> Dashboard </x-nav-link>
      @else 
        <x-nav-link href='/dashboard'> Dashboard </x-nav-link>
      @endif
      <x-nav-link href='/explore'> Explore </x-nav-link>
      @if (Auth::check() && (Auth::user()->role === 'teacher' || Auth::user()->role === 'headteacher'))
        
      @else
        <x-nav-link href='/assignments'> Assignments </x-nav-link>
      @endif
      @if (Auth::check() && (Auth::user()->role === 'teacher' || Auth::user()->role === 'headteacher'))
        
      @else
        <x-nav-link href='/progress'> Progress </x-nav-link>
      @endif
      @if (Auth::check() && (Auth::user()->role === 'teacher' || Auth::user()->role === 'headteacher'))
        
      @else
        <x-nav-link href='/leaderboard'> Leaderboard </x-nav-link>
      @endif
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
      <a href="/" class="flex items-center gap-3">
        <img src="{{ asset('images/logo.png') }}" alt="Bookworms" class="h-10 md:h-12 drop-shadow-sm" draggable="false">
        <span class="text-2xl md:text-3xl font-display font-black tracking-tight text-[#e87a90]">Bookworms</span>
      </a>
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
<footer class="bg-white w-full pt-16 pb-8 mt-auto">
  <div class="px-6 md:px-12 flex flex-col md:flex-row justify-center md:justify-around items-center md:items-start gap-12 max-w-4xl mx-auto">
    
    <!-- Pages -->
    <div class="w-full md:w-1/2 flex flex-col items-center">
      <ul class="space-y-3 text-center">
        <li class="font-system text-[#e87a90] font-bold tracking-[0.2em] text-xs mb-4">PAGES</li>
        
        <!-- Dashboard -->
        @if (Auth::check() && Auth::user()->isAdmin())
          <li><a href='/admin/index' class="text-[#755f54] hover:text-[#e87a90] font-medium transition-colors">Dashboard</a></li>
        @elseif (Auth::check() && (Auth::user()->role === 'teacher' || Auth::user()->role === 'headteacher'))
          <li><a href="{{ route('teacher.index') }}" class="text-[#755f54] hover:text-[#e87a90] font-medium transition-colors">Dashboard</a></li>
        @else 
          <li><a href='/dashboard' class="text-[#755f54] hover:text-[#e87a90] font-medium transition-colors">Dashboard</a></li>
        @endif
        
        <!-- Main-->
        <li><a href="/explore" class="text-[#755f54] hover:text-[#e87a90] font-medium transition-colors">Explore</a></li>
        @if (Auth::check() && (Auth::user()->role === 'teacher' || Auth::user()->role === 'headteacher'))
        
        @else
          <li><a href="/assignments" class="text-[#755f54] hover:text-[#e87a90] font-medium transition-colors">Assignments</a></li>
          <li><a href="/progress" class="text-[#755f54] hover:text-[#e87a90] font-medium transition-colors">Progress</a></li>
          <li><a href="/leaderboard" class="text-[#755f54] hover:text-[#e87a90] font-medium transition-colors">Leaderboard</a></li>
        @endif
        
        <!-- Guest-->
        @guest
        <li><a href="/login" class="text-[#755f54] hover:text-[#e87a90] font-medium transition-colors">Login</a></li>
        @endguest
        
        <!-- Auth -->
        @auth
        <li><a href='{{ route("user.show", ["id" => Auth::id()]) }}' class="text-[#755f54] hover:text-[#e87a90] font-medium transition-colors">Manage Account</a></li>
        <li>
          <form method="POST" action="/logout" class="m-0 p-0 inline-block">
            @csrf
            <button type="submit" class="text-[#755f54] hover:text-[#e87a90] font-medium transition-colors bg-transparent border-none p-0 cursor-pointer">Log Out</button>
          </form>          
        </li>
        @endauth
      </ul>
    </div>
    
    <!-- legal and content -->
    <div class="w-full md:w-1/2 flex flex-col items-center">
      <ul class="space-y-3 text-center">
        <li class="font-system text-[#ffb84d] font-bold tracking-[0.2em] text-xs mb-4">LEGAL & SUPPORT</li>
        <li><a href="https://www.gov.uk/data-protection" class="text-[#755f54] hover:text-[#ffb84d] font-medium transition-colors" target="_blank">GDPR / Data Protection</a></li>
        <li><a href="{{ url('/#contact') }}" class="text-[#755f54] hover:text-[#ffb84d] font-medium transition-colors">Contact Us</a></li>
      </ul>
    </div>

  </div>

  <!-- footer stuff -->
  <div class="text-center pt-10 mt-12 border-t border-[#755f5420] w-full">
    <p class="text-xs font-bold text-[#755f54]/60 tracking-[0.2em]">
        &copy; {{ date('Y') }} BOOKWORMS
    </p>
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