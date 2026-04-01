<x-layout title="My Assignments">
    <div class="w-full px-6 md:px-10 lg:px-16 py-8 flex-1 flex flex-col gap-8 lg:gap-12 font-sans text-primary">
        <div class="bg-[#755f540a] border border-[#755f5420] rounded-3xl p-6 md:p-10 relative overflow-hidden shadow-sm flex flex-col md:flex-row justify-between md:items-center gap-6">
            <div class="relative z-10">
                <h1 class="text-4xl md:text-5xl font-display text-primary flex items-center gap-4">
                    My Reading Logbook!
                </h1>
                <p class="text-base md:text-lg font-medium text-primary/70 mt-3">Keep track of the books you have read or are currently reading! Make sure to leave reviews on every book!</p>
            </div>
        </div>
        <!-- ========================================= -->
        <!-- CURRENT ASSIGNMENTS -->
        <div class="flex flex-col gap-4">
            <h2 class="text-2xl md:text-3xl font-display text-primary pl-2">Current Assignments</h2>
            
            @if($currentAssignments->isEmpty())
                <div class="bg-[#755f540a] border border-[#755f5420] rounded-3xl p-10 text-center flex flex-col items-center justify-center">
                    <p class="text-lg font-bold text-primary/60">You currently have no assignments!<br>Ask your teacher for a new book to read!</p>
                    
                    @if(session('success'))
                        <p class="text-green-600 font-bold mt-4">{{ session('success') }}</p>
                    @endif
                    @if(session('error'))
                        <p class="text-red-500 font-bold mt-4">{{ session('error') }}</p>
                    @endif

                    <form action="{{ route('assignments.notify') }}" method="POST" class="mt-4" onsubmit="this.querySelector('button').disabled = true; this.querySelector('button').classList.add('opacity-50', 'cursor-not-allowed'); this.querySelector('button').innerText = 'Notifying...';">
                        @csrf
                        <button type="submit" class="inline-flex items-center justify-center w-full md:w-auto gap-1.5 text-xs font-bold bg-primary hover:bg-primary/80 text-white px-4 py-2 rounded-full transition-colors">
                            Notify your teacher!
                        </button>
                    </form>
                </div>
            @else
                <!-- responsive grid based on weekly goal count -->
                @php
                    $assignCount = $currentAssignments->count();
                    $gridClass = 'grid-cols-1 md:grid-cols-2 xl:grid-cols-3'; // Default (3 or more)
                    if ($assignCount == 1) {
                        $gridClass = 'grid-cols-1';
                    } elseif ($assignCount == 2) {
                        $gridClass = 'grid-cols-1 md:grid-cols-2';
                    }
                @endphp
                
                <!-- Currently assigned books -->
                <div class="grid {{ $gridClass }} gap-6">
                    @foreach($currentAssignments as $book)
                        <div class="bg-white border-2 border-primary/20 rounded-3xl p-6 shadow-sm hover:shadow-md transition-shadow flex flex-col gap-4 relative overflow-hidden">
                            <!-- Due date -->
                            <div class="absolute top-2 right-2 px-4 py-1.5 rounded-2xl font-bold text-xs uppercase tracking-widest {{ $book->is_overdue ? 'bg-red-500 text-white' : 'bg-orange-200 text-orange-900' }}">
                                Due in {{ $book->due_date->diffForHumans(null, true) }}
                            </div>

                            <div class="flex gap-4 items-center mt-4">
                                <!-- Cover -->
                                <div class="w-16 h-24 bg-orange-100 rounded-lg overflow-hidden flex-shrink-0 border border-[#755f5420] relative">
                                    @if($book->cover_id && !str_starts_with($book->cover_id, 'PLACEHOLDER_'))
                                        <img src="{{ str_starts_with($book->cover_id, 'LOCAL_') ? asset('storage/' . str_replace('LOCAL_', '', $book->cover_id)) : 'https://books.google.com/books/content?id='.$book->cover_id.'&printsec=frontcover&img=1&zoom=1' }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-[8px] font-black text-primary/30 text-center p-1">NO COVER</div>
                                    @endif
                                </div>
                                <!-- Title -->
                                <div>
                                    <h3 class="text-lg font-bold leading-tight">{{ html_entity_decode($book->title ?? '', ENT_QUOTES) }}</h3>
                                    <p class="text-xs font-semibold text-primary/50 mt-1 uppercase tracking-wider">Assigned: {{ Carbon\Carbon::parse($book->pivot->created_at)->format('M d') }} | Due: {{ $book->due_date->format('M d') }}</p>
                                </div>
                            </div>

                            <!-- Mark complete -->
                            <div class="mt-auto pt-2">
                                <form action="{{ route('assignments.complete', $book->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full bg-primary hover:bg-secondary text-white font-bold py-3 rounded-xl shadow-sm transition-colors flex items-center justify-center gap-2">
                                        Mark as Read & Review!
                                    </button> 
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- READING HISTORY -->
        <div class="flex flex-col gap-4 mt-4">
            <!-- if student hasn't completed reviews -->
            @php
                $hasntReviewed = false;
                foreach($readingHistory as $book)
                {
                    if(!$reviews->has($book->id))
                    {
                        $hasntReviewed = true;
                        break;
                    }
                }
            @endphp
            
            @if($hasntReviewed)
                <div class="bg-red-50 border border-red-200 text-red-600 px-5 py-4 rounded-2xl flex items-center gap-3 shadow-sm mb-2">
                    <p class="text-lg font-bold">Please write a review for all of your completed books!</p>
                </div>
            @endif

            <h2 class="text-2xl md:text-3xl font-display text-primary pl-2">Reading History</h2>

            <!-- Notebook design (holy help me please.) -->
            <div class="bg-[#faf8f5] border border-[#755f5430] rounded-3xl shadow-sm overflow-hidden relative">
                
                <!-- Red notebook line -->
                <div class="hidden md:block absolute left-[13.5rem] top-0 bottom-0 w-0.5 bg-red-400/40 z-0"></div>

                <div class="w-full relative z-10">
                    <!-- For desktop -->
                    <div class="hidden md:flex border-b-2 border-primary/20 py-4 px-6 font-black text-sm uppercase tracking-widest text-primary/60 bg-[#755f5405] relative z-10">
                        <div class="w-48 flex-shrink-0 text-left">Week</div>
                        <div class="flex-1 pl-6">Title</div>
                        <div class="w-32 text-center">Review</div>
                        <div class="w-32 text-center">Stars</div>
                    </div>

                    <!-- Rows -->
                    <div class="flex flex-col">
                        @forelse($readingHistory as $book)
                            @php
                                $review = $reviews->get($book->id);
                                $hasReview = !is_null($review);
                            @endphp
                            
                            <!-- SINGLE ROW -->
                            <div class="flex flex-col md:flex-row md:items-center border-b border-[#755f5420] py-4 px-6 hover:bg-white/50 transition-colors relative z-10">
                                
                                <!-- WEEK -->
                                <div class="w-full md:w-48 flex-shrink-0 text-primary font-bold text-sm text-left pr-4">
                                    <span class="md:hidden text-xs text-primary/50 uppercase tracking-widest block mb-1">Completed:</span>
                                    {{ Carbon\Carbon::parse($book->pivot->updated_at)->format('M d, Y, g:i A') }}
                                </div>

                                <!-- TITLE & COVER -->
                                <div class="w-full md:flex-1 flex items-center gap-4 md:pl-6 mt-4 md:mt-0">
                                    <div class="w-10 h-14 bg-orange-100 rounded overflow-hidden flex-shrink-0 shadow-sm border border-[#755f5420]">
                                        @if($book->cover_id && !str_starts_with($book->cover_id, 'PLACEHOLDER_'))
                                            <img src="{{ str_starts_with($book->cover_id, 'LOCAL_') ? asset('storage/' . str_replace('LOCAL_', '', $book->cover_id)) : 'https://books.google.com/books/content?id='.$book->cover_id.'&printsec=frontcover&img=1&zoom=1' }}" class="w-full h-full object-cover">
                                        @endif
                                    </div>
                                    <a href="{{ url('/books/' . $book->id) }}" class="font-bold text-base md:text-lg hover:text-orange-600 transition-colors line-clamp-2">{{ html_entity_decode($book->title ?? '', ENT_QUOTES) }}</a>
                                </div>

                                <!-- REVIEW/UPDATE REVIEW -->
                                <div class="w-full md:w-32 mt-4 md:mt-0 flex md:justify-center">
                                    @if($hasReview)
                                        <a href="{{ url('/books/' . $book->id . '/review') }}" class="inline-flex items-center justify-center w-full md:w-auto gap-1.5 text-xs font-bold bg-orange-100 hover:bg-orange-200 text-primary border border-orange-300 px-4 py-2 rounded-full transition-colors">
                                            Update Review
                                        </a>
                                    @else
                                        <a href="{{ url('/books/' . $book->id . '/review') }}" class="inline-flex items-center justify-center w-full md:w-auto gap-1.5 text-xs font-bold bg-secondary text-white hover:bg-primary px-4 py-2 rounded-full">
                                            Write Review!
                                        </a>
                                    @endif
                                </div>

                                <!-- STARS -->
                                <div class="w-full md:w-32 mt-3 md:mt-0 flex md:justify-center items-center">
                                    @if($hasReview)
                                        <div class="flex gap-0.5" title="{{ $review->rating }} out of 5 stars">
                                            @for($i = 1; $i <= 5; $i++)
                                                @if($i <= $review->rating)
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 text-yellow-400 drop-shadow-sm">
                                                        <path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.006 5.404.434c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.434 2.082-5.005Z" clip-rule="evenodd" />
                                                    </svg>
                                                @else
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-yellow-400/30">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385c.148.621-.531 1.114-1.005.777l-4.797-2.88a.562.562 0 0 0-.582 0l-4.797 2.88c-.474.337-1.153-.156-1.005-.777l1.285-5.385a.563.563 0 0 0-.182-.557l-4.204-3.602a.563.563 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                                                    </svg>
                                                @endif
                                            @endfor
                                        </div>
                                    @else
                                        <!-- no rating -->
                                        <span class="text-md font-semibold text-primary/50">No rating</span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="py-16 text-center text-primary/50 font-medium">
                                Your reading log is empty, start reading some books and they will appear here!
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            @if($readingHistory->hasPages())
                <div class="mt-4 w-full">
                    {{ $readingHistory->links() }}
                </div>
            @endif

        </div>
    </div>
</x-layout>