<x-teacher.layout :classroom="$classroom" :yearGroups="$yearGroups" title="Generate Reading List">
  
  <div class="space-y-6">
    
    <!-- Header -->
    <div class="bg-[#755f540a] border-2 border-[#755f5420] rounded-3xl p-5 sm:p-6 shadow-sm">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
        
        <!-- Auto assign all books -->
        @if($generateAllUsedThisWeek ?? false)
            @php
                // calculate when the button unlocks end of sunday
                $unlocksAt = now()->endOfWeek()->addSecond();
            @endphp
            <button type="button" disabled 
                class="bg-[#755f5410] border border-[#755f5420] text-primary/40 rounded-lg px-4 py-2.5 cursor-not-allowed flex items-center text-xs font-black tracking-widest gap-2"
                title="Books have already been assigned to this class this week">
                ASSIGNABLE {{ $unlocksAt->diffForHumans(['parts' => 2]) }}
            </button>
        @else
            <form action="{{ route('teacher.reading.generateAll', $classroom->id) }}" method="POST">
                @csrf
                <button type="submit" class="bg-primary text-background rounded-lg px-4 py-2.5 hover:bg-orange-900 transition-colors flex items-center text-xs font-black tracking-widest gap-2">
                    AUTO ASSIGN ALL BOOKS
                </button>
            </form>
        @endif

        @if(session('error'))
            <div class="text-red-600 text-sm font-bold bg-red-50 px-3 py-1.5 rounded-lg border border-red-200">
                {{ session('error') }}
            </div>
        @endif
      </div>  
    </div>

    <!-- calculate max weekly goals target for empty states -->
    @php
      $maxTarget = 1;
      foreach($students as $student) {
          $t = $student->weeklyGoal->target ?? 1;
          $t = min(max($t, 1), 3);
          if($t > $maxTarget) {
              $maxTarget = $t;
          }
      }
    @endphp

    <!-- ========================================= -->
    <!-- STUDENT ASSIGNMENT GRID -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-4">
      
      @forelse($students as $s)
        @php
          $cardStyle = $s->is_exceptional
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
          <div class="flex justify-between items-start gap-2 w-full mb-4 pb-4 border-b border-[#755f5415]">
            <div class="flex items-start gap-2.5 min-w-0 flex-1">
              
              <!-- avatar -->
              <a href="{{ route('user.show', $s->user->id) }}">
                <div class="relative flex-shrink-0 mt-1">
                  <img class="h-10 w-10 sm:h-11 sm:w-11 rounded-full object-cover ring-2 ring-white shadow-sm" src="{{ asset($s->pfp ?? '/images/pfp/cat.png') }}" alt="{{ $s->first_name }}">
                  <!-- level badge -->
                  <span class="absolute -bottom-1 left-1/4 -translate-x-1/2 text-[8px] font-black px-1.5 py-0.5 rounded-full border border-white shadow-sm {{ $ortBadgeClass }}">
                    {{ $s->level }}
                  </span>
                </div>
                
                <!-- student info -->
                <div class="flex flex-col min-w-0 pt-0.5">
                  <h3 class="font-bold text-primary text-xs sm:text-sm truncate w-full" title="{{ $s->first_name }} {{ $s->last_name }}">
                    {{ $s->first_name }} {{ $s->last_name }}
                  </h3>
                  <!-- username -->
                  <p class="text-[9px] sm:text-[10px] text-primary/50 font-semibold truncate w-full">
                    {{ '@' . $s->user->username }}
                  </p>
                </div>
              </div>
            </a>

            <!-- currently reading -->
            <div class="flex flex-col items-end min-w-0 w-[45%] shrink-0 text-right">
              <p class="text-[8px] font-bold text-primary/40 mb-1 uppercase tracking-wider">Reading:</p>
              @if($s->currentBook())
                <div class="bg-green-50/80 border border-green-200/60 rounded-lg p-1.5 w-full inline-block">
                  <p class="text-[9px] font-bold text-green-800 leading-tight line-clamp-2" title="{{ $s->currentBook()->title }}">
                    {{ $s->currentBook()->title }}
                  </p>
                </div>
              @else
                <p class="text-[9px] text-red-500 font-bold italic bg-red-50/80 py-1 px-1.5 rounded-md border border-red-100">No book assigned</p>
              @endif
            </div>
          </div>

          <!-- recently assigned books -->
          @php
            $currentlyReadingBooks = $s->books()->wherePivot('status', 'reading')->orderByPivot('created_at', 'desc')->get();
          @endphp
          
          @if($currentlyReadingBooks->isNotEmpty())
            <div class="mb-4 w-full">
              <p class="text-[10px] font-bold text-green-600/80 mb-1 flex justify-between items-center uppercase tracking-wider">
                <span>Recently Assigned:</span>
              </p>
              <div class="flex flex-col gap-1.5 max-h-[120px] overflow-y-auto pr-1 custom-scrollbar">
                @foreach($currentlyReadingBooks as $crBook)
                  <div class="bg-green-50 border border-green-200 rounded-lg p-2 flex items-start gap-2 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-4 text-green-500 mt-0.5 shrink-0">
                      <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule="evenodd" />
                    </svg>
                    <div class="min-w-0 flex-1">
                      <p class="text-[11px] font-bold text-green-800 leading-tight line-clamp-2" title="{{ $crBook->title }}">{{ $crBook->title }}</p>
                      <p class="text-[9px] text-green-600 font-bold mt-0.5 truncate">Assigned {{ $crBook->pivot->created_at->diffForHumans() }}</p>
                    </div>
                  </div>
                @endforeach
              </div>
            </div>
          @endif

          <!-- manually assign books form -->
          <form action="{{ route('teacher.reading.assignBook', [$classroom->id, $s->id]) }}" method="POST" class="flex-1 flex flex-col w-full">
            @csrf

            @php
              $target = $s->weeklyGoal->target ?? 1;
              $target = min(max($target, 1), 3);
              
              $rlBooks = $s->readingListBooks ?? collect();
              $bookChunks = collect($s->recommendedBooks ?? [])->chunk(10);
            @endphp

            <!-- ASSIGN [WEEKLY GOAL] BOOKS -->
            <div class="text-[10px] font-bold text-primary/50 mt-1 mb-2 uppercase">ASSIGN {{ $target }} {{ Str::plural('BOOK', $target) }}</div>

            <div class="flex flex-col gap-4 mb-4">
              @for($i = 0; $i < $target; $i++)
                @php
                  $chunk = $bookChunks->get($i, collect());
                  $sliderOptions = $rlBooks->concat($chunk)->unique('id');
                @endphp
                
                <div>
                  <label class="text-[9px] font-bold text-primary/70 flex justify-between items-center mb-1.5 px-1">
                    <span class="flex items-center gap-1">
                      <span class="bg-primary text-background rounded-full w-3.5 h-3.5 flex items-center justify-center text-[8px]">{{ $i + 1 }}</span>
                      PICK BOOK {{ $i + 1 }}
                    </span>
                    <span class="text-[8px] bg-[#755f540a] border border-[#755f5420] px-1.5 py-0.5 rounded">{{ $sliderOptions->count() }} options</span>
                  </label>

                  <!-- book recommendation list -->
                  <div class="flex overflow-x-auto gap-3 pb-2 pt-1 snap-x custom-scrollbar">
                    @forelse($sliderOptions as $book)
                      @php
                        $isRequested = $rlBooks->contains('id', $book->id);

                        $bookBorderColour = match($book->ort_colour) {
                            'Pink' => 'border-pink-400 peer-checked:bg-pink-100 text-pink-700',
                            'Red' => 'border-red-400 peer-checked:bg-red-100 text-red-700',
                            'Yellow' => 'border-yellow-400 peer-checked:bg-yellow-100 text-yellow-700',
                            'Light Blue' => 'border-blue-400 peer-checked:bg-blue-100 text-blue-700',
                            'Green' => 'border-green-400 peer-checked:bg-green-100 text-green-700',
                            'Orange' => 'border-orange-400 peer-checked:bg-orange-100 text-orange-700',
                            'Turquoise' => 'border-teal-400 peer-checked:bg-teal-100 text-teal-700',
                            'Purple' => 'border-purple-400 peer-checked:bg-purple-100 text-purple-700',
                            'Gold' => 'border-amber-400 peer-checked:bg-amber-100 text-amber-800',
                            'White' => 'border-gray-300 peer-checked:bg-gray-100 text-gray-700',
                            'Lime', 'Lime+' => 'border-lime-400 peer-checked:bg-lime-100 text-lime-800',
                            'Grey' => 'border-gray-400 peer-checked:bg-gray-100 text-gray-800',
                            'Dark Blue' => 'border-blue-800 peer-checked:bg-blue-200 text-blue-900',
                            'Dark Red' => 'border-red-800 peer-checked:bg-red-200 text-red-900',
                            default => 'border-[#755f5440] peer-checked:bg-orange-100 text-primary',
                        };

                        $badgeClass = match($book->ort_colour) {
                            'Light Purple' => 'bg-purple-200 text-purple-900',
                            'Pink' => 'bg-pink-400 text-white',
                            'Red' => 'bg-red-500 text-white',
                            'Yellow' => 'bg-yellow-300 text-yellow-900',
                            'Light Blue' => 'bg-blue-300 text-blue-900',
                            'Green' => 'bg-green-500 text-white',
                            'Orange' => 'bg-orange-500 text-white',
                            'Turquoise' => 'bg-teal-400 text-teal-900',
                            'Purple' => 'bg-purple-500 text-white',
                            'Gold' => 'bg-amber-400 text-amber-900',
                            'White' => 'bg-gray-100 text-gray-800',
                            'Lime', 'Lime+' => 'bg-lime-400 text-lime-900',
                            'Grey' => 'bg-gray-400 text-gray-900',
                            'Dark Blue' => 'bg-blue-800 text-white',
                            'Dark Red' => 'bg-red-800 text-white',
                            default => 'bg-white text-gray-800',
                        };

                        $peerDifficulty = $book->peer_difficulty ?? null;
                        $peerBadge = match($peerDifficulty) {
                            'easy' => ['emoji' => '😊', 'label' => 'EASY', 'classes' => 'bg-green-100 text-green-700 border-green-300'],
                            'okay' => ['emoji' => '🙂', 'label' => 'OKAY', 'classes' => 'bg-amber-100 text-amber-700 border-amber-300'],
                            'hard' => ['emoji' => '😅', 'label' => 'HARD', 'classes' => 'bg-red-100 text-red-700 border-red-300'],
                            default => null,
                        };

                        $bgHighlight = $isRequested ? 'bg-indigo-50/40 shadow-sm' : 'bg-white';
                      @endphp
                      
                      <!-- make the cursor a click cursor -->
                      <label class="cursor-pointer relative group flex-shrink-0 w-[110px] snap-start h-full">
                        <input type="radio" name="book_ids[{{ $i }}]" value="{{ $book->id }}" class="peer sr-only" required>
                        
                        <!-- i hecking love 'modern' looks -->
                        <div class="border-2 border-dashed {{ $bookBorderColour }} rounded-xl p-2 {{ $bgHighlight }} transition-all group-hover:shadow-md peer-checked:border-solid peer-checked:ring-current flex flex-col h-full overflow-hidden relative">
                          
                          <!-- on list badge -->
                          @if($isRequested)
                            <span class="absolute top-0 left-0 text-[7px] font-black px-1.5 py-0.5 rounded-lg bg-indigo-500 text-white z-20 flex items-center gap-0.5">
                              ON LIST
                            </span>
                          @endif

                          <!-- ort colour badge -->
                          <span class="absolute top-0 right-0 text-[7px] font-black px-1.5 py-0.5 rounded-lg z-20 {{ $badgeClass }}">
                              {{ $book->ort_level ?? $book->ort_colour }}
                          </span>

                          <!-- book cover image -->
                          <div class="w-full h-24 mb-1.5 rounded bg-gray-50 flex items-center justify-center overflow-hidden shrink-0 relative group-hover:opacity-90 transition-opacity">
                              @if($book->cover_id && str_starts_with($book->cover_id, 'LOCAL_'))
                                  @php $imagePath = str_replace('LOCAL_', '', $book->cover_id); @endphp
                                  <img src="{{ asset('storage/' . $imagePath) }}" alt="{{ html_entity_decode($book->title ?? '', ENT_QUOTES) }}" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                              
                              @elseif($book->cover_id && str_starts_with($book->cover_id, 'PLACEHOLDER_'))
                                  @php $bgColor = str_replace('PLACEHOLDER_', '', $book->cover_id); @endphp
                                  <div class="absolute inset-0 w-full h-full group-hover:scale-105 transition-transform duration-500 flex items-center justify-center p-2 text-center" style="background-color: {{ $bgColor }};">
                                      <span class="font-black text-white text-[9px] leading-tight drop-shadow-md line-clamp-3">{{ html_entity_decode($book->title ?? '', ENT_QUOTES) }}</span>
                                  </div>
                              
                              @elseif($book->cover_id)
                                  <img src="https://books.google.com/books/content?id={{ $book->cover_id }}&printsec=frontcover&img=1&zoom=1" alt="{{ html_entity_decode($book->title ?? '', ENT_QUOTES) }}" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                              
                              @else
                                  <span class="font-bold text-primary/30 text-[10px] tracking-widest -rotate-12">NO COVER</span>
                              @endif

                              <!-- peer difficulty badge -->
                              @if($peerBadge)
                                  <span class="absolute bottom-1 left-1 text-[7px] font-black px-1.5 py-0.5 rounded-md border z-20 shadow-sm {{ $peerBadge['classes'] }}" 
                                        title="Other students rated this {{ strtolower($peerBadge['label']) }}">
                                      {{ $peerBadge['emoji'] }} {{ $peerBadge['label'] }}
                                  </span>
                              @endif
                          </div>
                          
                          <!-- title and author -->
                          <div class="flex-1 min-h-[40px]">
                            <h4 class="text-[10px] font-bold text-primary leading-tight line-clamp-2" title="{{ $book->title }}">{{ $book->title }}</h4>
                            <p class="text-[8px] font-medium text-primary/60 mt-0.5 truncate">by {{ $book->author }}</p>
                          </div>

                          <!-- tags section -->
                          <div class="mt-1 flex flex-wrap gap-1 shrink-0">
                            <!-- genres-->
                            @if($book->genres->isNotEmpty())
                              @foreach($book->genres->take(2) as $genre)
                                <span class="bg-primary/80 text-background text-[7px] font-black px-1 py-0.5 rounded shadow-sm uppercase tracking-wide truncate max-w-[90px]">{{ $genre->name }}</span>
                              @endforeach
                            @endif

                            <!-- phonics -->
                            @if($book->phonics->isNotEmpty())
                              @foreach($book->phonics->take(2) as $phonic)
                                <span class="bg-blue-400 text-white text-[7px] font-black px-1 py-0.5 rounded shadow-sm uppercase tracking-wide truncate max-w-[90px]">{{ $phonic->sound }}</span>
                              @endforeach
                            @endif
                          </div>

                        </div>
                      </label>
                    @empty
                      <p class="text-[10px] text-primary/50 italic text-center py-4 w-full">Not enough books available.</p>
                    @endforelse
                  </div>
                </div>
              @endfor
            </div>
            
            <!-- submit button -->
            <button type="submit" class="mb-2 w-full bg-primary/10 text-primary hover:bg-primary hover:text-white font-bold rounded-xl px-3 py-2 transition-colors text-xs border border-primary/20">
              Confirm Selection
            </button>

            <!-- empty state filler -->
            @if($target < $maxTarget)
              <div class="flex-1 flex flex-col items-center justify-center bg-primary/5 rounded-xl border border-dashed border-[#755f5430] mb-4 p-4 text-center min-h-[100px]">
                  <img src="/images/home/wormMovement1.png" alt="Worm" class="h-10 md:h-12">
                <span class="text-[10px] font-bold text-primary/40 leading-snug">Increase {{ $s->first_name }}'s target<br>goal to fill the blank space</span>
              </div>
            @endif
          </form>

        </div>

      @empty
        <!-- empty state -->
        <div class="col-span-full text-center p-8 bg-[#755f540a] rounded-3xl border-2 border-dashed border-[#755f5420]">
          <h3 class="text-lg font-bold text-primary">No students available</h3>
        </div>
      @endforelse
    </div>
  </div>
</x-teacher.layout>