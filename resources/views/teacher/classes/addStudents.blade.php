<x-teacher.layout :classroom="$classroom" :yearGroups="$yearGroups" title="{{ $classroom->name }} - Add Students">
    @php
        $now = now(); 
        $academicYearStart = $now->month >= 9 ? $now->year : $now->year - 1; 
        $expectedAge = $classroom->year_group + 4; 
        $minDob = \Carbon\Carbon::create($academicYearStart - $expectedAge - 1, 9, 1)->format('Y-m-d'); 
        $maxDob = \Carbon\Carbon::create($academicYearStart - $expectedAge, 8, 31)->format('Y-m-d'); 
        $yearLabel = $classroom->year_group === 0 ? 'Reception' : 'Year ' . $classroom->year_group;
    @endphp

    <div class="mt-4">
        <!-- error/success messages -->
        @if(session('success'))
        <div class="mb-4 bg-green-100 border-2 border-green-500 text-green-700 px-4 py-3 rounded-lg font-bold flex items-center gap-2">
            {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="mb-4 bg-red-100 border-2 border-red-500 text-red-700 px-4 py-3 rounded-lg font-bold flex items-center gap-2">
            {{ session('error') }}
        </div>
        @endif

        <!-- validation error summary -->
        @if($errors->any())
        <div class="mb-4 bg-red-100 border-2 border-red-500 text-red-700 px-4 py-3 rounded-lg font-bold">
            <ul class="list-disc list-inside text-sm font-semibold space-y-1 ml-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- MAIN FORM -->
        <form action="{{ route('teacher.classes.storeStudents', $classroom->id) }}" method="POST">
            @csrf
            
            <!-- Students name container -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-4 md:gap-5" id="studentname_container">
                <!-- cards -->
            </div>
            
            <!-- form submit -->
            <div class="mt-8 pt-6 border-t border-[#755f5420] flex justify-end">
                <button type="submit" class="bg-primary text-background font-black text-lg rounded-2xl px-10 py-4 shadow-md hover:bg-orange-900 transition-all hover:-translate-y-1 focus:ring-4 focus:ring-primary/30">
                    Save Students
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

        // Calculate expanded DOB range for exceptional students
        const minDate = new Date(minDob);
        const maxDate = new Date(maxDob);
        const expandedMinDob = new Date(minDate.getFullYear() - 2, minDate.getMonth(), minDate.getDate()).toISOString().split('T')[0];
        const expandedMaxDob = new Date(maxDate.getFullYear() + 2, maxDate.getMonth(), maxDate.getDate()).toISOString().split('T')[0];
        
        // Preset buttons
        document.getElementById('setten').addEventListener('click', () => { input.value = 10; render(10); }); // 10
        document.getElementById('settwenty').addEventListener('click', () => { input.value = 20; render(20); }); // 20
        document.getElementById('setthirty').addEventListener('click', () => { input.value = 30; render(30); }); // 30
        input.addEventListener('input', () => render(parseInt(input.value) || 0)); // render on input change

        // Change DOB range on is_exceptional toggle
        container.addEventListener('change', function (e) {
            if (e.target.matches('input[type="checkbox"]') && e.target.name.includes('is_exceptional')) {
                const studentBlock = e.target.closest('.student-card');
                const dobInput = studentBlock.querySelector('.dob-input');
                const dobText = studentBlock.querySelector('.dob-text');

                if (e.target.checked) {
                    dobInput.setAttribute('min', expandedMinDob);
                    dobInput.setAttribute('max', expandedMaxDob);
                    dobText.textContent = `Range: ${expandedMinDob} to ${expandedMaxDob}`;
                    studentBlock.classList.remove('border-[#755f5420]');
                    studentBlock.classList.add('border-green-400', 'border-dashed', 'bg-white');
                } else {
                    dobInput.setAttribute('min', minDob);
                    dobInput.setAttribute('max', maxDob);
                    dobText.textContent = `Range: ${minDob} to ${maxDob}`;
                    studentBlock.classList.add('border-[#755f5420]');
                    studentBlock.classList.remove('border-green-400', 'border-dashed', 'bg-green-50/20');
                    if (dobInput.value && (dobInput.value < minDob || dobInput.value > maxDob)) {
                        dobInput.value = '';
                    }
                }
            }
        });

        // create student input fields
        function render(count) {
            // limit user
            if(count > 100) count = 100; 
            
            container.innerHTML = '';
            for (let i = 1; i <= count; i++) {
                container.insertAdjacentHTML('beforeend', `
                    <div class="bg-white border border-[#755f5420] rounded-3xl p-5 flex flex-col shadow-sm transition-all duration-300 student-card relative group">
                        
                        <!-- Header & exceptional Checkbox -->
                        <div class="flex justify-between items-center mb-4 border-b border-[#755f5410] pb-3">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center font-bold text-xs shadow-inner">
                                    ${i}
                                </div>
                                <span class="font-black text-primary text-sm tracking-tight">Student</span>
                            </div>
                            
                            <label class="flex items-center gap-1.5 cursor-pointer group/toggle bg-gray-50 px-2 py-1 rounded-lg border border-gray-100 hover:bg-orange-50 transition" title="Allow DOB outside standard year range">
                                <input type="checkbox" value="1" name="students[\${i}][is_exceptional]" id="students[\${i}][is_exceptional]" class="w-3.5 h-3.5 rounded text-green-500 border-gray-300 focus:ring-green-500 cursor-pointer transition">
                                <span class="text-[9px] font-bold text-gray-500 group-hover/toggle:text-primary transition-colors uppercase tracking-widest">exceptional DOB</span>
                            </label>
                        </div>

                        <!-- Inputs -->
                        <div class="space-y-3 flex-1">
                            <!-- Name Row -->
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[10px] font-bold text-primary/70 uppercase tracking-widest mb-1 pl-1">First Name</label>
                                    <input type="text" name="students[\${i}][first_name]" required class="w-full rounded-xl border-2 border-primary/10 bg-white px-3 py-2.5 text-sm font-semibold text-primary focus:border-primary focus:ring-0 transition-colors shadow-sm placeholder:text-gray-300 placeholder:font-medium" placeholder="First">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-primary/70 uppercase tracking-widest mb-1 pl-1">Last Name</label>
                                    <input type="text" name="students[\${i}][last_name]" required class="w-full rounded-xl border-2 border-primary/10 bg-white px-3 py-2.5 text-sm font-semibold text-primary focus:border-primary focus:ring-0 transition-colors shadow-sm placeholder:text-gray-300 placeholder:font-medium" placeholder="Last">
                                </div>
                            </div>

                            <!-- DOB Row -->
                            <div>
                                <label class="block text-[10px] font-bold text-primary/70 uppercase tracking-widest mb-1 pl-1">Date of Birth</label>
                                <input type="date" name="students[\${i}][dob]" required min="\${minDob}" max="\${maxDob}" class="w-full rounded-xl border-2 border-primary/10 bg-white px-3 py-2.5 text-sm font-semibold text-primary focus:border-primary focus:ring-0 transition-colors shadow-sm dob-input">
                                <p class="text-[10px] font-medium text-primary/50 mt-1 pl-1 leading-tight dob-text">Range: \${minDob} to \${maxDob}</p>
                            </div>

                            <!-- Level Row -->
                            <div>
                                <label class="block text-[10px] font-bold text-primary/70 uppercase tracking-widest mb-1 pl-1">Reading Level</label>
                                <input type="number" name="students[\${i}][level]" min="1" max="20" step="1" value="1" required class="w-full rounded-xl border-2 border-primary/10 bg-white px-3 py-2.5 text-sm font-semibold text-primary focus:border-primary focus:ring-0 transition-colors shadow-sm text-center">
                            </div>
                        </div>
                    </div>
                `);
            }
        }
        
        // restore form if validation fails
        const oldStudents = @json(old('students', (object)[]));
        const oldKeys = Object.keys(oldStudents);

        if (oldKeys.length > 0) {
            input.value = oldKeys.length;
            render(oldKeys.length);

            // prefill values
            Object.entries(oldStudents).forEach(([index, student]) => {
                const card = container.querySelectorAll('.student-card')[parseInt(index) - 1];
                if (!card) return;

                const fn = card.querySelector(`input[name="students[${index}][first_name]"]`);
                const ln = card.querySelector(`input[name="students[${index}][last_name]"]`);
                const dob = card.querySelector(`input[name="students[${index}][dob]"]`);
                const lvl = card.querySelector(`input[name="students[${index}][level]"]`);
                const exc = card.querySelector(`input[name="students[${index}][is_exceptional]"]`);

                if (fn && student.first_name) fn.value = student.first_name;
                if (ln && student.last_name)  ln.value  = student.last_name;
                if (dob && student.dob)       dob.value = student.dob;
                if (lvl && student.level)     lvl.value = student.level;

                if (exc && student.is_exceptional) {
                    exc.checked = true;
                    exc.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
        } else {
            render(1);
        }
    });
</script>