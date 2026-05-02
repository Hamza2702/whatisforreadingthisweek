<x-layout>
    <div class="w-full mx-auto p-4 sm:p-6 lg:p-8">

        <!-- PROFILE -->
        <div class="bg-white border border-[#755f5420] rounded-2xl sm:rounded-3xl p-4 sm:p-6 md:p-8 shadow-sm">
            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4 mb-6">
                <!-- PFP TITLE -->
                <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                    <div class="flex-shrink-0">
                        <img src="{{ asset($user->pfp ?? '/images/pfp/cat.png') }}"
                            alt="Profile picture"
                            class="w-14 h-14 sm:w-16 sm:h-16 md:w-20 md:h-20 rounded-full object-cover ring-4 ring-white shadow">
                    </div>
                    <div class="min-w-0 flex-1">
                        <h3 class="text-xl sm:text-2xl md:text-3xl font-display text-primary leading-tight">
                            Profile Information
                        </h3>
                        <p class="text-xs sm:text-sm text-primary/60 mt-1">
                            Adjust the email and password if needed.
                        </p>
                    </div>
                </div>
            </div>

            <div id="profileMessage" class="hidden mb-4 px-4 py-3 rounded-lg font-bold text-sm"></div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">

                <!-- NAME -->
                <div class="bg-[#755f540a] border border-[#755f5410] rounded-2xl p-3 sm:p-4">
                    <p class="text-[10px] font-bold text-primary/60 uppercase tracking-widest mb-1">Name</p>
                    <p class="font-bold text-primary text-base sm:text-lg break-words">
                        {{ $user->name ?? 'Not set' }}
                    </p>
                </div>

                <!-- USERNAME -->
                <div class="bg-[#755f540a] border border-[#755f5410] rounded-2xl p-3 sm:p-4">
                    <p class="text-[10px] font-bold text-primary/60 uppercase tracking-widest mb-1">Username</p>
                    <p class="font-bold text-primary text-base sm:text-lg truncate">
                        @ {{ $user->username ?? 'Not set' }}
                    </p>
                </div>

                <!-- ROLE -->
                <div class="bg-[#755f540a] border border-[#755f5410] rounded-2xl p-3 sm:p-4">
                    <p class="text-[10px] font-bold text-primary/60 uppercase tracking-widest mb-1">Role</p>
                    <p class="font-bold text-primary text-base sm:text-lg capitalize">
                        {{ $user->role ?? 'User' }}
                    </p>
                </div>

                <!-- PHONE -->
                <div class="bg-white border-2 border-[#755f5420] rounded-2xl p-3 sm:p-4" data-field="phone">
                    <p class="text-[10px] font-bold text-primary/60 uppercase tracking-widest mb-1">Phone</p>
                    <div class="flex items-center justify-between gap-2 sm:gap-3">
                        <div class="flex-1 min-w-0 field-display">
                            <p class="font-bold text-primary text-base sm:text-lg truncate">
                                {{ $user->phone ?? 'Not set' }}
                            </p>
                        </div>
                        <div class="flex-1 min-w-0 field-edit hidden">
                            <input type="tel" value="{{ $user->phone }}"
                                placeholder="07123 456789"
                                data-input="value"
                                class="w-full px-3 py-2 border-2 border-primary/30 rounded-lg font-bold text-primary focus:outline-none focus:border-primary">
                            <p class="text-[11px] text-primary/50 mt-1 leading-snug">
                                UK number, e.g. 07123 456789
                            </p>
                        </div>
                        <div class="flex items-center gap-1 flex-shrink-0">
                            <button type="button" class="edit-btn p-2 text-primary/50 hover:text-primary hover:bg-orange-50 rounded-lg transition" title="Edit">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                </svg>
                            </button>
                            <button type="button" class="save-btn hidden p-2 text-green-600 hover:text-green-800 hover:bg-green-50 rounded-lg transition" title="Save">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                            </button>
                            <button type="button" class="cancel-btn hidden p-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition" title="Cancel">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- EMAIL -->
                <div class="bg-white border-2 border-[#755f5420] rounded-2xl p-3 sm:p-4 sm:col-span-2" data-field="email">
                    <p class="text-[10px] font-bold text-primary/60 uppercase tracking-widest mb-1">Email</p>
                    <div class="flex items-center justify-between gap-2 sm:gap-3">
                        <div class="flex-1 field-display min-w-0">
                            <p class="font-bold text-primary text-base sm:text-lg truncate">
                                {{ $user->email ?? 'Not set' }}
                            </p>
                        </div>
                        <div class="flex-1 min-w-0 field-edit hidden">
                            <input type="email" value="{{ $user->email }}"
                                data-input="value"
                                class="w-full px-3 py-2 border-2 border-primary/30 rounded-lg font-bold text-primary focus:outline-none focus:border-primary">
                        </div>
                        <div class="flex items-center gap-1 flex-shrink-0">
                            <button type="button" class="edit-btn p-2 text-primary/50 hover:text-primary hover:bg-orange-50 rounded-lg transition" title="Edit">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                </svg>
                            </button>
                            <button type="button" class="save-btn hidden p-2 text-green-600 hover:text-green-800 hover:bg-green-50 rounded-lg transition" title="Save">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                            </button>
                            <button type="button" class="cancel-btn hidden p-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition" title="Cancel">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- PASSWORD -->
                <div class="bg-white border-2 border-[#755f5420] rounded-2xl p-3 sm:p-4 sm:col-span-2" data-field="password">
                    <p class="text-[10px] font-bold text-primary/60 uppercase tracking-widest mb-1">Password</p>
                    <div class="flex items-start justify-between gap-2 sm:gap-3">
                        <div class="flex-1 min-w-0 field-display">
                            <p class="font-bold text-primary text-base sm:text-lg tracking-widest">********</p>
                        </div>
                        <div class="flex-1 min-w-0 field-edit hidden">
                            <input type="password" placeholder="New password"
                                data-input="value"
                                class="w-full px-3 py-2 mb-2 border-2 border-primary/30 rounded-lg font-bold text-primary focus:outline-none focus:border-primary">
                            <input type="password" placeholder="Confirm new password"
                                data-input="value_confirmation"
                                class="w-full px-3 py-2 border-2 border-primary/30 rounded-lg font-bold text-primary focus:outline-none focus:border-primary">
                            <p class="text-[11px] text-primary/50 mt-2 leading-snug">
                                Must be at least 8 characters and include uppercase, lowercase, a number, and a symbol. Be sure to write this down somewhere!
                            </p>
                        </div>
                        <div class="flex items-center gap-1 flex-shrink-0">
                            <button type="button" class="edit-btn p-2 text-primary/50 hover:text-primary hover:bg-orange-50 rounded-lg transition" title="Edit">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                </svg>
                            </button>
                            <button type="button" class="save-btn hidden p-2 text-green-600 hover:text-green-800 hover:bg-green-50 rounded-lg transition" title="Save">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                            </button>
                            <button type="button" class="cancel-btn hidden p-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition" title="Cancel">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {

        // profile editing
        const updateUrl  = "{{ route('account.updateField', $user->id) }}";
        const csrfToken  = "{{ csrf_token() }}";
        const messageBox = document.getElementById('profileMessage');

        // show message
        function showMessage(text, isError = false) {
            messageBox.textContent = text;
            messageBox.className = isError
                ? 'mb-4 px-4 py-3 rounded-lg font-bold text-sm bg-red-100 border-2 border-red-500 text-red-700'
                : 'mb-4 px-4 py-3 rounded-lg font-bold text-sm bg-green-100 border-2 border-green-500 text-green-700';
            setTimeout(() => messageBox.classList.add('hidden'), 5000);
        }

        // get data
        document.querySelectorAll('[data-field]').forEach(card => {
            const field      = card.dataset.field;
            const display    = card.querySelector('.field-display');
            const editArea   = card.querySelector('.field-edit');
            const inputs     = editArea.querySelectorAll('input[data-input]');
            const mainInput  = editArea.querySelector('input[data-input="value"]');
            const editBtn    = card.querySelector('.edit-btn');
            const saveBtn    = card.querySelector('.save-btn');
            const cancelBtn  = card.querySelector('.cancel-btn');

            const originalValue = mainInput.value; // original val

            //edit mode
            const enterEditMode = () => {
                display.classList.add('hidden');
                editArea.classList.remove('hidden');
                editBtn.classList.add('hidden');
                saveBtn.classList.remove('hidden');
                cancelBtn.classList.remove('hidden');
                mainInput.focus();
                if (mainInput.type !== 'password') mainInput.select();
            };

            // back to normal
            const exitEditMode = () => {
                display.classList.remove('hidden');
                editArea.classList.add('hidden');
                editBtn.classList.remove('hidden');
                saveBtn.classList.add('hidden');
                cancelBtn.classList.add('hidden');
                if (field === 'password') {
                    inputs.forEach(i => i.value = '');
                }
            };

            editBtn.addEventListener('click', enterEditMode);

            // cancel
            cancelBtn.addEventListener('click', () => {
                if (field !== 'password') mainInput.value = originalValue;
                exitEditMode();
            });

            // save
            saveBtn.addEventListener('click', async () => {
                const payload = { field };
                inputs.forEach(i => { payload[i.dataset.input] = i.value; });

                if (!payload.value || !payload.value.trim()) {
                    showMessage('Please enter a value', true);
                    return;
                }

                // update
                saveBtn.disabled = true;
                try {
                    const res = await fetch(updateUrl, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(payload),
                    });

                    const data = await res.json();

                    if (!res.ok || !data.success) {
                        const msg = data.errors
                            ? Object.values(data.errors).flat().join(' ')
                            : (data.message || 'Update failed');
                        showMessage(msg, true);
                        saveBtn.disabled = false;
                        return;
                    }

                    // add to database
                    const p = display.querySelector('p');
                    if (field === 'password')
                        p.textContent = '********';
                    else
                        p.textContent = data.value;

                    showMessage(data.message || 'Updated successfully');
                    exitEditMode();
                } catch (e) {
                    showMessage('Network error. Please try again.', true);
                } finally {
                    saveBtn.disabled = false;
                }
            });

                    inputs.forEach(input => {
                input.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter')  { e.preventDefault(); saveBtn.click(); }
                    if (e.key === 'Escape') { e.preventDefault(); cancelBtn.click(); }
                });
            });
        });
    });
    </script>
</x-layout>