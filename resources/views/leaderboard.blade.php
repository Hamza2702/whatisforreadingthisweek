<x-layout title="Classroom Leaderboard">
    <div class="w-full lg:w-3/5 xl:w-full mx-auto px-3 sm:px-6 py-6 sm:py-8 flex flex-col gap-4 sm:gap-6">
        <!-- Header -->
        <div class="bg-white border border-[#755f5420] rounded-3xl p-5 sm:p-8 flex flex-col sm:flex-row items-center justify-between gap-4 shadow-sm w-full relative overflow-hidden">
            <div class="relative z-10 text-center sm:text-left">
                <!-- Year -->
                <h1 class="text-2xl sm:text-4xl font-black text-primary tracking-tight">
                    Class Leaderboard - {{ $class ? ($class->year_group == 0 ? 'Reception' : 'Year ' . $class->year_group) : 'Your Class' }}
                </h1>
                <!-- Month -->
                <p class="text-primary/60 text-sm sm:text-base font-medium mt-1">Top readers for the month of <strong class="text-primary">{{ $monthName }}</strong></p>
            </div>
            <!-- Reset -->
            <div class="relative z-10 flex flex-row gap-3">
                <div class="bg-background/90 border border-primary px-4 py-2 sm:px-5 sm:py-3 rounded-2xl flex flex-col items-center justify-center shadow-sm min-w-[100px] sm:min-w-[120px]" id="countdown-container" data-target="{{ $targetDateIso }}">
                    <div class="flex items-baseline gap-1 mt-0.5">
                        <span id="countdown-value" class="text-lg sm:text-2xl font-black text-primary"></span>
                        <span id="countdown-unit" class="text-[10px] sm:text-xs font-bold text-primary uppercase"></span>
                    </div>
                </div>
            </div>
        </div>
        <!-- MONTH // ALL TIME -->
        <div class="flex space-x-1 sm:space-x-2 bg-white p-1 sm:p-1.5 rounded-2xl w-fit mx-auto sm:mx-0 border border-[#755f5420] shadow-sm relative z-10">
            <button id="btn-monthly" onclick="switchTab('monthly')" class="px-4 sm:px-6 py-2 sm:py-2.5 rounded-xl bg-primary text-white font-bold text-xs sm:text-sm transition-all duration-200 shadow-sm">
                This Month
            </button>
            <button id="btn-alltime" onclick="switchTab('alltime')" class="px-4 sm:px-6 py-2 sm:py-2.5 rounded-xl text-primary/60 hover:text-primary font-bold text-xs sm:text-sm transition-all duration-200">
                All Time
            </button>
        </div>

        <!-- LEADERBOARD -->
        <div class="bg-[#755f540a] border border-[#755f5420] rounded-3xl p-3 sm:p-6 shadow-sm overflow-hidden">
            <!-- Monthly -->
            <div id="board-monthly" class="flex flex-col gap-2 sm:gap-3">
                @forelse($monthlyStudents as $index => $boardStudent)
                    @php
                        $rank = $index + 1;
                        $bgClass = 'bg-white';
                        $borderClass = 'border-orange-100';
                        $rankColour = 'text-primary/40 bg-gray-100';
                        
                        // get current user
                        $isCurrentUser = auth()->user()->student && $boardStudent->id === auth()->user()->student->id;

                        // 1st, 2nd, 3rd
                        if ($rank === 1) {
                            $bgClass = 'bg-yellow-50';
                            $rankColour = 'text-yellow-700 bg-yellow-200';
                        } elseif ($rank === 2) {
                            $bgClass = 'bg-slate-100';
                            $rankColour = 'text-slate-700 bg-slate-200';
                        } elseif ($rank === 3) {
                            $bgClass = 'bg-orange-50';
                            $rankColour = 'text-orange-800 bg-orange-200';
                        }

                        // current user
                        if ($isCurrentUser) {
                            $borderClass = ' border-primary border-2 shadow-md relative z-10';
                        }
                    @endphp

                    <!-- Leaderboard -->
                    <div class="{{ $bgClass }} {{ $borderClass }} rounded-2xl p-3 sm:p-5 flex items-center justify-between group gap-2 sm:gap-4">
                        <!-- Left side -->
                        <div class="flex items-center gap-2 sm:gap-6 min-w-0 flex-1">
                            <!-- rank -->
                            <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full flex items-center justify-center font-black text-sm sm:text-base {{ $rankColour }} shadow-sm shrink-0">
                                {{ $rank }}
                            </div>
                            <!-- pfp -->
                            <div class="w-10 h-10 sm:w-14 sm:h-14 rounded-full bg-[#755f5410] shadow-sm overflow-hidden shrink-0">
                                <a href="{{ route('user.show', ['id' => $boardStudent->user_id]) }}" target="_blank">
                                    <img src="{{ $boardStudent->pfp ? asset($boardStudent->pfp) : asset('/images/Placeholder.jpeg') }}" class="w-full h-full object-cover">
                                </a>
                            </div>
                            <!-- name -->
                            <div class="flex flex-col min-w-0 flex-1">
                                <h3 class="text-sm sm:text-base font-black text-primary leading-tight flex items-center gap-1 sm:gap-2">
                                    <span class="truncate">{{ $boardStudent->first_name }} {{ $boardStudent->last_name }}</span>
                                    @if($isCurrentUser)
                                        <span class="px-1.5 py-0.5 bg-primary text-white text-[8px] sm:text-[9px] uppercase tracking-widest rounded-md shrink-0">You</span>
                                    @endif
                                </h3>
                                <!-- username and level -->
                                <div class="flex flex-wrap items-center gap-x-1 sm:gap-x-2 gap-y-0.5 mt-0.5 sm:mt-1 text-[10px] sm:text-sm">
                                    <span class="font-bold text-primary/50 truncate max-w-[90px] sm:max-w-none">{{ '@' . optional($boardStudent->user)->username }}</span>
                                    <span class="text-primary/30 hidden sm:inline">|</span>
                                    <span class="font-bold text-secondary whitespace-nowrap">Lvl {{ $boardStudent->level }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Right -->
                        <div class="flex flex-col items-end justify-center shrink-0 text-right pl-2 sm:pl-4">
                            <!-- monthly books count -->
                            <span class="text-xl sm:text-3xl font-black text-primary leading-none">{{ $boardStudent->books_read_count }}</span>
                            <span class="text-[8px] sm:text-[10px] font-bold text-primary/50 uppercase tracking-widest mt-1">
                                <span class="hidden sm:inline">Books Read</span>
                                <!-- mobile users -->
                                <span class="sm:hidden">Books</span>
                            </span>
                        </div>
                    </div>
                @empty
                <!-- Empty-->
                    <div class="py-12 flex flex-col items-center justify-center text-center px-4">
                        <h3 class="text-lg font-black text-primary">No reading data yet!</h3>
                        <p class="text-xs sm:text-sm text-primary/60 mt-1">Check back later when students start reading this month.</p>
                    </div>
                @endforelse
            </div>

            <!-- ALL TIME -->
            <div id="board-alltime" class="hidden flex-col gap-2 sm:gap-3">
                @forelse($allTimeStudents as $index => $boardStudent)
                    @php
                        $rank = $index + 1;
                        $bgClass = 'bg-white';
                        $borderClass = 'border-orange-100';
                        $rankColour = 'text-primary/40 bg-gray-100';
                        
                        // get current user
                        $isCurrentUser = auth()->user()->student && $boardStudent->id === auth()->user()->student->id;

                        // 1st, 2nd, 3rd
                        if ($rank === 1) {
                            $bgClass = 'bg-yellow-50';
                            $rankColour = 'text-yellow-700 bg-yellow-200';
                        } elseif ($rank === 2) {
                            $bgClass = 'bg-slate-100';
                            $rankColour = 'text-slate-700 bg-slate-200';
                        } elseif ($rank === 3) {
                            $bgClass = 'bg-orange-50';
                            $rankColour = 'text-orange-800 bg-orange-200';
                        }

                        // current user
                        if ($isCurrentUser) {
                            $borderClass = ' border-primary border-2 shadow-md relative z-10';
                        }
                    @endphp

                    <!-- Leaderboard -->
                    <div class="{{ $bgClass }} {{ $borderClass }} rounded-2xl p-3 sm:p-5 flex items-center justify-between group gap-2 sm:gap-4">
                        <!-- Left -->
                        <div class="flex items-center gap-2 sm:gap-6 min-w-0 flex-1">
                            <!-- rank -->
                            <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full flex items-center justify-center font-black text-sm sm:text-base {{ $rankColour }} shadow-sm shrink-0">
                                {{ $rank }}
                            </div>
                            <!-- pfp -->
                            <div class="w-10 h-10 sm:w-14 sm:h-14 rounded-full bg-[#755f5410] shadow-sm overflow-hidden shrink-0">
                                <a href="{{ route('user.show', ['id' => $boardStudent->user_id]) }}" target="_blank">
                                    <img src="{{ $boardStudent->pfp ? asset($boardStudent->pfp) : asset('/images/Placeholder.jpeg') }}" class="w-full h-full object-cover">
                                </a>
                            </div>
                            <!-- name -->
                            <div class="flex flex-col min-w-0 flex-1">
                                <h3 class="text-sm sm:text-base font-black text-primary leading-tight flex items-center gap-1 sm:gap-2">
                                    <span class="truncate">{{ $boardStudent->first_name }} {{ $boardStudent->last_name }}</span>
                                    @if($isCurrentUser)
                                        <span class="px-1.5 py-0.5 bg-primary text-white text-[8px] sm:text-[9px] uppercase tracking-widest rounded-md shrink-0">You</span>
                                    @endif
                                </h3>
                                <!-- username and level -->
                                <div class="flex flex-wrap items-center gap-x-1 sm:gap-x-2 gap-y-0.5 mt-0.5 sm:mt-1 text-[10px] sm:text-sm">
                                    <span class="font-bold text-primary/50 truncate max-w-[90px] sm:max-w-none">{{ '@' . optional($boardStudent->user)->username }}</span>
                                    <span class="text-primary/30 hidden sm:inline">|</span>
                                    <span class="font-bold text-secondary whitespace-nowrap">Lvl {{ $boardStudent->level }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Right -->
                        <div class="flex flex-col items-end justify-center shrink-0 text-right pl-2 sm:pl-4">
                            <!-- all time book count -->
                            <span class="text-xl sm:text-3xl font-black text-primary leading-none">{{ $boardStudent->all_time_read_count }}</span>
                            <span class="text-[8px] sm:text-[10px] font-bold text-primary/50 uppercase tracking-widest mt-1">
                                <span class="hidden sm:inline">TOTAL BOOKS READ</span>
                                <!-- mobile users -->
                                <span class="sm:hidden">Total</span>
                            </span>
                        </div>
                    </div>
                @empty
                <!-- Empty-->
                    <div class="py-12 flex flex-col items-center justify-center text-center px-4">
                        <h3 class="text-lg font-black text-primary">No reading data yet!</h3>
                        <p class="text-xs sm:text-sm text-primary/60 mt-1">Check back later when students start reading.</p>
                    </div>
                @endforelse
            </div>

        </div>
    </div>
    
    <script>
        // Monthly / all time
        function switchTab(tabId) {
            const btnMonthly = document.getElementById('btn-monthly');
            const btnAllTime = document.getElementById('btn-alltime');
            const boardMonthly = document.getElementById('board-monthly');
            const boardAllTime = document.getElementById('board-alltime');

            if (tabId === 'monthly') {
                // Show monthly
                boardMonthly.classList.remove('hidden');
                boardMonthly.classList.add('flex');
                boardAllTime.classList.add('hidden');
                boardAllTime.classList.remove('flex');
                
                // active style toggle
                btnMonthly.classList.add('bg-primary', 'text-white', 'shadow-sm');
                btnMonthly.classList.remove('text-primary/60', 'hover:text-primary');
                
                // inactive style toggle
                btnAllTime.classList.remove('bg-primary', 'text-white', 'shadow-sm');
                btnAllTime.classList.add('text-primary/60', 'hover:text-primary');

            } else if (tabId === 'alltime') {
                // Show all time
                boardAllTime.classList.remove('hidden');
                boardAllTime.classList.add('flex');
                boardMonthly.classList.add('hidden');
                boardMonthly.classList.remove('flex');

                // active style toggle
                btnAllTime.classList.add('bg-primary', 'text-white', 'shadow-sm');
                btnAllTime.classList.remove('text-primary/60', 'hover:text-primary');
                
                // inactive style toggle
                btnMonthly.classList.remove('bg-primary', 'text-white', 'shadow-sm');
                btnMonthly.classList.add('text-primary/60', 'hover:text-primary');
            }
        }

        // countdown
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('countdown-container');
            const valueEl = document.getElementById('countdown-value');
            const unitEl = document.getElementById('countdown-unit');
            
            if (!container) return;

            // target date
            const targetDateStr = container.getAttribute('data-target');
            const targetDate = new Date(targetDateStr).getTime();

            // update the countdown
            function updateCountdown() {
                const now = new Date().getTime();
                const distance = targetDate - now;

                // if its less/ equals to 0
                if (distance <= 0) {
                    clearInterval(timerInterval);
                    valueEl.textContent = "0h 0m 00s";
                    unitEl.style.display = 'none';
                    
                    // testing
                    if (window.location.search.includes('test=true')) {
                        window.location.href = window.location.pathname + '?test_month=next';
                    } else {
                        window.location.reload();
                    }
                    return; 
                }

                // time
                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                // days greater than 0
                if (days > 0) {
                    valueEl.textContent = days;
                    unitEl.textContent = days === 1 ? 'Day' : 'Days';
                    unitEl.style.display = 'inline'; 
                } else {
                    // seconds
                    const paddedSeconds = seconds.toString().padStart(2, '0');
                    valueEl.textContent = `${hours}h ${minutes}m ${paddedSeconds}s`;
                    unitEl.style.display = 'none'; 
                }
            }

            updateCountdown();
            const timerInterval = setInterval(updateCountdown, 1000);
        });
    </script>
</x-layout>