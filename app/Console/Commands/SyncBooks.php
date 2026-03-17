<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Book;
use App\Models\Genre;
use App\Models\Phonic;
use Exception;

class SyncBooks extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // added filters for testing
    protected $signature = 'books:sync {--target=all : target to run (all, genres, authors)} {--limit=0 : limit author count (0 for all)} {--author= : test a specific author}';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get books from googles api by genre and phonics, and use openlibrary for any readable copies';

    // phonics level - sounds map
    protected array $levelPhonicsMap = [];
    // stores phonics ids from db
    protected array $phonicDbMap = [];
    // total books saved
    protected int $totalSaved = 0;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Syncing books');
        
        // create phonics in db and build lookup map
        $this->setupPhonics();
        $target = $this->option('target');
        $specificAuthor = $this->option('author');

        // run only for an author for testing
        if (!empty($specificAuthor)) {
            $this->info("\nRunning test for Author: {$specificAuthor}");
            $query = 'inauthor:"' . $specificAuthor . '"';
            $this->fetchAndProcessBooks($query, 0, null);
            $this->info("Done, total new books saved: {$this->totalSaved}");
            return;
        }

        // if ($target === 'all' || $target === 'genres') {
        //     $this->syncGenres();
        // }

        // run authors sync
        if ($target === 'all' || $target === 'authors') {
            $this->syncAuthors();
        }

        $this->info("Done, total new books saved overall: {$this->totalSaved}");
    }

    // setup phonics sounds in db
    private function setupPhonics()
    {   
        // phonics levels and sounds
        $this->levelPhonicsMap = [
            1 => ['s', 'a', 't', 'p', 'i', 'n', 'm', 'd', 'g', 'o', 'c', 'k', 'ck', 'e', 'u', 'r', 'h', 'b', 'f', 'ff', 'l', 'll', 'ss'],
            2 => ['j', 'v', 'w', 'x', 'y', 'z', 'zz', 'qu', 'ch', 'sh', 'th', 'ng'],
            3 => ['ai', 'ee', 'igh', 'oa', 'oo', 'ar', 'or', 'ur', 'ow', 'oi', 'ear', 'air', 'ure', 'er'],
            4 => ['ay', 'ou', 'ie', 'ea', 'oy', 'ir', 'ue', 'aw', 'wh', 'ph', 'ew', 'oe', 'au'],
            5 => ['a-e', 'e-e', 'i-e', 'o-e', 'u-e', 'eigh', 'ey', 'ei'],
            6 => ['c(soft)', 'g(soft)', 'dge', 'kn', 'gn', 'wr', 'mb', 'tch'],
            7 => ['tion', 'sion', 'cial', 'tial', 'ture']
        ];
        
        // make sure each sounds exists in db
        foreach ($this->levelPhonicsMap as $level => $sounds) {
            foreach ($sounds as $sound) {
                $phonicModel = Phonic::firstOrCreate(['sound' => $sound]);
                $this->phonicDbMap[$sound] = $phonicModel->id;
            }
        }
    }

    // sync books by genres
    private function syncGenres()
    {   
        // search genres w/ display names
        $targetGenres = [
            'Adventure' => 'Adventure',
            'Animals' => 'Animals',
            'Bedtime' => 'Bedtime',
            'Comedy' => 'Humorous',
            'Fairy Tales' => 'Fairy Tales & Folklore',
            'Fantasy' => 'Fantasy & Magic',
            'History' => 'Historical',
            'Picture Books' => 'Picture Books',
            'Science Fiction' => 'Science Fiction',
            'Sports' => 'Sports & Recreation',
        ];

        foreach ($targetGenres as $displayName => $searchTerm) {
            $this->info("\nGoing through genre: {$displayName}");

            // create genre if it doesn't exist
            $genre = Genre::firstOrCreate([
                'name' => $displayName,
                'slug' => strtolower(str_replace([' ', '&'], ['-', 'and'], $displayName))
            ]);

            // fetch multiple pages from API
            for ($offset = 0; $offset <= 120; $offset += 40) {
                $query = 'subject:"Juvenile Fiction" ' . $searchTerm . ' -"Young Adult" -"Teen" -"YA"';
                $this->fetchAndProcessBooks($query, $offset, $genre);
            }
        }
    }

    // sync books by authors
    private function syncAuthors()
    {
        // get authors from txt file
        $authors = $this->getAuthorsArray();
        
        if (empty($authors)) {
            return;
        }

        // limit number of authors for testing
        $limit = (int) $this->option('limit');
        if ($limit > 0) {
            $authors = array_slice($authors, 0, $limit);
        }

        $this->info("\nGoing through " . count($authors) . " Authors...");

        foreach ($authors as $index => $authorName) {
            $this->info("--- [" . ($index + 1) . "/" . count($authors) . "] Author: {$authorName}");

            // search books by author
            $query = 'inauthor:"' . $authorName . '"';
            $this->fetchAndProcessBooks($query, 0, null);
        }
    }

    // fetch books from google books api
    // filter
    // check openlibrary for readable copies
    // save into db
    private function fetchAndProcessBooks($query, $offset, $genre = null)
    {
        try {
            // call googles books api
            $googleResponse = Http::timeout(30)->retry(3, 5000)->get(
                "https://www.googleapis.com/books/v1/volumes",
                [
                    'q' => $query,
                    'langRestrict' => 'en',
                    'maxResults' => 40,
                    'startIndex' => $offset,
                    'printType' => 'books',
                    'key' => env('GOOGLE_BOOKS_API_KEY')
                ]
            );

            // API fail
            if ($googleResponse->failed()) {
                // quota exceeded error, warn and stop
                if ($googleResponse->status() === 429) {
                    $this->error("\nGOOGLE API QUOTA EXCEEDED");
                    exit(1);
                }
                $this->error("API request failed for query: {$query}");
                return; 
            }

            // get books returned from API
            $items = $googleResponse->json('items') ?? [];
            if (empty($items)) {
                $this->line("   -> <fg=red>No books found on Google Books for this query.</>");
                return;
            }

            // counters
            $savedForThisPage = 0;
            $readableFound = 0;
            $skippedExisting = 0;
            $skippedNotPrimary = 0; 

            // loop through each book
            foreach ($items as $item) {
                $volumeInfo = $item['volumeInfo'] ?? [];

                // skip books without covers
                if (empty($volumeInfo['imageLinks']['thumbnail'])) continue; 

                // check if language is english
                $language = strtolower($volumeInfo['language'] ?? 'en');
                if ($language !== 'en') {
                    $skippedNotPrimary++;
                    continue;
                }

                // build searchable text for filtering
                $categories = strtolower(implode(' ', $volumeInfo['categories'] ?? []));
                $description = strtolower($volumeInfo['description'] ?? '');

                // skip young adult and teens
                if (preg_match('/\b(young adult|ya fiction|teen|teens|teenager|mature|erotica)\b/i', $categories . ' ' . $description)) {
                    $skippedNotPrimary++;
                    continue;
                }

                if (preg_match('/\b(ages 12\+|ages 13\+|ages 14\+|12 and up|13 and up|secondary school)\b/i', $description)) {
                    $skippedNotPrimary++;
                    continue;
                }

                // skip adult fiction
                if (str_contains($categories, 'fiction') && !preg_match('/\b(juvenile|children|kids|young)\b/i', $categories)) {
                    $skippedNotPrimary++;
                    continue;
                }

                // check author
                $authorName = (!empty($volumeInfo['authors']) && is_array($volumeInfo['authors'])) ? $volumeInfo['authors'][0] : 'Unknown Author';
                
                // get title
                $rawTitle = $volumeInfo['title'] ?? 'Unknown Title';
                
                // clean title from brackets like (penguin classics etc)
                $cleanTitle = preg_replace('/\s*\([^)]*\)/', '', $rawTitle);
                
                // remove spam words
                $cleanTitle = preg_replace('/\b(annotated|illustrated|unabridged|edition)\b/i', '', $cleanTitle);
                
                // remove authors name from title + cleanup title
                $authorPattern = preg_quote($authorName, '/');
                $cleanTitle = preg_replace("/\b(by|-)?\s*{$authorPattern}\b/i", '', $cleanTitle);
                $cleanTitle = preg_replace("/^{$authorPattern}'?s\s+/i", '', $cleanTitle); // somewhat bad since books are only called their authors name
                $cleanTitle = trim(preg_replace('/[-\s:;,]+$/', '', $cleanTitle));
                $cleanTitle = trim(preg_replace('/\s+/', ' ', $cleanTitle));

                
                // check book by title
                $existingBook = Book::whereRaw('LOWER(title) = ?', [strtolower($cleanTitle)])->first();
                if ($existingBook) {
                    if ($genre) {
                        $existingBook->genres()->syncWithoutDetaching([$genre->id]);
                    }
                    $skippedExisting++;
                    continue; 
                }

                // clean description
                $realDescription = isset($volumeInfo['description']) 
                    ? strip_tags($volumeInfo['description']) 
                    : 'No description available for this book.';

                // default openlibrary key
                $olKey = 'NO_OL_' . $item['id']; 
                
                // search openlibrary for readable version
                $olResponse = Http::timeout(5)->get('https://openlibrary.org/search.json', [
                    'title' => $cleanTitle,
                    'author' => $authorName,
                    'limit' => 2
                ]);

                if ($olResponse->successful()) {
                    $docs = $olResponse->json('docs') ?? [];
                    foreach ($docs as $doc) {
                        // if the readable text exists
                        if (!empty($doc['has_fulltext']) && $doc['has_fulltext'] === true && !empty($doc['ia'])) {
                            // store internet archive (ia) key
                            $olKey = $doc['ia'][0]; 
                            $readableFound++;
                            break;
                        }
                    }
                }

                // 2nd check by ol_key
                $existingOlBook = Book::where('ol_key', $olKey)->first();
                if ($existingOlBook) {
                    if ($genre) {
                        $existingOlBook->genres()->syncWithoutDetaching([$genre->id]);
                    }
                    $skippedExisting++;
                    continue; 
                }

                // delay api usage
                usleep(300000); 

                // calculate reading level
                $level = $this->calculateRealisticOrtLevel($volumeInfo);

                // get colour band
                $colour = $this->getOxfordColour($level);

                // save book to db
                try {
                    $book = Book::create([
                        'ol_key' => $olKey, 
                        'title' => $cleanTitle, 
                        'author' => $authorName,
                        'cover_id' => $item['id'], 
                        'ort_level' => $level,
                        'ort_colour' => $colour,
                        'description' => $realDescription, 
                    ]);

                    // attach genre if it was passed into function
                    if ($genre) {
                        $book->genres()->syncWithoutDetaching([$genre->id]);
                    }

                    // attach phonic sounds for low reading level
                    if ($level >= 1 && $level <= 7) {
                        // get sounds available
                        $availableSounds = $this->levelPhonicsMap[$level];
                        // randomise
                        shuffle($availableSounds);
                        // select between 2-4
                        $selectedSounds = array_slice($availableSounds, 0, rand(2, 4)); 
                        
                        $idsToAttach = [];
                        // convert sound names to phonic ids
                        foreach ($selectedSounds as $sound) {
                            $idsToAttach[] = $this->phonicDbMap[$sound];
                        }

                        // attach to book
                        $book->phonics()->syncWithoutDetaching($idsToAttach);
                    }
                    
                    $this->totalSaved++;
                    $savedForThisPage++;

                } catch (\Exception $dbException) {
                    // if db save fails, skip book and continue
                    $this->warn("   -> Skipped '{$cleanTitle}' due to DB error: " . $dbException->getMessage());
                    continue;
                }
            }
            
            $this->line("   -> Page " . (($offset/40)+1) . ": saved {$savedForThisPage} books. (<fg=yellow>{$readableFound} readable</>, <fg=gray>{$skippedExisting} already in DB, {$skippedNotPrimary} skipped Adult/YA/Non-En</>)");

        } catch (Exception $e) {
            $this->error("Error connecting to Google Books API: " . $e->getMessage());
        }
    }
    
    // get authors
    private function getAuthorsArray(): array 
    {
        // text file path
        $filePath = public_path('template/authors.txt');

        // if it cant find
        if (!file_exists($filePath)) {
            $this->error("\nMissing file: Could not find 'authors.txt' at '{$filePath}'");
            return [];
        }

        // read file lines
        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        $authors = [];
        
        // loop through
        foreach ($lines as $line) {
            // remove whitespace
            $line = trim($line);
            
            // skip short lines
            if (empty($line) || strlen($line) <= 2) {
                continue;
            }
            
            // if name format is last, first
            if (str_contains($line, ',')) {
                $parts = explode(',', $line, 2);
                $firstName = trim($parts[1]);
                $lastName = trim($parts[0]);
                
                // remove dots from initials
                $firstName = str_replace('.', ' ', $firstName);
                $lastName = str_replace('.', ' ', $lastName);
                
                // convert to first, last
                $formattedName = $firstName . ' ' . $lastName;
            } else {
                // if it already first and last
                $formattedName = str_replace('.', ' ', $line);
            }
            
            $authors[] = $formattedName;
        }
        
        // remove any duplicates
        return array_unique($authors);
    }

    // create the reading level for each book
    private function calculateRealisticOrtLevel(array $volumeInfo): int 
    {
        // get metadata from googles api
        $pageCount = $volumeInfo['pageCount'] ?? 0;
        $categories = $volumeInfo['categories'] ?? [];
        $title = strtolower($volumeInfo['title'] ?? '');
        $description = strtolower($volumeInfo['description'] ?? '');
        // convert categories into string
        $catString = strtolower(implode(' ', $categories));

        // combine text for keyword searches
        $textToSearch = $title . ' ' . $description . ' ' . $catString;

        // detect picture books
        $isPictureBook = preg_match('/\b(picture book|picture books|board book)\b/i', $textToSearch);
        // detect phonic books
        $isPhonics = preg_match('/\b(phonics|decodable|read with oxford)\b/i', $textToSearch);
        
        // detect ks1 books (Early years foundation stage)
        $isEYFSorKS1 = preg_match('/\b(eyfs|reception|key stage 1|ks1|early reader|first reader|beginner reader|early reading)\b/i', $textToSearch);
        
        // detect ks2 books
        $isKS2 = preg_match('/\b(ks2|key stage 2|year 4|year 5|year 6|ages 8|ages 9|ages 10|ages 11|ages 12|middle grade|chapter book|novel|classic fantasy)\b/i', $textToSearch);

        // if page count is missing, estimate based on the book type
        if ($pageCount < 10) {
            if ($isPhonics) {
                $pageCount = 16;
            } elseif ($isEYFSorKS1) {
                $pageCount = 24;
            } elseif ($isPictureBook) {
                $pageCount = 32;
            } elseif ($isKS2) {
                $pageCount = 200; // novels = 200 pages
            } else {
                $pageCount = 100; // default
            }
        }

        // base levels calculation to normal uk sizes
        $level = 20;
        if ($pageCount <= 16) $level = 2; // Lvl 1-2 (Reception: Pink/Red)
        elseif ($pageCount <= 24) $level = 4; // Lvl 3-4 (Reception/Y1: Yellow/Light Blue)
        elseif ($pageCount <= 32) $level = 6; // Lvl 5-6 (Year 1: Green/Orange)
        elseif ($pageCount <= 48) $level = 8; // Lvl 7-8 (Year 2: Turquoise/Purple)
        elseif ($pageCount <= 64) $level = 10; // Lvl 9-10 (Lower KS2: Gold/White)
        elseif ($pageCount <= 96) $level = 12; // Lvl 11-12 (Lower KS2: Lime/Lime+)
        elseif ($pageCount <= 140) $level = 14; // Lvl 13-14 (Upper KS2: Grey)
        elseif ($pageCount <= 200) $level = 16; // Lvl 15-16 (Upper KS2: Dark Blue)
        elseif ($pageCount <= 250) $level = 18; // Lvl 17-18 (Upper KS2/Advanced: Dark Red)
        else $level = 20; // Lvl 19-20 (Advanced Y6+)

        // phonic books should be low levels
        if ($isPhonics && $pageCount <= 48) {
            $level = min($level, 3);
        }
        
        // eyfs should be low
        if ($isEYFSorKS1 && $pageCount <= 48) {
            $level = min($level, 6);
        }

        // picture books should be low
        if ($isPictureBook && $pageCount <= 64) {
            $level = min($level, 8); 
        }

        // ks2 books at dark blue/grey
        if ($isKS2) {
            $level = max($level, 14);
        }

        return $level;
    }

    // get book reading colour
    private function getOxfordColour($level) {
        return match((int)$level) {
            0 => 'Light Purple',
            1 => 'Pink', // Early Reception
            2 => 'Red', // Reception
            3 => 'Yellow', // Reception / Early Y1
            4 => 'Light Blue', // Y1
            5 => 'Green', // Y1
            6 => 'Orange', // Y1 / Early Y2
            7 => 'Turquoise', // Y2
            8 => 'Purple', // Y2
            9 => 'Gold', // Y3
            10 => 'White', // Y3 / Early Y4
            11 => 'Lime', // Y4
            12 => 'Lime+', // Y4
            13, 14 => 'Grey', // Y5
            15, 16 => 'Dark Blue', // Y5 / Early Y6
            17, 18, 19, 20 => 'Dark Red', // Y6
            default => 'Dark Red',
        };
    }
}