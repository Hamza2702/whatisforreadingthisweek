<x-teacher.layout :yearGroups="$yearGroups" title="Teacher - Create Class">

    @php
        // Calculate the dynamic 10-year window
        $currentYear = (int) date('Y');
        $minYear = $currentYear - 5;
        $maxYear = $currentYear + 5;
    @endphp
    <div class="bg-white rounded-3xl p-6 md:p-10 shadow-sm border border-[#755f5420] max-w-3/4 mx-3/4">
        <form action="{{ route('teacher.classes.store') }}" method="POST" class="space-y-6">
            @csrf
            
            <!-- Year -->
            <div>
                <label for="year_group" class="block text-sm font-semibold text-primary">Year Group</label>
                <select name="year_group" id="year_group" required
                    class="mt-2 block w-full rounded-xl border border-[#755f5420] bg-[#755f540a] text-primary focus:border-primary focus:ring-2 focus:ring-primary/30 sm:text-sm px-4 py-3">
                    <option value="0">Reception</option>
                    <option value="1">Year 1</option>
                    <option value="2">Year 2</option>
                    <option value="3">Year 3</option>
                    <option value="4">Year 4</option>
                    <option value="5">Year 5</option>
                    <option value="6">Year 6</option>
                </select>
            </div>

            <!-- Class name -->
            <div>
                <label for="name" class="block text-sm font-semibold text-primary">Class Name</label>
                <input type="text" name="name" id="name" placeholder="e.g. Sheep, Fish, Roald Dahl."
                    class="mt-2 block w-full rounded-xl border border-[#755f5420] bg-[#755f540a] text-primary focus:border-primary focus:ring-2 focus:ring-primary/30 sm:text-sm px-4 py-3">
                <x-form-error name="name"/>
            </div>

            <!-- Academic Year -->
            <div>
                <label class="block text-sm font-semibold text-primary">Academic Year</label>
                <div class="mt-2 flex gap-4">
                    <!-- Start date -->
                    <input type="number" name="academic_start" id="academic_start" 
                        min="{{ $minYear }}" max="{{ $maxYear }}" step="1"
                        required placeholder="Start (e.g. {{ $currentYear }})"
                        class="w-1/2 rounded-xl border border-[#755f5420] bg-[#755f540a] text-primary focus:border-primary focus:ring-2 focus:ring-primary/30 sm:text-sm px-4 py-3">

                    <!-- End date -->
                    <input type="number" name="academic_end" id="academic_end" 
                        min="{{ $minYear }}" max="{{ $maxYear }}" step="1"
                        required placeholder="End (e.g. {{ $currentYear + 1 }})"
                        class="w-1/2 rounded-xl border border-[#755f5420] bg-[#755f540a] text-primary focus:border-primary focus:ring-2 focus:ring-primary/30 sm:text-sm px-4 py-3">
                </div>

                <input type="hidden" name="academic_year" id="academic_year">
            </div>

            <p id="academic_year_error" class="text-xs font-bold text-red-500 mt-1"></p>
            <x-form-error name="academic_end"/>

            <!-- Submit button -->
            <div class="pt-4">
                <button type="submit"
                        class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-4 text-base bg-primary text-background font-bold rounded-xl hover:bg-orange-900 shadow-md hover:shadow-lg transition-all duration-200 focus:ring-4 focus:ring-primary/30">
                    Create Class
                </button>
                <x-form-error name="year_group"/>
            </div>
        </form>

    </div>

</x-teacher.layout>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const startInput = document.getElementById('academic_start');
        const endInput   = document.getElementById('academic_end');
        const hidden     = document.getElementById('academic_year');
        const errorMSG   = document.getElementById('academic_year_error');

        function updateAcademicYear() {
            const start = parseInt(startInput.value, 10);
            const end   = parseInt(endInput.value, 10);

            // reset the hidden input and error message
            hidden.value = '';
            if (errorMSG) errorMSG.innerText = '';
            // check if both inputs are valid integers
            if (Number.isInteger(start) && Number.isInteger(end)) {
                // if start is greater than or equal to end, show error
                if (start >= end) {
                    if (errorMSG) {
                        errorMSG.innerText = 'End year must be greater than start year.';
                    }
                } else {
                    // update
                    hidden.value = `${start}-${end}`;
                }
            }
        }
        
        // event listeners
        startInput.addEventListener('input', updateAcademicYear);
        endInput.addEventListener('input', updateAcademicYear);
    });
</script>