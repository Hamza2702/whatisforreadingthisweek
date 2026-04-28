<x-site-layout title="Bookworms">

    <!-- =========================================== -->
    <!-- COVER -->
    <section class="relative w-full px-6 md:px-12 lg:px-16 xl:px-24 pt-10 pb-20 md:py-24 flex flex-col lg:flex-row items-center gap-12 lg:gap-8 overflow-x-hidden lg:overflow-visible">
        
        <!-- left area -->
        <div class="w-full lg:w-1/2 flex flex-col items-start z-10">
            <!-- title -->
            <h1 class="font-system text-6xl sm:text-7xl lg:text-[5.5rem] font-black leading-[0.95] tracking-tight text-[#2d2420]">
                Automate <br>
                <span class="text-[#e87a90]">Reading Lists</span> <br>
                Instantly.
            </h1>

            <!-- subtext -->
            <p class="mt-8 text-lg md:text-xl text-[#755f54]/80 font-medium max-w-lg leading-relaxed">
                Designed to make reading management easier for teachers and more engaging for pupils!
            </p>

            <!-- Stats/CTA Area -->
            <div class="mt-10 flex flex-col sm:flex-row items-start sm:items-center gap-8 border-l-2 border-[#e87a90] pl-6 z-20">

                <!-- Dynamic Database Stats -->
                <div class="flex gap-6">
                    <div class="flex flex-col">
                        <span class="text-xs font-bold uppercase tracking-wider text-[#755f54]/50">SCHOOLS</span>
                        <span class="text-xl font-black text-[#755f54] font-system">{{ $schoolsCount >= 1000 ? number_format($schoolsCount / 1000, 1) . 'k' : number_format($schoolsCount) }} +</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-xs font-bold uppercase tracking-wider text-[#755f54]/50">TOTAL AUTHORS</span>
                        <span class="text-xl font-black text-[#755f54] font-system">{{ $authorsCount >= 1000 ? number_format($authorsCount / 1000, 1) . 'k' : number_format($authorsCount) }} +</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-xs font-bold uppercase tracking-wider text-[#755f54]/50">TOTAL BOOKS</span>
                        <span class="text-xl font-black text-[#755f54] font-system">{{ $booksCount >= 1000 ? number_format($booksCount / 1000, 1) . 'k' : number_format($booksCount) }} +</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- word cloud -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none -z-10 flex justify-center items-center opacity-15 lg:relative lg:w-1/2 lg:overflow-visible lg:pointer-events-auto lg:z-0 lg:opacity-50 lg:justify-end lg:mt-0">
            <!-- glow -->
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full h-full lg:w-3/4 lg:h-3/4 bg-pink-100/50 rounded-full blur-3xl -z-10"></div>
            <!-- word cloud image -->
            <img src="{{ asset('images/wordcloud.png') }}" alt="Bookworms Word Cloud" class="w-[250%] md:w-[150%] max-w-none lg:w-[140%] lg:translate-x-12 mix-blend-multiply [clip-path:inset(2px)]" draggable="false">
        </div>
    </section>

    <!-- =========================================== -->
    <!-- WHAT IS BOOKWORMS-->
    <section class="bg-[#1c1613] text-white py-24 md:py-32 w-full mt-12">
        <div class="max-w-4xl mx-auto px-6 md:px-12">
            
            <h3 class="font-system text-[#ffb84d] font-bold tracking-[0.2em] uppercase text-xs mb-6">THE PROBLEM & THE SOLUTION</h3>
            
            <h2 class="font-system text-5xl md:text-6xl font-black mb-10 tracking-tight">
                What is <span class="text-[#e87a90]">Bookworms</span>?
            </h2>
            
            <div class="space-y-8 text-lg md:text-xl text-gray-300 font-medium leading-relaxed">
                <p>In UK primary school education, weekly reading assignments are essential to literacy development. However, classrooms consist of pupils learning at different reading levels with varied genre preferences, making it a challenge for teachers.</p>
                <p>Teachers spend considerable amounts of time tracking each pupil's history, abiltiy and curriculum alignment. Manual assignment is prone to inconsistency, leaving some pupils with too challenging or repetitive books.</p>
                <p><strong class="text-white">Bookworms changes this.</strong> This reading management system automates weekly book assignments while giving teachers full control. The ORT colour bands are considered, reading history and feedback to generate perfectly tailored reading lists for pupils.</p>
            </div>

            <!-- Features Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-16 pt-16 border-t border-white/10">
                <div>
                    <h4 class="font-system text-[#ffb84d] font-black text-xl mb-2">Track</h4>
                    <p class="text-sm text-gray-400">Curriculum topics taught are maintained in the system and easily monitor pupil reading ability with the ORT book band system!</p>
                </div>
                <div>
                    <h4 class="font-system text-[#e87a90] font-black text-xl mb-2">Assign</h4>
                    <p class="text-sm text-gray-400">Automatically generate targeted reading lists that perfectly match each pupil's level, genre preferences, and history.</p>
                </div>
                <div>
                    <h4 class="font-system text-[#4db8ff] font-black text-xl mb-2">Review</h4>
                    <p class="text-sm text-gray-400">Pupils read their weekly assignments and submit feedback, giving you the analytics needed to adjust for next week.</p>
                </div>
            </div>

        </div>
    </section>

    <!-- =========================================== -->
    <!-- REAL TIME STATISTICS -->
    <section class="py-24 bg-white relative w-full border-t border-[#755f5420]">
        <div class="max-w-6xl mx-auto px-6 md:px-12 lg:px-16 text-center z-10 relative">
            
            <h3 class="font-system text-[#ffb84d] font-bold tracking-[0.2em] uppercase text-xs mb-4">REAL TIME STATISTICS</h3>
            <h2 class="font-system text-4xl md:text-5xl font-black text-[#2d2420] mb-6 tracking-tight">
                The solution to <br class="hidden md:block">
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#e87a90] via-[#4db8ff] to-[#ffb84d] ">outdated reading platforms.</span>
            </h2>
            <p class="text-lg text-[#755f54]/80 font-medium max-w-2xl mx-auto mb-16 leading-relaxed">
                Bookworms is the future. Use Bookworms to automate, track and inspire the next generation of primary school pupils!
            </p>

            <!-- Dynamic Stats Grid (8 blocks) -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 md:gap-8">
                
                <!-- total schools -->
                <div class="bg-[#fdfbf7] rounded-3xl p-6 md:p-8 border border-[#755f5410]">
                    <div class="text-2xl sm:text-4xl md:text-5xl font-black text-[#e87a90] mb-2 font-system">{{ number_format($teachersCount ?? 0) }}</div>
                    <div class="text-xs md:text-sm font-bold tracking-wider text-[#755f54]/60">TOTAL TEACHERS</div>
                </div>
                
                <!-- active pupils -->
                <div class="bg-[#fdfbf7] rounded-3xl p-6 md:p-8 border border-[#755f5410]">
                    <div class="text-2xl sm:text-4xl md:text-5xl font-black text-[#4db8ff] mb-2 font-system">{{ number_format($pupilsCount ?? 0) }}</div>
                    <div class="text-xs md:text-sm font-bold tracking-wider text-[#755f54]/60">ACTIVE PUPILS</div>
                </div>

                <!-- books -->
                <div class="bg-[#fdfbf7] rounded-3xl p-6 md:p-8 border border-[#755f5410]">
                    <div class="text-2xl sm:text-4xl md:text-5xl font-black text-[#ffb84d] mb-2 font-system">{{ number_format($booksCount ?? 0) }}</div>
                    <div class="text-xs md:text-sm font-bold tracking-wider text-[#755f54]/60">BOOKS AVAILABLE</div>
                </div>

                <!-- phonics -->
                <div class="bg-[#fdfbf7] rounded-3xl p-6 md:p-8 border border-[#755f5410]">
                    <div class="text-2xl sm:text-4xl md:text-5xl font-black text-[#2d2420] mb-2 font-system">{{ number_format($phonicsCount ?? 0) }}</div>
                    <div class="text-xs md:text-sm font-bold tracking-wider text-[#755f54]/60">PHONICS TOPICS</div>
                </div>

                <!-- books assigned -->
                <div class="bg-[#fdfbf7] rounded-3xl p-6 md:p-8 border border-[#755f5410]">
                    <div class="text-2xl sm:text-4xl md:text-5xl font-black text-[#4db8ff] mb-2 font-system">{{ number_format($booksAssignedCount ?? 0) }}</div>
                    <div class="text-xs md:text-sm font-bold tracking-wider text-[#755f54]/60">BOOKS ASSIGNED</div>
                </div>

                <!-- favourited books -->
                <div class="bg-[#fdfbf7] rounded-3xl p-6 md:p-8 border border-[#755f5410]">
                    <div class="text-2xl sm:text-4xl md:text-5xl font-black text-[#e87a90] mb-2 font-system">{{ number_format($favouritedCount ?? 0) }}</div>
                    <div class="text-xs md:text-sm font-bold tracking-wider text-[#755f54]/60">FAVOURITED BOOKS</div>
                </div>

                <!-- active streaks -->
                <div class="bg-[#fdfbf7] rounded-3xl p-6 md:p-8 border border-[#755f5410]">
                    <div class="text-2xl sm:text-4xl md:text-5xl font-black text-[#2d2420] mb-2 font-system">{{ number_format($activeStreaksCount ?? 0) }}</div>
                    <div class="text-xs md:text-sm font-bold tracking-wider text-[#755f54]/60">ACTIVE STREAKS</div>
                </div>

                <!-- reviews written -->
                <div class="bg-[#fdfbf7] rounded-3xl p-6 md:p-8 border border-[#755f5410]">
                    <div class="text-2xl sm:text-4xl md:text-5xl font-black text-[#ffb84d] mb-2 font-system">{{ number_format($reviewsCount ?? 0) }}</div>
                    <div class="text-xs md:text-sm font-bold tracking-wider text-[#755f54]/60">STUDENT BOOK REVIEWS WRITTEN</div>
                </div>
            </div>
        </div>
    </section>


    <!-- WHY BOOKWORMS -->
    <section class="bg-[#1c1613] text-white py-24 md:py-32 w-full mt-12">
        <div class="max-w-6xl mx-auto px-6 md:px-12 lg:px-16">
            <div class="text-center md:text-left mb-16 flex flex-col md:flex-row justify-between items-end gap-6">
                <div class="max-w-2xl">
                    <h3 class="font-system text-[#4db8ff] font-bold tracking-[0.2em] uppercase text-xs mb-4">WHY BOOKWORMS?</h3>
                    <h2 class="font-system text-4xl md:text-5xl font-black text-white tracking-tight">
                        A reading system that doesn't look like it's from the early <span class="text-[#4db8ff]">2000s.</span>
                    </h2>
                </div>
                <div class="hidden md:block">
                    <a href="#contact" class="bg-[#2d2420] text-white text-base font-bold px-8 py-3.5 rounded-full shadow-lg transition-all hover:bg-[#e87a90] hover:-translate-y-1 inline-block">
                        Use it for your school
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-16 pt-16 border-t border-white/10">
                <div>
                    <h4 class="font-system text-[#ffb84d] font-black text-xl mb-2">Built for real classrooms.</h4>
                    <p class="text-sm text-gray-400">
                        Bookworms gives teachers full control without the admin headache. Easily manage classes, monitor pupils, assign books, and adapt reading support around real classroom needs.
                    </p>
                </div>
                <div>
                    <h4 class="font-system text-[#e87a90] font-black text-xl mb-2">A library pupils want to explore.</h4>
                    <p class="text-sm text-gray-400">
                        With tens of thousands of books, rich filtering, favourites, and personalised recommendations, Bookworms makes reading feel modern, engaging, and genuinely exciting for pupils.
                    </p>
                </div>
                <div>
                    <h4 class="font-system text-[#4db8ff] font-black text-xl mb-2">Data that actually helps.</h4>
                    <p class="text-sm text-gray-400">
                        From reading streaks and reviews to progress tracking and genre insights, Bookworms turns pupil activity into clear, useful data teachers can act on immediately.
                    </p>
                </div>
            </div>
            
            <!-- Mobile -->
            <div class="mt-12 text-center md:hidden">
                <a href="#contact" class="bg-[#2d2420] text-white text-base font-bold px-8 py-3.5 rounded-full shadow-lg transition-all hover:bg-[#e87a90] hover:-translate-y-1 inline-block w-full">
                    Use it for your school
                </a>
            </div>

        </div>
    </section>

    <!-- CONTACT SECTION -->
    <section class="bg-[#fdfbf7] py-24 md:py-32 w-full" id="contact">
        <div class="w-full px-6 md:px-12 lg:px-16 xl:px-40 flex flex-col lg:flex-row-reverse items-start gap-12 lg:gap-20">

            <!-- Right -->
            <div class="w-full lg:w-1/2 lg:text-right">
                <h3 class="font-system text-[#e87a90] font-bold tracking-[0.2em] uppercase text-xs mb-6">
                    GET IN TOUCH
                </h3>
                <h2 class="font-system text-5xl md:text-6xl font-black tracking-tight text-[#2d2420]">
                    Contact <br>
                    <span class="text-[#e87a90]">Us</span>
                </h2>
                <p class="mt-6 text-lg text-[#755f54]/70 font-medium leading-relaxed">
                    Have a question, want a demo of the Bookworms prototype, or planning on purchasing?
                </p>
            </div>

            <!-- Contact details -->
            <div class="w-full lg:w-1/2 space-y-10">

                <!-- Email -->
                <div class="flex items-start gap-4">    
                    <div class="w-10 h-10 rounded-full bg-[#e87a90]/10 flex items-center justify-center shrink-0 mt-0.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-[#e87a90]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0-8.953 5.468a1.5 1.5 0 0 1-1.594 0L2.25 6.75" />
                        </svg>
                    </div>
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-[#e87a90]">Email</span>
                        <a href="mailto:querybookworms@outlook.com" class="block text-lg font-bold text-[#2d2420] hover:text-[#e87a90] transition-colors">
                            querybookworms@outlook.com
                        </a>
                    </div>
                </div>

                <!-- Phone -->
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-full bg-[#ffb84d]/10 flex items-center justify-center shrink-0 mt-0.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-[#ffb84d]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
                        </svg>
                    </div>
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-[#ffb84d]">Phone</span>
                        <a class="block text-lg font-bold text-[#2d2420] hover:text-[#ffb84d] transition-colors">
                            071 234 56789
                        </a>
                    </div>
                </div>

                <!-- Location -->
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-full bg-[#4db8ff]/10 flex items-center justify-center shrink-0 mt-0.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-[#4db8ff]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                        </svg>
                    </div>
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-[#4db8ff]">Based In</span>
                        <p class="text-lg font-bold text-[#2d2420] hover:text-[#4db8ff] transition-colors">Birmingham, UK</p>
                        <p class="text-sm text-[#755f54]/60 mt-0.5">Supporting schools across the United Kingdom</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-site-layout>