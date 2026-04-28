<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BookReviewSeeder extends Seeder
{
    public function run(): void
    {
        $titles = [
            'Loved this book!',
            'Great read for kids',
            'My child enjoyed it',
            'Fantastic story',
            'Really engaging',
            'Perfect for bedtime',
            'Wonderful illustrations',
            'Highly recommend',
            'Good but short',
            'A new favourite',
            'Brilliant book',
            'Fun and educational',
            'Could not put it down',
            'Nice story overall',
            'Great for early readers',
            'Entertaining read',
            'Well written',
            'Colourful and fun',
            'A bit boring',
            'Not bad at all',
            'My kids ask for this every night',
            'A lovely little book',
            'Good for phonics practice',
            'Simple but enjoyable',
            'One of the best',
            'Average book',
            'Pretty decent',
            'Exceeded expectations',
            'Would read again',
            'Great value',
        ];

        $descriptions = [
            'My child absolutely loved reading this book. The story kept them engaged from start to finish and they asked to read it again straight away.',
            'A wonderful book for young readers. The language is accessible and the story is interesting enough to hold their attention throughout.',
            'We read this together at bedtime and it was perfect. Not too long, not too short, with a lovely story that my child really connected with.',
            'The illustrations are beautiful and really bring the story to life. My child spent ages looking at every page and pointing things out.',
            'Great for building confidence in reading. The vocabulary is age-appropriate and the sentences are well structured for early readers.',
            'A solid book that does what it needs to do. Nothing extraordinary but a reliable choice for reading practice at this level.',
            'My child was not particularly interested in this one. It took a few attempts to get through it but the story is okay overall.',
            'Excellent book for this reading level. It challenges without being too difficult and the story is genuinely entertaining for children.',
            'We have read this one many times now and it never gets old. A real favourite in our household that I would recommend to anyone.',
            'The story is engaging and my child learned some new words from it. A great addition to our reading collection at home.',
            'Perfect length for a quick reading session. The plot is simple but effective and my child understood it without any help.',
            'I was surprised by how much my child enjoyed this. They usually prefer different types of books but this one really captured their imagination.',
            'A good book for practising phonics sounds. The repetition is helpful without being tedious and the story ties everything together nicely.',
            'Not our favourite but still a decent read. The story could be more exciting but the language and structure are good for this level.',
            'My child brought this home from school and we both enjoyed it. Well written with a clear message that children can easily understand.',
        ];

        $ratingWeights = [1, 2, 3, 3, 3, 4, 4, 4, 4, 5, 5, 5, 5, 5];
        $now = Carbon::now();
        $reviewBatch = [];
        $batchLimit = 5000;
        $totalInserted = 0;

        DB::table('book_reviews')->truncate();

        // chunk student books
        DB::table('book_student')
            ->select('student_id', 'book_id', 'school_id', 'status')
            ->whereIn('status', ['reading', 'completed'])
            ->orderBy('id')
            ->chunk(2000, function ($assignments) use (
                &$reviewBatch,
                &$totalInserted,
                $batchLimit,
                $titles,
                $descriptions,
                $ratingWeights,
                $now
            ) {
                foreach ($assignments as $assignment) {
                    // 10% of reviews dont get reviewed
                    if (rand(1, 100) > 90) {
                        continue;
                    }

                    $daysAgo = rand(1, 365);
                    $createdAt = $now->copy()->subDays($daysAgo)->format('Y-m-d H:i:s');

                    $reviewBatch[] = [
                        'rating' => $ratingWeights[array_rand($ratingWeights)],
                        'title' => $titles[array_rand($titles)],
                        'description' => $descriptions[array_rand($descriptions)],
                        'upvotes' => rand(0, 25),
                        'student_id' => $assignment->student_id,
                        'book_id' => $assignment->book_id,
                        'school_id' => $assignment->school_id,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ];
                }

                if (count($reviewBatch) >= $batchLimit) {
                    foreach (array_chunk($reviewBatch, 2000) as $chunk) {
                        DB::table('book_reviews')->insert($chunk);
                    }

                    $totalInserted += count($reviewBatch);
                    $reviewBatch = [];
                }
            });

        if (!empty($reviewBatch)) {
            foreach (array_chunk($reviewBatch, 2000) as $chunk) {
                DB::table('book_reviews')->insert($chunk);
            }

            $totalInserted += count($reviewBatch);
        }

        $this->command->info("Added {$totalInserted} reviews from assigned student books");
    }
}