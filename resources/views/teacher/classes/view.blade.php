<x-teacher.layout :classroom="$classroom" :yearGroups="$yearGroups" :title="$classroom->name">

    <div class="w-full flex-1 flex flex-col space-y-6">

        <!-- ========================================= -->
        <!-- HEADER -->
        <div class="bg-[#755f540a] border border-[#755f5420] rounded-3xl p-6 md:p-8 flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 right-0 -mt-8 -mr-8 w-40 h-40 bg-orange-200/30 rounded-full blur-3xl pointer-events-none"></div>

            <div class="space-y-2 relative z-10">
                <h1 class="text-3xl md:text-4xl font-black text-primary tracking-tight">
                    {{ $classroom->name }}
                </h1>
                <div class="flex items-center gap-3">
                    <span class="px-4 py-1.5 bg-primary text-background rounded-full text-xs font-bold uppercase tracking-wider shadow-sm">
                        {{ $classroom->year_group == 0 ? 'Reception' : 'Year ' . $classroom->year_group }}
                    </span>
                    <span class="text-primary/80 font-semibold text-sm">
                        {{ $classroom->students_count ?? $classroom->students->count() }} Students
                    </span>
                </div>
            </div>

            <!-- Download CSV button -->
            @unless(isset($noData))
                <div class="relative z-10 flex flex-col items-start md:items-end gap-2">
                    <span class="text-xs font-bold text-primary/60 uppercase tracking-widest">Download CSV statistics</span>
                    <a href="{{ route('teacher.classes.exportStatistics', $classroom->id) }}">
                        <span class="px-6 py-3 rounded-full text-sm font-bold bg-primary text-background shadow-sm hover:bg-orange-900 transition-colors inline-block">
                            Download CSV
                        </span>
                    </a>
                </div>
            @endunless
        </div>

        <!-- ========================================= -->
        <!-- ANNOUNCEMENTS -->
        <div>
            <h2 class="text-2xl font-display text-primary tracking-tight mb-4">Class Announcements</h2>
            <div class="space-y-4 max-h-[450px] overflow-y-auto pr-2 [&::-webkit-scrollbar-track]:w-2 [&::-webkit-scrollbar-track]:bg-transparent [&::-webkit-scrollbar-thumb]:bg-orange-200 [&::-webkit-scrollbar-thumb]:rounded-full">
                @forelse($announcements as $announcement)
                    <div class="bg-white rounded-2xl p-5 md:p-6 shadow-sm border border-[#755f5420] relative group">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-2 mb-3 pr-20">
                            <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold tracking-wider {{ $announcement->student_id ? 'bg-secondary text-background' : 'bg-orange-100 text-orange-800' }}">
                                {{ $announcement->student_id ? 'To - ' . $announcement->first_name . ' ' . $announcement->last_name : 'To - Entire class' }}
                            </span>
                            <span class="text-xs font-medium text-primary/60">
                                {{ \Carbon\Carbon::parse($announcement->created_at)->format('M d, Y \a\t h:i A') }}
                            </span>
                        </div>
                        <p class="text-primary/80 text-sm md:text-base leading-relaxed whitespace-pre-wrap">{{ $announcement->message }}</p>

                        <!-- Delete button -->
                        <form action="{{ route('teacher.announcements.delete', $announcement->id) }}"
                                method="POST"
                                class="absolute top-5 right-5 opacity-0 group-hover:opacity-100 transition-opacity"
                                onsubmit="return confirm('Are you sure you want to delete this announcement? This cannot be undone.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-black p-1 flex items-center gap-1">
                                <span class="text-xs font-bold tracking-wider">DELETE</span>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </form>
                    </div>
                @empty
                    <div class="text-center py-8 bg-[#755f540a] rounded-2xl border border-dashed border-[#755f5430]">
                        <p class="text-primary/60 font-medium">No announcements have been made yet.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- ========================================= -->
        <!-- STATISTICS SECTION -->
        @unless(isset($noData))
            <div class="pt-4">
                <h2 class="text-2xl font-display text-primary tracking-tight mb-4">Class Statistics</h2>
                <div class="flex flex-col gap-6 lg:gap-8">
                    {{-- Hide the duplicate stats header since we already have one above --}}
                    @include('teacher.classes._statistics-content', ['showStatsHeader' => false])
                </div>
            </div>
        @else
            <div class="bg-white border border-[#755f5420] rounded-3xl p-8 text-center shadow-sm">
                <h3 class="text-xl font-display text-primary mb-2">No statistics yet</h3>
                <p class="text-primary/60">Once students start completing books, statistics will appear here.</p>
            </div>
        @endunless

    </div>

    @unless(isset($noData))
        @include('teacher.classes._statistics-scripts')
    @endunless
</x-teacher.layout>