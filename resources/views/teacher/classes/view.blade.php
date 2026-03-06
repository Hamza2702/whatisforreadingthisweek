<x-teacher.layout :yearGroups="$yearGroups" :title="$classroom->name">
  
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
  </div>
</x-teacher.layout>