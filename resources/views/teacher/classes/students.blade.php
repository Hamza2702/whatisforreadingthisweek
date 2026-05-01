<x-teacher.layout :classroom="$classroom" :yearGroups="$yearGroups" title="Manage">
  
  <div>
    <!-- error/success messages -->
    @if(session('success'))
      <div class="mb-4 bg-green-100 border-2 border-green-500 text-green-700 px-4 py-3 rounded-lg font-bold flex items-center gap-2">
        {{ session('success') }}
      </div>
    @endif

    @if(session('error'))
      <div class="mb-4 bg-red-100 border-2 border-red-500 text-red-700 px-4 py-3 rounded-lg font-bold flex items-center gap-2">
        {{ session('error') }}
      </div>
    @endif
    <h2 class="text-xl font-bold text-primary mb-4 px-2">Classroom Students</h2>

    <!-- ========================================= -->
    <!-- STUDENT GRID -->
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-8 2xl:grid-cols-5 gap-4 auto-rows-fr">
      
      <!-- import students card -->
      <div class="col-span-1 border-2 border-dashed border-[#755f5440] bg-white/40 hover:bg-white/80 rounded-3xl p-4 sm:p-5 flex flex-col justify-center items-center text-center shadow-sm transition-all duration-100 min-h-[240px]">
        <form action="{{ route('teacher.classes.importStudents', $classroom?->id) }}" method="POST" enctype="multipart/form-data" class="w-full flex flex-col items-center h-full justify-between gap-3">
          @csrf
          
          <span class="text-xs font-black text-primary uppercase tracking-widest">
            Import Students
          </span>

          <!-- File input -->
          <div class="relative w-full flex-1 flex flex-col">
            <input type="file" name="students_csv" id="grid_students_csv" accept=".csv,.txt" required class="hidden" onchange="document.getElementById('gridFileNameDisplay').textContent = this.files[0] ? this.files[0].name : 'Click to browse...'">
            
            <!-- Clickable area -->
            <button type="button" onclick="document.getElementById('grid_students_csv').click()" class="w-full h-full min-h-[80px] border-2 border-dashed border-primary/30 rounded-xl p-2 bg-white/60 hover:bg-white hover:border-primary/60 transition-all flex flex-col items-center justify-center gap-1 group">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-6 text-primary/40 group-hover:text-primary/70 transition-colors">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
              </svg>
              <span id="gridFileNameDisplay" class="text-primary/70 font-bold text-[11px] truncate w-full px-2">Upload File</span>
            </button>
          </div>
          
          <!-- Error messages -->
          @error('students_csv')
            <p class="text-red-600 text-[10px] font-bold bg-red-100 px-2 py-1 rounded-md w-full">{{ $message }}</p>
          @enderror

          <!-- Submit Button -->
          <button type="submit" class="w-full bg-primary text-background font-bold rounded-xl px-3 py-2.5 shadow-sm hover:bg-orange-900 transition-colors text-xs">
            Import
          </button>

          <!-- Template download -->
          <a title="Imported files must follow this format" href="{{ asset('template/student_import_template.csv') }}" class="text-[10px] font-semibold text-primary/60 hover:text-primary underline mt-1">
            Download CSV Template
          </a>
        </form>
      </div>

      <!-- Students -->
      @forelse($students as $s)
        @php
          $cardStyle = $s->is_exceptional
              ? 'border-2 border-dashed border-green-400 bg-white' 
              : 'border border-[#755f5420] bg-white';
        @endphp

        <!-- Student card -->
        <div class="{{ $cardStyle }} h-full rounded-3xl flex flex-col items-center text-center shadow-sm hover:shadow-md group relative">

          <!-- avatar -->
          <div class="relative mb-3 mt-2 flex-shrink-0">
            <img class="h-16 w-16 rounded-full object-cover ring-4 ring-white shadow-sm" src="{{ asset($s->pfp ?? '/images/pfp/cat.png') }}" alt="{{ $s->first_name }}">
          </div>

           <!-- level -->
          <span class="bg-level-{{ $s->level ?? '0' }} text-level-{{ $s->level ?? '0' }} text-[10px] font-black px-2 py-0.5 rounded-full border-2 border-white shadow-sm">
            LVL {{ $s->level }}
          </span>

          <!-- student info -->
          <div class="w-full flex-1 flex flex-col justify-center">
            <h3 class="font-bold text-primary text-sm truncate w-full" title="{{ $s->first_name }} {{ $s->last_name }}">
              {{ $s->first_name }} {{ $s->last_name }}
            </h3>
            <!-- username -->
            <p class="text-[11px] text-primary/60 font-semibold truncate w-full">
              {{ '@' . $s->user->username }}
            </p>
            
          </div>

          <!-- Buttons -->
          <div class="pt-2 border-t border-[#755f5415] w-full grid grid-cols-4 gap-1">
            
            <!-- view -->
            <a href="{{ route('user.show', $s->user->id) }}" title="View Profile" class="flex flex-col items-center justify-center p-2 text-primary/50 hover:text-primary hover:bg-orange-50 rounded-xl transition">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
              </svg>
              <span class="text-[10px] font-bold mt-1">View</span>
            </a>

            <!-- manage students -->
            <a href="{{ route('user.manage', $s->user->id) }}" title="Manage Student" class="flex flex-col items-center justify-center p-2 text-primary/50 hover:text-primary hover:bg-orange-50 rounded-xl transition">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
              </svg>
              <span class="text-[10px] font-bold mt-1">Manage</span>
            </a>

            <!-- transfer -->
            <form action="{{ route('teacher.classes.transferStudent', [$classroom->id, $s->id]) }}" 
                  method="POST" 
                  class="m-0 w-full"
                  onsubmit="return handleTransfer(event, this, '{{ $s->first_name }} {{ $s->last_name }}', '{{ $classroom->name }}')">
              @csrf
              @method('DELETE')
              <button type="submit" title="Transfer student" class="w-full flex flex-col items-center justify-center p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M22 10.5h-6m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM4 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 10.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                </svg>
                <span class="text-[10px] font-bold mt-1">Transfer</span>
              </button>
            </form>

            <!-- delete -->
            <form action="{{ route('teacher.classes.removeStudent', [$classroom->id, $s->id]) }}" method="POST" class="m-0 w-full"
              onsubmit="return confirm('Remove this student? {{ $s->first_name }} {{ $s->last_name }} will no longer be part of {{ $classroom->name }} and will not exist as a user of Bookworms.')">
              @csrf
              @method('DELETE')
              <button type="submit" title="Remove Student" class="w-full flex flex-col items-center justify-center p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                </svg>
                <span class="text-[10px] font-bold mt-1">Delete</span>
              </button>
            </form>
          </div>
        </div>

      @empty
        <!-- if classroom is empty -->
        <div class="col-span-full md:col-span-2 lg:col-span-4 xl:col-span-6 2xl:col-span-8 bg-[#755f540a] border-2 border-dashed border-[#755f5430] rounded-3xl p-8 flex flex-col items-center justify-center text-center">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-10 text-primary/30 mb-3">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
          </svg>
          <h3 class="text-lg font-bold text-primary">Your classroom is empty</h3>
          <p class="text-primary/60 text-sm mt-1 max-w-sm">
            Use the import box on the left to add students via CSV, or click the "Create Students" button in the menu above!
          </p>
        </div>
      @endforelse

    </div>
  </div>
  <script>
  function handleTransfer(event, form, studentName, className) {
      if (!confirm(`Transfer ${studentName}? A CSV file will be downloaded first, then they will be removed from ${className}.`)) {
          return false;
      }
      // let form submit normally then refresh page after delay
      setTimeout(() => window.location.reload(), 2000);
      return true;
  }
  </script>
</x-teacher.layout>