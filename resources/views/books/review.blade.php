<x-layout :title="($existingReview ? 'Edit Review - ' : 'Review ') . html_entity_decode($book->title ?? '', ENT_QUOTES)">
    <div class="w-full max-w-6xl mx-auto px-6 md:px-10 py-8 flex flex-col gap-6">
        
        <!-- Back button -->
        <a href="{{ url('/books/' . $book->id) }}" class="text-primary/60 font-bold text-xs tracking-widest flex items-center gap-2 hover:text-primary transition-colors w-fit">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
            </svg>
            BACK TO BOOK
        </a>

        <!-- Review form -->
        <div class="bg-white border border-[#755f5420] rounded-3xl p-6 md:p-10 shadow-sm flex flex-col md:flex-row gap-8 lg:gap-12 relative overflow-hidden">
            
            <!-- Book cover -->
            <div class="w-full md:w-1/3 lg:w-1/4 flex-shrink-0">
                <div class="relative w-full aspect-[2/3] bg-[#755f540a] border border-[#755f5410] rounded-2xl overflow-hidden shadow-md">
                    @if($book->cover_id && str_starts_with($book->cover_id, 'LOCAL_'))
                        @php $imagePath = str_replace('LOCAL_', '', $book->cover_id); @endphp
                        <img src="{{ asset('storage/' . $imagePath) }}" alt="{{ html_entity_decode($book->title ?? '', ENT_QUOTES) }}" class="absolute inset-0 w-full h-full object-cover rounded-xl shadow-sm">
                    @elseif($book->cover_id && str_starts_with($book->cover_id, 'PLACEHOLDER_'))
                        @php $bgColour = str_replace('PLACEHOLDER_', '', $book->cover_id); @endphp
                        <div class="absolute inset-0 w-full h-full rounded-xl shadow-sm flex items-center justify-center p-4 text-center" style="background-color: {{ $bgColour }};">
                            <span class="font-black text-white text-sm drop-shadow-md line-clamp-4">{{ html_entity_decode($book->title ?? '', ENT_QUOTES) }}</span>
                        </div>
                    @elseif($book->cover_id)
                        <img src="https://books.google.com/books/content?id={{ $book->cover_id }}&printsec=frontcover&img=1&zoom=1" alt="{{ html_entity_decode($book->title ?? '', ENT_QUOTES) }}" class="absolute inset-0 w-full h-full object-cover rounded-xl shadow-sm">
                    @else
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="font-bold text-primary/30 text-xs tracking-widest -rotate-12">NO COVER</span>
                        </div>
                    @endif
                </div>

                <!-- Book info under cover -->
                <div class="mt-4 text-center">
                    <h3 class="font-black text-primary text-lg">{{ html_entity_decode($book->title ?? '', ENT_QUOTES) }}</h3>
                    <p class="text-xs font-bold text-primary/50 uppercase tracking-widest mt-1">{{ html_entity_decode($book->author ?? '', ENT_QUOTES) }}</p>
                </div>

                <!-- Editing review -->
                @if($existingReview)
                    <div class="mt-6 bg-amber-50 border border-amber-200 rounded-xl p-4 text-center">
                        <p class="text-xs font-black text-amber-700 tracking-widest mb-1">EDITING YOUR REVIEW</p>
                        <p class="text-[10px] text-amber-600">Originally posted {{ $existingReview->created_at->format('M d, Y') }}</p>
                        @if($existingReview->updated_at->gt($existingReview->created_at))
                            <p class="text-[10px] text-amber-600">Last edited {{ $existingReview->updated_at->format('M d, Y') }}</p>
                        @endif
                    </div>

                    <!-- Delete review -->
                    <div class="mt-4">
                        <form action="{{ url('/books/' . $book->id . '/review/' . $existingReview->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete your review? This cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full bg-red-50 border-2 border-red-200 hover:border-red-400 hover:bg-red-100 text-red-600 font-black text-xs tracking-widest py-3 px-4 rounded-xl transition-all flex items-center justify-center gap-2">
                                DELETE REVIEW
                            </button>
                        </form>
                    </div>
                @endif
            </div>

            <!-- Review form -->
            <div class="flex-1 flex flex-col gap-6">
                <div>
                    <h1 class="text-3xl md:text-4xl font-black text-primary leading-tight">
                        {{ $existingReview ? 'Edit Your Review' : 'Write Your Review' }}
                    </h1>
                    <p class="text-sm text-primary/50 mt-2">
                        {{ $existingReview ? 'Update your thoughts about this book' : 'Share your thoughts about this book with other students' }}
                    </p>
                </div>

                <!-- Error messages -->
                @if(session('error'))
                    <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl p-4 text-sm font-bold">
                        {{ session('error') }}
                    </div>
                @endif

                @if(session('success'))
                    <div class="bg-green-50 border border-green-200 text-green-700 rounded-xl p-4 text-sm font-bold">
                        {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl p-4 text-sm">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $error)
                                <li class="font-bold">{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Rating -->
                <div class="bg-[#755f540a] border border-[#755f5420] rounded-2xl p-5">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <h3 class="text-xs font-black text-primary/60 tracking-widest">YOUR RATING</h3>
                            <div class="flex space-x-1" id="rating-container">
                                <!-- Rating emojis -->
                            </div>
                        </div>
                        <a href="#" class="text-primary/60 hover:text-primary text-xs font-black tracking-widest transition" id="clear-rating">RESET</a>
                    </div>
                </div>

                <!-- Form -->
                <form action="{{ url('/books/' . $book->id . '/review') }}" method="POST" class="space-y-6">
                    @csrf
                    <input type="hidden" id="rating-input" name="rating" value="{{ old('rating', $existingReview->rating ?? 1) }}">

                    <!-- Title -->
                    <div>
                        <label for="title" class="block text-[10px] font-black text-primary/60 tracking-widest mb-2">REVIEW TITLE</label>
                        <input type="text" id="title" name="title" value="{{ old('title', $existingReview->title ?? '') }}" class="w-full p-4 border border-[#755f5420] rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition text-sm font-medium text-primary bg-[#755f540a]" placeholder="Summarise your experience" minlength="1" maxlength="80" required>
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="reviewtext" class="block text-[10px] font-black text-primary/60 tracking-widest mb-2">REVIEW DETAILS</label>
                        <textarea id="reviewtext" name="description" class="w-full p-4 border border-[#755f5420] rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition resize-none text-sm font-medium text-primary bg-[#755f540a]" placeholder="What did you enjoy about this book? Would you recommend it to others?" rows="5" minlength="10" maxlength="280" required>{{ old('description', $existingReview->description ?? '') }}</textarea>
                        <p class="text-[10px] font-bold text-primary/30 mt-1 text-right"><span id="char-count">0</span>/280</p>
                    </div>

                    <!-- Buttons -->
                    <div class="flex gap-4 pt-2">
                        <a href="{{ url('/books/' . $book->id) }}" class="flex-1 bg-[#755f540a] border-2 border-[#755f5420] hover:border-primary/40 text-primary font-black text-xs tracking-widest py-4 px-6 rounded-xl transition-all hover:-translate-y-0.5 flex items-center justify-center gap-2">
                            CANCEL
                        </a>
                        <button type="submit" class="flex-1 bg-primary hover:bg-primary/90 text-white font-black text-xs tracking-widest py-4 px-6 rounded-xl shadow-md transition-all hover:-translate-y-0.5 flex items-center justify-center gap-2">
                            {{ $existingReview ? '✏️ UPDATE REVIEW' : '⭐ SUBMIT REVIEW' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // get elements
        const container = document.getElementById("rating-container");
        const clearButton = document.getElementById("clear-rating");
        const ratingInput = document.getElementById("rating-input");
        let selectedRating = parseInt(ratingInput.value) || 1;
        const grayStyle = "color: transparent; text-shadow: 0 0 #c4b5a4;";
        
        // update rating display
        function updateDisplay(rating) {
            const emojis = container.querySelectorAll("span");
            emojis.forEach((emoji, index) => {
                if (index + 1 <= rating) {
                    emoji.style.cssText = "";
                } else {
                    emoji.style.cssText = grayStyle;
                }
            });
        }

        // create rating emojis
        function createRatingEmojis() {
            container.innerHTML = "";
            for (let i = 1; i <= 5; i++) {
                const emoji = document.createElement("span");
                emoji.textContent = "⭐";
                emoji.className = "text-2xl cursor-pointer transition-transform hover:scale-110";
                emoji.dataset.value = i;

                // on mouse enter
                emoji.addEventListener("mouseenter", function() {
                    updateDisplay(parseInt(this.dataset.value));
                });

                // on click
                emoji.addEventListener("click", function() {
                    // set rating value
                    selectedRating = parseInt(this.dataset.value);
                    ratingInput.value = selectedRating;
                    updateDisplay(selectedRating);
                });

                container.appendChild(emoji);
            }
        }

        // mouse leave
        container.addEventListener("mouseleave", function() {
            updateDisplay(selectedRating);
        });

        // on click clear
        clearButton.addEventListener("click", function(e) {
            e.preventDefault();
            selectedRating = 1;
            ratingInput.value = 1;
            updateDisplay(1);
        });

        createRatingEmojis();
        updateDisplay(selectedRating);

        // character counter
        const textarea = document.getElementById("reviewtext");
        const charCount = document.getElementById("char-count");
        
        textarea.addEventListener("input", function() {
            charCount.textContent = textarea.value.length;
        });
        
        charCount.textContent = textarea.value.length;
    </script>
</x-layout>