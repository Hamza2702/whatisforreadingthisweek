<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $title }}</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
  @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="z-50 bg-blue-200 flex flex-col min-h-screen">

  <!-- Navigation Bar -->
  <nav class="z-50 justify-between items-center bg-white px-7 desktop-nav hidden md:flex border-b-4 border-blue-300 p-1">
    <div>
      <a href="/"><img src="/images/Logo.jpeg" alt="Logo" class="bookwormLogo max-w-20"></a>
    </div>
    <!-- Navigation bar -->
    <div class="flex justify-between space-x-3">
      <x-nav-link href='/'> Dashboard </x-nav-link>
      <x-nav-link href='/explore'> Explore </x-nav-link>
      <x-nav-link href='/assignments'> Assignments </x-nav-link>
      <x-nav-link href='/progress'> Progress </x-nav-link>
      <x-nav-link href='/leaderboard'> Leaderboard </x-nav-link>
    </div>

    <!-- Navigation bar guest access -->
    <div class=" flex items-center space-x-3">
      @guest
      <x-nav-link href='/login'> Login </x-nav-link>
      @endguest

      <!-- Navigation bar logged in user -->
      @auth
      <div class="group flex flex-col justify-start bg-white">
        <!-- User Image (CHANGE PATH) -->
        <x-nav-link href='/account/user'>
          <a href='/account/user'><img src="{{ Auth::user()->pfp ?? '/images/Placeholder.jpeg' }}" alt="Pfp icon" class="h-6 md:h-8 hover:opacity-75 rounded-full"></a>
        </x-nav-link>
        <div class="group-hover:flex fixed top-[60px] right-[40px] flex-col z-10 p-4 space-y-2 hidden bg-white">
          <x-nav-link href="/account/user">Manage</x-nav-link>
          <form class=" m-0" method="POST" action="/logout">
            @csrf
            <button class="hover:text-primary">Log Out</button>
          </form>
        </div>
      </div>
      @endauth
      
    </div>
    <div class="worm hidden">
        <img src="/images/home/wormMovement1.png" alt="Worm" class="h-8">
    </div>
  </nav>  
  <main class="flex-grow p-4 text-white">
      {{ $slot }}
  </main>

  <footer class="mt-auto bg-blue-300 w-full p-6 text-white">
    <div class="flex flex-wrap justify-center space-y-6 md:space-y-0 md:flex-nowrap">
      <div class="w-full md:w-1/3 text-center flex flex-col items-center">
        <ul class="space-y-1">
          <div class="flex justify-between space-x-3">
      </div>
          <li class="font-semibold">Pages</li>
          <li><a href="/" class="hover:underline">Dashboard</a></li>
          <li><a href="/explore" class="hover:underline">Explore</a></li>
          <li><a href="/assignments" class="hover:underline">Assignments</a></li>
          <li><a href="/progress" class="hover:underline">Progress</a></li>
          <li><a href="/leaderboard" class="hover:underline">Leaderboard</a></li>
          @guest
          <li><a href="/login" class="hover:underline">Login</a></li>
          <li><a href="/register" class="hover:underline">Register</a></li>
          @endguest
          @auth
          <li><a href="/account/user" class="hover:underline">Manage Account</a></li>
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
</body>
</html>