<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Book;
use App\Models\Genre;
use App\Models\Phonic;

class FixBookGenres extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
        protected $signature = 'books:fix-genres';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix book genres';

    /**
     * Execute the console command.
     */
    protected array $levelPhonicsMap = [];
    protected array $phonicDbMap = [];
    public function handle()
    {
        $this->info('Fixing book genres');

        // setup phonics
        $this->setupPhonics();

        // categories
        $genreCategories = [
            'Adventure', 'Animals', 'Bedtime', 'Comedy', 'Fairy Tales', 'Fantasy', 
            'History', 'Picture Books', 'Science Fiction', 'Sports'
        ];
        
        // map genre name to database id
        $genreDbMap = [];
        // create genres if they dont exist
        foreach ($genreCategories as $name) {
            // create slug from name like fairy tales to fairy-tales
            $slug = strtolower(str_replace([' ', '&'], ['-', 'and'], $name));
            // create genre if missing
            $genreModel = Genre::firstOrCreate(['name' => $name, 'slug' => $slug]);
            // store id
            $genreDbMap[$name] = $genreModel->id;
        }

        // genre categories keywords for title/desc
        $genreKeywords = [
            'Adventure' => ['adventure', 'quest', 'journey', 'explorer', 'treasure', 'expedition', 'survival', 'island'],
            'Animals' => ['animal', 'dog', 'cat', 'bear', 'rabbit', 'mouse', 'pet', 'zoo', 'wildlife', 'creature', 'fox', 'wolf', 'lion', 'bird'],
            'Bedtime' => ['bedtime', 'sleep', 'goodnight', 'dream', 'lullaby', 'night time', 'moon'],
            'Comedy' => ['funny', 'hilarious', 'comedy', 'humor', 'humour', 'silly', 'laugh', 'joke', 'prank'],
            'Fairy Tales' => ['fairy tale', 'folklore', 'folk tale', 'princess', 'prince', 'dragon', 'fable', 'myth', 'legend', 'castle'],
            'Fantasy' => ['magic', 'wizard', 'witch', 'spell', 'fantasy', 'enchanted', 'elf', 'goblin', 'monster', 'ghost'],
            'History' => ['history', 'historical', 'world war', 'tudor', 'victorian', 'roman', 'ancient', 'timeline', 'king', 'queen', 'empire'],
            'Picture Books' => ['picture book', 'board book', 'illustrated book', 'beautifully illustrated'],
            'Science Fiction' => ['space', 'alien', 'robot', 'sci-fi', 'science fiction', 'future', 'spaceship', 'planet', 'galaxy', 'time travel'],
            'Sports' => ['sport', 'football', 'soccer', 'basketball', 'tennis', 'olympics', 'racing', 'athlete', 'team', 'match'],
        ];

        // counters
        $booksUpdatedWithGenres = 0;
        $booksUpdatedWithPhonics = 0;

        // loop through books in chunks for memory issues
        Book::chunk(200, function ($books) use ($genreKeywords, $genreDbMap, &$booksUpdatedWithGenres, &$booksUpdatedWithPhonics) {
            foreach ($books as $book) {
                
                // GENRES

                // combine title/desc for keyword searches
                $textToSearch = strtolower($book->title . ' ' . $book->description);

                $matchedGenreIds = [];

                // loop through each genre keyword list
                foreach ($genreKeywords as $genreName => $keywords) {
                    foreach ($keywords as $keyword) {
                        // \b = word boundaries = no partial matches
                        if (preg_match('/\b' . preg_quote($keyword, '/') . '\b/i', $textToSearch)) {
                            // add genre id
                            $matchedGenreIds[] = $genreDbMap[$genreName];
                            break;
                        }
                    }
                }

                // add matched genres to book
                if (!empty($matchedGenreIds)) {
                    $book->genres()->syncWithoutDetaching($matchedGenreIds);
                    $booksUpdatedWithGenres++;
                }

                // PHONICS
                // make sure level is integer
                $level = (int) $book->ort_level;
                
                // only low levels use phonics
                if ($level >= 1 && $level <= 7) {
                    // only add phonics if none exist
                    if ($book->phonics()->count() === 0) {
                        
                        // check if level exists in phonics map
                        if (isset($this->levelPhonicsMap[$level])) {
                            // get phonics sounds in phonics map
                            $availableSounds = $this->levelPhonicsMap[$level];
                            // randomise order
                            shuffle($availableSounds);
                            // get 2-4 phonics sounds
                            $selectedSounds = array_slice($availableSounds, 0, rand(2, 4)); 
                            
                            $idsToAttach = [];
                            // convert phonics sounds to db ids
                            foreach ($selectedSounds as $sound) {
                                $idsToAttach[] = $this->phonicDbMap[$sound];
                            }
                            
                            // add phonics to book
                            $book->phonics()->syncWithoutDetaching($idsToAttach);
                            $booksUpdatedWithPhonics++;
                        }
                    }
                }
            }
        });

        $this->newLine();
        $this->info("Added genres to {$booksUpdatedWithGenres} books.");
        $this->info("Added phonics to {$booksUpdatedWithPhonics} books.");
    }

    // set up phonics
    private function setupPhonics()
    {
        $this->levelPhonicsMap = [
            1 => ['s', 'a', 't', 'p', 'i', 'n', 'm', 'd', 'g', 'o', 'c', 'k', 'ck', 'e', 'u', 'r', 'h', 'b', 'f', 'ff', 'l', 'll', 'ss'],
            2 => ['j', 'v', 'w', 'x', 'y', 'z', 'zz', 'qu', 'ch', 'sh', 'th', 'ng'],
            3 => ['ai', 'ee', 'igh', 'oa', 'oo', 'ar', 'or', 'ur', 'ow', 'oi', 'ear', 'air', 'ure', 'er'],
            4 => ['ay', 'ou', 'ie', 'ea', 'oy', 'ir', 'ue', 'aw', 'wh', 'ph', 'ew', 'oe', 'au'],
            5 => ['a-e', 'e-e', 'i-e', 'o-e', 'u-e', 'eigh', 'ey', 'ei'],
            6 => ['c(soft)', 'g(soft)', 'dge', 'kn', 'gn', 'wr', 'mb', 'tch'],
            7 => ['tion', 'sion', 'cial', 'tial', 'ture']
        ];
        
        // make sure each phonics sound exists in db
        foreach ($this->levelPhonicsMap as $level => $sounds) {
            foreach ($sounds as $sound) {
                // create if its missing
                $phonicModel = Phonic::firstOrCreate(['sound' => $sound]);
                $this->phonicDbMap[$sound] = $phonicModel->id;
            }
        }
    }
}