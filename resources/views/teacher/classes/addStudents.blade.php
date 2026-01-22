<x-teacher.layout :yearGroups="$yearGroups" title="{{ $classroom->name }} - Add Students">
    <div class="rounded-xl border-2 border-primary p-4">
        <form action="{{ route('teacher.classes.storeStudents', $classroom->id) }}" method="POST" class="space-y-4">
            @csrf
            <!-- How many students to add -->
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
    const studentCountInput = document.getElementById('student_count');
    const container = document.getElementById('studentname_container');
    const setTenBtn = document.getElementById('setten');
    const setTwentyBtn = document.getElementById('settwenty');
    const setThirtyBtn = document.getElementById('setthirty');
    // Set 10 students
    setTenBtn.addEventListener('click', function () {
        studentCountInput.value = 10;
        displayStudents(10);
    });
    // Set 20 students
    setTwentyBtn.addEventListener('click', function () {
        studentCountInput.value = 20;
        displayStudents(20);
    });
    // Set 30 students
    setThirtyBtn.addEventListener('click', function () {
        studentCountInput.value = 30;
        displayStudents(30);
    });

    // Display student fields
    function displayStudents(count) {
        container.innerHTML = '';

        // for every extra student
        for (let i = 1; i <= count; i++) {
            const studentBlock = document.createElement('div');
            studentBlock.classList.add(
                'border',
                'border-secondary',
                'rounded-lg',
                'p-2',
                
            );

            studentBlock.innerHTML = `
                <div class="font-semibold text-black">Student ${i}</div>
                // fname
                <div>
                    <label class="block text-sm font-medium text-black">First Name</label>
                    <input type="text" name="students[${i}][first_name]" required class="block w-full rounded-md border-2 border-gray-300 bg-white text-black shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                </div>
                // lname
                <div>
                    <label class="block text-sm font-medium text-black">Last Name</label>
                    <input type="text" name="students[${i}][last_name]" required class="block w-full rounded-md border-2 border-gray-300 bg-white text-black shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                </div>
                // dob
                <div>
                    <label class="block text-sm font-medium text-black">Date of Birth</label>
                    <input type="date" name="students[${i}][dob]" required class="block w-1/3 rounded-md border-2 border-gray-300 bg-white text-black shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                </div>
                // level
                <div>
                    <label class="block text-sm font-medium text-black">Reading Level</label>
                    <input type="number" name="students[${i}][level]" min="1" max="20" step="1" value="1" required class="block w-1/4 rounded-md border-2 border-gray-300 bg-white text-black shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                </div>
            `;

            container.appendChild(studentBlock);
        }
    }

    studentCountInput.addEventListener('input', function () {
        // get current value
        const count = parseInt(this.value, 10);

        // if greater than 0, display student inputs
        if (count >= 1) {
            displayStudents(count);
        } else {
            container.innerHTML = '';
        }
    });

    // default = 1
    displayStudents(1);
});
</script>
