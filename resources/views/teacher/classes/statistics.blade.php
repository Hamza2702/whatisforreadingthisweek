<x-teacher.layout :yearGroups="$yearGroups" title="Classroom Statistics">
    <div class="w-full px-2 md:px-4 py-2 flex-1 flex flex-col gap-6 lg:gap-8 font-sans">

        @if(isset($noData))
            <!-- EMPTY -->
            <div class="bg-white border border-[#755f5420] rounded-3xl p-12 text-center shadow-sm">
                <h2 class="text-2xl font-display text-primary mb-2">No data available</h2>
                <p class="text-primary/60">This classroom has no recorded student activity to show statistics for.</p>
                <a href="{{ route('teacher.index') }}" class="inline-block mt-6 px-6 py-3 bg-primary text-background font-bold rounded-xl hover:bg-orange-900 transition-colors">
                    Back to Dashboard
                </a>
            </div>
        @else
            @include('teacher.classes._statistics-content')
        @endif

    </div>

    @unless(isset($noData))
        @include('teacher.classes._statistics-scripts')
    @endunless
</x-teacher.layout>