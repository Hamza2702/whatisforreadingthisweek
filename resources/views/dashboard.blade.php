<x-layout title="Dashboard">
  <div class="w-full px-6 md:px-10 lg:px-16 py-8 flex-1 flex flex-col justify-center">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-10">
      <!-- Welcome profile -->
      <div class="lg:col-span-8 bg-[#755f540a] border border-[#755f5420] rounded-3xl p-6 md:p-10 flex flex-col justify-between shadow-sm relative overflow-hidden">
        <div class="flex flex-col md:flex-row gap-6 md:gap-8 items-start md:items-center w-full relative z-10">
          <!-- PFP -->
          <div class="relative flex-shrink-0">
            <img src="{{ Auth::user()->pfp ?? '/images/Placeholder.jpeg' }}" alt="Profile Picture" class="w-24 h-24 md:w-28 md:h-28 rounded-full ring-4 ring-white shadow-md object-cover">
          </div>

          <!-- Student -->
          <div class="flex-1 space-y-2 text-primary text-center md:text-left">
            <!-- Name 0 = first, 1 = last-->
            <h2 class="text-3xl md:text-4xl font-display tracking-tight">Welcome back, {{ explode(' ', Auth::user()->name)[0] }}!</h2>
            <!-- Username and school -->
            <div class="flex flex-wrap items-center justify-center md:justify-start gap-3 text-base">
              <span class="opacity-70 font-semibold">{{ '@' . Auth::user()->username }}</span>
              <span class="opacity-70 font-medium">|</span>
              <span class="opacity-70 font-medium">
                {{ Auth::user()->school?->name ?? 'No School Assigned' }}
              </span>
            </div>
            
            <!-- Info w/ lvl and fav genre, need more. -->
            <div class="flex items-center justify-center md:justify-start gap-2 pt-2">
              <!-- Genre -->
              <span class="px-4 py-1.5 rounded-full text-sm font-bold bg-primary text-background flex items-center gap-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd" />
                </svg>
                Romance
              </span>
              <!-- Reading Level -->
              <span class="px-4 py-1.5 rounded-full text-sm font-bold bg-level-{{ Auth::user()->student->level ?? '0' }} text-level-{{ Auth::user()->student->level ?? '0' }} flex items-center gap-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                </svg>
                Level {{ Auth::user()->student->level ?? '0' }}
              </span>
            </div>
          </div>
        </div>

        <!-- Streak -->
        <div class="mt-10 bg-white/60 rounded-2xl p-5 md:px-8 shadow-sm border border-white/50 relative z-10">
          <div class="flex items-center justify-between mb-4">
            <span class="text-lg font-bold text-primary">READING STREAK</span>
            <span class="text-sm font-black text-orange-600 flex items-center gap-1">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                <path fill-rule="evenodd" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z" />
                <path fill-rule="evenodd" d="M12 18a3.75 3.75 0 0 0 .495-7.468 5.99 5.99 0 0 0-1.925 3.547 5.975 5.975 0 0 1-2.133-1.001A3.75 3.75 0 0 0 12 18Z" />
              </svg>

              10 Days
            </span>
          </div>
          <!-- Days -->
          <div class="flex items-center justify-between gap-2 md:gap-4">
            <!-- Filled days -->
            <div class="h-10 md:h-12 flex-1 bg-orange-400 rounded-xl shadow-inner"></div>
            <div class="h-10 md:h-12 flex-1 bg-orange-400 rounded-xl shadow-inner"></div>
            <div class="h-10 md:h-12 flex-1 bg-orange-400 rounded-xl shadow-inner"></div>
            <div class="h-10 md:h-12 flex-1 bg-orange-400 rounded-xl shadow-inner"></div>
            <!-- Empty days -->
            <div class="h-10 md:h-12 flex-1 bg-orange-100 rounded-xl"></div>
            <div class="h-10 md:h-12 flex-1 bg-orange-100 rounded-xl"></div>
            <div class="h-10 md:h-12 flex-1 bg-orange-100 rounded-xl"></div>
          </div>
        </div>
      </div>

      <!-- Reading progress statistics -->
      <div class="lg:col-span-4 grid grid-cols-2 gap-4 lg:gap-6">
        <!-- Books read-->
        <div class="bg-primary rounded-3xl p-6 flex flex-col justify-center items-center text-center shadow-md ">
          <span class="text-5xl font-black text-background mb-2">15</span>
          <span class="text-sm font-bold text-background/80 tracking-widest">BOOKS READ</span>
        </div>
        <!-- Average rating -->
        <div class="bg-[#755f5415] border border-primary/10 rounded-3xl p-6 flex flex-col justify-center items-center text-center shadow-sm">
          <span class="text-5xl font-black text-primary mb-2">3.5</span>
          <span class="text-sm font-bold text-primary/70 tracking-widest">AVERAGE RATING</span>
        </div>
        <!-- Genres explored -->
        <div class="bg-[#755f5415] border border-primary/10 rounded-3xl p-6 flex flex-col justify-center items-center text-center shadow-sm">
          <span class="text-5xl font-black text-primary mb-2">6</span>
          <span class="text-sm font-bold text-primary/70 tracking-widest">GENRES EXPLORED</span>
        </div>
        <!-- Phonics mastered -->
        <div class="bg-[#755f5415] border border-primary/10 rounded-3xl p-6 flex flex-col justify-center items-center text-center shadow-sm">
          <span class="text-5xl font-black text-primary mb-2">7</span>
          <span class="text-sm font-bold text-primary/70 tracking-widest">PHONICS MASTERED</span>
        </div>
      </div>

      <!-- Current book -->
      <div class="lg:col-span-12 bg-white rounded-3xl p-6 md:p-10 shadow-sm border border-[#755f5420] flex flex-col md:flex-row gap-8 md:gap-12 items-center">
        
        <!-- Book cover placeholder -->
        <div class="w-40 h-56 md:w-48 md:h-72 bg-orange-100 rounded-2xl flex-shrink-0 flex items-center justify-center  ring-orange-50">
          <span class="text-secondary text-3xl font-bold">Cover</span>
        </div>

        <div class="flex-1 flex flex-col justify-center text-center md:text-left space-y-6">
          <div>
            <span class="inline-block px-4 py-1.5 bg-primary text-background rounded-full text-sm font-bold tracking-wider mb-4">
              CURRENTLY READING
            </span>
            <h1 class=" text-4xl md:text-5xl text-primary font-bold tracking-tight">
              South of the Border, West of the Sun
            </h1>
            <p class="text-xl text-primary/70 font-medium mt-2">
              by <span class="text-orange-500 font-bold">Haruki Murakami</span>
            </p>
          </div>
          <p class="text-primary/80 text-base md:text-lg leading-relaxed ">
            Hajime, a successful jazz bar owner in Tokyo, leads a seemingly perfect life with his wife and children.
            However, his past resurfaces when he reunites with Shimamoto, his childhood friend and first love.
            As their paths cross again, Hajime finds himself torn between the stability of his current life and the allure of a rekindled romance.
          </p>
          
          <div class="pt-4">
            <a href="#" class="inline-flex items-center justify-center px-8 py-4 text-lg bg-primary text-background font-bold rounded-xl shadow-md hover:bg-orange-900 hover:shadow-lg transition-all duration-200 focus:ring-4 focus:ring-primary/30">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 mr-2">
                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
              </svg>
              Write a Review
            </a>
          </div>
        </div>
      </div>

    </div>
  </div>
</x-layout>