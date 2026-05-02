<x-teacher.layout :yearGroups="$yearGroups" title="Teacher Dashboard">

<!-- schooladmin SECTION -->
  @if(auth()->user()->role === 'schooladmin' && isset($schooladminStats))
    @if(session('error'))
    <div class="bg-red-100 border border-red-300 text-red-800 rounded-2xl p-4 mb-6 font-semibold">
      {{ session('error') }}
    </div>
  @endif
  <div class="bg-white rounded-3xl p-6 md:p-8 shadow-sm border border-red-900/10 mb-8 space-y-6 relative overflow-hidden">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 relative z-10">
      <div>
        <h2 class="text-2xl font-black text-primary">School Admin Dashboard</h2>
        <p class="text-primary/60 text-sm">School teachers</p>
      </div>
    </div>

    <!-- Teacher list -->
    <div class="flex flex-col gap-3 overflow-y-auto max-h-[250px] pr-2 pb-2 bg-[#755f540a] border border-[#755f5420] rounded-3xl p-5 [&::-webkit-scrollbar-track]:w-2 [&::-webkit-scrollbar-track]:bg-transparent  [&::-webkit-scrollbar-thumb]:rounded-full relative z-10">
      
      @foreach($schooladminStats['teachers_data'] as $teacher)
      <div class="bg-white rounded-2xl p-4 shadow-sm border relative flex-shrink-0 flex flex-col md:flex-row md:items-center justify-between gap-4 group transition-colors hover:bg-gray-50/50">
        
        <!-- Teacher Info -->
        <div class="flex items-center gap-3">
            <img src="{{ $teacher->pfp ?? '/images/Placeholder.jpeg' }}" class="w-10 h-10 md:w-12 md:h-12 rounded-full object-cover ring-2 ring-white shadow-sm">
            <div class="flex flex-col">
                <span class="text-sm md:text-base font-bold text-primary leading-tight">
                    {{ $teacher->name }}
                </span>
            </div>
        </div>

        <!-- Year groups -->
        <div class="flex flex-wrap gap-3 md:justify-end items-center">
            @forelse($teacher->classrooms as $room)
                <span class="px-3 py-1.5 bg-white border border-gray-200 text-primary/80 rounded-xl text-xs font-bold shadow-sm">
                    {{ $room->year_group == 0 ? 'Reception' : 'Year ' . $room->year_group }}
                </span>
            @empty
              @if (auth()->user()->role === 'schooladmin')
                <!-- nothing -->
              @else
                <span class="text-xs text-gray-400 font-semibold">No classes</span>
              @endif
            @endforelse

            <!-- Delete button -->
            @if($teacher->id !== auth()->id())
                <form action="{{ route('schooladmin.teachers.destroy', $teacher->id) }}" method="POST" class="m-0 ml-2 border-l border-gray-200 pl-3" onsubmit="return confirm('Are you sure you want to delete {{ $teacher->name }}? This action cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" title="Delete Teacher" class="w-8 h-8 flex items-center justify-center bg-white border-2 border-red-200 text-red-400 rounded-full hover:bg-red-500 hover:text-white hover:border-red-500 transition-all duration-100 focus:outline-none focus:ring-2 focus:ring-red-400 focus:ring-offset-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </form>
            @endif
        </div>

      </div>
      @endforeach

    </div>
  </div>
  @endif
  <div class="bg-white rounded-3xl p-6 md:p-8 shadow-sm border border-[#755f5420] space-y-6">
    <!-- Classes -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-5">
      
      <!-- collect() to sort active classes to top and inactive at the bottom -->
      @forelse(collect($yearGroups ?? [])->sortByDesc('active') as $group)

        @php
          $displayYear = $group['year'] == 0 ? 'Reception' : 'Year ' . $group['year'];
        @endphp

        <!-- Check if classroom is active -->
        @if (!$group['active'])
            <!-- INACTIVE -->
            <div id="classroom-{{ $group['id'] ?? $group['slug'] }}" data-classroom-id="{{ $group['id'] ?? $group['slug'] }}" class="bg-gray-50 border border-gray-200 transition-all rounded-3xl p-5 md:p-6 shadow-sm flex flex-col justify-between group relative">
        @else
            <!-- ACTIVE -->
            <div id="classroom-{{ $group['id'] ?? $group['slug'] }}" data-classroom-id="{{ $group['id'] ?? $group['slug'] }}" class="bg-[#755f540a] border border-[#755f5420] hover:border-primary/30 rounded-3xl p-5 md:p-6 shadow-sm hover:shadow-md flex flex-col justify-between group relative">
        @endif
        
          <!-- delete button -->
          <form action="{{ route('teacher.classes.removeClassroom', $group['id'] ?? $group['slug']) }}" method="POST" class="absolute -top-3 -right-3 z-20 m-0" onsubmit="return confirm('Are you sure you want to delete {{ $group['name'] }}? You cannot restore a classroom.')">
            @csrf
            @method('DELETE')
            <button type="submit" title="Delete Class" class="w-8 h-8 flex items-center justify-center bg-white border-2 border-red-200 text-red-400 rounded-3xl hover:bg-red-500 hover:text-white hover:border-red-500 shadow-md transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-red-400 focus:ring-offset-2">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="size-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
              </svg>
            </button>
          </form>

          <!-- if inactive = grey hover -->
          <div class="@if(!$group['active']) opacity-50 grayscale group-hover:opacity-100 group-hover:grayscale-0 transition-all duration-300 @endif">
            
            <div class="flex justify-between items-start mb-4">
              
              <div class="flex items-center gap-2">
                <!-- year group -->
                <span class="px-4 py-2 bg-primary text-background rounded-lg text-xs font-black uppercase tracking-widest shadow-sm">
                  {{ $displayYear }}
                </span>

                <!-- inactive badge -->
                @if (!$group['active'])
                  <span class="px-2 py-1 bg-gray-500 text-white rounded-md text-[10px] font-bold uppercase tracking-wider">
                    Inactive
                  </span>
                @endif
              </div>
              
              <!-- student count-->
              <span class="flex items-center gap-1.5 bg-white px-3 py-1.5 rounded-lg border border-primary/10 text-primary/80 text-sm font-bold shadow-sm" title="Total Students">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                </svg>
                {{ $group['students'] }}
              </span>
            </div>

            <!-- Class name and classroom id-->
            <h3 class="text-2xl font-black text-primary mb-1 truncate" title="{{ $group['name'] }}">
              {{ $group['name'] }} <span class="text-base font-semibold text-primary/40 ml-1">#{{ $group['id'] ?? $group['slug'] }}</span>
            </h3>
          </div>

          <!-- Buttons -->
          @if (!$group['active'])
              <!-- INACTIVE CLASSROOM -->
              <div class="mt-6 w-full flex flex-col gap-2">
                
                <!-- academic year -->
                <div class="text-start">
                    <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest bg-gray-200 px-3 py-1 rounded-md">
                        {{ str_replace(['/', '-'], ' to ', $group['academic_year'] ?? '') }}
                    </span>
                </div>

                @if (!$group['is_progressed'])
                  @if ($group['year'] == '6')
                      <!-- Progress (not year 6), accidentally archive button -->
                      <form action="{{ route('teacher.classes.restoreClassroom', $group['slug']) }}" method="POST" class="inline-block w-full m-0" onsubmit="return confirm('Are you sure you want to restore this classroom? All previous students will be added automatically');">
                          @csrf
                          @method('PATCH')
                          <button type="submit" class="bg-amber-500 rounded-xl p-3 flex flex-col justify-center items-center text-center hover:bg-amber-600 w-full shadow-sm transition-colors cursor-pointer border-none outline-none">
                              <span class="text-xs font-bold text-white uppercase tracking-widest leading-tight">Restore classroom</span>
                          </button>
                      </form>
                  @else
                      <!-- Progress button (reception - y5) not progressed -->
                      <form action="{{ route('teacher.classes.progressClassroom', $group['slug']) }}" method="POST" class="inline-block w-full m-0" onsubmit="return confirm('Are you sure you want to progress this class to the next year? A new active classroom will be created for Year {{ $group['year'] + 1 }}, and the students will be moved into it.');">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="bg-green-500 rounded-xl p-3 flex flex-col justify-center items-center text-center hover:bg-green-600 w-full shadow-sm transition-colors cursor-pointer border-none outline-none mt-2">
                            <span class="text-xs font-bold text-white uppercase tracking-widest leading-tight">Progress students</span>
                        </button>
                      </form>

                      <!-- restore button before progressing -->
                      <form action="{{ route('teacher.classes.restoreClassroom', $group['slug']) }}" method="POST" class="inline-block w-full m-0" onsubmit="return confirm('Are you sure you want to restore this classroom? All previous students will be added automatically');">
                          @csrf
                          @method('PATCH')
                          <button type="submit" class="bg-amber-500 rounded-xl p-3 flex flex-col justify-center items-center text-center hover:bg-amber-600 w-full shadow-sm transition-colors cursor-pointer border-none outline-none">
                              <span class="text-xs font-bold text-white uppercase tracking-widest leading-tight">Restore classroom</span>
                          </button>
                      </form>
                  @endif
              @else
                  <!-- locked, no restore -->
                  <div class="mt-4 w-full bg-gray-200 rounded-xl p-3 flex justify-center items-center text-center shadow-inner border border-gray-300">
                      <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest leading-tight">
                          Already Progressed
                      </span>
                  </div>
              @endif
                  <!-- view statistics -->
                  <a href="{{ route('teacher.classes.showStatistics', $group['slug']) }}" 
                    class="bg-blue-500 rounded-xl p-3 flex flex-col justify-center items-center text-center hover:bg-blue-600 w-full shadow-sm transition-colors no-underline">
                      <span class="text-xs font-bold text-white uppercase tracking-widest leading-tight">View Statistics</span>
                  </a>
              </div>
          @else
            <!-- Active classroom buttons -->
            <div>
              <!-- academic year -->
                <div class="text-start">
                    <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest bg-gray-200 px-3 py-1 rounded-md">
                        {{ str_replace(['/', '-'], ' to ', $group['academic_year'] ?? '') }}
                    </span>
                </div>
            </div>
            <div class="mt-6 grid grid-cols-2 gap-2">
              <!-- view class -->
              <a href="{{ url('/teacher/classes/'.$group['slug'].'/view') }}" class="py-2.5 bg-primary border-2 border-primary text-background font-bold text-[10px] xl:text-xs rounded-xl text-center hover:bg-orange-900 transition-colors flex flex-col items-center justify-center gap-1 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
                View
              </a>
              
              <!-- manage students -->
              <a href="{{ url('/teacher/classes/'.$group['slug'].'/students') }}" class="py-2.5 bg-white border-2 border-primary/10 text-primary/70 font-bold text-[10px] xl:text-xs rounded-xl text-center hover:bg-orange-50 hover:border-primary/30 hover:text-primary transition-colors flex flex-col items-center justify-center gap-1 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                </svg>
                Manage
              </a>
              
              <!-- reading list books -->
              <a href="{{ url('/teacher/classes/'.$group['slug'].'/reading-list') }}" class="py-2.5 bg-white border-2 border-primary/10 text-primary/70 font-bold text-[10px] xl:text-xs rounded-xl text-center hover:bg-orange-50 hover:border-primary/30 hover:text-primary transition-colors flex flex-col items-center justify-center gap-1 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                </svg>
                Books
              </a>

              <!-- leaderboard -->
              <a href="{{ url('/leaderboard/'.($group['id'] ?? $group['slug'])) }}" class="py-2.5 bg-white border-2 border-primary/10 text-primary/70 font-bold text-[10px] xl:text-xs rounded-xl text-center hover:bg-orange-50 hover:border-primary/30 hover:text-primary transition-colors flex flex-col items-center justify-center gap-1 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 0 0 2.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 0 1 2.916.52 6.003 6.003 0 0 1-5.395 4.972m0 0a6.726 6.726 0 0 1-2.749 1.35m0 0a6.772 6.772 0 0 1-3.044 0" />
                </svg>
                Leaderboard
              </a>
            </div>
          @endif
        </div>

      @empty

        <!-- if classes are empty -->
        @if (auth()->user()->role === 'schooladmin')
          <!-- do nothing -->
          <div class="col-span-full w-full bg-[#755f540a] border-2 border-dashed border-[#755f5430] rounded-3xl p-12 flex flex-col items-center justify-center text-center">
            <h3 class="text-xl font-bold text-primary">No classes assigned</h3>
            <p class="text-primary/60 mt-2 max-w-sm">
              You're the school administrator! Create teachers and they will be able to create classrooms!
            </p>
          </div>
        @else
          <div class="col-span-full w-full bg-[#755f540a] border-2 border-dashed border-[#755f5430] rounded-3xl p-12 flex flex-col items-center justify-center text-center">
            <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center shadow-sm mb-4">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-10 text-primary/40">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
              </svg>
            </div>
            <h3 class="text-xl font-bold text-primary">No classes assigned yet</h3>
            <p class="text-primary/60 mt-2 max-w-sm">
              You haven't created any classrooms. Click "Create Class" to get started!
            </p>
          </div>
        @endif

      @endforelse

    </div>

  </div>

</x-teacher.layout>