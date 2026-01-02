<x-teacher.layout :yearGroups="$yearGroups" title="Teacher Dashboard">
  <div class="space-y-3">
    @foreach(($yearGroups ?? []) as $group)
      <div class="grid grid-cols-12 gap-3 rounded-xl border-2 border-primary p-3">
        <div class="col-span-12 sm:col-span-3 flex flex-col justify-center px-2">
          <div class="text-sm font-bold text-gray-800">{{ $group['year'] }}</div>
          <div class="text-xs font-semibold text-gray-600">{{ $group['students'] }} students</div>
        </div>

        <a href="{{ url('/teacher/classes/'.$group['slug'].'/view') }}"
           class="col-span-12 sm:col-span-3 flex items-center justify-center rounded-lg bg-secondary px-4 py-3 text-sm font-bold text-white hover:bg-primary transition text-center">
          View
        </a>

        <a href="{{ url('/teacher/classes/'.$group['slug'].'/students') }}"
           class="col-span-12 sm:col-span-3 flex items-center justify-center rounded-lg bg-secondary px-4 py-3 text-sm font-bold text-white hover:bg-primary transition text-center">
          Manage Students
        </a>

        <a href="{{ url('/teacher/classes/'.$group['slug'].'/reading-list') }}"
           class="col-span-12 sm:col-span-3 flex items-center justify-center rounded-lg bg-secondary px-4 py-3 text-sm font-bold text-white hover:bg-primary transition text-center">
          Generate Reading<br class="hidden sm:block">List
        </a>
      </div>
    @endforeach

    @if(empty($yearGroups))
      <div class="rounded-xl bg-background p-4 text-sm font-semibold text-gray-700">
        No classes assigned yet.
      </div>
    @endif
  </div>
</x-teacher.layout>
