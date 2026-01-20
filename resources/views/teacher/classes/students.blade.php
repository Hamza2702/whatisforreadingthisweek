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
    <!-- Manage Students List -->
    <div class="my-6 flex-col text-sm font-semibold text-gray-700">
      <a class="text-sm mr-4 font-bold bg-secondary rounded-md p-4 text-background hover:bg-primary" href="#">Add Students</a>
      <a class="text-sm mr-4 font-bold bg-secondary rounded-md p-4 text-background hover:bg-primary" href="#">Import Students</a>
      @if ($classroom->students_count > 0)
        <a class="text-sm mr-4 font-bold bg-secondary rounded-md p-4 text-background hover:bg-primary" href="{{ route('teacher.classes.export', $classroom->id) }}">Export CSV</a>
      @else
        <span>No students to export.</span>
      @endif
    </div>
    <!-- Students List -->
    <div class="mt-8 divide-y max-h-[500px] overflow-y-auto">
      @foreach($students as $s)
        <div class="py-3 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <img class="h-10 w-10 rounded-full object-cover" src="{{ asset($s->pfp ?? '/images/pfp/cat.png') }}" alt="">
            <div class="font-semibold text-gray-800">{{ $s->first_name }} {{ $s->last_name }}</div>
            <div class="text-sm text-gray-600">{{ $s->user->username }}</div>
            <div class="text-sm text-gray-600">Level {{ $s->level }}</div>
          </div>
          <div>
            <a class="text-sm mx-4 font-bold text-secondary hover:text-primary" href="#">
              View
            </a>
            <a class="text-sm mx-4 font-bold text-secondary hover:text-primary" href="#">
              Manage
            </a>
          </div>
        </div>
      @endforeach
    </div>
  </div>
</x-teacher.layout>
