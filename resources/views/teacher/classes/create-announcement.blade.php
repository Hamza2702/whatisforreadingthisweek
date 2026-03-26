<x-teacher.layout :classroom="$classroom" title="Create announcement">

    <div class="bg-white rounded-3xl p-6 md:p-10 border border-[#755f5420] max-w-5xl mx-auto mt-8">
        
        <div class="mb-8 border-b border-[#755f5420] pb-4 text-center md:text-left">
            <h2 class="text-3xl font-display text-primary tracking-tight">Create announcement</h2>
            <p class="text-sm text-primary/70 mt-1">Post an announcement message to the classroom</p>
        </div>

        <form action="{{ route('teacher.classes.announcements.store', $classroom->id) }}" method="POST" class="space-y-6">
            @csrf
            
            <!-- Announcement for -->
            <div>
                <label class="block text-sm font-semibold text-primary mb-3">Who is this announcement for?</label>
                
                <!-- Checkbox -->
                <div class="flex items-center mb-4 bg-[#755f540a] border border-[#755f5420] p-4 rounded-xl">
                    <!-- Class -->
                    <input id="entire_class" name="entire_class" type="checkbox" value="1" checked class="w-5 h-5 text-orange-600 border-[#755f5420] rounded focus:ring-orange-500/30 cursor-pointer">
                    <label for="entire_class" class="ml-3 text-sm font-bold text-primary cursor-pointer w-full">
                        Send to entire class
                    </label>
                </div>

                <!-- Dropdown -->
                <div id="student_select_container" class="hidden transition-all duration-300">
                    <!-- Student -->
                    <label for="student_id" class="block text-sm font-semibold text-primary">Select specific student</label>
                    <select name="student_id" id="student_id"
                        class="mt-2 block w-full rounded-xl border border-[#755f5420] bg-[#755f540a] text-primary focus:border-primary focus:ring-2 focus:ring-primary/30 sm:text-sm px-4 py-3 cursor-pointer">
                        <option value="" disabled selected>Search for a student...</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}">
                                {{ $student->first_name }} {{ $student->last_name }} - Level {{ $student->level ?? '0' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Message -->
            <div>
                <label for="message" class="block text-sm font-semibold text-primary">Announcement Message</label>
                <textarea name="message" id="message" rows="5" required placeholder="E.g., 'Check out The BFG by Roald Dahl'"
                    class="mt-2 block w-full rounded-xl border border-[#755f5420] bg-[#755f540a] text-primary focus:border-primary focus:ring-2 focus:ring-primary/30 sm:text-sm px-4 py-3 resize-none"></textarea>
                <x-form-error name="message"/>
                <x-form-error name="student_id"/>
            </div>

            <!-- Submit button -->
            <div class="pt-4 flex items-center justify-between">
                <a href="{{ route('teacher.classes.view', $classroom->id) }}" class="text-sm font-bold text-primary/60 hover:text-primary transition-colors">
                    Cancel
                </a>
                <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-4 text-base bg-primary text-background font-bold rounded-xl hover:bg-orange-900 shadow-md hover:shadow-lg transition-all duration-200 focus:ring-4 focus:ring-primary/30">
                    Post Announcement
                </button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // checkbox and student container/students
            const entireClassCheckbox = document.getElementById('entire_class');
            const studentSelectContainer = document.getElementById('student_select_container');
            const studentSelect = document.getElementById('student_id');

            // toggle student checkbox
            function toggleStudentSelect() {
                if (entireClassCheckbox.checked) {
                    studentSelectContainer.classList.add('hidden');
                    studentSelect.removeAttribute('required');
                    studentSelect.value = ""; // reset dropdown
                } else {
                    studentSelectContainer.classList.remove('hidden');
                    studentSelect.setAttribute('required', 'required');
                }
            }

            entireClassCheckbox.addEventListener('change', toggleStudentSelect);
            toggleStudentSelect(); // initialised on page load
        });
    </script>
</x-teacher.layout>