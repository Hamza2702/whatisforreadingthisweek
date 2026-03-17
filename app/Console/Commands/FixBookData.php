<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Book;

// fix the ort_colours column and fix (implemented) books that aren't in level 1
class FixBookData extends Command
{   
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
        protected $signature = 'books:fix-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix book data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting');

        $fixedColours = 0;
        $level1books = 0;

        // fix books in chunks for large dbs
        Book::chunk(200, function ($books) use (&$fixedColours, &$level1books) {
            foreach ($books as $book) {
                $changed = false;

                // scan all low level books for page counts and make em into level 1
                // if greater than lvl 2, less than 8
                if ($book->ort_level >= 2 && $book->ort_level <= 8) {
                    // book title w/ description
                    $textToSearch = strtolower($book->title . ' ' . $book->description);
                    
                    // keyword regex list - board books, counting, baby, toddler, lift the flap, nursery rhymes etc
                    if (preg_match('/\b(level 1|step 1|phase 1|first words|baby|toddler|alphabet|abc|abcs|wordless|board book|my first|counting book|123|nursery rhymes|cvc|sight words|early phonics|lift[- ]the[- ]flap|touch[- ]and[- ]feel)\b/i', $textToSearch)) {
                        // set book to lvl 1
                        $book->ort_level = 1;
                        $level1books++;
                        $changed = true;
                    }
                }

                // assign missing ORT colour
                $correctColour = $this->getOxfordColour($book->ort_level);

                if ($book->ort_colour !== $correctColour) {
                    $book->ort_colour = $correctColour;
                    $fixedColours++;
                    $changed = true;
                }

                if ($changed) {
                    $book->save();
                }
            }
        });

        $this->newLine();
        $this->info("Finished, assigned colours to {$fixedColours} books");
        $this->info("Created {$level1books} level 1 books");

        // fix reading levels
        $this->fixReadingLevels();
    }

    // fix reading levels that dont have books
    private function fixReadingLevels(){
        $this->info('Fixing reading levels');

        $updatedCount = 0;

        // weird levels that don't contain books
        $levelsToSplit = [4, 6, 8, 10, 12, 14, 16, 18, 20];

        // loop through books
        Book::whereIn('ort_level', $levelsToSplit)->chunk(200, function ($books) use (&$updatedCount) {
            foreach ($books as $book) {
                
                // use db id to spilt them in 50/50
                if ($book->id % 2 !== 0) {
                    $book->ort_level = $book->ort_level - 1;
                    
                    // calculcate colourband
                    $book->ort_colour = $this->getOxfordColour($book->ort_level);
                    
                    $book->save();
                    $updatedCount++;
                }
            }
        });

        $this->newLine();
        $this->info("Distributed {$updatedCount} books");
    }

    // get reading colour
    private function getOxfordColour($level) {
        return match((int)$level) {
            0 => 'Light Purple',
            1 => 'Pink', // early reception
            2 => 'Red', // reception
            3 => 'Yellow', // reception / early Y1
            4 => 'Light Blue', // Y1
            5 => 'Green', // Y1
            6 => 'Orange', // Y1 / early Y2
            7 => 'Turquoise', // Y2
            8 => 'Purple', // Y2
            9 => 'Gold', // Y3
            10 => 'White', // Y3 / early Y4
            11 => 'Lime', // Y4
            12 => 'Lime+', // Y4
            13, 14 => 'Grey', // Y5
            15, 16 => 'Dark Blue', // Y5 / early Y6
            17, 18, 19, 20 => 'Dark Red', // Y6
            default => 'Dark Red',
        };
    }
}