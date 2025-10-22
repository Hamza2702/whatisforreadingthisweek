<x-layout title="Home">
 <div class="flex justify-center items-center">
    <!-- Welcome -->
    <div class="w-full max-w-4xl bg-blue-300 rounded-2xl p-4 space-y-4">
      <!-- Header -->
      <div class="flex items-center space-x-4">
        <!-- PFP -->
        <div class="w-12 h-12 bg-orange-200 rounded-full flex items-center justify-center text-xs font-semibold text-gray-600">
          <img src="/images/pfp/dog.png" alt="Profile Picture" class="w-12 h-12 rounded-full">
        </div>
        <!-- Name -->
        <div>
          <h2 class="text-lg font-semibold">Welcome, John Doe!</h2>
        </div>
      </div>

      <!-- Info w/ lvl and fav genre, need more. -->
      <div class="flex items-center text-center space-x-2">
        <span class="px-2 py-1 rounded-md text-sm font-semibold bg-level-20 text-level-20">
          Level 20
        </span>
        <span class="px-2 py-1 rounded-md text-sm text-white font-semibold flex flex-col leading-tight">
        <span>Favourite genre:</span>
        <span class="text-base font-bold">Horror</span>
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
    
  </div>
</x-layout>
