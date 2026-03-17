<x-layout title="Explore Books">

    <!-- Invisible filter form -->
    <form action="{{ route('explore') }}" method="GET" id="explore-form"></form>

    <div class="w-full px-4 sm:px-6 md:px-10 py-6 sm:py-8 flex flex-col gap-6 lg:gap-8">

        <!-- Top bar -->
        <div class="bg-white border border-[#755f5420] rounded-3xl p-4 sm:p-5 flex flex-col sm:flex-row items-center justify-between gap-4 shadow-sm w-full">
            <div class="flex items-center justify-center sm:justify-start gap-4 w-full sm:w-auto">
                <div class="text-sm font-bold text-primary/70 flex items-center gap-2">
                    <span class="flex items-center justify-center w-12 h-12 rounded-full  bg-secondary text-white text-xs">{{ $books->total() }}</span>
                    Books found
                </div>
                @csrf
                    @if(session('success'))
                        <div class="mx-6 mt-4 p-3 bg-green-100 text-green-700 rounded-xl text-xs font-bold text-center">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="mx-6 mt-4 p-3 bg-red-100 text-red-700 rounded-xl text-xs font-bold">
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>- {{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
            </div>
            <!-- Right side of top bar -->
            <div class="flex items-center justify-between sm:justify-end gap-3 w-full sm:w-auto">
                <span class="text-[10px] font-bold text-primary/60 tracking-widest hidden sm:inline shrink-0">SORT BY</span>
                <!-- part of the explore-form -->
                <select name="sort" form="explore-form" onchange="submitFilters()" class="w-full sm:w-auto bg-[#755f540a] border border-[#755f5420] text-primary text-xs font-bold rounded-xl px-4 py-3 outline-none cursor-pointer focus:border-primary focus:ring-0 shadow-sm transition-colors">
                    <option value="level-low"   {{ request('sort') == 'level-low'   ? 'selected' : '' }}>Level: Low to High</option>
                    <option value="level-high"  {{ request('sort') == 'level-high'  ? 'selected' : '' }}>Level: High to Low</option>
                    <option value="newest"      {{ request('sort') == 'newest'      ? 'selected' : '' }}>Newest Added</option>
                    <option value="a-z"         {{ request('sort') == 'a-z'         ? 'selected' : '' }}>Title: A to Z</option>
                    <option value="author-a-z"  {{ request('sort') == 'author-a-z'  ? 'selected' : '' }}>Author: A to Z</option>
                    <option value="author-z-a"  {{ request('sort') == 'author-z-a'  ? 'selected' : '' }}>Author: Z to A</option>
                    
                    <!-- custom sort (for teachers/admins) -->
                    @if (Auth::user()?->isTeacher() || Auth::user()?->isAdmin())
                        <option value="custom" {{ request('sort') == 'custom' ? 'selected' : '' }}>Created Books First</option>
                    @endif
                </select>
            </div>
        </div>

        <!-- Main -->
        <div class="flex flex-col lg:flex-row gap-6 lg:gap-8 items-start">

            <!--=========== FILTERS ===========-->
            <aside class="w-full lg:max-w-[320px] shrink-0 flex flex-col gap-5">

                <div class="bg-white border border-[#755f5420] rounded-3xl shadow-sm overflow-hidden">

                    <!-- Header -->
                    <div class="px-5 py-4 border-b border-[#755f5420] bg-white/50 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 text-primary">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />
                        </svg>
                        <h2 class="text-xs font-black text-primary tracking-widest">FILTERS</h2>
                    </div>

                    <div class="p-5 space-y-7">

                        <!-- Available online toggle -->
                        <div>
                            <label class="block text-[10px] font-bold text-primary/60 tracking-widest mb-3">AVAILABLE ONLINE</label>
                            <label class="cursor-pointer flex items-center gap-3">
                                <!-- part of the explore-form -->
                                <input type="checkbox" name="readable" form="explore-form" value="1" {{ request('readable') == '1' ? 'checked' : '' }} onchange="submitFilters()" class="peer hidden">
                                <div class="w-10 h-6 bg-[#755f5420] rounded-full peer-checked:bg-green-500 relative transition-colors shadow-inner border border-[#755f5410]">
                                    <div class="w-4 h-4 bg-white rounded-full absolute top-1 left-1 peer-checked:translate-x-4 transition-transform shadow-sm"></div>
                                </div>
                                <span class="text-xs font-black text-primary/70 peer-checked:text-primary transition-colors">Online books only</span>
                            </label>
                        </div>

                        <!-- Search title -->
                        <div>
                            <label class="block text-[10px] font-bold text-primary/60 tracking-widest mb-2">SEARCH TITLE</label>
                            <div class="relative">
                                <!-- part of the explore-form -->
                                <input type="text" id="searchInput" name="search" form="explore-form" value="{{ request('search') }}" oninput="debounceSearch()" placeholder="e.g. The Gruffalo..." class="w-full rounded-2xl border border-[#755f5420] bg-[#755f540a] px-4 py-3 pl-10 text-sm font-bold text-primary focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all placeholder:text-primary/30">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 text-primary/30 absolute left-3.5 top-1/2 -translate-y-1/2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                                </svg>
                            </div>
                        </div>

                        <!-- Search author -->
                        <div>
                            <label class="block text-[10px] font-bold text-primary/60 tracking-widest mb-2">SEARCH AUTHOR</label>
                            <div class="relative">
                                <!-- part of the explore-form -->
                                <input type="text" id="authorInput" name="author" form="explore-form" value="{{ request('author') }}" oninput="debounceSearch()" placeholder="e.g. Julia Donaldson..." class="w-full rounded-2xl border border-[#755f5420] bg-[#755f540a] px-4 py-3 pl-10 text-sm font-bold text-primary focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all placeholder:text-primary/30">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 text-primary/30 absolute left-3.5 top-1/2 -translate-y-1/2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                </svg>
                            </div>
                        </div>

                        <!-- Level range -->
                        <div>
                            <label class="block text-[10px] font-bold text-primary/60 tracking-widest mb-2">LEVEL RANGE (1–20)</label>
                            <div class="flex items-center gap-2">
                                <!-- part of the explore-form -->
                                <input type="number" name="level_min" form="explore-form" value="{{ request('level_min') }}" min="1" max="20" placeholder="Min" onchange="submitFilters()" class="w-1/2 rounded-2xl border border-[#755f5420] bg-[#755f540a] px-3 py-3 text-center text-sm font-bold text-primary focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all">
                                <span class="text-primary/30 font-black">–</span>
                                <input type="number" name="level_max" form="explore-form" value="{{ request('level_max') }}" min="1" max="20" placeholder="Max" onchange="submitFilters()" class="w-1/2 rounded-2xl border border-[#755f5420] bg-[#755f540a] px-3 py-3 text-center text-sm font-bold text-primary focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all">
                            </div>
                        </div>

                        <!-- Genres -->
                        <div>
                            <label class="block text-[10px] font-bold text-primary/60 tracking-widest mb-3">GENRES</label>
                            <div class="flex flex-wrap gap-2">
                                @foreach($genres as $genre)
                                    <label class="cursor-pointer">
                                        <!-- part of the explore-form -->
                                        <input type="checkbox" class="peer hidden" name="genre[]" form="explore-form" value="{{ $genre->slug }}" {{ in_array($genre->slug, request('genre', [])) ? 'checked' : '' }} onchange="submitFilters()">
                                        <span class="px-3 py-1.5 rounded-full text-[11px] font-bold border border-[#755f5420] bg-white text-primary/70 peer-checked:border-primary peer-checked:bg-primary peer-checked:text-white transition-all shadow-sm block hover:-translate-y-0.5 select-none">
                                            {{ $genre->name }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Phonics -->
                        @if(isset($phonics) && count($phonics) > 0)
                        <div>
                            <label class="block text-[10px] font-bold text-primary/60 tracking-widest mb-3">PHONICS</label>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($phonics as $phonic)
                                    <label class="cursor-pointer">
                                        <input type="checkbox" class="peer hidden" name="phonic[]" form="explore-form" value="{{ $phonic->id }}" {{ in_array($phonic->id, request('phonic', [])) ? 'checked' : '' }} onchange="submitFilters()">
                                        <span class="min-w-[32px] h-8 px-2 flex items-center justify-center rounded-lg text-[11px] font-black border border-[#755f5420] bg-white text-primary/70 peer-checked:border-primary peer-checked:bg-primary peer-checked:text-white transition-all shadow-sm hover:-translate-y-0.5 select-none">
                                            {{ $phonic->sound }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <!-- Clear -->
                        <a href="{{ route('explore') }}" class="flex items-center justify-center font-bold bg-primary px-6 py-3 rounded-2xl text-xs tracking-widest text-white hover:bg-secondary transition-colors text-center">
                            CLEAR ALL FILTERS
                        </a>
                    </div>
                </div>

                <!-- ADD BOOKS MANUALLY (TEACHERS ONLY) -->
                @if (Auth::user()?->isTeacher() || Auth::user()?->isAdmin())
                <div class="bg-white border border-[#755f5420] rounded-3xl shadow-sm overflow-hidden mb-6">
                    <!-- header -->
                    <div class="px-5 pt-4 bg-white/50 flex items-center gap-2">
                        <h2 class="text-xs font-black text-gray-500">Can't find a book?</h2>
                    </div>
                    <div class="px-5 py-2 border-b border-[#755f5420] bg-white/50 flex items-center gap-2">
                        <h2 class="text-xs font-black text-primary tracking-widest">ADD BOOKS MANUALLY</h2>
                    </div>
                    <!-- form -->
                    <form action="{{ route('explore.addBook') }}" method="POST" enctype="multipart/form-data" class="space-y-4 pb-4">
                        @csrf
                        <!-- Book title -->
                        <div class="px-6 pt-4">
                            <label for="title" class="text-xs font-bold tracking-widest text-primary/60 mb-2 block">BOOK TITLE</label>
                            <input type="text" name="title" id="title" placeholder="e.g. The Gruffalo" required
                                class="block w-full rounded-xl border border-[#755f5420] bg-[#755f540a] text-primary focus:border-primary focus:ring-2 focus:ring-primary/20 sm:text-sm px-4 py-3 outline-none transition-all">
                        </div>

                        <!-- Author -->
                        <div class="px-6">
                            <label for="author" class="text-xs font-bold tracking-widest text-primary/60 mb-2 block">AUTHOR</label>
                            <input type="text" name="author" id="author" placeholder="e.g. Julia Donaldson" required
                                class="block w-full rounded-xl border border-[#755f5420] bg-[#755f540a] text-primary focus:border-primary focus:ring-2 focus:ring-primary/20 sm:text-sm px-4 py-3 outline-none transition-all">
                        </div>

                        <!-- ORT level -->
                        <div class="px-6">
                            <label for="ort_level" class="text-xs font-bold tracking-widest text-primary/60 mb-2 block">READING LEVEL (0-20)</label>
                            <input type="number" name="ort_level" id="ort_level" min="0" max="20" placeholder="e.g. 4" required
                                class="block w-full rounded-xl border border-[#755f5420] bg-[#755f540a] text-primary focus:border-primary focus:ring-2 focus:ring-primary/20 sm:text-sm px-4 py-3 outline-none transition-all">
                        </div>

                        <!-- Description -->
                        <div class="px-6">
                            <label for="description" class="text-xs font-bold tracking-widest text-primary/60 mb-2 block">DESCRIPTION</label>
                            <textarea name="description" id="description" rows="3" placeholder="A brief summary..."
                                class="block w-full rounded-xl border border-[#755f5420] bg-[#755f540a] text-primary focus:border-primary focus:ring-2 focus:ring-primary/20 sm:text-sm px-4 py-3 outline-none transition-all resize-none"></textarea>
                        </div>

                        <!-- Cover image -->
                        <div class="px-6">
                            <label for="cover_image" class="text-xs font-bold tracking-widest text-primary/60 mb-2 block">COVER (OPTIONAL)</label>
                            <input type="file" name="cover_image" id="cover_image" accept="image/*"
                                class="block w-full text-sm text-primary/70 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:tracking-widest file:bg-primary file:text-white hover:file:bg-secondary file:cursor-pointer transition-all border border-[#755f5420] bg-[#755f540a] rounded-xl cursor-pointer">
                        </div>

                        <!-- Submit button -->
                        <div class="px-6 pt-4">
                            <button type="submit" class="w-full flex items-center justify-center font-bold bg-primary px-6 py-3.5 rounded-2xl text-xs tracking-widest text-white hover:bg-secondary transition-colors text-center">
                                ADD BOOK
                            </button>
                        </div>
                    </form>
                </div>
                @endif
                    
            </aside>

            <!--=========== BOOKS ===========-->
            <div class="flex-1 w-full min-w-0 flex flex-col gap-6">

                <!-- Book grid -->
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-7 gap-3 sm:gap-4 items-stretch">
                    @forelse($books as $book)
                        <div class="bg-white border border-[#755f5420] rounded-3xl p-3 sm:p-3.5 shadow-sm flex flex-col group hover:border-[#755f5430] h-full">

                            <!-- Book cover -->
                            <div class="relative w-full aspect-[2/3] bg-[#755f540a] border border-[#755f5410] rounded-2xl overflow-hidden flex items-center justify-center p-3">
                                
                                @if($book->cover_id && str_starts_with($book->cover_id, 'LOCAL_'))
                                    @php $imagePath = str_replace('LOCAL_', '', $book->cover_id); @endphp
                                    <img src="{{ asset('storage/' . $imagePath) }}" alt="{{ html_entity_decode($book->title ?? '', ENT_QUOTES) }}" class="absolute inset-0 w-full h-full object-cover rounded-xl shadow-sm group-hover:scale-105 transition-transform duration-500">
                                @elseif($book->cover_id && str_starts_with($book->cover_id, 'PLACEHOLDER_'))
                                    @php $bgColor = str_replace('PLACEHOLDER_', '', $book->cover_id); @endphp
                                    <div class="absolute inset-0 w-full h-full rounded-xl shadow-sm group-hover:scale-105 transition-transform duration-500 flex items-center justify-center p-4 text-center" style="background-color: {{ $bgColor }};">
                                        <span class="font-black text-white text-sm drop-shadow-md line-clamp-4">{{ html_entity_decode($book->title ?? '', ENT_QUOTES) }}</span>
                                    </div>
                                @elseif($book->cover_id)
                                    <img src="https://books.google.com/books/content?id={{ $book->cover_id }}&printsec=frontcover&img=1&zoom=1" alt="{{ html_entity_decode($book->title ?? '', ENT_QUOTES) }}" class="absolute inset-0 w-full h-full object-cover rounded-xl group-hover:scale-105 transition-transform duration-500">
                                @else
                                    <span class="font-bold text-primary/30 text-xs tracking-widest -rotate-12">NO COVER</span>
                                @endif

                                <!-- Reading level badge -->
                                @php
                                    $bgClass   = 'bg-level-'   . str_replace(' ', '', $book->ort_level);
                                    $textClass = 'text-level-' . str_replace(' ', '', $book->ort_level ?? '0');
                                @endphp
                                <div class="absolute top-2.5 right-2.5 {{ $bgClass }} {{ $textClass }} text-[9px] sm:text-[10px] font-black px-2 sm:px-3 py-1 sm:py-1.5 rounded-full border-2 border-white flex items-center gap-1 z-10" style="background-color: {{ $book->ort_colour ?? '' }}">
                                    LVL {{ $book->ort_level }}
                                </div>

                                <!-- Available online badge for non custom books -->
                                @if($book->ol_key && !str_starts_with($book->ol_key, 'NO_OL_'))
                                    <div class="absolute bottom-2.5 left-2.5 bg-green-500 text-white text-[8px] sm:text-[9px] font-black tracking-widest px-2 sm:px-2.5 py-1 rounded-full border-2 border-white z-10">
                                        ONLINE
                                    </div>
                                @endif

                                <!-- Delete button -->
                                @if((Auth::user()?->isTeacher() || Auth::user()?->isAdmin()) && str_starts_with($book->ol_key, 'NO_OL_CUSTOM_'))
                                    <form action="{{ route('explore.deleteBook', $book->id) }}" method="POST" class="absolute top-2.5 left-2.5 z-20" onsubmit="return confirm('Are you sure you want to permanently delete this locally created book?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="bg-red-500/90 hover:bg-red-600 text-white p-1.5 rounded-full border border-white transition-colors" title="Delete Custom Book">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
                                        </button>
                                    </form>
                                @endif

                            </div>

                            <!-- Title and author -->
                            <div class="mt-4 px-1 flex-1 flex flex-col">
                                <h3 class="text-sm font-black text-primary leading-tight line-clamp-2 min-h-[36px] break-words" title="{{ html_entity_decode($book->title ?? '', ENT_QUOTES) }}">{{ html_entity_decode($book->title ?? '', ENT_QUOTES) }}</h3>
                                <p class="text-[10px] font-bold text-primary/50 uppercase tracking-widest mt-1.5 truncate" title="{{ html_entity_decode($book->author ?? '', ENT_QUOTES) }}">{{ html_entity_decode($book->author ?? '', ENT_QUOTES) }}</p>
                            </div>

                            <!-- Actions -->
                            <div class="mt-4 pt-3.5 border-t border-[#755f5410] flex flex-col gap-2">
                                <a href="{{ route('books.show', $book->id) }}" class="w-full flex items-center justify-center bg-[#755f540a] hover:bg-primary hover:text-white text-primary font-black text-xs tracking-widest py-3 rounded-xl transition-colors">
                                    VIEW
                                </a>

                                <div class="flex items-center gap-1.5 sm:gap-2">
                                    <button type="button" class="flex-1 flex items-center justify-center bg-[#755f540a] hover:bg-primary hover:text-white text-primary font-black text-[9px] sm:text-[10px] tracking-widest py-2.5 rounded-xl transition-colors">
                                        QUICK ADD
                                    </button>

                                    <button type="button" class="flex items-center justify-center bg-[#755f540a] hover:bg-primary hover:text-white text-primary p-2.5 rounded-xl transition-colors group" title="Favourite">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 group-hover:fill-current">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full bg-white border border-[#755f5420] rounded-3xl p-6 sm:p-12 flex flex-col items-center justify-center text-center">
                            <span class="text-primary mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                                </svg>
                            </span>
                            <h3 class="text-xl font-black text-primary">No Books Found</h3>
                            <p class="text-sm font-medium text-primary/60 mt-2 max-w-sm">There are no books matching these filters. Try expanding the filters.</p>
                            <a href="{{ route('explore') }}" class="mt-6 px-6 py-3 bg-primary text-white text-xs font-bold tracking-widest rounded-xl hover:bg-secondary transition-colors">
                                CLEAR ALL FILTERS
                            </a>
                        </div>
                    @endforelse
                </div>

                <!-- Pagination -->
                <div class="pt-6 border-t border-primary w-full overflow-x-auto">
                    {{ $books->links() }}
                </div>
            </div>
            <!-- end books -->
        </div>
    </div>

    <script>
        const form = document.getElementById('explore-form');
        const searchInput = document.getElementById('searchInput');
        const authorInput = document.getElementById('authorInput');

        // submit filters
        function submitFilters() {
            const activeEl = document.activeElement;
            // focuses for both search boxes
            if (activeEl && (activeEl.id === 'searchInput' || activeEl.id === 'authorInput')) {
                sessionStorage.setItem('searchFocusId', activeEl.id);
                sessionStorage.setItem('searchCursorPos', activeEl.selectionStart);
            } else {
                sessionStorage.removeItem('searchFocusId');
            }
            form.submit();
        }

        // search timeout / debounce
        let searchTimeout = null;
        function debounceSearch() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => submitFilters(), 600);
        }

        document.addEventListener('DOMContentLoaded', () => {
            const focusId = sessionStorage.getItem('searchFocusId');
            if (focusId) {
                const input = document.getElementById(focusId);
                if (input) {
                    const pos = sessionStorage.getItem('searchCursorPos');
                    input.focus();
                    if (pos) input.setSelectionRange(pos, pos);
                }
                sessionStorage.removeItem('searchFocusId');
                sessionStorage.removeItem('searchCursorPos');
            }
        });
    </script>
</x-layout>