@php
    $weeklyBookTarget = $weeklyTarget;
    if ($weeklyBookTarget == 1) {
        $goalText = 'book';
    } elseif ($weeklyBookTarget == 2) {
        $goalText = 'books';
    } else {
        $goalText = 'books';
    }
@endphp
<x-layout title="My Progress">
    <div class="w-full px-6 md:px-10 lg:px-16 py-8 flex-1 flex flex-col justify-center gap-6 lg:gap-10 font-sans">
        
        <!-- ========================================= -->
        <!-- PROGRESS -->
        <div class="bg-[#755f540a] border border-[#755f5420] rounded-3xl p-6 md:p-10 relative overflow-hidden shadow-sm">
            <div class="flex flex-col md:flex-row justify-between md:items-center gap-6 w-full relative z-10">
                <div>
                    <h1 class="text-4xl md:text-5xl font-display text-primary">Your Progress!</h1>
                    <p class="text-base md:text-lg font-medium text-primary/70 mt-2">Keep reading books, learn new phonic sounds and explore genres to show your teacher that you're ready for the next level!</p>
                </div>
                
                <!-- Levels -->
                <div class="flex flex-wrap items-center gap-3 md:gap-4">
                    <!-- Current level -->
                    <span class="text-sm font-bold text-primary/60">Current Level:</span>
                    <span class="px-4 md:px-6 py-2 rounded-full text-sm md:text-base font-bold bg-level-{{ $level }} text-level-{{ $level }} flex items-center gap-2 shadow-sm flex-shrink-0">
                        Level {{ $level }}
                    </span>
                    
                    <!-- If level 20 -->
                    @if ($level == 20)
                        <!-- nothing -->
                    @else
                        <!-- arrow -->
                        <span class="text-primary">&rarr;</span>
                        <!-- next level -->
                        <span class="px-4 md:px-6 py-2 rounded-full text-sm md:text-base font-bold bg-level-{{ $level + 1 }} text-level-{{ $level + 1 }} flex items-center gap-2 shadow-sm flex-shrink-0">
                            Level {{ $level + 1 }}
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- ========================================= -->
        <!-- STATS -->
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 lg:gap-6">
            <!-- Total books read -->
            <div class="bg-primary rounded-3xl p-6 flex flex-col justify-center items-center text-center shadow-md">
                <span class="text-4xl md:text-5xl lg:text-6xl font-black text-background mb-2">{{ $totalBooks }}</span>
                <span class="text-[10px] md:text-xs font-bold text-background/80 tracking-widest uppercase">{{ $goalText }} Read</span>
            </div>

            <!-- Total books read this month -->
            <div class="bg-white border border-primary/10 rounded-3xl p-6 flex flex-col justify-center items-center text-center shadow-sm">
                <span class="text-4xl md:text-5xl lg:text-6xl font-black text-primary mb-2">{{ $booksThisWeek }}</span>
                <span class="text-[10px] md:text-xs font-bold text-primary/70 tracking-widest">THIS MONTH</span>
            </div>

            <!-- Average rating -->
            <div class="bg-[#755f5415] border border-primary/10 rounded-3xl p-6 flex flex-col justify-center items-center text-center shadow-sm">
                <span class="text-4xl md:text-5xl lg:text-6xl font-black text-primary mb-2">{{ $avgRating }}</span>
                <span class="text-[10px] md:text-xs font-bold text-primary/70 tracking-widest">AVERAGE RATING</span>
            </div>
        </div>

        <!-- ========================================= -->
        <!-- STATS -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-10">
            
            <!-- Weekly goals -->
            <div class="lg:col-span-4 bg-[#755f540a] border border-[#755f5420] rounded-3xl p-6 md:p-8 flex flex-col justify-center shadow-sm relative overflow-hidden">
                <h3 class="text-xl md:text-2xl font-display text-primary mb-2">Weekly Goal</h3>
                <p class="text-sm font-semibold text-primary/60 mb-6">Hit your target of {{ $weeklyTarget }} {{ $goalText }} this week!</p>
                
                <!-- weekly goals progress -->
                <div class="flex items-center justify-between gap-3">
                    @for($i = 1; $i <= $weeklyTarget; $i++)
                        @if($i <= $booksThisWeek)
                            <!-- filled target -->
                            <div class="h-12 md:h-16 flex-1 bg-green-500 rounded-xl flex items-center justify-center">
                                <span class="text-white font-black text-xl">{{ $i }}</span>
                            </div>
                        @else
                            <!-- empty target -->
                            <div class="h-12 md:h-16 flex-1 bg-[#755f5410] border border-[#755f5430] rounded-xl flex items-center justify-center">
                                <span class="text-primary/30 font-black text-xl">{{ $i }}</span>
                            </div>
                        @endif
                    @endfor
                </div>

                <!-- if the weekly goal is hit -->
                @if($booksThisWeek >= $weeklyTarget)
                    <div class="mt-6 text-center text-sm font-bold text-green-600 bg-green-50 py-2 rounded-lg border border-green-300">
                        You've hit your weekly goal! Amazing work!<br>Ask your teacher to increase your goal next week!
                    </div>
                @endif
            </div>

            <!-- Chart.js 6 month activity graph -->
            <div class="lg:col-span-8 bg-white border border-[#755f5420] rounded-3xl p-6 md:p-8 flex flex-col shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-xl md:text-2xl font-display text-primary">Reading Activity</h3>
                        <p class="text-xs md:text-sm font-semibold text-primary/60 mt-1">Books read over the past 6 months</p>
                    </div>
                </div>

                <!-- chartjs canvas -->
                <div class="relative flex-1 w-full min-h-[200px]">
                    <canvas id="readingActivityChart"></canvas>
                </div>
            </div>

        </div>

        <!-- ========================================= -->
        <!-- RECENT READING HISTORY -->
        <div class="bg-[#755f540a] border border-[#755f5420] rounded-3xl p-6 md:p-10 flex flex-col w-full shadow-sm">
            <div class="flex items-center justify-between mb-8">
                <h2 class="text-2xl md:text-3xl font-display text-primary flex items-center gap-3">
                    Recent Reading History
                </h2>
            </div>
            
            <div class="flex flex-row items-start gap-6 md:gap-8 overflow-x-auto pb-6 w-full snap-x [&::-webkit-scrollbar-track]:w-2 [&::-webkit-scrollbar-track]:bg-transparent [&::-webkit-scrollbar-thumb]:bg-orange-200 [&::-webkit-scrollbar-thumb]:rounded-full">
                
                <!-- Currently reading book -->
                @if($currentlyReading)
                    <div class="flex flex-col items-center gap-4 flex-shrink-0 snap-start w-40 md:w-48">
                        <!-- link to book -->
                        <a href="{{ url('/books/' . $currentlyReading->id) }}" class="w-40 h-56 md:w-48 md:h-72 bg-white rounded-2xl flex flex-col items-center justify-center overflow-hidden relative border-4 border-primary shadow-md hover:shadow-lg hover:border-background transition-all cursor-pointer">
                            
                            <!-- Grayscale book cover -->
                            @if($currentlyReading->cover_id && str_starts_with($currentlyReading->cover_id, 'LOCAL_'))
                                @php $imagePath = str_replace('LOCAL_', '', $currentlyReading->cover_id); @endphp
                                <img src="{{ asset('storage/' . $imagePath) }}" alt="{{ html_entity_decode($currentlyReading->title ?? '', ENT_QUOTES) }}" class="absolute inset-0 w-full h-full object-cover grayscale opacity-80">
                            
                            @elseif($currentlyReading->cover_id && str_starts_with($currentlyReading->cover_id, 'PLACEHOLDER_'))
                                @php $bgColour = str_replace('PLACEHOLDER_', '', $currentlyReading->cover_id); @endphp
                                <div class="absolute inset-0 w-full h-full flex items-center justify-center p-4 text-center grayscale opacity-80" style="background-color: {{ $bgColour }};">
                                    <span class="font-black text-white text-sm drop-shadow-md line-clamp-4">{{ html_entity_decode($currentlyReading->title ?? '', ENT_QUOTES) }}</span>
                                </div>
                            
                            @elseif($currentlyReading->cover_id)
                                <img src="https://books.google.com/books/content?id={{ $currentlyReading->cover_id }}&printsec=frontcover&img=1&zoom=1" alt="{{ html_entity_decode($currentlyReading->title ?? '', ENT_QUOTES) }}" class="absolute inset-0 w-full h-full object-cover grayscale opacity-80">
                            
                            @else
                                <div class="absolute inset-0 flex items-center justify-center bg-orange-100 grayscale opacity-80">
                                    <span class="font-bold text-primary/30 text-xs tracking-widest -rotate-12">NO COVER</span>
                                </div>
                            @endif

                            <!-- Reading progress -->
                            <div class="absolute inset-0 flex flex-col items-center justify-center z-10 p-4 bg-black/10 backdrop-blur-[2px]">
                                <span class="bg-primary text-white text-xs font-bold px-4 py-2 rounded-full tracking-widest shadow-md">READING</span>
                            </div>
                        </a>
                        
                        <!-- Book info -->
                        <div class="text-center w-full px-1 flex flex-col items-center">
                            <h4 class="text-base font-bold text-primary truncate w-full" title="{{ html_entity_decode($currentlyReading->title ?? '', ENT_QUOTES) }}">{{ html_entity_decode($currentlyReading->title ?? '', ENT_QUOTES) }}</h4>
                            <p class="text-xs font-bold text-red-500 tracking-wide mt-1 mb-3">CURRENTLY READING</p>

                            <!-- Update review/write a review -->
                            <a href="{{ url('/books/' . $currentlyReading->id . '/review') }}" class="inline-flex w-full items-center justify-center px-4 py-2.5 text-xs bg-primary text-background font-bold rounded-xl shadow-sm hover:bg-orange-900 hover:shadow-md transition-all duration-200 focus:ring-2 focus:ring-primary/30">
                                {{ in_array($currentlyReading->id, $reviewedBookIds) ? 'Update Review' : 'Write a Review' }}
                            </a>
                        </div>
                    </div>
                @endif

                <!-- Completed book -->
                @forelse($readingHistory as $book)
                    <div class="flex flex-col items-center gap-4 flex-shrink-0 snap-start w-40 md:w-48 group">
                        <!-- link to book -->
                        <a href="{{ url('/books/' . $book->id) }}" class="w-40 h-56 md:w-48 md:h-72 bg-white rounded-2xl flex items-center justify-center overflow-hidden relative border border-[#755f5420] shadow-sm transition-all duration-300 group-hover:-translate-y-2 group-hover:shadow-lg cursor-pointer">
                            
                            <!-- Completed book cover -->
                            @if($book->cover_id && str_starts_with($book->cover_id, 'LOCAL_'))
                                @php $imagePath = str_replace('LOCAL_', '', $book->cover_id); @endphp
                                <img src="{{ asset('storage/' . $imagePath) }}" alt="{{ html_entity_decode($book->title ?? '', ENT_QUOTES) }}" class="absolute inset-0 w-full h-full object-cover">
                            
                            @elseif($book->cover_id && str_starts_with($book->cover_id, 'PLACEHOLDER_'))
                                @php $bgColour = str_replace('PLACEHOLDER_', '', $book->cover_id); @endphp
                                <div class="absolute inset-0 w-full h-full flex items-center justify-center p-4 text-center" style="background-color: {{ $bgColour }};">
                                    <span class="font-black text-white text-sm drop-shadow-md line-clamp-4">{{ html_entity_decode($book->title ?? '', ENT_QUOTES) }}</span>
                                </div>
                            
                            @elseif($book->cover_id)
                                <img src="https://books.google.com/books/content?id={{ $book->cover_id }}&printsec=frontcover&img=1&zoom=1" alt="{{ html_entity_decode($book->title ?? '', ENT_QUOTES) }}" class="absolute inset-0 w-full h-full object-cover">
                            
                            @else
                                <div class="absolute inset-0 flex items-center justify-center bg-orange-100">
                                    <span class="font-bold text-primary/30 text-xs tracking-widest -rotate-12">NO COVER</span>
                                </div>
                            @endif
                            
                            <!-- completed -->
                            <div class="absolute bottom-1 right-1 bg-green-500 text-white rounded-full p-2 border border-white">
                                <span class="text-xs font-bold">Completed</span>
                            </div>
                        </a>

                        <!-- Book info -->
                        <div class="text-center w-full px-1 flex flex-col items-center">
                            <h4 class="text-base font-bold text-primary truncate w-full" title="{{ html_entity_decode($book->title ?? '', ENT_QUOTES) }}">{{ html_entity_decode($book->title ?? '', ENT_QUOTES) }}</h4>
                            <p class="text-xs font-semibold text-primary/60 mt-1 mb-3">Finished {{ $book->pivot->updated_at->diffForHumans() }}</p>

                            <!-- Update review/write a review -->
                            <a href="{{ url('/books/' . $book->id . '/review') }}" class="inline-flex w-full items-center justify-center px-4 py-2.5 text-xs bg-primary text-background font-bold rounded-xl shadow-sm hover:bg-orange-900 hover:shadow-md transition-all duration-200 focus:ring-2 focus:ring-primary/30">
                                {{ in_array($book->id, $reviewedBookIds) ? 'Update Review' : 'Write a Review' }}
                            </a>
                        </div>
                    </div>
                @empty
                    <!-- no completed books -->
                    @if(!$currentlyReading)
                        <div class="w-full text-center py-12 opacity-60">
                            <p class="text-xl font-display text-primary">No completed books yet!</p>
                        </div>
                    @endif
                @endforelse
            </div>
        </div>

        <!-- ========================================= -->
        <!-- GENRES AND PHONICS -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-10">

            <!-- Genres explored -->
            <div class="bg-[#755f540a] border border-[#755f5420] rounded-3xl p-6 md:p-10 flex flex-col shadow-sm min-h-[350px]">
                <h2 class="text-2xl md:text-3xl font-display text-primary mb-8 flex items-center gap-3">
                    Genres Explored
                </h2>
                
                <div class="flex flex-col gap-6 w-full">
                    @forelse($genresCount as $genre => $count)
                        <div class="w-full">
                            <div class="flex justify-between items-end mb-2">
                                <!-- genre -->
                                <span class="text-base md:text-lg font-bold text-primary">{{ $genre }}</span>
                                <!-- book count -->
                                <span class="text-xs md:text-sm text-primary/60 font-black uppercase tracking-widest">{{ $count }} {{ $count == 1 ? 'book' : 'books' }}</span>
                            </div>
                            <!-- bar -->
                            <div class="w-full bg-white rounded-full h-3 overflow-hidden shadow-inner">
                                <div class="bg-primary h-full rounded-full" style="width: {{ max(10, ($count / max($genresCount)) * 100) }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-base text-primary/60 font-medium italic mt-2">Read books to discover new genres!</p>
                    @endforelse
                </div>
            </div>

            <!-- Phonics learned -->
            <div class="bg-[#755f540a] border border-[#755f5420] rounded-3xl p-6 md:p-10 flex flex-col shadow-sm min-h-[350px]">
                <h2 class="text-2xl md:text-3xl font-display text-primary mb-8 flex items-center gap-3">
                    Phonics Learned
                </h2>
                <!-- display phonics -->
                @php
                    if ($level >= 8){
                        $phonicMessage = "You're doing great! Keep on reading!";
                    } else if ($level < 8) {
                        $phonicMessage = "Read more books to master new phonics!";
                    }
                @endphp
                <div class="flex flex-wrap gap-3 md:gap-4">
                    @forelse($phonicsMastered as $phonic)
                        <div class="bg-white text-primary rounded-2xl px-5 py-3 text-xl md:text-2xl font-black shadow-sm border border-[#755f5420] hover:bg-primary hover:text-white transition-colors cursor-default">
                            {{ $phonic }}
                        </div>
                    @empty
                        <p class="text-lg text-primary/60 font-medium mt-2">{{ $phonicMessage }}</p>
                    @endforelse
                </div>
            </div>

        </div>
    </div>

    <!-- Chartjs script -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // get canvas context
            const ctx = document.getElementById('readingActivityChart').getContext('2d');
            
            // Get data from controller
            const chartData = @json($chartData);

            // extract month labels and books read values
            const labels = Object.keys(chartData);
            const dataValues = Object.values(chartData);

            // create new bar chart
            new Chart(ctx, {
                type: 'bar',
                // data
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Books Finished',
                        data: dataValues,
                        backgroundColor: '#fb923c',
                        hoverBackgroundColor: '#f97316',
                        borderRadius: 0,
                        borderSkipped: false,
                        barThickness: 50,
                    }]
                },
                // options
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        // tool tip
                        tooltip: {
                            backgroundColor: '#6D4423',
                            padding: 12,
                            cornerRadius: 8,
                            displayColors: false,
                            callbacks: {
                                label: function(context) {
                                    return context.raw + (context.raw === 1 ? ' book' : ' books');
                                }
                            }
                        }
                    },
                    scales: {
                        // y
                        y: {
                            beginAtZero: true,
                            border: { display: false },
                            ticks: {
                                stepSize: 1,
                                precision: 1, 
                                color: '#6D4423',
                            },
                        },
                        // x
                        x: {
                            border: { display: false },
                            grid: {
                                display: false,
                                drawBorder: false
                            },
                            ticks: {
                                color: '#6D4423',
                            }
                        }
                    }
                }
            });
        });
    </script>
</x-layout>