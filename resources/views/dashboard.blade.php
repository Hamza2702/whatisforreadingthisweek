<x-layout title="Dashboard">
 <div class="flex flex-col justify-center items-center">
    <!-- Welcome -->
    <div class="w-full max-w-4xl rounded-lg p-4 space-y-4 border-2 border-primary">
      <!-- Header -->
      <div class="flex items-center space-x-4">
        <!-- PFP -->
        <div class="w-12 h-12 bg-orange-200 rounded-full flex items-center justify-center text-xs font-semibold text-gray-600">
          <img src="{{ Auth::user()->pfp }}" alt="Profile Picture" class="w-12 h-12 rounded-full">
        </div>
        <!-- Name -->
        <div>
          <h2 class="text-2xl text-primary font-bold">Welcome, {{ Auth::user()->name }}!</h2><span class="text-md text-primary font-medium">{{ '@' . Auth::user()->username }}</span>
        </div>
      </div>

      <!-- Info w/ lvl and fav genre, need more. -->
      <div class="flex items-center text-center space-x-2 text-primary">
        <span class="px-3 py-3 rounded-md text-sm font-semibold bg-level-{{ Auth::user()->level }} text-level-{{ Auth::user()->level }}">
          Level {{ Auth::user()->level }}
        </span>
        <span class="px-3 py-1 rounded-md text-sm font-semibold flex flex-col leading-tight bg-primary">
          <span class="text-background">Favourite genre:</span>
          <span class="text-background font-bold">Romance</span>
        </span>
      </div>

      <!-- Streak -->
      <div>
        <div class="flex items-center justify-between text-md text-black font-medium mb-1">
          <span>Current streak: 10</span>
        </div>
        <div class="flex items-center space-x-1">
          <div class="w-1/5 h-3 bg-orange-400 rounded-full"></div>
          <div class="w-1/5 h-3 bg-orange-400 rounded-full"></div>
          <div class="w-1/5 h-3 bg-orange-400 rounded-full"></div>
          <div class="w-1/5 h-3 bg-orange-300 rounded-full"></div>
          <div class="w-1/5 h-3 bg-orange-300 rounded-full"></div>
          <div class="w-1/5 h-3 bg-orange-300 rounded-full"></div>
          <div class="w-1/5 h-3 bg-orange-300 rounded-full"></div>
        </div>
      </div>
    </div>

    <!-- Current book -->
    <div class="w-full font-sans text-primary max-w-4xl rounded-lg mt-4 py-4 space-y-4">
      <!-- This week's book -->
      <div class=" font-semibold px-4">
        <h1 class="font-script text-2xl underline underline-offset-4 decoration-2">This Week's Book</h1>
        <!-- Book title and author -->
        <div class="font-bold text-purple">
          South of the Border, West of the Sun by <span class="font-medium">Haruki Murakami</span>
        </div>
      </div>
      <!-- Blurb/summary -->
      <div class=" font-semibold px-4">
        <p class="text-sm">
          Hajime, a successful jazz bar owner in Tokyo, leads a seemingly perfect life with his wife and children.
          However, his past resurfaces when he reunites with Shimamoto, his childhood friend and first love.
          As their paths cross again, Hajime finds himself torn between the stability of his current life and the allure of a rekindled romance.
          "South of the Border, West of the Sun" explores themes of memory, longing, and the choices that shape our lives.
      </div>
      <!-- Review button -->
      <div class="px-4">
        <a href="" class="bg-primary text-background font-semibold px-4 py-2 rounded-md hover:bg-secondary hover:text-white ">
          Write a Review
        </a>
      </div>
    </div>

      
    <!-- Reading progress -->
    <div class="w-full max-w-4xl bg-primary rounded-lg mt-4 py-4 space-y-4">
      <!-- Header -->
      <div class="font-semibold px-4">
        <span>
          Your Reading Progress
        </span>
      </div>
      <!-- Statistics -->
      <div class="flex flex-row justify-around px-4 text-center">
        <div>
          <div class="text-2xl font-bold">15</div>
          <div class="text-sm font-medium">Books read</div>
        </div>
        <div>
          <div class="text-2xl font-bold">3.5</div>
          <div class="text-sm font-medium">Average rating</div>
        </div>
        <div>
          <div class="text-2xl font-bold">6</div>
          <div class="text-sm font-medium">Genres explored</div>
        </div>
        <div>
          <div class="text-2xl font-bold">7</div>
          <div class="text-sm font-medium">Phonics topics covered</div>
        </div>
    </div>
  </div>
</x-layout>
