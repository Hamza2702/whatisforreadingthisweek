<x-teacher.layout title="Manage Banned Books">

    <!-- Invisible filter form -->
    <form action="{{ route('headteacher.banned-books') }}" method="GET" id="filter-form"></form>
    <!-- success messages -->
    @if(session('success'))
        <div class="mb-6 p-4 bg-green-100 border border-green-300 text-green-800 rounded-2xl font-bold flex items-center gap-2 shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="size-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="w-full flex flex-col gap-6 lg:gap-8">

        <!-- Top bar -->
        <div class="bg-white border border-[#755f5420] rounded-3xl p-4 sm:p-5 flex flex-col sm:flex-row items-center justify-between gap-4 shadow-sm w-full">
            <div class="flex flex-wrap items-center justify-center sm:justify-start gap-4 w-full sm:w-auto">
                <div class="text-sm font-bold text-primary/70 flex items-center gap-2 shrink-0">
                    <span class="flex items-center justify-center w-12 h-12 rounded-full bg-secondary text-white text-xs">{{ $books->total() }}</span>
                    Books found
                </div>
            </div>
            
            <!-- Sort filters -->
            <div class="flex items-center justify-between sm:justify-end gap-3 w-full sm:w-auto">
                <span class="text-[10px] font-bold text-primary/60 tracking-widest hidden sm:inline shrink-0">SORT BY</span>
                <select name="sort" form="filter-form" onchange="submitFilters()" class="w-full sm:w-auto bg-[#755f540a] border border-[#755f5420] text-primary text-xs font-bold rounded-xl px-4 py-3 outline-none cursor-pointer focus:border-primary focus:ring-0 shadow-sm transition-colors">
                    <option value="level-low"   {{ request('sort') == 'level-low'   ? 'selected' : '' }}>Level: Low to High</option>
                    <option value="level-high"  {{ request('sort') == 'level-high'  ? 'selected' : '' }}>Level: High to Low</option>
                    <option value="newest"      {{ request('sort') == 'newest'      ? 'selected' : '' }}>Newest Added</option>
                    <option value="a-z"         {{ request('sort') == 'a-z'         ? 'selected' : '' }}>Title: A to Z</option>
                    <option value="author-a-z"  {{ request('sort') == 'author-a-z'  ? 'selected' : '' }}>Author: A to Z</option>
                    <option value="author-z-a"  {{ request('sort') == 'author-z-a'  ? 'selected' : '' }}>Author: Z to A</option>
                    <option value="custom"      {{ request('sort') == 'custom'      ? 'selected' : '' }}>Created Books First</option>
                </select>
            </div>
        </div>

        <!-- Main -->
        <div class="flex flex-col lg:flex-row gap-6 lg:gap-8 items-start">
            <!--=========== FILTERS SIDEBAR ===========-->
            <aside class="w-full lg:max-w-[320px] shrink-0 flex flex-col gap-5">

                <div class="bg-white border border-[#755f5420] rounded-3xl shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-[#755f5420] bg-white/50 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 text-primary">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />
                        </svg>
                        <h2 class="text-xs font-black text-primary tracking-widest">FILTERS</h2>
                    </div>

                    <div class="p-5 space-y-7">
                        
                        <!-- Ban filter status -->
                        <div>
                            <label class="block text-[10px] font-bold text-primary/60 tracking-widest mb-2">BAN STATUS</label>
                            <select name="status" form="filter-form" onchange="submitFilters()" class="w-full bg-[#755f540a] border border-[#755f5420] text-primary text-sm font-bold rounded-2xl px-4 py-3 outline-none cursor-pointer focus:border-primary focus:ring-2 focus:ring-primary/20 transition-colors">
                                <option value="" {{ request('status') == '' ? 'selected' : '' }}>Show all books</option>
                                <option value="banned" {{ request('status') == 'banned' ? 'selected' : '' }}>All banned books</option>
                                <option value="restricted" {{ request('status') == 'restricted' ? 'selected' : '' }}>Unreadable only</option>
                                <option value="hidden" {{ request('status') == 'hidden' ? 'selected' : '' }}>Hidden only</option>
                                <option value="unbanned" {{ request('status') == 'unbanned' ? 'selected' : '' }}>Only unbanned books</option>
                            </select>
                        </div>

                        <!-- Available online toggle -->
                        <div>
                            <label class="block text-[10px] font-bold text-primary/60 tracking-widest mb-3">AVAILABLE ONLINE</label>
                            <label class="cursor-pointer flex items-center gap-3">
                                <input type="checkbox" name="readable" form="filter-form" value="1" {{ request('readable') == '1' ? 'checked' : '' }} onchange="submitFilters()" class="peer hidden">
                                <div class="w-10 h-6 bg-[#755f5420] rounded-full peer-checked:bg-green-500 relative transition-colors shadow-inner border border-[#755f5410] after:content-[''] after:absolute after:top-1 after:left-1 after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-transform after:shadow-sm peer-checked:after:translate-x-4"></div>
                                <span class="text-xs font-black text-primary/70 peer-checked:text-primary transition-colors">Online books only</span>
                            </label>
                        </div>

                        <!-- Search title -->
                        <div>
                            <label class="block text-[10px] font-bold text-primary/60 tracking-widest mb-2">SEARCH TITLE</label>
                            <div class="relative">
                                <input type="text" id="searchInput" name="search" form="filter-form" value="{{ request('search') }}" oninput="debounceSearch()" placeholder="e.g. Matilda" class="w-full rounded-2xl border border-[#755f5420] bg-[#755f540a] px-4 py-3 pl-10 text-sm font-bold text-primary focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all placeholder:text-primary/30">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 text-primary/30 absolute left-3.5 top-1/2 -translate-y-1/2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                                </svg>
                            </div>
                        </div>

                        <!-- Search author -->
                        <div>
                            <label class="block text-[10px] font-bold text-primary/60 tracking-widest mb-2">SEARCH AUTHOR</label>
                            <div class="relative">
                                <input type="text" id="authorInput" name="author" form="filter-form" value="{{ request('author') }}" oninput="debounceSearch()" placeholder="e.g. Roald Dahl" class="w-full rounded-2xl border border-[#755f5420] bg-[#755f540a] px-4 py-3 pl-10 text-sm font-bold text-primary focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all placeholder:text-primary/30">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 text-primary/30 absolute left-3.5 top-1/2 -translate-y-1/2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                </svg>
                            </div>
                        </div>

                        <!-- Level range -->
                        <div>
                            <label class="block text-[10px] font-bold text-primary/60 tracking-widest mb-2">LEVEL RANGE (1–20)</label>
                            <div class="flex items-center gap-2">
                                <input type="number" name="level_min" form="filter-form" value="{{ request('level_min') }}" min="1" max="20" placeholder="Min" onchange="submitFilters()" class="w-1/2 rounded-2xl border border-[#755f5420] bg-[#755f540a] px-3 py-3 text-center text-sm font-bold text-primary focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all">
                                <span class="text-primary/30 font-black">–</span>
                                <input type="number" name="level_max" form="filter-form" value="{{ request('level_max') }}" min="1" max="20" placeholder="Max" onchange="submitFilters()" class="w-1/2 rounded-2xl border border-[#755f5420] bg-[#755f540a] px-3 py-3 text-center text-sm font-bold text-primary focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all">
                            </div>
                        </div>

                        <!-- Genres -->
                        <div>
                            <label class="block text-[10px] font-bold text-primary/60 tracking-widest mb-3">GENRES</label>
                            <div class="flex flex-wrap gap-2">
                                @foreach($genres as $genre)
                                    <label class="cursor-pointer">
                                        <input type="checkbox" class="peer hidden" name="genre[]" form="filter-form" value="{{ $genre->slug }}" {{ in_array($genre->slug, request('genre', [])) ? 'checked' : '' }} onchange="submitFilters()">
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
                                        <input type="checkbox" class="peer hidden" name="phonic[]" form="filter-form" value="{{ $phonic->id }}" {{ in_array($phonic->id, request('phonic', [])) ? 'checked' : '' }} onchange="submitFilters()">
                                        <span class="min-w-[32px] h-8 px-2 flex items-center justify-center rounded-lg text-[11px] font-black border border-[#755f5420] bg-white text-primary/70 peer-checked:border-primary peer-checked:bg-primary peer-checked:text-white transition-all shadow-sm hover:-translate-y-0.5 select-none">
                                            {{ $phonic->sound }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <!-- Clear -->
                        <a href="{{ route('headteacher.banned-books') }}" class="flex items-center justify-center font-bold bg-primary px-6 py-3 rounded-2xl text-xs tracking-widest text-white hover:bg-secondary transition-colors text-center">
                            CLEAR ALL FILTERS
                        </a>
                    </div>
                </div>
            </aside>

            <!--=========== BOOKS ===========-->
            <div class="flex-1 w-full min-w-0 flex flex-col gap-6">

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-7 gap-3 sm:gap-4 items-stretch">
                    @forelse($books as $book)
                        @php
                            // check if the book is banned for this school
                            $bannedRecord = $school->bannedBooks->firstWhere('id', $book->id);
                            $isBanned = $bannedRecord !== null;
                            $banType = $isBanned ? $bannedRecord->pivot->ban_type : null;
                        @endphp

                        <div class="bg-white border {{ $isBanned ? ($banType === 'hide' ? 'border-red-500 bg-red-50/50' : 'border-orange-400 bg-orange-50/50') : 'border-[#755f5420] hover:border-[#755f5430]' }} rounded-3xl p-3 sm:p-3.5 shadow-sm flex flex-col group transition-all duration-200 h-full">

                            <!-- Book cover -->
                            <div class="relative w-full aspect-[2/3] bg-[#755f540a] border border-[#755f5410] rounded-2xl overflow-hidden flex items-center justify-center p-3 {{ $isBanned ? 'opacity-60 grayscale group-hover:grayscale-0 transition-all' : '' }}">
                                
                                <!-- if book is banned -->
                                @if($isBanned)
                                    <div class="absolute inset-0 {{ $banType === 'hide' ? 'bg-red-900/10' : 'bg-orange-900/10' }} z-20 pointer-events-none"></div>
                                @endif

                                @if($book->cover_id && str_starts_with($book->cover_id, 'LOCAL_'))
                                    @php $imagePath = str_replace('LOCAL_', '', $book->cover_id); @endphp
                                    <img src="{{ asset('storage/' . $imagePath) }}" alt="{{ html_entity_decode($book->title ?? '', ENT_QUOTES) }}" class="absolute inset-0 w-full h-full object-cover rounded-2xl shadow-sm group-hover:scale-105 transition-transform duration-500">
                                @elseif($book->cover_id && str_starts_with($book->cover_id, 'PLACEHOLDER_'))
                                    @php $bgColour = str_replace('PLACEHOLDER_', '', $book->cover_id); @endphp
                                    <div class="absolute inset-0 w-full h-full rounded-2xl shadow-sm group-hover:scale-105 transition-transform duration-500 flex items-center justify-center p-4 text-center" style="background-color: {{ $bgColour }};">
                                        <span class="font-black text-white text-sm drop-shadow-md line-clamp-4">{{ html_entity_decode($book->title ?? '', ENT_QUOTES) }}</span>
                                    </div>
                                @elseif($book->cover_id)
                                    <img src="https://books.google.com/books/content?id={{ $book->cover_id }}&printsec=frontcover&img=1&zoom=1" alt="{{ html_entity_decode($book->title ?? '', ENT_QUOTES) }}" class="absolute inset-0 w-full h-full object-cover rounded-2xl group-hover:scale-105 transition-transform duration-500">
                                @else
                                    <span class="font-bold text-primary/30 text-xs tracking-widest -rotate-12">NO COVER</span>
                                @endif

                                <!-- Reading level badge -->
                                @php
                                    $bgClass   = 'bg-level-'   . str_replace(' ', '', $book->ort_level);
                                    $textClass = 'text-level-' . str_replace(' ', '', $book->ort_level ?? '0');
                                @endphp
                                <div class="absolute top-2.5 right-2.5 {{ $bgClass }} {{ $textClass }} text-[9px] sm:text-[10px] font-black px-2 sm:px-3 py-1 sm:py-1.5 rounded-full border-2 border-white flex items-center gap-1 z-30" style="background-color: {{ $book->ort_colour ?? '#000' }}">
                                    LVL {{ $book->ort_level }}
                                </div>

                                <!-- Available online badge -->
                                @if($book->ol_key && !str_starts_with($book->ol_key, 'NO_OL_'))
                                    <div class="absolute bottom-2.5 left-2.5 bg-green-500 text-white text-[8px] sm:text-[9px] font-black tracking-widest px-2 sm:px-2.5 py-1 rounded-full border-2 border-white z-10">
                                        ONLINE
                                    </div>
                                @endif

                            </div>

                            <!-- Title and author -->
                            <div class="mt-4 px-1 flex-1 flex flex-col">
                                <h3 class="text-sm font-black text-primary leading-tight line-clamp-2 min-h-[36px] break-words" title="{{ html_entity_decode($book->title ?? '', ENT_QUOTES) }}">{{ html_entity_decode($book->title ?? '', ENT_QUOTES) }}</h3>
                                <p class="text-[10px] font-bold text-primary/50 uppercase tracking-widest mt-1.5 truncate" title="{{ html_entity_decode($book->author ?? '', ENT_QUOTES) }}">{{ html_entity_decode($book->author ?? '', ENT_QUOTES) }}</p>
                            </div>

                            <!-- Actions -->
                            <div class="mt-4 pt-3.5 border-t border-[#755f5410] flex flex-col gap-2">
                                <form action="{{ route('headteacher.toggle-ban', $book->id) }}" method="POST" class="w-full m-0">
                                    @csrf
                                    @if($isBanned)
                                        <div class="text-[9px] text-center font-bold uppercase tracking-widest {{ $banType === 'hide' ? 'text-red-500' : 'text-orange-500' }} mb-1">
                                            Currently: {{ $banType === 'hide' ? 'Hidden Entirely' : 'Unreadable' }}
                                        </div>
                                        <button type="submit" name="action" value="unban" class="w-full py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-black text-[10px] sm:text-[11px] uppercase tracking-widest rounded-xl transition-colors shadow-sm flex items-center justify-center gap-1.5">
                                            UNBAN BOOK
                                        </button>
                                    @else
                                        <button type="submit" name="action" value="restrict" class="w-full flex items-center justify-center bg-orange-50 hover:bg-orange-500 hover:text-white text-orange-600 font-black text-[9px] sm:text-[10px] tracking-widest py-2 rounded-xl transition-colors mb-1.5" title="Keep visible, disable reading">
                                            MAKE UNREADABLE
                                        </button>
                                        <button type="submit" name="action" value="hide" class="w-full flex items-center justify-center bg-red-50 hover:bg-red-500 hover:text-white text-red-600 font-black text-[9px] sm:text-[10px] tracking-widest py-2 rounded-xl transition-colors" title="Hide book entirely from the school">
                                            HIDE ENTIRELY
                                        </button>
                                    @endif
                                </form>
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
                            <p class="text-sm font-medium text-primary/60 mt-2 max-w-sm">There are no books matching your current search or filters.</p>
                            <a href="{{ route('headteacher.banned-books') }}" class="mt-6 px-6 py-3 bg-primary text-white text-xs font-bold tracking-widest rounded-xl hover:bg-secondary transition-colors">
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
        </div>
    </div>
    <script>
        // get form
        const form = document.getElementById('filter-form');
        
        // submit filters
        function submitFilters() {
            const activeEl = document.activeElement;
            if (activeEl && (activeEl.id === 'searchInput' || activeEl.id === 'authorInput')) {
                sessionStorage.setItem('htSearchFocusId', activeEl.id);
                sessionStorage.setItem('htSearchCursorPos', activeEl.selectionStart);
            } else {
                sessionStorage.removeItem('htSearchFocusId');
            }
            form.submit();
        }
        let searchTimeout = null;
        function debounceSearch() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => submitFilters(), 1000);
        }

        document.addEventListener('DOMContentLoaded', () => {
            const focusId = sessionStorage.getItem('htSearchFocusId');
            if (focusId) {
                const input = document.getElementById(focusId);
                if (input) {
                    const pos = sessionStorage.getItem('htSearchCursorPos');
                    input.focus();
                    if (pos) input.setSelectionRange(pos, pos);
                }
                sessionStorage.removeItem('htSearchFocusId');
                sessionStorage.removeItem('htSearchCursorPos');
            }
        });
    </script>
</x-teacher.layout>