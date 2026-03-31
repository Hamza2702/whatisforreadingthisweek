<x-layout :title="html_entity_decode($book->title ?? '', ENT_QUOTES)">
    <div class="w-full max-w-6xl mx-auto px-6 md:px-10 py-8 flex flex-col gap-6">
        
        <!-- Back to explore button -->
        <a href="{{ route('explore') }}" class="text-primary/60 font-bold text-xs tracking-widest flex items-center gap-2 hover:text-primary transition-colors w-fit">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
            </svg>
            EXPLORE MORE BOOKS
        </a>

        <!-- Book card -->
        <div class="bg-white border border-[#755f5420] rounded-3xl p-6 md:p-10 shadow-sm flex flex-col md:flex-row gap-8 lg:gap-12 relative overflow-hidden">
            
            <!-- book cover image -->
            <div class="w-full md:w-1/3 lg:w-1/4 flex-shrink-0 relative z-10">
                <div class="relative w-full aspect-[2/3] bg-[#755f540a] border border-[#755f5410] rounded-2xl overflow-hidden shadow-md">
                    <!-- image covers-->
                    @if($book->cover_id && str_starts_with($book->cover_id, 'LOCAL_'))
                        @php $imagePath = str_replace('LOCAL_', '', $book->cover_id); @endphp
                        <img src="{{ asset('storage/' . $imagePath) }}" alt="{{ html_entity_decode($book->title ?? '', ENT_QUOTES) }}" class="absolute inset-0 w-full h-full object-cover rounded-xl shadow-sm group-hover:scale-105 transition-transform duration-500">
                    
                    @elseif($book->cover_id && str_starts_with($book->cover_id, 'PLACEHOLDER_'))
                        <!-- placeholder -->
                        @php $bgColor = str_replace('PLACEHOLDER_', '', $book->cover_id); @endphp
                        <div class="absolute inset-0 w-full h-full rounded-xl shadow-sm group-hover:scale-105 transition-transform duration-500 flex items-center justify-center p-4 text-center" style="background-color: {{ $bgColor }};">
                            <span class="font-black text-white text-sm drop-shadow-md line-clamp-4">{{ html_entity_decode($book->title ?? '', ENT_QUOTES) }}</span>
                        </div>
                    
                    @elseif($book->cover_id)
                        <img src="https://books.google.com/books/content?id={{ $book->cover_id }}&printsec=frontcover&img=1&zoom=1" alt="{{ html_entity_decode($book->title ?? '', ENT_QUOTES) }}" class="absolute inset-0 w-full h-full object-cover rounded-xl shadow-sm group-hover:scale-105 transition-transform duration-500">
                    
                    @else
                        <span class="font-bold text-primary/30 text-xs tracking-widest -rotate-12">NO COVER</span>
                    @endif
                    
                    <!-- oxford reading levels -->
                    @php 
                        $bgClass = 'bg-level-' . str_replace(' ', '', $book->ort_level);
                        $textClass = 'text-level-' . str_replace(' ', '', $book->ort_level);
                    @endphp
                    <div class="absolute top-4 right-4 {{ $bgClass }} {{ $textClass }} text-black text-xs font-black px-4 py-2 rounded-full border-2 border-white shadow-sm" style="background-color: {{ $book->ort_colour ?? '' }}">
                        LVL {{ $book->ort_level }}
                    </div>
                </div>
            </div>

            <!-- book details -->
            <div class="flex-1 flex flex-col gap-6 relative z-10">
                
                <!-- title and auth -->
                <div>
                    <h1 class="text-3xl md:text-5xl font-black text-primary leading-tight">{{ html_entity_decode($book->title ?? '', ENT_QUOTES) }}</h1>
                    <p class="text-base md:text-lg font-bold text-primary/50 uppercase tracking-widest mt-2">{{ html_entity_decode($book->author ?? '', ENT_QUOTES) }}</p>
                </div>

                <!-- genres -->
                @if($book->genres && $book->genres->count() > 0)
                    <div class="flex flex-wrap gap-2">
                        @foreach($book->genres as $genre)
                            <span class="px-4 py-2 rounded-full text-xs font-bold border border-[#755f5420] bg-white text-primary shadow-sm block">
                                {{ $genre->name }}
                            </span>
                        @endforeach
                    </div>
                @endif

                <!-- reading level -->
                <div class="bg-[#755f540a] border border-[#755f5420] rounded-2xl p-5 md:p-6 space-y-5">
                    <div>
                        <h3 class="text-[10px] font-black text-primary/60 tracking-widest mb-2">READING LEVEL</h3>
                        <div class="flex items-center gap-3">
                            <span class="w-5 h-5 rounded-full border-2 border-white shadow-sm {{ $bgClass }}" style="background-color: {{ $book->ort_colour ?? '' }}"></span>
                            <span class="font-bold text-primary text-sm">Oxford Reading Tree: Level {{ $book->ort_level }} ({{ ucfirst($book->ort_colour ?? 'No') }} Band)</span>
                        </div>
                    </div>

                    <!-- book desc -->
                    <div>
                        <h3 class="text-[10px] font-black text-primary/60 tracking-widest mb-2">DESCRIPTION</h3>
                        <p class="text-sm font-medium text-primary leading-relaxed line-clamp-6">
                            {{ $book->description }}
                        </p>
                    </div>

                    <!-- phonics -->
                    @if($book->phonics && $book->phonics->count() > 0)
                        <div class="pt-2 border-t border-[#755f5415]">
                            <h3 class="text-[10px] font-black text-primary/60 tracking-widest mb-3 mt-2">PHONIC SOUNDS</h3>
                            <div class="flex flex-wrap gap-2">
                                @foreach($book->phonics as $phonic)
                                    <span class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-[#755f5420] text-primary text-sm font-black shadow-sm">
                                        {{ $phonic->sound }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
                
                <!-- Add book, read book, favourite -->
                <div class="mt-auto pt-4 flex flex-col gap-3">
                    
                    <!-- Buttons -->
                    <div class="flex flex-col sm:flex-row gap-4 w-full items-stretch">        
                        <!-- Add to reading list / banned book -->
                        @if($banType)
                            <!-- Book banned -->
                            <div class="flex-1 bg-red-200 border border-red-500 text-red-600 font-black text-[10px] sm:text-xs tracking-widest py-4 px-4 rounded-xl flex items-center justify-center gap-2 shadow-sm text-center">
                                BOOK BANNED BY ADMINISTRATOR
                            </div>
                        @else
                            <!-- Add to reading list -->
                            <a href="" class="flex-1 bg-primary hover:bg-secondary border-2 border-primary hover:border-secondary text-white font-black text-xs tracking-widest py-4 px-4 rounded-xl shadow-sm transition-all hover:-translate-y-0.5 flex items-center justify-center gap-2 cursor-pointer">
                                ADD TO READING LIST
                            </a>
                        @endif

                        <!-- Read online openlibrary -->
                        @if(!str_starts_with($book->ol_key, 'NO_OL_'))
                            <!-- openlibrary interactive reader -->
                            @if($banType)
                                <!-- disabled -->
                                <button disabled class="flex-1 bg-green-500 text-white font-black text-xs tracking-widest py-4 px-4 rounded-xl shadow-md flex items-center justify-center gap-2 opacity-50 cursor-not-allowed border-2 border-green-500 text-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5 shrink-0">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                                    </svg>
                                    READ BOOK ONLINE
                                </button>
                            @else
                                <!-- available -->
                                <a href="https://archive.org/details/{{ $book->ol_key }}/mode/2up?view=theater" target="_blank" rel="noopener noreferrer" class="flex-1 bg-green-500 hover:bg-green-600 border-2 border-green-500 hover:border-green-600 text-white font-black text-xs tracking-widest py-4 px-4 rounded-xl shadow-md transition-all hover:-translate-y-0.5 flex items-center justify-center gap-2 text-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5 shrink-0">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                                    </svg>
                                    READ BOOK ONLINE
                                </a>
                            @endif
                        @endif
                        
                        <!-- Add to favourites -->
                        <button @if($banType) disabled @endif class="flex-1 bg-white border border-[#755f5420] text-primary font-black text-xs tracking-widest py-4 px-4 rounded-xl shadow-sm transition-all flex items-center justify-center gap-2 @if($banType) opacity-50 cursor-not-allowed @else hover:border-primary hover:text-secondary hover:-translate-y-0.5 @endif">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5 shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                            </svg>
                            ADD TO FAVOURITE
                        </button>

                    </div>

                    <!-- Online book help -->
                    @if(!str_starts_with($book->ol_key, 'NO_OL_'))
                        <p class="text-primary text-[10px] sm:text-xs text-center mt-1 w-full">
                            Need help with borrowing books?<a href="https://help.archive.org/help/borrowing-from-the-lending-library/#:~:text=How%20do%20I%20get%20set%20up%20to%20borrow%20books%20through%20archive.org%3F" target="_blank" class="font-black underline-offset-4 underline ml-1 hover:text-secondary transition-colors">Check this out</a>
                        </p>
                    @endif

                </div>
            </div>
        </div>
        <!-- Student Reviews -->
        <div class="bg-white border border-[#755f5420] rounded-3xl p-6 md:p-10 shadow-sm">
            <h2 class="text-2xl font-black text-primary mb-8 flex items-center gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-7 h-7 text-primary/60">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" />
                </svg>
                Student Reviews
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                
                <!-- Rating Summary -->
                <div class="bg-[#755f540a] border border-[#755f5420] rounded-2xl p-6">
                    <div class="flex items-center mb-5">
                        <!-- Big review number -->
                        <div class="text-4xl font-black text-primary mr-3">{{ $reviews->count() > 0 ? round($reviews->avg('rating'), 1) : '0' }}</div>
                        <div>
                            <!-- Stars -->
                            <div class="flex">
                                @for ($i = 0; $i < ($reviews->count() > 0 ? round($reviews->avg('rating')) : 0); $i++)
                                    ⭐
                                @endfor
                                @for ($i = ($reviews->count() > 0 ? round($reviews->avg('rating')) : 0); $i < 5; $i++)
                                    <span style="color: transparent; text-shadow: 0 0 #c4b5a4;">⭐</span>
                                @endfor
                            </div>
                            <!-- Ratings -->
                            <p class="text-xs font-bold text-primary/40 tracking-widest mt-1">{{ $reviews->count() }} {{ Str::plural('RATING', $reviews->count()) }}</p>
                        </div>
                    </div>

                    <!-- Rating bars -->
                    <div class="space-y-3 mb-8">
                        @for ($i = 5; $i > 0; $i--)
                            <div class="flex items-center gap-3">
                                <p class="w-12 text-xs font-black text-primary/60">{{ $i }} star</p>
                                <div class="flex-1 h-2.5 bg-[#755f5415] rounded-full overflow-hidden">
                                    <div class="h-full bg-primary/60 rounded-full transition-all duration-500" style="width: {{ $reviews->count() > 0 ? round(($reviews->where('rating', $i)->count() / $reviews->count()) * 100, 1) : 0 }}%;"></div>
                                </div>
                                <p class="w-12 text-right text-xs font-bold text-primary/50">{{ $reviews->count() > 0 ? round(($reviews->where('rating', $i)->count() / $reviews->count()) * 100, 1) : 0 }}%</p>
                            </div>
                        @endfor
                    </div>

                    <!-- Write a review -->
                    <div class="border-t border-[#755f5415] pt-6">
                        @php
                            $student = Auth::check() ? Auth::user()->student : null;
                            $existingReview = $student ? $reviews->where('student_id', $student->id)->first() : null;
                            $hasRead = $student ? DB::table('book_student')->where('student_id', $student->id)->where('book_id', $book->id)->exists() : false;
                        @endphp
                        <!-- Existing reviews -->
                        <h3 class="font-black text-primary text-sm mb-1">
                            {{ $existingReview ? 'Edit your review' : 'Review this book' }}
                        </h3>
                        <p class="text-xs text-primary/50 mb-4">
                            @if(!$student)
                                Log in to express your thoughts on this book
                            @elseif(!$hasRead)
                                You must read this book before you can review it
                            @else
                                {{ $existingReview ? 'Update your thoughts about this book' : 'Share your thoughts with other students' }}
                            @endif
                        </p>
                        @if(!$student)
                            <button disabled class="w-full text-center bg-[#755f5410] border-2 border-[#755f5420] text-primary/40 font-black text-xs tracking-widest py-3 px-4 rounded-xl cursor-not-allowed">
                                LOG IN TO REVIEW
                            </button>
                        @elseif(!$hasRead)
                            <button disabled class="w-full flex items-center justify-center gap-2 bg-[#755f5410] border-2 border-[#755f5420] text-primary/40 font-black text-xs tracking-widest py-3 px-4 rounded-xl cursor-not-allowed" title="Read the book first!">
                                READ BOOK TO REVIEW
                            </button>
                        @else
                            <a href="{{ url('/books/' . $book->id . '/review') }}" class="block text-center bg-white border-2 border-[#755f5420] hover:border-primary text-primary font-black text-xs tracking-widest py-3 px-4 rounded-xl transition-all hover:-translate-y-0.5 shadow-sm">
                                {{ $existingReview ? 'EDIT YOUR REVIEW' : 'WRITE A REVIEW' }}
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Reviews -->
                <div class="md:col-span-2 bg-[#755f540a] border border-[#755f5420] rounded-2xl p-6">
                    <!-- Filters -->
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="font-black text-primary text-sm tracking-wide">Reader Feedback</h3>
                        <select name="review-sort" id="review-sort" class="rounded-xl bg-white text-xs font-bold text-primary px-4 py-2 border border-[#755f5420] focus:ring-2 focus:ring-primary/20 focus:border-primary">
                            <option value="top" {{ ($currentSort ?? 'top') === 'top' ? 'selected' : '' }}>Top reviews</option>
                            <option value="recent" {{ ($currentSort ?? '') === 'recent' ? 'selected' : '' }}>Most recent</option>
                            @if(Auth::check() && Auth::user()->student)
                                <option value="classroom" {{ ($currentSort ?? '') === 'classroom' ? 'selected' : '' }}>Classmates</option>
                            @endif
                        </select>
                    </div>

                    <!-- Reviews -->
                    <div class="space-y-6 max-h-[500px] overflow-y-auto pr-2">
                        <!-- Loop through reviews -->
                        @if($reviews->count() > 0)
                            @foreach($reviews as $review)
                                <div class="border-b border-[#755f5415] pb-6 last:border-0">
                                    <div class="flex items-center mb-3">
                                        <!-- PFP -->
                                        <a href="{{ route('user.show', $review->student->user->id) }}" target="_blank">
                                        @if($review->student && $review->student->user)
                                            <img src="{{ $review->student->user->pfp ?? '/images/Placeholder.jpeg' }}" class="w-10 h-10 rounded-full object-cover mr-3 border-2 border-[#755f5420]">
                                        @else
                                            <img src="/images/Placeholder.jpeg" class="w-10 h-10 rounded-full object-cover mr-3 border-2 border-[#755f5420]">
                                        @endif
                                        </a>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <!-- Name -->
                                                <h4 class="font-black text-primary text-sm">{{ $review->student ? $review->student->first_name . ' ' . $review->student->last_name : 'Anonymous' }}</h4>
                                                <!-- Classmate tags -->
                                                @if(($currentSort ?? '') === 'classroom' && Auth::check() && Auth::user()->student && $review->student && $review->student->classroom_id === Auth::user()->student->classroom_id)
                                                    <span class="text-[10px] font-black text-background tracking-widest bg-primary px-2 py-1 rounded-lg">CLASSMATE</span>
                                                @endif
                                            </div>
                                            <!-- Created date -->
                                            <p class="text-[10px] font-bold text-primary/40 tracking-widest">{{ $review->created_at->format('H:i, M d, Y.') }}</p>
                                        </div>
                                    </div>

                                    <div class="ml-[52px]">
                                        <!-- Rating and Title -->
                                        <div class="flex items-center gap-2 mb-1">
                                            <h5 class="font-black text-primary text-sm">{{ $review->title }}</h5>
                                            <div class="flex">
                                                @for ($i = 0; $i < $review->rating; $i++)
                                                    ⭐
                                                @endfor
                                                @for ($i = $review->rating; $i < 5; $i++)
                                                    <span style="color: transparent; text-shadow: 0 0 #c4b5a4;">⭐</span>
                                                @endfor
                                            </div>
                                        </div>

                                        <!-- Description -->
                                        @if($review->description)
                                            <p class="text-sm text-primary/70 leading-relaxed mb-4">{{ $review->description }}</p>
                                        @endif

                                        <!-- Helpful button -->
                                        <div class="flex items-center gap-3 text-xs">
                                            <button 
                                                class="helpful-btn border border-[#755f5420] hover:border-primary text-primary/60 hover:text-primary hover:bg-primary/5 rounded-full px-4 py-1.5 transition-all font-bold"
                                                data-review-id="{{ $review->id }}"
                                                data-upvoted="{{ in_array($review->id, $upvotedReviewIds) ? 'true' : 'false' }}">
                                                Helpful (<span class="upvote-count">{{ $review->upvotes }}</span>)
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <!-- No reviews (empty) -->
                            <div class="py-12 text-center">
                                @if(($currentSort ?? '') === 'classroom')
                                    <p class="font-black text-primary/40 text-sm tracking-wide mb-2">NO CLASSMATE REVIEWS YET</p>
                                    <p class="text-xs text-primary/30 mb-6">Be the first student in your class to review this book!</p>
                                @else
                                    <div class="text-5xl mb-4">📖</div>
                                    <p class="font-black text-primary/40 text-sm tracking-wide mb-2">NO REVIEWS YET</p>
                                    <p class="text-xs text-primary/30 mb-6">Be the first student to share your thoughts about this book!</p>
                                @endif
                                <!-- If not a student -->
                                @if(!$student)
                                    <button disabled class="inline-flex items-center justify-center bg-[#755f5410] border-2 border-[#755f5420] text-primary/40 font-black text-xs tracking-widest py-3 px-6 rounded-xl cursor-not-allowed">
                                        LOG IN TO REVIEW
                                    </button>
                                    <!-- Hasn't read the book -->
                                @elseif(!$hasRead)
                                    <button disabled class="inline-flex items-center justify-center gap-2 bg-[#755f5410] border-2 border-[#755f5420] text-primary/40 font-black text-xs tracking-widest py-3 px-6 rounded-xl cursor-not-allowed" title="Read the book first!">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4">
                                        <path fill-rule="evenodd" d="M12 1.5a5.25 5.25 0 00-5.25 5.25v3a3 3 0 00-3 3v6.75a3 3 0 003 3h10.5a3 3 0 003-3v-6.75a3 3 0 00-3-3v-6.75a3 3 0 00-3-3v-3c0-2.9-2.35-5.25-5.25-5.25zm3.75 8.25v-3a3.75 3.75 0 10-7.5 0v3h7.5z" clip-rule="evenodd" />
                                        </svg>
                                        READ BOOK TO REVIEW
                                    </button>
                                @else
                                    <a href="{{ url('/books/' . $book->id . '/review') }}" class="inline-block bg-white border-2 border-[#755f5420] hover:border-primary text-primary font-black text-xs tracking-widest py-3 px-6 rounded-xl transition-all hover:-translate-y-0.5 shadow-sm">
                                        WRITE THE FIRST REVIEW
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {

        // Filter/sort dropdown

        // get sort dropdown
        var sortSelect = document.getElementById('review-sort');
        
        if (sortSelect) {
            sortSelect.addEventListener('change', function() {
                // get selected value
                var selectedSort = this.value;

                // update url with sort parameter
                var currentUrl = new URL(window.location.href);
                currentUrl.searchParams.set('sort', selectedSort);

                // reload page with new sort filter
                window.location.href = currentUrl.toString();
            });
        }

        // Upvote buttons

        // get all helpful buttons
        var helpfulButtons = document.querySelectorAll('.helpful-btn');
        // get csrf token
        var csrfToken = document.querySelector('meta[name="csrf-token"]');

        // stop if csrf token missing
        if (!csrfToken) {
            console.error('CSRF token meta tag not found');
            return;
        }
        
        // apply active styles
        function setActive(button) {
            button.style.borderColor = '#755f54';
            button.style.backgroundColor = 'rgba(117, 95, 84, 0.1)';
            button.style.color = '#755f54';
            button.style.fontWeight = '800';
        }

        // remove active style
        function setInactive(button) {
            button.style.borderColor = '';
            button.style.backgroundColor = '';
            button.style.color = '';
            button.style.fontWeight = '';
        }

        // loop through each helpful button
        helpfulButtons.forEach(function(button) {
            // get review id and state
            var reviewId = button.getAttribute('data-review-id');
            var countSpan = button.querySelector('.upvote-count');
            var isUpvoted = button.getAttribute('data-upvoted') === 'true';

            // stop spam clicks
            var isProcessing = false;

            // set initial state if already upvoted
            if (isUpvoted) {
                setActive(button);
            }

            // on click
            button.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                // block if there is a request already in progress
                if (isProcessing) return;
                isProcessing = true;

                // while loading, reduce opacity
                button.style.opacity = '0.6';

                var currentCount = parseInt(countSpan.textContent) || 0;

                // if upvoted
                if (isUpvoted) {
                    // remove upvote
                    countSpan.textContent = Math.max(0, currentCount - 1);
                    setInactive(button);
                } else {
                    // add upvote
                    countSpan.textContent = currentCount + 1;
                    setActive(button);
                }

                // send request
                var xhr = new XMLHttpRequest();

                // post request to upvote route
                xhr.open('POST', '/books/reviews/' + reviewId + '/upvote', true);

                // set headers
                xhr.setRequestHeader('Content-Type', 'application/json');
                xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken.getAttribute('content'));
                xhr.setRequestHeader('Accept', 'application/json');

                // handle response
                xhr.onload = function() {
                    button.style.opacity = '1';

                    if (xhr.status === 200) {
                        try {
                            var data = JSON.parse(xhr.responseText);
                            if (data.success) {
                                // update count from server
                                countSpan.textContent = data.upvotes;
                                isUpvoted = data.upvoted;

                                // update styles
                                if (isUpvoted) {
                                    setActive(button);
                                } else {
                                    setInactive(button);
                                }
                            }
                        } catch (parseError) {
                            // reset on parse error
                            console.error('Failed to parse response:', parseError);
                            countSpan.textContent = currentCount;
                            if (isUpvoted) { setActive(button); } else { setInactive(button); }
                        }
                    } else if (xhr.status === 401) {
                        // not logged in
                        countSpan.textContent = currentCount;
                        if (isUpvoted) { setActive(button); } else { setInactive(button); }
                        alert('You must be logged in to vote');
                    } else {
                        // other error
                        console.error('Upvote failed: HTTP ' + xhr.status);
                        countSpan.textContent = currentCount;
                        if (isUpvoted) { setActive(button); } else { setInactive(button); }
                    }

                    // unlock the button
                    isProcessing = false;
                };

                // network error fallback
                xhr.onerror = function() {
                    button.style.opacity = '1';
                    console.error('Upvote request failed');

                    // reset ui
                    countSpan.textContent = currentCount;
                    if (isUpvoted) { setActive(button); } else { setInactive(button); }
                    isProcessing = false;
                };

                // send request
                xhr.send();
            });
        });
    });
    </script>
</x-layout>