<x-teacher.layout :yearGroups="$yearGroups" title="Manage Students">
  <div class="rounded-xl border-2 border-primary p-4">
    @php 
      if ($classroom->year_group == 0) {
          $displayYear = 'Reception';
      } else {
          $displayYear = 'Year ' . $classroom->year_group;
      }
    @endphp
    <div class="text-lg font-bold text-gray-800">{{ $displayYear . " - " . $classroom->name}}</div>
    <div class="text-sm font-semibold text-gray-600">
      {{ $classroom->students->count() }} students
    </div>
    
    <!-- Error messages -->
    @if (session('success'))
      <div class="my-4 p-4 bg-green-100 text-green-700 rounded-md">
        {{ session('success') }}
      </div>
    @endif
    
    @if (session('error'))
      <div class="my-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-md">
        {{ session('error') }}
      </div>
    @endif
    
    <!-- Manage Students List -->
    <div class="my-6 flex-col text-sm font-semibold text-gray-700">
      <a class="text-sm mr-4 font-bold bg-secondary rounded-md p-4 text-background hover:bg-primary" href="{{ route('teacher.classes.addStudents', $classroom->id) }}">Add Students</a>
      @if ($classroom->students_count > 0)
        <a class="text-sm mr-4 font-bold bg-secondary rounded-md p-4 text-background hover:bg-primary" href="{{ route('teacher.classes.export', $classroom->id) }}">Export CSV</a>
      @else
        <span class="pr-2">No students to export.</span>
      @endif
      @if ($classroom->students_count == 0)
        <span class="pr-2">No students to remove.</span>
      @else
        <a class="text-sm mr-4 font-bold bg-red-400 rounded-md p-4 text-background hover:bg-red-500" onclick="return confirm('Are you sure you want to remove ALL students from this classroom?')" href="{{ route('teacher.classes.removeAllStudents', $classroom->id) }}">Remove all students</a>
      @endif
    </div>
    <!-- Import Students -->
    <h2 class="text-lg font-bold text-gray-800 mb-4">Import Students</h2>
    <form id="importForm" action="{{ route('teacher.classes.importStudents', $classroom->id) }}" method="POST" enctype="multipart/form-data">
      @csrf
      <div class="mb-4">
        <div class="relative">
          <input type="file" name="students_csv" id="students_csv" accept=".csv,.txt" required class="hidden" onchange="updateFileName(this)">
          <button type="button" onclick="document.getElementById('students_csv').click()" class="w-full max-w-md border-2 border-primary rounded-md p-3 text-sm text-left bg-white hover:bg-gray-50 flex items-center justify-between">
            <span id="fileNameDisplay" class="text-gray-500">Choose a CSV file...</span>
          </button>
        </div>
        
        @error('students_csv')
          <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
      </div>
      <!-- Submit button -->
      <div class="flex gap-4 mb-2">
        <button type="submit" id="submitBtn" class="bg-secondary text-background font-bold rounded-md px-4 py-2 hover:bg-primary text-sm">Import</button>
      </div>
    </form>
    <!-- Template download -->
    <div>
      <p class="text-sm text-primary font-semibold">
        <strong>Download the file <span><a href="{{ asset('template/student_import_template.csv') }}" class="underline">template</a></span> for the required format. The username must be unique across the system and the level should be a number (1-20).
      </p>
    </div>
</div>

    <!-- Students -->
    <div class="divide-y mt-4 border-2 p-4 border-primary rounded-lg max-h-[500px] overflow-y-auto">
      @forelse($students as $s)
        <div class="py-3 flex items-center justify-between">
          <!-- Student info -->
          <div class="flex items-center gap-3">
            <img class="h-10 w-10 rounded-full object-cover" src="{{ asset($s->pfp ?? '/images/pfp/cat.png') }}" alt="">
            <div class="font-semibold text-gray-800">{{ $s->first_name }} {{ $s->last_name }}</div>
            <div class="text-sm text-gray-600">{{ $s->user->username }}</div>
            <div class="text-sm text-gray-600">Level {{ $s->level }}</div>
          </div>
          <!-- Buttons -->
          <div>
            <!-- Remove student from class -->
            <form action="{{ route('teacher.classes.removeStudent', [$classroom->id, $s->id]) }}"
                  method="POST"
                  onsubmit="return confirm('Remove this student? {{ $s->first_name }} {{ $s->last_name }} will no longer be part of {{ $classroom->name }}.')"
                  class="inline">
                @csrf
                @method('DELETE')

                <button type="submit"
                    class="text-sm mx-2 font-bold border-2 border-red-400 rounded-md p-2
                          text-secondary hover:text-primary hover:bg-red-100">
                    Remove
                </button>
            </form>
            <!-- View student profile -->
            <a class="text-sm mx-2 font-bold border-2 border-primary border-solid rounded-md p-2 text-secondary hover:text-primary" href="{{ route('user.show', $s->user->id) }}">
              View
            </a>
            <!-- Manage student -->
            <a class="text-sm mx-2 font-bold border-2 border-primary border-solid rounded-md p-2 text-secondary hover:text-primary" href="#">
              Manage
            </a>
          </div>
        </div>
      @empty
        <div class="py-8 text-center text-gray-500">
          No students in the classroom yet... add or import some!
        </div>
      @endforelse
    </div>
  </div>
</x-teacher.layout>
<script>
  // Update input to show selected file name
  function updateFileName(input) {
    const fileNameDisplay = document.getElementById('fileNameDisplay');
    // If a file is selected, show the files name
    if (input.files && input.files.length > 0) {
      fileNameDisplay.textContent = input.files[0].name;
      fileNameDisplay.classList.remove('text-gray-500');
      fileNameDisplay.classList.add('text-gray-800', 'font-semibold');
    } else {
      fileNameDisplay.textContent = 'Choose a CSV file...';
      fileNameDisplay.classList.remove('text-gray-800', 'font-semibold');
      fileNameDisplay.classList.add('text-gray-500');
    }
  }
</script>