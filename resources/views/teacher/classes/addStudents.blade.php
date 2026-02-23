<x-teacher.layout :yearGroups="$yearGroups" title="{{ $classroom->name }} - Add Students">
    <div class="rounded-xl border-2 border-primary p-4">
        <form action="{{ route('teacher.classes.storeStudents', $classroom->id) }}" method="POST" class="space-y-4">
            @csrf
            <!-- How many students to add -->

            @php
                $now = now(); // current year
                $academicYearStart = $now->month >= 9 ? $now->year : $now->year - 1; // year starts in september 
                $expectedAge = $classroom->year_group + 4; // expected age based off year group
                $minDob = \Carbon\Carbon::create($academicYearStart - $expectedAge - 1, 9, 1)->format('Y-m-d'); // students should be born after sep 1st
                $maxDob = \Carbon\Carbon::create($academicYearStart - $expectedAge, 8, 31)->format('Y-m-d'); // students should be born before aug 31st
                $yearLabel = $classroom->year_group === 0 ? 'Reception' : 'Year ' . $classroom->year_group;
            @endphp

            <div>
                <label for="student_count" class="block text-sm font-medium text-black">How many students do you want to add?</label>
                <input type="number" name="student_count" id="student_count" min="1" max="50" step="1" value="1" required class="mt-1 block w-1/4 rounded-md border-2 border-gray-300 bg-white text-black shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                <div class="flex flex-row gap-4">
                    <!-- 10 -->
                    <a id="setten" class="mt-2 p-4 w-1/5 rounded-md text-sm text-center font-semibold bg-secondary text-background hover:bg-primary cursor-pointer">10 Students</a>
                    <!-- 20 -->
                    <a id="settwenty" class="mt-2 p-4 w-1/5 rounded-md text-sm text-center font-semibold bg-secondary text-background hover:bg-primary cursor-pointer">20 Students</a>
                    <!-- 30 -->
                    <a id="setthirty" class="mt-2 p-4 w-1/5 rounded-md text-sm text-center font-semibold bg-secondary text-background hover:bg-primary cursor-pointer">30 Students</a>
                </div>
            </div>
            <!-- Students name container -->
            <div class="flex flex-col gap-4 mt-4" id="studentname_container">

            </div>
            <!-- Submit button -->
            <div>
                <button type="submit"
                        class="rounded-lg bg-primary px-4 py-2 text-sm font-bold text-white hover:bg-secondary transition">
                    Add Students
                </button>
                <x-form-error name="addstudents"/>
            </div>
        </form>
    </div>
</x-teacher.layout>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const input = document.getElementById('student_count'); // student count input
        const container = document.getElementById('studentname_container'); // student container
        const minDob = '{{ $minDob }}'; // minimum date of birth calculated
        const maxDob = '{{ $maxDob }}'; // maximum dob calculated
        const yearLabel = '{{ $yearLabel }}'; // year group label

        // Calculate expanded DOB range for special students
        const minDate = new Date(minDob);
        const maxDate = new Date(maxDob);
        const expandedMinDob = new Date(minDate.getFullYear() - 2, minDate.getMonth(), minDate.getDate()).toISOString().split('T')[0];
        const expandedMaxDob = new Date(maxDate.getFullYear() + 2, maxDate.getMonth(), maxDate.getDate()).toISOString().split('T')[0];
        

        // Preset buttons
        document.getElementById('setten').addEventListener('click', () => { input.value = 10; render(10); }); // 10
        document.getElementById('settwenty').addEventListener('click', () => { input.value = 20; render(20); }); // 20
        document.getElementById('setthirty').addEventListener('click', () => { input.value = 30; render(30); }); // 30
        input.addEventListener('input', () => render(parseInt(input.value) || 0)); // render on input change

        // Change DOB range on is_special toggle
        container.addEventListener('change', function (e) {
            if (e.target.matches('input[type="checkbox"]') && e.target.name.includes('is_special')) {
                const studentBlock = e.target.closest('.border');
                const dobInput = studentBlock.querySelector('input[type="date"]');
                const dobText = studentBlock.querySelector('.dob-text');

                if (e.target.checked) {
                    dobInput.setAttribute('min', expandedMinDob);
                    dobInput.setAttribute('max', expandedMaxDob);
                    // change text to show expanded range
                    dobText.textContent = `Students born in ${yearLabel} from ${expandedMinDob} to ${expandedMaxDob}`;
                } else {
                    dobInput.setAttribute('min', minDob);
                    dobInput.setAttribute('max', maxDob);
                    dobText.textContent = `Students born in ${yearLabel} from ${minDob} to ${maxDob}`;
                    // clear value if its out of the standard range
                    if (dobInput.value && (dobInput.value < minDob || dobInput.value > maxDob)) {
                        dobInput.value = '';
                    }
                }
            }
        });

        // create student input fields
        function render(count) {
            container.innerHTML = '';
            for (let i = 1; i <= count; i++) {
                container.insertAdjacentHTML('beforeend', `
                    <div class="border border-secondary rounded-lg p-2">
                    <!-- Student -->
                        <div class="font-semibold text-black">Student ${i}</div>
                        <!-- First name -->
                        <div class="flex items-center gap-2 mb-2">
                            <input type="checkbox" value="1" name="students[${i}][is_special]" id="students[${i}][is_special]" class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                            <label for="students[${i}][is_special]" class="text-sm font-medium text-black">Is this student born from a different date other than ${minDob} - ${maxDob}?</label>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-black">First Name</label>
                            <input type="text" name="students[${i}][first_name]" required class="block w-full rounded-md border-2 border-gray-300 bg-white text-black shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                        </div>
                        <!-- Last name -->
                        <div>
                            <label class="block text-sm font-medium text-black">Last Name</label>
                            <input type="text" name="students[${i}][last_name]" required class="block w-full rounded-md border-2 border-gray-300 bg-white text-black shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                        </div>
                        <!-- Date of birth -->
                        <div>
                            <label class="block text-sm font-medium text-black">Date of Birth</label>
                            <input type="date" name="students[${i}][dob]" required min="${minDob}" max="${maxDob}" class="block w-1/3 rounded-md border-2 border-gray-300 bg-white text-black shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                            <p id="dob-text-${i}" class="text-xs text-gray-500 mt-1 dob-text">Students born in ${yearLabel} from: ${minDob} to ${maxDob}</p>
                        </div>
                        <!-- Reading level -->
                        <div>
                            <label class="block text-sm font-medium text-black">Reading Level</label>
                            <input type="number" name="students[${i}][level]" min="1" max="20" step="1" value="1" required class="block w-1/4 rounded-md border-2 border-gray-300 bg-white text-black shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                        </div>
                    </div>
                `);
            }
        }
        render(1);
    });
</script>