@php
  $classCount   = collect($yearGroups ?? [])->count();
  $studentTotal = collect($yearGroups ?? [])->sum('students');
@endphp

<x-layout title="{{ $title ?? 'Teacher' }}">
  <div class="min-h-screen py-8 px-4 sm:px-6 lg:px-8">
    <div class="mx-auto w-full max-w-4xl rounded-2xl border-4 border-primary p-4 sm:p-6 shadow">

      <!-- Header card -->
      <div class="rounded-xl border-2 border-primary p-4">
        <div class="flex items-center gap-4">
          <div class="h-14 w-14 shrink-0 overflow-hidden rounded-full">
            <img
              src="{{ asset(Auth::user()->pfp) }}"
              alt="Profile Picture"
              class="h-full w-full object-cover"
              onerror="this.style.display='none'"
            />
          </div>

          <div class="flex-1">
            <div class="text-lg font-bold text-gray-800">
              Welcome back, {{ Auth::user()->name }}
            </div>
            <div class="text-sm font-semibold text-gray-600">
              Classes: {{ $classCount }} - Students: {{ $studentTotal }}
            </div>
            <!-- School Name -->
            <div class="text-sm font-semibold text-gray-600">
              {{ Auth::user()->school?->name ?? 'No School Assigned' }}
            </div>
          </div>
          <!-- Don't show on index page -->
          @if (!request()->routeIs('teacher.index'))
            <div class="mt-4">
              <a href="{{ route('teacher.index') }}" class="text-md font-black text-primary hover:text-secondary">
                ← Back
              </a>
            </div>
          @endif
        </div>
      </div>

      {{-- Page-specific content --}}
      <div class="mt-4">
        {{ $slot }}
      </div>

    </div>
  </div>
</x-layout>
