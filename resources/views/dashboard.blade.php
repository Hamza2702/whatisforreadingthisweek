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
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-6 text-orange-500 mb-1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.866 8.21 8.21 0 0 0 3 2.48Z" />
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

      <!-- ========================================= -->
      <!-- ANNOUNCEMENTS SECTION -->
      @php
          $student = Auth::user()->student;
          $announcements = collect();
          $hasHiddenRecent = false;
          
          if ($student && $student->classroom_id) {
              // get ids of announcements the student has hidden
              $hiddenIds = DB::table('hidden_announcements')
                  ->where('student_id', $student->id)
                  ->pluck('announcement_id');

              // query for the student's announcements
              $query = DB::table('announcements')
                  ->join('classrooms', 'announcements.classroom_id', '=', 'classrooms.id')
                  ->join('users', 'classrooms.teacher_id', '=', 'users.id')
                  ->where('announcements.classroom_id', $student->classroom_id)
                  ->where(function($q) use ($student) {
                      $q->whereNull('announcements.student_id')
                        ->orWhere('announcements.student_id', $student->id);
                  })
                  ->select(
                      'announcements.*',
                      'classrooms.name as class_name',
                      'classrooms.year_group',
                      'users.name as teacher_name',
                      'users.pfp as teacher_pfp'
                  );

              // check if there are any hidden announcements fro mthe last 30 days
              $hasHiddenRecent = DB::table('announcements')
                  ->whereIn('id', $hiddenIds)
                  ->where('created_at', '>=', now()->subMonth())
                  ->exists();

              // get visible announcements
              $announcements = $query->whereNotIn('announcements.id', $hiddenIds)
                  ->orderBy('announcements.created_at', 'desc')
                  ->take(15) 
                  ->get();
          }
      @endphp

      <div class="lg:col-span-12 mt-8 mb-4">
        <h2 class="text-2xl md:text-3xl font-display text-primary tracking-tight mb-5">Announcements</h2>
        
        @if($announcements->count() > 0)
            <!-- Scroll wheel announcements -->
            <div class="flex flex-col gap-4 overflow-y-auto max-h-[450px] pr-2 pb-2 bg-[#755f540a] border border-[#755f5420] rounded-3xl p-6 [&::-webkit-scrollbar-track]:w-2 [&::-webkit-scrollbar-track]:bg-transparent [&::-webkit-scrollbar-thumb]:bg-orange-200 [&::-webkit-scrollbar-thumb]:rounded-full">
                
                @foreach($announcements as $announcement)
                <div class="bg-[#fcd9c842] rounded-3xl p-6 shadow-md border border-orange-100 relative flex-shrink-0 flex flex-col gap-4 group transition-colors hover:bg-background">
                    
                    <!-- Hide announcements -->
                    <form action="{{ route('student.announcements.hide', $announcement->id) }}" method="POST" class="absolute top-5 right-5 opacity-0 group-hover:opacity-100 transition-opacity">
                        @csrf
                        <button type="submit" class="text-black p-1 flex">
                            <span>HIDE ANNOUNCEMENT</span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </form>

                    <!-- Class announcement / message  -->
                    <div class="flex items-center pr-8">
                        <span class="px-3 py-1 rounded-full text-[10px] md:text-xs font-bold tracking-wider {{ $announcement->student_id ? 'bg-secondary text-background' : 'bg-orange-200 text-orange-900' }}">
                            {{ $announcement->student_id ? 'MESSAGE' : 'CLASS ANNOUNCEMENT' }}
                        </span>
                        <span class="text-xs text-primary/60 font-medium">
                            {{ \Carbon\Carbon::parse($announcement->created_at)->diffForHumans() }}
                        </span>
                    </div>

                    <!-- Teacher info -->
                    <div class="flex items-center gap-3">
                        <img src="{{ $announcement->teacher_pfp ? asset($announcement->teacher_pfp) : asset('/images/Placeholder.jpeg') }}" 
                             alt="{{ $announcement->teacher_name }}" 
                             class="w-10 h-10 md:w-12 md:h-12 rounded-full object-cover ring-2 ring-white shadow-sm">
                        <div class="flex flex-col">
                          <!-- Name -->
                            <span class="text-sm md:text-base font-bold text-primary leading-tight">
                                {{ $announcement->teacher_name }}
                            </span>
                            <!-- Class -->
                            <span class="text-xs text-primary/70 font-medium mt-0.5">
                                {{ $announcement->class_name ?: ($announcement->year_group == 0 ? 'Reception' : 'Year ' . $announcement->year_group) }}
                            </span>
                        </div>
                    </div>

                    <!-- Message -->
                    <p class="text-primary/80 text-sm md:text-base leading-relaxed font-medium mt-1">
                        {{ $announcement->message }}
                    </p>
                </div>
                @endforeach
                
            </div>
        @else
            <!-- Empty state -->
            <div class="pb-2 bg-[#755f540a] border border-[#755f5420] rounded-3xl p-8 md:p-12 flex flex-col items-center justify-center text-center">
                <h3 class="text-xl font-bold text-primary">No new announcements</h3>
                <p class="text-sm md:text-base text-primary/60 max-w-sm mt-2">Check back later for any messages<br> from your teacher!</p>
                
                <!-- Restore announcement -->
                @if($hasHiddenRecent)
                    <form action="{{ route('student.announcements.restore') }}" method="POST" class="mt-6">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 text-md font-bold text-orange-600 transition-colors group">
                            Restore previous announcements in the last month
                        </button>
                    </form>
                @endif
            </div>
        @endif
      </div>

      <!-- Current book -->
      @php
          $currentBook = $student ? $student->currentBook() : null;
      @endphp

      @if($currentBook)
        <div class="lg:col-span-12 bg-white rounded-3xl p-6 md:p-10 shadow-sm border border-[#755f5420] flex flex-col md:flex-row gap-8 md:gap-12 items-center">
          
          <!-- Book cover -->
          <a href="{{ route('books.show', $currentBook->id) }}" target="_blank" class="w-40 h-56 md:w-48 md:h-72 flex-shrink-0 relative rounded-2xl overflow-hidden shadow-md border border-[#755f5410] bg-[#755f540a] block group transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
              
              @if($currentBook->cover_id && str_starts_with($currentBook->cover_id, 'LOCAL_'))
                  @php $imagePath = str_replace('LOCAL_', '', $currentBook->cover_id); @endphp
                  <img src="{{ asset('storage/' . $imagePath) }}" alt="{{ html_entity_decode($currentBook->title ?? '', ENT_QUOTES) }}" class="absolute inset-0 w-full h-full object-cover">
              
              @elseif($currentBook->cover_id && str_starts_with($currentBook->cover_id, 'PLACEHOLDER_'))
                  @php $bgColor = str_replace('PLACEHOLDER_', '', $currentBook->cover_id); @endphp
                  <div class="absolute inset-0 w-full h-full flex items-center justify-center p-4 text-center" style="background-color: {{ $bgColor }};">
                      <span class="font-black text-white text-sm drop-shadow-md line-clamp-4">{{ html_entity_decode($currentBook->title ?? '', ENT_QUOTES) }}</span>
                  </div>
              
              @elseif($currentBook->cover_id)
                  <img src="https://books.google.com/books/content?id={{ $currentBook->cover_id }}&printsec=frontcover&img=1&zoom=1" alt="{{ html_entity_decode($currentBook->title ?? '', ENT_QUOTES) }}" class="absolute inset-0 w-full h-full object-cover">
              
              @else
                  <div class="absolute inset-0 flex items-center justify-center bg-orange-100">
                      <span class="font-bold text-primary/30 text-xs tracking-widest -rotate-12">NO COVER</span>
                  </div>
              @endif
          </a>

          <div class="flex-1 flex flex-col justify-center text-center md:text-left space-y-6">
            <div>
              <span class="inline-block px-4 py-1.5 bg-primary text-background rounded-full text-sm font-bold tracking-wider mb-4">
                CURRENTLY READING
              </span>
              <h1 class=" text-4xl md:text-5xl text-primary font-bold tracking-tight">
                {{ html_entity_decode($currentBook->title ?? '', ENT_QUOTES) }}
              </h1>
              <p class="text-xl text-primary/70 font-medium mt-2">
                by <span class="text-orange-500 font-bold">{{ html_entity_decode($currentBook->author ?? '', ENT_QUOTES) }}</span>
              </p>
            </div>
            <p class="text-primary/80 text-base md:text-lg leading-relaxed line-clamp-3 md:line-clamp-4">
              {{ $currentBook->description ?? 'No description available for this book.' }}
            </p>
            
            <div class="pt-4 flex flex-col sm:flex-row gap-4">
              <!-- Read Online Button -->
              @if(!str_starts_with($currentBook->ol_key, 'NO_OL_'))
                  <a href="https://archive.org/details/{{ $currentBook->ol_key }}/mode/2up?view=theater" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center px-8 py-4 text-lg bg-green-500 text-white font-bold rounded-xl shadow-md hover:bg-green-600 hover:shadow-lg transition-all duration-200">
                      READ ONLINE
                  </a>
              @endif

              <!-- Write a Review Button -->
              <a href="{{ url('/books/' . $currentBook->id . '/review') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg bg-primary text-background font-bold rounded-xl shadow-md hover:bg-orange-900 hover:shadow-lg transition-all duration-200 focus:ring-4 focus:ring-primary/30">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 mr-2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                </svg>
                Write a Review
              </a>
            </div>
          </div>
        </div>
      @else
        <!-- Empty state when no book is assigned -->
        <div class="lg:col-span-12 bg-white rounded-3xl p-6 md:p-10 shadow-sm border border-[#755f5420] flex flex-col items-center justify-center text-center py-16">
            <h2 class="text-2xl font-black text-primary mb-2">Currently not reading a book</h2>
            <p class="text-primary/60 max-w-md text-sm md:text-base leading-relaxed">
                Explore the library to find a new book to read, or ask your teacher to assign your next book!
            </p>
            <a href="{{ route('explore') }}" class="mt-8 inline-flex items-center justify-center px-8 py-4 text-sm bg-primary text-background font-black tracking-widest rounded-xl shadow-sm hover:bg-orange-900 transition-all hover:-translate-y-0.5">
                EXPLORE LIBRARY
            </a>
        </div>
      @endif

    </div>
  </div>
</x-layout>