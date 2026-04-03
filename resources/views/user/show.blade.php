<x-layout title="{{ $user->username }}'s Profile">
    <div class="w-full lg:w-3/5 xl:w-full mx-auto px-6 py-8 flex flex-col gap-6">
        <!-- ========================================= -->
        <!-- Profile header -->
        <div class="bg-[#755f540a] border border-[#755f5420] rounded-3xl p-6 md:p-8 flex flex-col sm:flex-row items-center sm:items-start gap-6 shadow-sm relative overflow-hidden">
                <!-- Avatar and level -->
                <div class="relative flex-shrink-0">
                    <img src="{{ asset($user->pfp ?? '/images/Placeholder.jpeg') }}" alt="Profile Picture" class="w-24 h-24 md:w-28 md:h-28 rounded-full ring-4 ring-white shadow-md object-cover">
                    <span class="absolute -bottom-2 -right-2 bg-level-{{ $user->student->level ?? '0' }} text-level-{{ $user->student->level ?? '0' }} text-xs font-black px-3 py-1 rounded-full border-2 border-white shadow-sm">
                        LVL {{ $user->student->level ?? '0' }}
                    </span>
                </div>

                <!-- User info -->
                <div class="flex-1 text-primary text-center sm:text-left space-y-2 mt-2 sm:mt-0 relative z-10">
                    <h1 class="text-3xl md:text-4xl font-display font-bold text-primary tracking-tight">
                        @if($user->student)
                            {{ $user->student->first_name }} {{ $user->student->last_name }}
                        @else
                            {{ $user->name }}
                        @endif
                    </h1>
                    <!-- Username and school -->
                    <div class="flex flex-wrap items-center justify-center md:justify-start gap-3 text-base">
                    <span class="opacity-70 font-semibold">{{ '@' . $user->username }}</span>
                    <span class="opacity-70 font-medium">|</span>
                    <span class="opacity-70 font-medium">
                        {{ $user->school->name ?? 'No School Assigned' }}
                    </span>
                </div>
                
                <!-- Edit? -->
                <!-- @if(Auth::id() === $user->id || Auth::user()->role === 'Teacher')
                <div class="pt-2">
                    <button class="px-5 py-2 bg-white border-2 border-primary/10 text-primary text-xs font-bold rounded-xl hover:border-secondary transition-colors">
                        Edit Profile
                    </button>
                </div>
                @endif -->
            </div>

            <div class="worm absolute -bottom-3 -right-3 opacity-50 z-0 hidden sm:block">
                <img src="/images/home/wormMovement1.png" alt="Worm" class="h-10">
            </div>
        </div>

        <!-- ========================================= -->
        <!-- User information -->
        <div class="grid grid-cols-3 gap-3 md:gap-5">
            <!-- streak -->
            <div class="bg-white border border-[#755f5420] rounded-3xl p-4 flex flex-col items-center justify-center text-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-6 text-orange-500 mb-1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.866 8.21 8.21 0 0 0 3 2.48Z" />
                </svg>
                <span class="text-2xl font-black text-primary leading-none">{{ $streakCount }}</span>
                <span class="text-[10px] font-bold text-primary/60 uppercase tracking-widest mt-1">Reading Streak</span>
            </div>

            <!-- average rating -->
            <div class="bg-white border border-[#755f5420] rounded-3xl p-4 flex flex-col items-center justify-center text-center">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6 text-yellow-400 mb-1">
                    <path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.006 5.404.434c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.434 2.082-5.005Z" clip-rule="evenodd" />
                </svg>
                <span class="text-2xl font-black text-primary leading-none">{{ $avgRating }}</span>
                <span class="text-[10px] font-bold text-primary/60 uppercase tracking-widest mt-1">Avg Rating</span>
            </div>

            <!-- favourite genre -->
            <div class="bg-primary border border-primary rounded-3xl p-4 flex flex-col items-center justify-center text-center">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6 text-pink-300 mb-1">
                    <path d="m11.645 20.91-.007-.003-.022-.012a15.247 15.247 0 0 1-.383-.218 25.18 25.18 0 0 1-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0 1 12 5.052 5.5 5.5 0 0 1 16.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 0 1-4.244 3.17 15.247 15.247 0 0 1-.383.219l-.022.012-.007.004-.003.001a.752.752 0 0 1-.704 0l-.003-.001Z" />
                </svg>
                <span class="text-sm md:text-base font-black text-background leading-tight truncate w-full" title="{{ $topGenreText }}">{{ $topGenreText }}</span>
                @php

                $genres = "genre";

                if (strpos($topGenreText, '&') !== false){
                    $genres = "genres";
                } else {
                    $genres = "genre";
                }
                @endphp
                <span class="text-[10px] font-bold text-background/70 uppercase tracking-widest mt-1">Top {{ $genres }}</span>
            </div>
        </div>

        <!-- ========================================= -->
        <!-- Favourite books -->
        <div class="grid grid-cols-1 gap-3 md:gap-5 w-full">
            <div class="bg-white border border-[#755f5420] rounded-3xl p-6 flex flex-col items-start justify-start text-left w-full overflow-hidden">
                <span class="font-bold text-primary text-lg mb-4 flex items-center gap-2">
                    ❤︎⁠
                    @if(Auth::id() === $user->id)
                        Your
                    @else
                        {{ $user->username . "'s" }}
                    @endif
                    favourite books
                </span>
                <div class="flex flex-row items-start gap-4 rounded-2xl overflow-x-auto w-full">
                    <!-- Book -->
                    <div class="flex flex-col items-center gap-2 w-1/4 sm:w-1/4 flex-shrink-0">
                        <div class="w-full h-36 bg-red-200 rounded-lg flex items-center justify-center"></div>
                        <div class="text-center w-full px-1">
                            <h4 class="text-xs sm:text-sm font-bold text-primary truncate" title="The Very Hungry Caterpillar">The Very Hungry Caterpillar</h4>
                            <p class="text-[9px] sm:text-[10px] font-semibold text-primary/60 truncate" title="Eric Carle">Eric Carle</p>
                        </div>
                    </div>
                    <!-- Book -->
                    <div class="flex flex-col items-center gap-2 w-1/4 sm:w-1/4 flex-shrink-0">
                        <div class="w-full h-36 bg-red-200 rounded-lg flex items-center justify-center"></div>
                        <div class="text-center w-full px-1">
                            <h4 class="text-xs sm:text-sm font-bold text-primary truncate" title="The Very Hungry Caterpillar">The Very Hungry Caterpillar</h4>
                            <p class="text-[9px] sm:text-[10px] font-semibold text-primary/60 truncate" title="Eric Carle">Eric Carle</p>
                        </div>
                    </div>
                    <!-- Book -->
                    <div class="flex flex-col items-center gap-2 w-1/4 sm:w-1/4 flex-shrink-0">
                        <div class="w-full h-36 bg-red-200 rounded-lg flex items-center justify-center"></div>
                        <div class="text-center w-full px-1">
                            <h4 class="text-xs sm:text-sm font-bold text-primary truncate" title="The Very Hungry Caterpillar">The Very Hungry Caterpillar</h4>
                            <p class="text-[9px] sm:text-[10px] font-semibold text-primary/60 truncate" title="Eric Carle">Eric Carle</p>
                        </div>
                    </div>
                    <!-- Book -->
                    <div class="flex flex-col items-center gap-2 w-1/4 sm:w-1/4 flex-shrink-0">
                        <div class="w-full h-36 bg-red-200 rounded-lg flex items-center justify-center"></div>
                        <div class="text-center w-full px-1">
                            <h4 class="text-xs sm:text-sm font-bold text-primary truncate" title="The Very Hungry Caterpillar">The Very Hungry Caterpillar</h4>
                            <p class="text-[9px] sm:text-[10px] font-semibold text-primary/60 truncate" title="Eric Carle">Eric Carle</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================= -->
        <!-- Reading history, genres liked, phonics mastered -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- reading history -->
            <div class="bg-white border border-[#755f5420] rounded-3xl p-6 flex flex-col h-[350px]">
                <h3 class="font-bold text-primary text-lg mb-4 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-5 text-primary/50"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" /></svg>
                    Reading History
                </h3>
                <div class="flex-1 overflow-y-auto pr-2 space-y-3">
                    
                    @php
                        // get books that are marked as completed in pivot table ordered by latest finished
                        $readingHistory = $user->student ? $user->student->books()->wherePivot('status', 'completed')->latest('book_student.updated_at')->get() : [];
                    @endphp

                    @forelse($readingHistory as $book)
                        <div class="flex items-center gap-3 p-3 rounded-2xl bg-[#755f540a] border border-[#755f5410]">
                            <!-- book cover -->
                            <div class="relative w-10 h-14 bg-[#755f540a] border border-[#755f5410] rounded-md overflow-hidden flex-shrink-0 flex items-center justify-center shadow-sm">
                                @if($book->cover_id && str_starts_with($book->cover_id, 'LOCAL_'))
                                    @php $imagePath = str_replace('LOCAL_', '', $book->cover_id); @endphp
                                    <img src="{{ asset('storage/' . $imagePath) }}" alt="{{ html_entity_decode($book->title ?? '', ENT_QUOTES) }}" class="absolute inset-0 w-full h-full object-cover">
                                @elseif($book->cover_id && str_starts_with($book->cover_id, 'PLACEHOLDER_'))
                                    @php $bgColour = str_replace('PLACEHOLDER_', '', $book->cover_id); @endphp
                                    <div class="absolute inset-0 w-full h-full flex items-center justify-center p-1 text-center" style="background-color: {{ $bgColour }};">
                                        <span class="font-black text-white text-[6px] leading-tight drop-shadow-md line-clamp-4">{{ html_entity_decode($book->title ?? '', ENT_QUOTES) }}</span>
                                    </div>
                                @elseif($book->cover_id)
                                    <img src="https://books.google.com/books/content?id={{ $book->cover_id }}&printsec=frontcover&img=1&zoom=1" alt="{{ html_entity_decode($book->title ?? '', ENT_QUOTES) }}" class="absolute inset-0 w-full h-full object-cover">
                                @else
                                    <span class="font-bold text-primary/30 text-[6px] tracking-widest -rotate-12 text-center leading-tight">NO<br>COVER</span>
                                @endif
                            </div>
                            
                            <!-- book info -->
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-bold text-primary truncate" title="{{ $book->title }}">{{ $book->title }}</h4>
                                <p class="text-[10px] font-semibold text-primary/60 truncate">by {{ $book->author }}</p>
                            </div>
                            
                            <!-- date completed -->
                            <div class="flex flex-col items-end justify-center">
                                <span class="text-[9px] font-bold text-primary/40 uppercase">Finished</span>
                                <span class="text-xs font-black text-primary/70">
                                    {{ $book->pivot->updated_at->format('M d') }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="h-full flex flex-col items-center justify-center text-center opacity-60">
                            <p class="text-sm font-bold text-primary">No completed books yet!</p>
                            <p class="text-[10px] text-primary/60 mt-1">Books will appear once {{ $user->name }} has finished a book!</p>
                        </div>
                    @endforelse

                </div>
            </div>

            <!-- Genres liked and phonics mastered -->
            <div class="flex flex-col gap-6 h-[350px]">
                
                <!-- genres explored -->
                <div class="bg-white border border-[#755f5420] rounded-3xl p-5 flex-1 flex flex-col">
                    <h3 class="font-bold text-primary text-sm mb-3 uppercase tracking-widest text-center md:text-left">Genres Explored</h3>
                    
                    <div class="flex flex-wrap gap-2 justify-center md:justify-start overflow-y-auto max-h-[120px] custom-scrollbar pr-2 pb-1">
                        @php
                            $likedGenres = $user->student ? $user->student->preferredGenres : [];
                        @endphp

                        @forelse($likedGenres as $genre)
                            <span class="px-3 py-1 bg-orange-50 border border-orange-100 text-primary rounded-full text-xs font-bold shadow-sm">
                                {{ $genre->name }}
                            </span>
                        @empty
                            <p class="text-xs text-primary/50 italic text-center w-full mt-2">No liked genres added yet.</p>
                        @endforelse
                    </div>
                </div>
                <!-- phonics mastered -->
                <div class="bg-white border border-[#755f5420] rounded-3xl p-5 flex-1 flex flex-col">
                    <h3 class="font-bold text-primary text-sm mb-3 uppercase tracking-widest text-center md:text-left">Phonics Mastered</h3>
                    <div class="flex flex-wrap gap-2 justify-center md:justify-start overflow-y-auto max-h-[120px] custom-scrollbar pr-2 pb-1">
                    @php
                        if ($level >= 8){
                            $phonicMessage = "This user has mastered all phonics! Amazing work!";
                        } else if ($level < 8) {
                            $phonicMessage = "This user has not mastered any phonics yet. Encourage them to keep reading to master new phonics!";
                        }
                    @endphp
                    @forelse($phonicsMastered as $phonic)
                        <span class="px-3 py-1 bg-orange-50 border border-orange-100 text-primary rounded-full text-xs font-bold shadow-sm">
                            {{ $phonic }}
                        </span>
                    @empty
                        <p class="text-lg text-primary/60 font-medium mt-2">{{ $phonicMessage }}</p>
                    @endforelse
                </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>