<x-teacher.layout :yearGroups="$yearGroups" :title="$classroom->name">
  <div class="rounded-xl border-2 border-primary p-4">
    <div class="text-lg font-bold text-gray-800 flex items-center justify-between">
      <div class="text-lg font-bold text-gray-800">{{ $classroom->name }}</div>
    </div>
    <div class="text-sm font-semibold text-gray-600">
      Year {{ $classroom->year_group }} - {{ $classroom->students_count ?? $classroom->students->count() }} students
    </div>
  </div>
  <!-- Add stats of classroom -->
  <div class="mt-4 rounded-xl border-2 border-primary p-4 text-black">
    
  </div>
</x-teacher.layout>
