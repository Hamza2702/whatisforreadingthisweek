@php
  $classCount   = collect($yearGroups ?? [])->count();
  $studentTotal = collect($yearGroups ?? [])->sum('students');
  // get teacher and class counts
  $htTeacherCount = 0;
  $htClassCount = 0;
  if (auth()->check() && auth()->user()->role === 'headteacher') {
      $schoolId = auth()->user()->school_id;
      $htTeacherCount = \App\Models\User::where('school_id', $schoolId)->whereIn('role', ['teacher', 'headteacher'])->count();
      $htClassCount = \App\Models\Classroom::where('school_id', $schoolId)->count();
  }
@endphp
@props(['title' => '', 'yearGroups' => [], 'classroom' => null])

<x-layout :classroom="$classroom" :yearGroups="$yearGroups" title="{{ $title ?? 'Teacher Dashboard' }}">
  <div class="w-full px-6 md:px-10 lg:px-16 py-8 flex-1 flex flex-col justify-center">
    
    <!-- Header grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

      <!-- ========================================= -->
      <!-- WELCOME PROFILE -->
      <div class="lg:col-span-8 bg-[#755f540a] border border-[#755f5420] rounded-2xl p-6 md:p-8 flex flex-col md:flex-row justify-between items-center shadow-sm relative overflow-hidden">
        
        <!-- welcome card information  -->
        <div class="flex items-center gap-5 relative z-10 w-full md:w-auto">
          <div class="relative flex-shrink-0">
            <img src="{{ asset(Auth::user()->pfp ?? '/images/Placeholder.jpeg') }}" alt="Profile Picture" class="w-20 h-20 md:w-24 md:h-24 rounded-full ring-4 ring-white shadow-md object-cover">
          </div>
          <div class="space-y-1 text-primary text-left">
            <h2 class="text-2xl md:text-3xl font-display tracking-tight">
              Welcome back, {{ explode(' ', Auth::user()->name)[0] }}!
            </h2>
            <div class="flex flex-wrap items-center gap-2 text-xs md:text-sm">
              <span class="opacity-70">{{ Auth::user()->email }}</span>
              <span class="opacity-70 hidden md:block">|</span>
              <span class="opacity-70">{{ Auth::user()->school?->name ?? 'No School Assigned' }}</span>
            </div>
          </div>
        </div>

        <!-- SIDE GRID -->
        <div class="relative z-10 text-left md:text-right mt-6 md:mt-0 w-full md:w-auto">
          <!-- dashboard -->
          @if (request()->routeIs('teacher.index'))
            <h2 class="text-lg md:text-3xl font-sans text-primary">Your Classes</h2>
            <p class="text-xs md:text-sm text-primary/70 mt-1">Manage classrooms, view progress, and generate reading lists</p>
          <!-- create class -->
          @elseif (request()->routeIs('teacher.classes.create'))
            <h2 class="text-lg md:text-3xl font-sans text-primary">Create Class</h2>
            <p class="text-xs md:text-sm text-primary/70 mt-1">Set up a classroom!</p>
          <!-- banned books -->
          @elseif (request()->routeIs('headteacher.banned-books'))
            <h2 class="text-lg md:text-3xl font-sans text-primary">Manage Banned Books</h2>
            <p class="text-xs md:text-sm text-primary/70 mt-1">Manage banned books for your school</p>
          <!-- create teacher page -->
          @elseif (request()->routeIs('headteacher.teachers.create'))
            <h2 class="text-lg md:text-3xl font-sans text-primary">Create Teacher</h2>
            <p class="text-xs md:text-sm text-primary/70 mt-1">Set up a staff account!</p>
            <!-- students page -->
          @elseif (request()->routeIs('teacher.classes.students') && $classroom)
            <!-- inactive classroom -->
            @if (!$classroom->active)
              <h2 class="text-lg md:text-3xl font-sans text-primary">{{ $classroom->name }} is archived</h2>
              <div class="flex items-center md:justify-end gap-3 mt-1 text-primary text-xs md:text-sm">
                <span>{{ str_replace(['/', '-'], ' to ', $classroom->academic_year) }}</span>
                <span class="text-priamry/60">{{ $classroom->students_count ?? $classroom->students->count() }} Students</span>
              </div>
          @else
          <!-- active classroom -->
              <h2 class="text-lg md:text-3xl font-sans text-primary">{{ $classroom->name }}</h2>
              <div class="flex items-center md:justify-end gap-3 mt-1 text-primary/70 text-xs md:text-sm">
                <span class="font-bold">{{ $classroom->year_group == 0 ? 'Reception' : 'Year ' . $classroom->year_group}}</span>
                <span>{{ $classroom->students_count ?? $classroom->students->count() }} Students</span>
              </div>
          @endif
            <!-- add students page -->
          @elseif (request()->routeIs('teacher.classes.addStudents') && $classroom)
            <h2 class="text-lg md:text-3xl font-sans text-primary">{{ $classroom->name }}</h2>
            <p class="text-xs md:text-sm text-primary/70 mt-1">Add students to the class</p>
          @endif
        </div>

        <!-- Decorative Worm -->
        <div class="worm absolute -bottom-3 -right-3 opacity-50 z-0">
          <img src="/images/home/wormMovement1.png" alt="Worm" class="h-10 md:h-12">
        </div>
      </div>

      <!-- ========================================= -->
      <!-- SIDE GRID -->
      <div class="lg:col-span-4 grid grid-cols-2 gap-3 md:gap-4">
        <!-- =================== INDEX / CREATE PAGE =================== -->
        @if (request()->routeIs('teacher.index') || request()->routeIs('teacher.classes.create'))
          
          @if(auth()->user()->role === 'headteacher')
          
          <!-- =================== HEADTEACHER =================== -->
            <!-- create teacher -->
          @if (request()->routeIs('teacher.index'))
            <a href="{{ route('headteacher.teachers.create') }}" class="bg-primary rounded-xl p-4 flex flex-col justify-center items-center text-center shadow-md transition hover:-translate-y-1 hover:bg-secondary">
              <span class="text-xs font-bold text-background tracking-widest leading-tight">CREATE<br>TEACHER</span>
            </a>
          @else
            <!-- back to dashboard -->
            <a href="{{ route('teacher.index') }}" class="bg-primary rounded-xl p-4 flex flex-col justify-center items-center text-center shadow-md transition hover:-translate-y-1 hover:bg-secondary">
              <span class="text-xs font-bold text-background tracking-widest leading-tight">BACK TO<br>DASHBOARD</span>
            </a>
          @endif
          <!-- managed banned books -->
          <a href="{{ route('headteacher.banned-books') }}" class="bg-primary rounded-xl p-4 flex flex-col justify-center items-center text-center shadow-md transition hover:-translate-y-1 hover:bg-secondary">
            <span class="text-xs font-bold text-background tracking-widest leading-tight">MANAGE<br>BANNED BOOKS</span>
          </a>

          <!-- teachers stats -->
          <div class="bg-[#755f5415] border border-primary/10 rounded-xl p-4 flex flex-col justify-center items-center text-center shadow-sm">
            <span class="text-3xl font-black text-primary">{{ $htTeacherCount }}</span>
            <span class="text-[10px] font-bold text-primary/80 tracking-widest mt-1">TEACHERS</span>
          </div>

          <!-- classes stats -->
          <div class="bg-[#755f5415] border border-primary/10 rounded-xl p-4 flex flex-col justify-center items-center text-center shadow-sm">
            <span class="text-3xl font-black text-primary">{{ $htClassCount }}</span>
            <span class="text-[10px] font-bold text-primary/70 tracking-widest mt-1">CLASSES</span>
          </div>

        @else

          <!-- =================== TEACHER =================== -->
          
            <!-- create class -->
          @if (request()->routeIs('teacher.index'))
            <a href="{{ route('teacher.classes.create') }}" class="bg-primary rounded-xl p-4 flex flex-col justify-center items-center text-center shadow-md transition hover:-translate-y-1 hover:bg-secondary">
              <span class="text-xs font-bold text-background tracking-widest leading-tight">CREATE CLASS</span>
            </a>
          @elseif (request()->routeIs('teacher.classes.create'))
            <!-- back to dashboard -->
            <a href="{{ route('teacher.index') }}" class="bg-primary rounded-xl p-4 flex flex-col justify-center items-center text-center shadow-md transition hover:-translate-y-1 hover:bg-secondary">
              <span class="text-xs font-bold text-background tracking-widest leading-tight">BACK TO<br>DASHBOARD</span>
            </a>
          @endif

          <!-- classes stats -->
          <div class="bg-[#755f5415] border border-primary/10 rounded-xl p-4 flex flex-col justify-center items-center text-center shadow-sm">
            <span class="text-3xl font-black text-primary">{{ $classCount }}</span>
            <span class="text-[10px] font-bold text-primary/80 tracking-widest mt-1">CLASSES</span>
          </div>

          <!-- students stats -->
          <div class="bg-[#755f5415] border border-primary/10 rounded-xl p-4 flex flex-col justify-center items-center text-center shadow-sm">
            <span class="text-3xl font-black text-primary">{{ $studentTotal }}</span>
            <span class="text-[10px] font-bold text-primary/70 tracking-widest mt-1">STUDENTS</span>
          </div>

        @endif

        <!-- =================== STUDENTS PAGE =================== -->
        @elseif (request()->routeIs('teacher.classes.students'))

          <!-- back to dashboard -->
          <a href="{{ route('teacher.index') }}" class="bg-primary rounded-xl p-4 flex flex-col justify-center items-center text-center shadow-md transition hover:-translate-y-1 hover:bg-secondary">
              <span class="text-xs font-bold text-background tracking-widest leading-tight">BACK TO<br>DASHBOARD</span>
          </a>
          <!-- create students -->
          <a href="{{ route('teacher.classes.addStudents', $classroom->id) }}" class="bg-primary rounded-xl p-4 flex flex-col justify-center items-center text-center shadow-md transition hover:-translate-y-1 hover:bg-secondary">
            <span class="text-xs font-bold text-background tracking-widest leading-tight">CREATE<br>STUDENTS</span>
          </a>
          
          @if ($classroom->students()->count() > 0)
            <!-- export csv -->
            <a href="{{ route('teacher.classes.export', $classroom->id) }}" class="bg-primary rounded-xl p-4 flex flex-col justify-center items-center text-center shadow-md transition hover:-translate-y-1 hover:bg-secondary">
              <span class="text-xs font-bold text-background tracking-widest leading-tight">EXPORT<br>CSV</span>
            </a>
            @if (!$classroom->active)
              <!-- something -->
            @else
              <!-- archive classroom -->
              <form action="{{ route('teacher.classes.archiveClassroom', $classroom->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to archive this classroom?');">
                @csrf
                @method('PATCH')
                <button type="submit" class="bg-gray-500 rounded-xl p-4 flex flex-col justify-center items-center text-center shadow-md transition hover:-translate-y-1 hover:bg-black/80 w-full h-full cursor-pointer border-none outline-none">
                    <span class="text-xs font-bold text-background tracking-widest leading-tight">ARCHIVE<br>CLASSROOM</span>
                </button>
              </form>
            @endif
          @endif
        <!-- =================== HEADTEACHER PAGES =================== -->
        @elseif (request()->routeIs('headteacher.teachers.create'))
          <a href="{{ route('teacher.index') }}" class="bg-primary rounded-xl p-4 flex flex-col justify-center items-center text-center shadow-md transition hover:-translate-y-1 hover:bg-secondary">
            <span class="text-xs font-bold text-background tracking-widest leading-tight">BACK TO<br>DASHBOARD</span>
          </a>
        @elseif (request()->routeIs('headteacher.banned-books'))
          <a href="{{ route('teacher.index') }}" class="bg-primary rounded-xl p-4 flex flex-col justify-center items-center text-center shadow-md transition hover:-translate-y-1 hover:bg-secondary">
            <span class="text-xs font-bold text-background tracking-widest leading-tight">BACK TO<br>DASHBOARD</span>
          </a>
        @elseif (request()->routeIs('teacher.classes.view'))
        <!-- =================== CLASSROOM VIEW PAGE =================== -->
          <!-- back to class -->
          <a href="{{ route('teacher.index', $classroom->id) }}" class="col-span-2 bg-primary rounded-xl p-4 flex flex-col justify-center items-center text-center shadow-md transition hover:-translate-y-1 hover:bg-secondary">
            <span class="text-xs font-bold text-background tracking-widest leading-tight">← BACK TO VIEW</span>
          </a>
          <!-- CREATE ANNOUNCEMENT BUTTON -->
          <a href="{{ route('teacher.classes.announcements.create', $classroom->id) }}" class="col-span-2 bg-primary rounded-xl p-4 flex flex-col justify-center items-center text-center shadow-md transition hover:-translate-y-1 hover:bg-secondary">
            CREATE ANNOUNCEMENT
          </a>
        <!-- =================== STUDENTS CREATE PAGE =================== -->
        @elseif (request()->routeIs('teacher.classes.addStudents'))
          
          <!-- back to class -->
          <a href="{{ route('teacher.classes.students', $classroom->id) }}" class="col-span-2 bg-primary rounded-xl p-4 flex flex-col justify-center items-center text-center shadow-md transition hover:-translate-y-1 hover:bg-secondary">
            <span class="text-xs font-bold text-background tracking-widest leading-tight">← BACK TO CLASS</span>
          </a>

          <!-- add students -->
          <div class="col-span-2 bg-[#755f5415] border border-primary/10 rounded-xl p-4 flex flex-col items-center gap-3 shadow-sm">
            <span class="text-[10px] font-bold text-primary/80 tracking-widest uppercase text-center w-full border-b border-primary/10 pb-2">
              How many students?
            </span>
            
            <!-- add students input -->
            <input type="number" id="student_count" min="1" max="50" step="1" value="1" required class="w-full rounded-xl border-2 border-white bg-white/50 px-2 py-3 text-3xl font-black text-primary text-center focus:border-primary focus:bg-white focus:ring-0 shadow-sm transition-colors">
            
            <!-- add students preset -->
            <div class="grid grid-cols-3 gap-2 w-full mt-1">
              <button type="button" id="setten" class="rounded-lg bg-white border border-primary/10 text-primary font-black shadow-sm hover:bg-orange-50 hover:border-primary/40 transition-all text-sm py-2 hover:-translate-y-0.5">+10</button>
              <button type="button" id="settwenty" class="rounded-lg bg-white border border-primary/10 text-primary font-black shadow-sm hover:bg-orange-50 hover:border-primary/40 transition-all text-sm py-2 hover:-translate-y-0.5">+20</button>
              <button type="button" id="setthirty" class="rounded-lg bg-white border border-primary/10 text-primary font-black shadow-sm hover:bg-orange-50 hover:border-primary/40 transition-all text-sm py-2 hover:-translate-y-0.5">+30</button>
            </div>
          </div>        
        </div>        
      </div>
        <!-- =================== STUDENTS READING LIST =================== -->
        <!-- @elseif (request()->routeIs('teacher.classes.reading-list'))
          ... -->
        @endif
      </div>
    </div>

    <!-- =================== PAGE CONTENT =================== -->
    <div class="mt-8">
      {{ $slot }}
    </div>

  </div>
</x-layout>