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
                <div class="mt-auto pt-4 flex flex-col sm:flex-row gap-4">
                    
                    @if(!str_starts_with($book->ol_key, 'NO_OL_'))
                        <!-- openlibrary interactive reader -->
                        <div class="flex flex-col">
                            <a href="https://archive.org/details/{{ $book->ol_key }}/mode/2up?view=theater" target="_blank" rel="noopener noreferrer" class="flex-1 bg-green-500 hover:bg-green-600 text-white font-black text-xs tracking-widest py-4 px-6 rounded-xl shadow-md transition-all hover:-translate-y-0.5 flex items-center justify-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                                </svg>
                                READ BOOK ONLINE
                            </a>
                            <p class="text-primary mt-2 text-xs">Need help with borrowing books?<a href="https://help.archive.org/help/borrowing-from-the-lending-library/#:~:text=How%20do%20I%20get%20set%20up%20to%20borrow%20books%20through%20archive.org%3F" target="_blank" class="font-black underline-offset-4 underline ml-1">Check this out</a></p>
                        </div>
                    @endif
                    
                    <button class="flex-1 bg-white border-2 border-[#755f5420] hover:border-primary hover:text-secondary text-primary font-black text-xs tracking-widest py-4 px-6 rounded-xl shadow-sm transition-all hover:-translate-y-0.5 flex items-center justify-center gap-2 h-fit">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                        </svg>
                        ADD TO FAVOURITE
                    </button>
                </div>
            </div>
        </div>

    </div>
</x-layout>