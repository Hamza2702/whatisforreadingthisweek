<x-teacher.layout :yearGroups="$yearGroups" title="Headteacher - Create Teacher">

    <div class="bg-white rounded-3xl p-6 md:p-10 shadow-sm border border-[#755f5420] max-w-3xl mx-auto">
        
        <div class="mb-8 border-b border-[#755f5410] pb-6">
            <h1 class="text-2xl font-black text-primary">Add a New Teacher</h1>
            <p class="text-primary/60 text-sm mt-1">Create an account for a new staff member. The teacher will automatically be assigned to {{ auth()->user()->school->name }}</p>
        </div>

        <!-- Create new teacher form, enctype="multipart/form-data" is for pfps -->
        <form action="{{ route('headteacher.teachers.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-semibold text-primary mb-2">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" placeholder="e.g. Mr Doe" required
                        class="block w-full rounded-xl border border-[#755f5420] bg-[#755f540a] text-primary focus:border-primary focus:ring-2 focus:ring-primary/30 sm:text-sm px-4 py-3 outline-none transition-all">
                    <x-form-error name="name"/>
                </div>

                <!-- Username -->
                <div>
                    <label for="username" class="block text-sm font-semibold text-primary mb-2">Username <span class="text-red-500">*</span></label>
                    <input type="text" name="username" id="username" value="{{ old('username') }}" placeholder="e.g. johndoe" required
                        class="block w-full rounded-xl border border-[#755f5420] bg-[#755f540a] text-primary focus:border-primary focus:ring-2 focus:ring-primary/30 sm:text-sm px-4 py-3 outline-none transition-all">
                    <x-form-error name="username"/>
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-semibold text-primary mb-2">Email Address <span class="text-red-500">*</span></label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" placeholder="e.g. johndoe@school.com" required
                        class="block w-full rounded-xl border border-[#755f5420] bg-[#755f540a] text-primary focus:border-primary focus:ring-2 focus:ring-primary/30 sm:text-sm px-4 py-3 outline-none transition-all">
                    <x-form-error name="email"/>
                </div>

                <!-- Phone -->
                <div>
                    <label for="phone" class="block text-sm font-semibold text-primary mb-2">Phone Number <span class="text-primary/40 font-normal text-xs">(Optional)</span></label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone') }}" placeholder="e.g. 07123456789"
                        class="block w-full rounded-xl border border-[#755f5420] bg-[#755f540a] text-primary focus:border-primary focus:ring-2 focus:ring-primary/30 sm:text-sm px-4 py-3 outline-none transition-all">
                    <x-form-error name="phone"/>
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-semibold text-primary mb-2">Temporary Password <span class="text-red-500">*</span></label>
                    <input type="password" name="password" id="password" placeholder="At least 8 characters" required
                        class="block w-full rounded-xl border border-[#755f5420] bg-[#755f540a] text-primary focus:border-primary focus:ring-2 focus:ring-primary/30 sm:text-sm px-4 py-3 outline-none transition-all">
                    <x-form-error name="password"/>
                </div>

                <!-- Confirm password -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold text-primary mb-2">Confirm Password <span class="text-red-500">*</span></label>
                    <input type="password" name="password_confirmation" id="password_confirmation" placeholder="Retype password" required
                        class="block w-full rounded-xl border border-[#755f5420] bg-[#755f540a] text-primary focus:border-primary focus:ring-2 focus:ring-primary/30 sm:text-sm px-4 py-3 outline-none transition-all">
                </div>

                <!-- PFP -->
                <div class="md:col-span-2">
                    <label for="pfp" class="block text-sm font-semibold text-primary mb-2">Profile Picture <span class="text-primary/40 font-normal text-xs">(Optional)</span></label>
                    <input type="file" name="pfp" id="pfp" accept="image/*"
                        class="block w-full text-sm text-primary/70 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:tracking-widest file:bg-primary file:text-white hover:file:bg-secondary file:cursor-pointer transition-all border border-[#755f5420] bg-[#755f540a] rounded-xl cursor-pointer">
                    <x-form-error name="pfp"/>
                </div>
            </div>

            <!-- Submit button -->
            <div class="pt-6">
                <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-4 text-base bg-primary text-background font-bold rounded-xl hover:bg-orange-900 shadow-md hover:shadow-lg transition-all duration-200 focus:ring-4 focus:ring-primary/30">
                    Create Teacher Account
                </button>
            </div>
        </form>

    </div>

</x-teacher.layout>