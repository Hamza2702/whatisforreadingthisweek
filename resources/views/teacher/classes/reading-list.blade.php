<x-teacher.layout :classroom="$classroom" :yearGroups="$yearGroups" title="Generate Reading List">
  
  <div class="space-y-6">
    
    <!-- Header -->
    <div class="bg-[#755f540a] border-2 border-[#755f5420] rounded-3xl p-5 sm:p-6 shadow-sm">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
        
        
        <!-- Auto assign all books -->
        <form action="{{ route('teacher.reading.generateAll', $classroom->id) }}" method="POST">
            @csrf
            <button type="submit" class="bg-primary text-background rounded-lg px-4 py-2.5 hover:bg-orange-900 transition-colors flex items-center text-xs font-black tracking-widest gap-2">
              AUTO ASSIGN ALL BOOKS
            </button>
        </form>
      </div>  
    </div>

    <!-- ========================================= -->
    <!-- STUDENT ASSIGNMENT GRID -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-4 auto-rows-fr">
      
      @forelse($students as $s)
        @php
          $cardStyle = $s->is_special 
              ? 'border-2 border-dashed border-green-400 bg-white' 
              : 'border border-[#755f5420] bg-white';
              
          // ORT badges cutified
          $ortBadgeClass = match($s->ort_colour) {
              'Pink' => 'bg-pink-100 text-pink-700 border-pink-300',
              'Red' => 'bg-red-100 text-red-700 border-red-300',
              'Yellow' => 'bg-yellow-100 text-yellow-700 border-yellow-300',
              'Light Blue' => 'bg-blue-100 text-blue-700 border-blue-300',
              'Green' => 'bg-green-100 text-green-700 border-green-300',
              'Orange' => 'bg-orange-100 text-orange-700 border-orange-300',
              'Turquoise' => 'bg-teal-100 text-teal-700 border-teal-300',
              'Purple' => 'bg-purple-100 text-purple-700 border-purple-300',
              'Gold' => 'bg-amber-200 text-amber-800 border-amber-400',
              'White' => 'bg-gray-100 text-gray-700 border-gray-300',
              'Lime', 'Lime+' => 'bg-lime-200 text-lime-800 border-lime-400',
              'Grey' => 'bg-gray-200 text-gray-800 border-gray-400',
              'Dark Blue' => 'bg-blue-800 text-blue-100 border-blue-900',
              'Dark Red' => 'bg-red-800 text-red-100 border-red-900',
              default => 'bg-gray-100 text-gray-600 border-gray-300',
          };
        @endphp

        <!-- Student cards -->
        <div class="{{ $cardStyle }} h-full rounded-3xl flex flex-col shadow-sm hover:shadow-md transition group relative p-4">

          <!-- Header - avatar, name, level -->
          <div class="flex flex-col items-center text-center w-full mb-4 pb-4 border-b border-[#755f5415]">
            <!-- avatar -->
            <div class="relative mb-2 mt-1 flex-shrink-0">
              <img class="h-16 w-16 rounded-full object-cover ring-4 ring-white shadow-sm" src="{{ asset($s->pfp ?? '/images/pfp/cat.png') }}" alt="{{ $s->first_name }}">
            </div>

            <!-- level badge -->
            <span class="text-[10px] font-black px-2 py-0.5 rounded-full border-2 border-white shadow-sm mb-2 {{ $ortBadgeClass }}">
              LVL {{ $s->level }}
            </span>

            <!-- student info -->
            <div class="w-full flex-1 flex flex-col justify-center">
              <h3 class="font-bold text-primary text-sm truncate w-full" title="{{ $s->first_name }} {{ $s->last_name }}">
                {{ $s->first_name }} {{ $s->last_name }}
              </h3>
              <!-- username -->
              <p class="text-[11px] text-primary/60 font-semibold truncate w-full">
                {{ '@' . $s->user->username }}
              </p>
            </div>
          </div>

          <!-- preferences and assign books -->
          <div class="flex-1 flex flex-col justify-between w-full">
            
            <!-- student prefs -->
            <div class="mb-4 w-full">
              <!-- currently reading -->
              <p class="text-[10px] font-bold text-primary/50 mb-1">CURRENTLY READING:</p>
              @if($s->currentBook())
                <div class="bg-green-50 border border-green-200 rounded-lg p-2 flex items-start gap-2">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-4 text-green-600 mt-0.5 shrink-0">
                    <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule="evenodd" />
                  </svg>
                  <div class="min-w-0 flex-1">
                    <p class="text-[11px] font-bold text-green-800 leading-tight truncate" title="{{ $s->currentBook()->title }}">{{ $s->currentBook()->title }}</p>
                    <p class="text-[9px] text-green-600 font-semibold mt-0.5 truncate">Assigned: {{ $s->currentBook()->pivot->created_at->diffForHumans() }}</p>
                  </div>
                </div>
              @else
                <p class="text-[11px] text-red-500 font-bold italic bg-red-50 py-1.5 px-2 rounded-md inline-block border border-red-100">No book assigned</p>
              @endif
            </div>

            <!-- liked genres -->
            <div class="mb-4 w-full">
              <p class="text-[10px] font-bold text-primary/50 mb-1">LIKED GENRES:</p>
              <div class="flex flex-wrap gap-1">
                @forelse($s->preferredGenres ?? [] as $genre)
                  <span class="bg-orange-50 text-primary text-[9px] px-1.5 py-0.5 rounded-md border border-orange-100">{{ $genre->name }}</span>
                @empty
                  <span class="text-[10px] text-primary/40 italic">No genres liked</span>
                @endforelse
              </div>
            </div>

            <!-- manually assign books form -->
            <form action="{{ route('teacher.reading.assignBook', [$classroom->id, $s->id]) }}" method="POST" class="mt-auto flex flex-col gap-2 w-full">
              @csrf
              <label class="text-[10px] font-bold text-primary/50 flex justify-between items-center mt-1">
                <span>ASSIGN BOOK</span>
                <span class="text-[9px] bg-primary/10 px-1.5 rounded">{{ count($s->recommendedBooks ?? []) }} books</span>
              </label>
              
              <!-- book recommendation list -->
              <div class="flex flex-col gap-2 max-h-[260px] overflow-y-auto pr-1 pb-1 custom-scrollbar">
                @forelse($s->recommendedBooks ?? [] as $book)
                  
                  @php
                    $bookBorderColour = match($book->ort_colour) {
                        'Pink' => 'border-pink-400 peer-checked:bg-pink-50 text-pink-700',
                        'Red' => 'border-red-400 peer-checked:bg-red-50 text-red-700',
                        'Yellow' => 'border-yellow-400 peer-checked:bg-yellow-50 text-yellow-700',
                        'Light Blue' => 'border-blue-400 peer-checked:bg-blue-50 text-blue-700',
                        'Green' => 'border-green-400 peer-checked:bg-green-50 text-green-700',
                        'Orange' => 'border-orange-400 peer-checked:bg-orange-50 text-orange-700',
                        'Turquoise' => 'border-teal-400 peer-checked:bg-teal-50 text-teal-700',
                        'Purple' => 'border-purple-400 peer-checked:bg-purple-50 text-purple-700',
                        'Gold' => 'border-amber-400 peer-checked:bg-amber-50 text-amber-800',
                        'White' => 'border-gray-300 peer-checked:bg-gray-50 text-gray-700',
                        'Lime', 'Lime+' => 'border-lime-400 peer-checked:bg-lime-50 text-lime-800',
                        'Grey' => 'border-gray-400 peer-checked:bg-gray-50 text-gray-800',
                        'Dark Blue' => 'border-blue-800 peer-checked:bg-blue-100 text-blue-900',
                        'Dark Red' => 'border-red-800 peer-checked:bg-red-100 text-red-900',
                        default => 'border-[#755f5440] peer-checked:bg-orange-50 text-primary',
                    };
                  @endphp
                  
                  <!-- make the cursor a click cursor -->
                  <label class="cursor-pointer relative group w-full">
                    <input type="radio" name="book_id" value="{{ $book->id }}" class="peer sr-only" required>
                    
                    <!-- i hecking love 'modern' looks -->
                    <div class="border-2 border-dashed {{ $bookBorderColour }} rounded-xl p-2.5 bg-white transition-all group-hover:shadow-sm peer-checked:border-solid peer-checked:ring-1 peer-checked:ring-current text-left flex flex-col gap-1.5 w-full">
                      
                      <!-- title and author -->
                      <div class="w-full">
                        <div class="flex justify-between items-start gap-1 w-full">
                            <h4 class="text-[11px] font-bold text-primary leading-tight line-clamp-2 min-w-0" title="{{ $book->title }}">{{ $book->title }}</h4>
                            <span class="text-[8px] font-bold px-1 py-0.5 rounded whitespace-nowrap bg-gray-100 text-gray-500 shrink-0">{{ $book->ort_colour }}</span>
                        </div>
                        <p class="text-[9px] font-medium text-primary/60 mt-0.5 truncate w-full">by {{ $book->author }}</p>
                      </div>

                      <!-- genres-->
                      @if($book->genres->isNotEmpty())
                        <div class="flex flex-wrap gap-1 mt-1">
                          @foreach($book->genres as $genre)
                            <span class="bg-primary/80 border border-primary text-background text-[8px] font-black px-1.5 py-0.5 rounded shadow-sm uppercase tracking-wide">{{ $genre->name }}</span>
                          @endforeach
                        </div>
                      @endif

                      <!-- phonics -->
                      @if($book->phonics->isNotEmpty())
                        <div class="flex flex-wrap gap-1">
                          @foreach($book->phonics as $phonic)
                            <span class="bg-blue-300 border border-blue-500 text-white text-[8px] font-black px-1.5 py-0.5 rounded shadow-sm uppercase tracking-wide">{{ $phonic->sound }}</span>
                          @endforeach
                        </div>
                      @endif
                      
                    </div>
                  </label>
                @empty
                  <p class="text-xs text-primary/50 italic text-center py-2">No recommended books found.</p>
                @endforelse
              </div>

              <button type="submit" class="w-full bg-primary/10 text-primary hover:bg-primary hover:text-white font-bold rounded-xl px-3 py-2 transition-colors text-xs border border-primary/20 mt-2">
                Confirm
              </button>
            </form>

          </div>
        </div>

      @empty
        <!-- empty state -->
        <div class="col-span-full bg-[#755f540a] border-2 border-dashed border-[#755f5430] rounded-3xl p-8 flex flex-col items-center justify-center text-center">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-10 text-primary/30 mb-3">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
          </svg>
          <h3 class="text-lg font-bold text-primary">No students available</h3>
          <p class="text-primary/60 text-sm mt-1 max-w-sm">
            Add students to this classroom to start assigning weekly reading lists.
          </p>
        </div>
      @endforelse
    </div>
  </div>
</x-teacher.layout>