<x-teacher.layout :classroom="$classroom" :yearGroups="$yearGroups" :title="$classroom->name">
  
  <div class="w-full flex-1 flex flex-col space-y-6">
    
    <!-- ========================================= -->
    <!-- HEADER -->
    <div class="bg-[#755f540a] border border-[#755f5420] rounded-3xl p-6 md:p-8 flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-sm relative overflow-hidden">
      <div class="absolute top-0 right-0 -mt-8 -mr-8 w-40 h-40 bg-orange-200/30 rounded-full blur-3xl pointer-events-none"></div>
      
      <div class="space-y-2 relative z-10">
        <!-- Classroom name -->
        <h1 class="text-3xl md:text-4xl font-black text-primary tracking-tight">
          {{ $classroom->name }}
        </h1>
        <div class="flex items-center gap-3">
          <!-- Year group -->
          <span class="px-4 py-1.5 bg-primary text-background rounded-full text-xs font-bold uppercase tracking-wider shadow-sm">
            {{ $classroom->year_group == 0 ? 'Reception' : 'Year ' . $classroom->year_group }}
          </span>
          <!-- Students -->
          <span class="text-primary/80 font-semibold text-sm">
            {{ $classroom->students_count ?? $classroom->students->count() }} Students
          </span>
        </div>
      </div>
    </div>

    <!-- ========================================= -->
    <!-- ANNOUNCEMENTS -->
    @php
        // Fetch announcements safely without needing models set up
        $announcements = \Illuminate\Support\Facades\DB::table('announcements')
            ->leftJoin('students', 'announcements.student_id', '=', 'students.id')
            ->where('announcements.classroom_id', $classroom->id)
            ->select('announcements.*', 'students.first_name', 'students.last_name')
            ->orderBy('announcements.created_at', 'desc')
            ->get();
    @endphp

    <div class="mt-8">
        <h2 class="text-2xl font-display text-primary tracking-tight mb-4">Class Announcements</h2>
        <div class="space-y-4">
            @forelse($announcements as $announcement)
                <div class="bg-white rounded-2xl p-5 md:p-6 shadow-sm border border-[#755f5420]">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-2 mb-3">
                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold tracking-wider {{ $announcement->student_id ? 'bg-secondary text-background' : 'bg-orange-100 text-orange-800' }}">
                            {{ $announcement->student_id ? 'To - ' . $announcement->first_name . ' ' . $announcement->last_name : 'To - Entire class' }}
                        </span>
                        <span class="text-xs font-medium text-primary/60">
                            {{ \Carbon\Carbon::parse($announcement->created_at)->format('M d, Y \a\t h:i A') }}
                        </span>
                    </div>
                    <p class="text-primary/80 text-sm md:text-base leading-relaxed whitespace-pre-wrap">{{ $announcement->message }}</p>
                </div>
            @empty
                <div class="text-center py-8 bg-[#755f540a] rounded-2xl border border-dashed border-[#755f5430]">
                    <p class="text-primary/60 font-medium">No announcements have been made yet.</p>
                </div>
            @endforelse
        </div>
    </div>

  </div>
</x-teacher.layout>