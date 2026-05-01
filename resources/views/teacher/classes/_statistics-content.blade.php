@php($showStatsHeader = $showStatsHeader ?? true)

<!-- HEADER -->
@if($showStatsHeader)
<div class="bg-[#755f540a] border border-[#755f5420] rounded-3xl p-6 md:p-10 relative overflow-hidden shadow-sm">
    <div class="flex flex-col md:flex-row justify-between md:items-center gap-6">
        <!-- STATS -->
        <div>
            <span class="text-xs font-bold text-primary/60 uppercase tracking-widest">Classroom Statistics</span>
            <h1 class="text-4xl md:text-5xl font-display text-primary mt-1">
                {{ $classroom->name }}
                <span class="text-primary/40 text-2xl">#{{ $classroom->id }}</span>
            </h1>
            <p class="text-base md:text-lg font-medium text-primary/70 mt-2">
                Year {{ $classroom->year_group }} &middot; {{ $academicStart }} to {{ $academicEnd }} &middot; {{ $students->count() }} students
            </p>
        </div>
        <!-- DOWNLOAD CSV -->
        <div class="flex flex-col items-start md:items-end gap-2">
            <span class="text-xs font-bold text-primary/60 uppercase tracking-widest pb-4">Download CSV statistics</span>
            <div class="flex gap-2 no-print">
                <a href="{{ route('teacher.classes.exportStatistics', $classroom->id) }}">
                    <span class="px-6 py-3 rounded-full text-sm font-bold bg-primary text-background shadow-sm hover:bg-orange-900 transition-colors">
                        Download CSV
                    </span>
                </a>
            </div>
        </div>
    </div>
</div>
@endif

<!-- TOP STATS -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 lg:gap-6">
    <!-- books read -->
    <div class="bg-primary rounded-3xl p-6 flex flex-col justify-center items-center text-center shadow-md">
        <span class="text-4xl md:text-5xl font-black text-background mb-2">{{ $totalBooks }}</span>
        <span class="text-[10px] md:text-xs font-bold text-background/80 tracking-widest uppercase">Books Read</span>
    </div>
    <!-- most common difficulty -->
    <div class="bg-white border border-primary/10 rounded-3xl p-6 flex flex-col justify-center items-center text-center shadow-sm">
        <span class="text-3xl md:text-4xl font-black text-primary mb-2 leading-tight capitalize">{{ $avgDifficulty }}</span>
        <span class="text-[10px] md:text-xs font-bold text-primary/70 tracking-widest uppercase">Most Common Difficulty</span>
    </div>
    <!-- avg rating -->
    <div class="bg-[#755f5415] border border-primary/10 rounded-3xl p-6 flex flex-col justify-center items-center text-center shadow-sm">
        <span class="text-4xl md:text-5xl font-black text-primary mb-2">{{ $avgRating }}</span>
        <span class="text-[10px] md:text-xs font-bold text-primary/70 tracking-widest uppercase">Average Rating</span>
    </div>
    <!-- reviews written -->
    <div class="bg-white border border-primary/10 rounded-3xl p-6 flex flex-col justify-center items-center text-center shadow-sm">
        <span class="text-4xl md:text-5xl font-black text-primary mb-2">{{ $totalReviews }}</span>
        <span class="text-[10px] md:text-xs font-bold text-primary/70 tracking-widest uppercase">Reviews Written</span>
    </div>
</div>


<!-- READING ACTIVITY -->
<div class="bg-white border border-[#755f5420] rounded-3xl p-6 md:p-8 shadow-sm">
    <h3 class="text-2xl font-display text-primary mb-1">Reading Activity</h3>
    <p class="text-sm text-primary/60 mb-4">Books finished each month across the {{ $academicStart }}/{{ $academicEnd }} academic year</p>
    <div class="relative w-full" style="height: 280px;">
        <canvas id="activityChart"></canvas>
    </div>
</div>

<!-- LEVEL DISTRIBUTION AND READERS LEADERBOARD -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8">

    <div class="bg-[#755f540a] border border-[#755f5420] rounded-3xl p-6 md:p-8 shadow-sm">
        <h3 class="text-2xl font-display text-primary mb-1">Reading Level Distribution</h3>
        <p class="text-sm text-primary/60 mb-4">How many students are at each level</p>
        <div class="relative w-full" style="height: 240px;">
            <canvas id="levelChart"></canvas>
        </div>
    </div>

    <!-- most books finished -->
    <div class="bg-[#755f540a] border border-[#755f5420] rounded-3xl p-6 md:p-8 shadow-sm">
        <h3 class="text-2xl font-display text-primary mb-1">Bookworms</h3>
        <p class="text-sm text-primary/60 mb-4">Most books finished this year</p>
        <div class="flex flex-col gap-3">
            @forelse($topReaders as $i => $reader)
                <div class="flex items-center gap-4 bg-white rounded-2xl p-3 shadow-sm border border-[#755f5410]">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center font-black text-lg
                        @if($i == 0) bg-yellow-400 text-yellow-900
                        @elseif($i == 1) bg-gray-300 text-gray-800
                        @elseif($i == 2) bg-amber-600 text-amber-50
                        @else bg-primary/10 text-primary @endif">
                        {{ $i + 1 }}
                    </div>
                    <img src="{{ asset($reader->pfp ?? '/images/pfp/cat.png') }}" alt="" class="w-10 h-10 rounded-full object-cover ring-2 ring-white shadow-sm">
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-primary truncate">{{ $reader->first_name }} {{ $reader->last_name }}</p>
                        <p class="text-xs text-primary/60 font-semibold">Level {{ $reader->level }}</p>
                    </div>
                    <div class="text-right">
                        <span class="font-black text-primary text-xl">{{ $reader->books_read }}</span>
                        <p class="text-[10px] uppercase tracking-widest font-bold text-primary/60">books</p>
                    </div>
                </div>
            @empty
                <p class="text-primary/60 italic">No reading activity yet.</p>
            @endforelse
        </div>
    </div>

</div>

<!-- LOW BOOK COUNT STUDENTS-->
<div class="bg-amber-50 border-2 border-dashed border-amber-300 rounded-3xl p-6 md:p-8 shadow-sm">
    <h3 class="text-2xl font-display text-amber-900 mb-1">Students that need to read more.</h3>
    <p class="text-sm text-amber-800/70 mb-4">These students have a low number of books read, consider giving them extra support.</p>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
        @forelse($needsEncouragement as $reader)
            <div class="bg-white rounded-2xl p-3 shadow-sm border border-amber-200 flex items-center gap-3">
                <img src="{{ asset($reader->pfp ?? '/images/pfp/cat.png') }}" alt="" class="w-10 h-10 rounded-full object-cover ring-2 ring-white shadow-sm flex-shrink-0">
                <div class="flex-1 min-w-0">
                    <p class="font-bold text-primary truncate text-sm">{{ $reader->first_name }} {{ $reader->last_name }}</p>
                    <p class="text-xs text-primary/60 font-semibold">{{ $reader->books_read }} {{ $reader->books_read == 1 ? 'book' : 'books' }}</p>
                </div>
            </div>
        @empty
            <p class="text-amber-800/70 italic col-span-full">No data to show.</p>
        @endforelse
    </div>
</div>

<!-- WEEKLY GOALS-->
<div class="bg-white border border-[#755f5420] rounded-3xl p-6 md:p-8 shadow-sm">
    <h3 class="text-2xl font-display text-primary mb-1">Weekly Goal Performance</h3>
    <p class="text-sm text-primary/60 mb-4">
        A green week means 75% or more of the class hit their goal. Red weeks mean less than 75% of the class has hit their goals
    </p>
    <div class="relative w-full mb-6" style="height: 220px;">
        <canvas id="weeklyChart"></canvas>
    </div>
</div>

<!-- GENRES AND STUDENT LIST -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8">

    <div class="bg-[#755f540a] border border-[#755f5420] rounded-3xl p-6 md:p-8 shadow-sm">
        <h3 class="text-2xl font-display text-primary mb-1">Genres Explored</h3>
        <p class="text-sm text-primary/60 mb-4">Most read genres across the class</p>
        <div class="flex flex-col gap-4">
            @forelse($genresCount as $genre => $count)
                <div>
                    <div class="flex justify-between items-end mb-1">
                        <span class="font-bold text-primary">{{ $genre }}</span>
                        <span class="text-xs font-black text-primary/60 uppercase tracking-widest">{{ $count }} {{ $count == 1 ? 'book' : 'books' }}</span>
                    </div>
                    <div class="w-full bg-white rounded-full h-3 overflow-hidden shadow-inner">
                        <div class="bg-primary h-full rounded-full" style="width: {{ max(8, ($count / max($genresCount)) * 100) }}%"></div>
                    </div>
                </div>
            @empty
                <p class="text-primary/60 italic">No genre data available.</p>
            @endforelse
        </div>
    </div>
    <!-- all students -->
    <div class="bg-white border border-[#755f5420] rounded-3xl p-6 md:p-8 shadow-sm">
        <h3 class="text-2xl font-display text-primary mb-1">All Students</h3>
        <p class="text-sm text-primary/60 mb-4">Final reading level and books completed</p>
        <div class="flex flex-col gap-2 max-h-[400px] overflow-y-auto pr-2">
            @foreach($studentsWithCounts->sortByDesc('books_read') as $s)
                <div class="flex items-center gap-3 bg-[#755f540a] rounded-xl p-2 border border-[#755f5410]">
                    <img src="{{ asset($s->pfp ?? '/images/pfp/cat.png') }}" alt="" class="w-9 h-9 rounded-full object-cover">
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-primary truncate text-sm">{{ $s->first_name }} {{ $s->last_name }}</p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-level-{{ $s->level }} text-level-{{ $s->level }} shadow-sm">LVL {{ $s->level }}</span>
                    <span class="font-black text-primary text-sm w-12 text-right">{{ $s->books_read }}</span>
                </div>
            @endforeach
        </div>
    </div>

</div>

<!-- PHONICS -->
@if(!empty($showPhonics))
    <div class="bg-[#755f540a] border border-[#755f5420] rounded-3xl p-6 md:p-8 shadow-sm">
        <div class="flex items-start justify-between gap-4 mb-1">
            <div>
                <h3 class="text-2xl font-display text-primary mb-1">Phonics Explored</h3>
                <p class="text-sm text-primary/60 mb-4">Phonic sounds covered in books read by this class</p>
            </div>
            <span class="text-[10px] font-bold text-primary/60 uppercase tracking-widest bg-white border border-[#755f5420] rounded-full px-3 py-1 whitespace-nowrap">
                Levels 1–7
            </span>
        </div>

        @if(count($phonicsCount) > 0)
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                @foreach($phonicsCount as $phonic => $count)
                    <div class="bg-white rounded-2xl p-4 border border-[#755f5410] shadow-sm flex items-center justify-between gap-3">
                        <span class="font-bold text-primary text-lg truncate">{{ $phonic }}</span>
                        <div class="text-right flex-shrink-0">
                            <span class="font-black text-primary text-xl">{{ $count }}</span>
                            <p class="text-[10px] uppercase tracking-widest font-bold text-primary/60 leading-tight">
                                {{ $count == 1 ? 'book' : 'books' }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-primary/60 italic">No phonics data available for completed books yet</p>
        @endif
    </div>
@endif