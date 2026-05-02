<x-teacher.layout :classroom="$classroom" :yearGroups="$yearGroups" title="Manage Student">
    <div class="w-full px-2 sm:px-4 py-2 flex-1 flex flex-col gap-4 sm:gap-6 lg:gap-8 font-sans">

        <!-- PROFILE -->
        <div class="bg-white border border-[#755f5420] rounded-2xl sm:rounded-3xl p-4 sm:p-6 md:p-8 shadow-sm">
            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4 mb-6">
                <!-- PFP TITLE -->
                <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                    <a href="{{ route('user.show', $student->user->id) }}"
                       title="View {{ $student->first_name }}'s profile"
                       class="flex-shrink-0">
                        <img src="{{ asset($student->pfp ?? '/images/pfp/cat.png') }}"
                             class="w-14 h-14 sm:w-16 sm:h-16 md:w-20 md:h-20 rounded-full object-cover ring-4 ring-white shadow hover:ring-primary/30 transition cursor-pointer">
                    </a>
                    <div class="min-w-0 flex-1">
                        <h3 class="text-xl sm:text-2xl md:text-3xl font-display text-primary leading-tight">
                            Profile Information
                        </h3>
                        <p class="text-xs sm:text-sm text-primary/60 mt-1">
                            Adjust the reading level, weekly goal, email, and password if needed.
                        </p>
                    </div>
                </div>

                <!-- RANK AND CSV -->
                <div class="flex flex-row items-stretch gap-3 flex-shrink-0">
                    @if($classRank)
                        <div class="bg-[#755f540a] border border-[#755f5420] rounded-2xl px-4 py-2 sm:px-5 sm:py-3 text-center shadow-sm flex-1 lg:flex-none">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-primary/60">Class Rank</p>
                            <p class="text-2xl md:text-3xl font-black text-primary leading-none mt-1">
                                #{{ $classRank }}<span class="text-sm md:text-base text-primary/50">/{{ $classSize }}</span>
                            </p>
                        </div>
                    @endif
                    <!-- download csv -->
                    <a href="{{ route('user.manage.export', $student->user_id) }}"
                       class="flex-1 lg:flex-none flex items-center justify-center px-4 sm:px-6 py-3 rounded-2xl lg:rounded-full text-xs sm:text-sm font-bold bg-primary text-background shadow-sm hover:bg-orange-900 transition-colors text-center whitespace-nowrap">
                        Download CSV
                    </a>
                </div>
            </div>

            <div id="profileMessage" class="hidden mb-4 px-4 py-3 rounded-lg font-bold text-sm"></div> <!-- error/success -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">

                <!-- FNAME -->
                <div class="bg-[#755f540a] border border-[#755f5410] rounded-2xl p-3 sm:p-4">
                    <p class="text-[10px] font-bold text-primary/60 uppercase tracking-widest mb-1">First Name</p>
                    <p class="font-bold text-primary text-base sm:text-lg break-words">{{ $student->first_name }}</p>
                </div>

                <!-- LNAME -->
                <div class="bg-[#755f540a] border border-[#755f5410] rounded-2xl p-3 sm:p-4">
                    <p class="text-[10px] font-bold text-primary/60 uppercase tracking-widest mb-1">Last Name</p>
                    <p class="font-bold text-primary text-base sm:text-lg break-words">{{ $student->last_name }}</p>
                </div>

                <!-- DOB -->
                <div class="bg-[#755f540a] border border-[#755f5410] rounded-2xl p-3 sm:p-4">
                    <p class="text-[10px] font-bold text-primary/60 uppercase tracking-widest mb-1">Date of Birth</p>
                    <p class="font-bold text-primary text-base sm:text-lg">
                        {{ $student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth)->format('M j, Y') : 'Not set' }}
                    </p>
                </div>

                <!-- USERNAME -->
                <div class="bg-[#755f540a] border border-[#755f5410] rounded-2xl p-3 sm:p-4">
                    <p class="text-[10px] font-bold text-primary/60 uppercase tracking-widest mb-1">Username</p>
                    <p class="font-bold text-primary text-base sm:text-lg truncate">@ {{ $student->user->username }}</p>
                </div>

                <!-- LEVEL -->
                <div class="bg-white border-2 border-[#755f5420] rounded-2xl p-3 sm:p-4" data-field="level">
                    <p class="text-[10px] font-bold text-primary/60 uppercase tracking-widest mb-1">Reading Level</p>
                    <div class="flex items-center justify-between gap-2 sm:gap-3">
                        <div class="flex-1 min-w-0 field-display">
                            <p class="font-bold text-primary text-base sm:text-lg">Level {{ $student->level }}</p>
                        </div>
                        <div class="flex-1 min-w-0 field-edit hidden">
                            <input type="number" min="1" max="20" value="{{ $student->level }}"
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

                <!-- WEEKLY GOAL -->
                <div class="bg-white border-2 border-[#755f5420] rounded-2xl p-3 sm:p-4" data-field="weekly_goal">
                    <p class="text-[10px] font-bold text-primary/60 uppercase tracking-widest mb-1">Weekly Goal</p>
                    <div class="flex items-center justify-between gap-2 sm:gap-3">
                        <div class="flex-1 min-w-0 field-display">
                            <p class="font-bold text-primary text-base sm:text-lg">
                                {{ $weeklyGoalTarget }} {{ $weeklyGoalTarget == 1 ? 'book' : 'books' }} per week
                            </p>
                        </div>
                        <div class="flex-1 min-w-0 field-edit hidden">
                            <input type="number" min="1" max="3" value="{{ $weeklyGoalTarget }}"
                                data-input="value"
                                class="w-full px-3 py-2 border-2 border-primary/30 rounded-lg font-bold text-primary focus:outline-none focus:border-primary">
                            <p class="text-[11px] text-primary/50 mt-1 leading-snug">
                                Minimum 1, maximum 3 books per week.
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
                            <p class="font-bold text-primary text-base sm:text-lg truncate">{{ $student->user->email ?? 'Not set' }}</p>
                        </div>
                        <div class="flex-1 min-w-0 field-edit hidden">
                            <input type="email" value="{{ $student->user->email }}"
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

        <!-- TOP STATS -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 lg:gap-6">
            <!-- books read -->
            <div class="bg-primary rounded-2xl sm:rounded-3xl p-4 sm:p-6 flex flex-col justify-center items-center text-center shadow-md">
                <span class="text-3xl sm:text-4xl md:text-5xl font-black text-background mb-1 sm:mb-2">{{ $totalBooks }}</span>
                <span class="text-[9px] sm:text-[10px] md:text-xs font-bold text-background/80 tracking-widest uppercase">Books Read</span>
            </div>
            <!-- avg difficulty -->
            <div class="bg-white border border-primary/10 rounded-2xl sm:rounded-3xl p-4 sm:p-6 flex flex-col justify-center items-center text-center shadow-sm">
                <span class="text-2xl sm:text-3xl md:text-4xl font-black text-primary mb-1 sm:mb-2 capitalize break-words">{{ $avgDifficulty }}</span>
                <span class="text-[9px] sm:text-[10px] md:text-xs font-bold text-primary/70 tracking-widest uppercase">Avg Difficulty</span>
            </div>
            <!-- avg rating -->
            <div class="bg-[#755f5415] border border-primary/10 rounded-2xl sm:rounded-3xl p-4 sm:p-6 flex flex-col justify-center items-center text-center shadow-sm">
                <span class="text-3xl sm:text-4xl md:text-5xl font-black text-primary mb-1 sm:mb-2">{{ $avgRating }}</span>
                <span class="text-[9px] sm:text-[10px] md:text-xs font-bold text-primary/70 tracking-widest uppercase">Average Rating</span>
            </div>
            <!-- day streak -->
            <div class="bg-white border border-primary/10 rounded-2xl sm:rounded-3xl p-4 sm:p-6 flex flex-col justify-center items-center text-center shadow-sm">
                <span class="text-3xl sm:text-4xl md:text-5xl font-black text-primary mb-1 sm:mb-2">{{ $streak }}</span>
                <span class="text-[9px] sm:text-[10px] md:text-xs font-bold text-primary/70 tracking-widest uppercase">Day Streak</span>
            </div>
        </div>

        <!-- WEEKLY GOAL PERFORMANCE -->
        <div class="bg-white border border-[#755f5420] rounded-2xl sm:rounded-3xl p-4 sm:p-6 md:p-8 shadow-sm">
            <h3 class="text-xl sm:text-2xl font-display text-primary mb-1">Weekly Goal Performance</h3>
            <p class="text-xs sm:text-sm text-primary/60 mb-4">
                Target: {{ $weeklyGoalTarget }} {{ $weeklyGoalTarget == 1 ? 'book' : 'books' }} per week.
                A green bar means {{ $student->first_name }} hit their goal that week.
            </p>
            <div class="relative w-full h-[180px] sm:h-[200px] md:h-[220px]">
                <canvas id="studentWeeklyChart"></canvas>
            </div>
        </div>

        <!-- READING ACTIVITY -->
        <div class="bg-white border border-[#755f5420] rounded-2xl sm:rounded-3xl p-4 sm:p-6 md:p-8 shadow-sm">
            <h3 class="text-xl sm:text-2xl font-display text-primary mb-1">Reading Activity</h3>
            <p class="text-xs sm:text-sm text-primary/60 mb-4">Books finished each month across the {{ $academicStart }}/{{ $academicEnd }} academic year</p>
            <div class="relative w-full h-[220px] sm:h-[260px] md:h-[280px]">
                <canvas id="studentActivityChart"></canvas>
            </div>
        </div>

        <!-- CURRENTLY READING -->
        @if($currentlyReading->isNotEmpty())
        <div class="bg-[#755f540a] border border-[#755f5420] rounded-2xl sm:rounded-3xl p-4 sm:p-6 md:p-8 shadow-sm">
            <h3 class="text-xl sm:text-2xl font-display text-primary mb-1">Currently Reading</h3>
            <p class="text-xs sm:text-sm text-primary/60 mb-4">Books this student has started but not finished, encourage them!</p>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2 sm:gap-3">
                @foreach($currentlyReading as $b)
                    <a href="{{ route('books.show', $b->id) }}"
                        title="View {{ $b->title }}"
                        class="bg-white rounded-xl sm:rounded-2xl p-2 sm:p-3 shadow-sm border border-[#755f5410] hover:shadow-md hover:border-primary/30 transition block">
                        <p class="font-bold text-primary text-xs sm:text-sm truncate">{{ $b->title }}</p>
                        <p class="text-[10px] sm:text-xs text-primary/60 truncate">{{ $b->author }}</p>
                    </a>
                @endforeach
            </div>
        </div>
        @endif

        <!-- READING HISTORY -->
        <div class="bg-white border border-[#755f5420] rounded-2xl sm:rounded-3xl p-4 sm:p-6 md:p-8 shadow-sm">
            <h3 class="text-xl sm:text-2xl font-display text-primary mb-1">Reading History</h3>
            <p class="text-xs sm:text-sm text-primary/60 mb-4">All books completed this year</p>
            <div class="max-h-[360px] sm:max-h-[420px] overflow-y-auto pr-1 sm:pr-2 flex flex-col gap-2">
                @forelse($completedBooks as $b)
                    <a href="{{ route('books.show', $b->id) }}"
                        title="View {{ $b->title }}"
                        class="flex items-center gap-2 sm:gap-3 bg-[#755f540a] rounded-xl p-2 sm:p-3 border border-[#755f5410] hover:bg-white hover:border-primary/30 hover:shadow-sm transition">
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-primary text-sm sm:text-base truncate">{{ $b->title }}</p>
                            <p class="text-[10px] sm:text-xs text-primary/60 truncate">{{ $b->author }}</p>
                        </div>
                        <span class="text-[10px] sm:text-xs font-bold text-primary/60 flex-shrink-0 whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($b->finished_at)->format('M j, Y') }}
                        </span>
                    </a>
                @empty
                    <p class="text-primary/60 italic text-sm">No books completed yet this year.</p>
                @endforelse
            </div>
        </div>

        <!-- REVIEWS -->
        <div class="bg-[#755f540a] border border-[#755f5420] rounded-2xl sm:rounded-3xl p-4 sm:p-6 md:p-8 shadow-sm">
            <h3 class="text-xl sm:text-2xl font-display text-primary mb-1">Reviews Written</h3>
            <p class="text-xs sm:text-sm text-primary/60 mb-4">{{ $totalReviews }} reviews this year</p>
            <div class="max-h-[360px] sm:max-h-[420px] overflow-y-auto pr-1 sm:pr-2 flex flex-col gap-3">
                @forelse($reviews as $r)
                    <div class="bg-white rounded-xl sm:rounded-2xl p-3 sm:p-4 shadow-sm border border-[#755f5410]">
                        <div class="flex justify-between items-start gap-2 sm:gap-3 mb-1">
                            <p class="font-bold text-primary text-sm sm:text-base break-words min-w-0">{{ $r->title ?? $r->book_title }}</p>
                            <span class="text-xs font-bold text-primary/60 flex-shrink-0">{{ $r->rating }}/5</span>
                        </div>
                        @if($r->description)
                            <p class="text-xs sm:text-sm text-primary/70 break-words">{{ $r->description }}</p>
                        @endif
                        <p class="text-[10px] uppercase tracking-widest font-bold text-primary/40 mt-2">
                            {{ ucfirst($r->difficulty ?? 'unknown') }} difficulty
                        </p>
                    </div>
                @empty
                    <p class="text-primary/60 italic text-sm">No reviews written yet.</p>
                @endforelse
            </div>
        </div>

        <!-- GENRES AND PHONICS -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 lg:gap-8 items-start">

            <!-- GENRES EXPLORED -->
            <div class="bg-[#755f540a] border border-[#755f5420] rounded-2xl sm:rounded-3xl p-4 sm:p-6 md:p-8 shadow-sm h-full">
                <h3 class="text-xl sm:text-2xl font-display text-primary mb-1">Genres Explored</h3>
                <p class="text-xs sm:text-sm text-primary/60 mb-4">What {{ $student->first_name }} likes to read</p>
                <div class="flex flex-col gap-3 sm:gap-4">
                    @forelse($genresCount as $genre => $count)
                        <div>
                            <div class="flex justify-between items-end mb-1 gap-2">
                                <span class="font-bold text-primary text-sm sm:text-base truncate">{{ $genre }}</span>
                                <span class="text-[10px] sm:text-xs font-black text-primary/60 uppercase tracking-widest whitespace-nowrap flex-shrink-0">
                                    {{ $count }} {{ $count == 1 ? 'book' : 'books' }}
                                </span>
                            </div>
                            <div class="w-full bg-white rounded-full h-2.5 sm:h-3 overflow-hidden shadow-inner">
                                <div class="bg-primary h-full rounded-full"
                                    style="width: {{ max(8, ($count / max($genresCount)) * 100) }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-primary/60 italic text-sm">No genre data yet.</p>
                    @endforelse
                </div>
            </div>

            <!-- PHONICS -->
            <div class="bg-[#755f540a] border border-[#755f5420] rounded-2xl sm:rounded-3xl p-4 sm:p-6 md:p-8 shadow-sm h-full">
                <div class="flex items-start justify-between gap-3 sm:gap-4 mb-1">
                    <div class="min-w-0">
                        <h3 class="text-xl sm:text-2xl font-display text-primary mb-1">Phonics</h3>
                        <p class="text-xs sm:text-sm text-primary/60 mb-4">
                            @if($student->level >= 8)
                                {{ $student->first_name }} has mastered all phonics
                            @else
                                Phonic sounds {{ $student->first_name }} has encountered
                            @endif
                        </p>
                    </div>
                    <span class="text-[10px] font-bold text-primary/60 uppercase tracking-widest bg-white border border-[#755f5420] rounded-full px-2 sm:px-3 py-1 whitespace-nowrap flex-shrink-0">
                        Level {{ $student->level }}
                    </span>
                </div>

                @if($student->level >= 8)
                    <div class="bg-gradient-to-br from-green-100 to-emerald-50 border-2 border-green-300 rounded-2xl p-6 sm:p-8 flex flex-col items-center justify-center text-center">
                        <h4 class="text-base sm:text-xl font-display text-green-500 mb-1">Phonics Mastered!</h4>
                        <p class="text-xs sm:text-sm text-green-800/70 max-w-md">
                            {{ $student->first_name }} has progressed past Level 7 and doesn't need to track any phonics.
                        </p>
                    </div>
                @elseif(count($phonicsCount) > 0)
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 sm:gap-3">
                        @foreach($phonicsCount as $sound => $count)
                            <div class="bg-white rounded-xl sm:rounded-2xl p-3 sm:p-4 border border-[#755f5410] shadow-sm flex items-center justify-between gap-2 sm:gap-3">
                                <span class="font-bold text-primary text-base sm:text-lg truncate min-w-0">{{ $sound }}</span>
                                <div class="text-right flex-shrink-0">
                                    <span class="font-black text-primary text-lg sm:text-xl">{{ $count }}</span>
                                    <p class="text-[9px] sm:text-[10px] uppercase tracking-widest font-bold text-primary/60 leading-tight">
                                        {{ $count == 1 ? 'book' : 'books' }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="bg-white border-2 border-dashed border-[#755f5430] rounded-2xl p-6 sm:p-8 text-center">
                        <p class="text-primary/60 italic text-sm">
                            {{ $student->first_name }} hasn't read any books with phonics tracking yet.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {

        // profile editing
        const updateUrl  = "{{ route('user.manage.updateField', $student->user->id) }}";
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

            // edit mode
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
                    if (field === 'level')
                        p.textContent = 'Level ' + data.value;
                    else if (field === 'weekly_goal')
                        p.textContent = data.value + ' ' + (data.value == 1 ? 'book' : 'books') + ' per week';
                    else if (field === 'password')
                        p.textContent = '********';
                    else
                        p.textContent = data.value;
                    // reload page
                    if (field === 'weekly_goal') {
                        setTimeout(() => window.location.reload(), 1000);
                    }

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

        // ==============================================
        // CHART W/ MOBILE
        const isMobile = window.innerWidth < 640;

        // reading activity chart
        const data = @json($chartData);
        const el = document.getElementById('studentActivityChart');
        if (el) {
            new Chart(el.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: Object.keys(data),
                    datasets: [{
                        label: 'Books Finished',
                        data: Object.values(data),
                        backgroundColor: '#fb923c',
                        hoverBackgroundColor: '#f97316',
                        borderRadius: 4,
                        barThickness: isMobile ? 14 : 28,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { backgroundColor: '#6D4423', padding: 12, cornerRadius: 8, displayColors: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1, precision: 0, color: '#6D4423', font: { size: isMobile ? 10 : 12 } }
                        },
                        x: {
                            ticks: {
                                color: '#6D4423',
                                font: { size: isMobile ? 9 : 12 },
                                maxRotation: isMobile ? 45 : 0,
                                minRotation: isMobile ? 45 : 0,
                                autoSkip: true,
                                autoSkipPadding: 6,
                            },
                            grid: { display: false }
                        }
                    }
                }
            });
        }

        // weekly goal performance chart
        const weeks = @json($weeks);
        const weeklyEl = document.getElementById('studentWeeklyChart');
        if (weeklyEl) {
            new Chart(weeklyEl.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: weeks.map(w => w.label),
                    datasets: [{
                        label: '% of goal hit',
                        data: weeks.map(w => w.percentage),
                        backgroundColor: weeks.map(w => w.hit_goal ? '#22c55e' : '#ef4444'),
                        borderRadius: 4,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#6D4423',
                            padding: 12,
                            cornerRadius: 8,
                            callbacks: {
                                title: ctx => weeks[ctx[0].dataIndex].label + ' (' + weeks[ctx[0].dataIndex].date_range + ')',
                                label: ctx => {
                                    const w = weeks[ctx.dataIndex];
                                    return [
                                        w.percentage + '% of goal hit',
                                        w.books + ' / ' + w.target + ' books read'
                                    ];
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                color: '#6D4423',
                                font: { size: isMobile ? 10 : 12 },
                                callback: v => v + '%'
                            }
                        },
                        x: {
                            ticks: {
                                color: '#6D4423',
                                font: { size: isMobile ? 9 : 12 },
                                maxRotation: isMobile ? 45 : 0,
                                minRotation: 0,
                                autoSkip: true,
                                autoSkipPadding: 8
                            },
                            grid: { display: false }
                        }
                    }
                }
            });
        }
    });
    </script>
</x-teacher.layout>