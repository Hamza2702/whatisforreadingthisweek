<x-teacher.layout :yearGroups="$yearGroups" title="Teacher - Create Class">
    <div class="rounded-xl border-2 border-primary p-4">
        <form action="{{ route('teacher.classes.store') }}" method="POST" class="space-y-4">
            @csrf
            <!-- Year -->
            <div>
                <label for="year_group" class="block text-sm font-medium text-black">Year</label>
                <select name="year_group" id="year_group" required class="mt-1 block w-full rounded-md border-2 border-gray-300 bg-white text-black shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
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
                <label for="name" class="block text-sm font-medium text-black">Class Name</label>
                <input type="text" name="name" id="name" class="mt-1 block w-full rounded-md border-2 border-gray-300 bg-white text-black shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
            </div>
            <x-form-error name="name"/>
            <!-- Academic Year -->
            <div>
                <label class="block text-sm font-medium text-black">Academic Year</label>

                <div class="mt-1 flex gap-2">
                    <!-- Start date -->
                    <input type="number" name="academic_start" id="academic_start" min="0" max="99" step="1" required placeholder="Start Year {{ date('Y') }}" class="w-1/4 rounded-md border-2 border-gray-300 bg-white text-black shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                    <!-- End date -->
                    <input type="number" name="academic_end" id="academic_end" min="0" max="99" step="1" required placeholder="End Year {{ date('Y') + 1 }}" class="w-1/4 rounded-md border-2 border-gray-300 bg-white text-black shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                </div>
                <input type="hidden" name="academic_year" id="academic_year">
            </div>
            <p id="academic_year_error" class="text-sm text-red-600 mt-1"></p>
            <x-form-error name="academic_end"/>
            <!-- Submit button -->
            <div>
                <button type="submit"
                        class="rounded-lg bg-primary px-4 py-2 text-sm font-bold text-white hover:bg-secondary transition">
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
        const errorMSG    = document.getElementById('academic_year_error');

        function updateAcademicYear() {
            const start = parseInt(startInput.value, 10); // convert in integer
            const end   = parseInt(endInput.value, 10); // convert in integer

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

        // event listners
        startInput.addEventListener('input', updateAcademicYear);
        endInput.addEventListener('input', updateAcademicYear);
    });
</script>