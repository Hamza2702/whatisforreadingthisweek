<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

// php artisan books:backup
class BookBackupSeeder extends Seeder
{
    public function run()
    {
        $dir = database_path('books');

        $this->command->info('Reading file');

        // create genres/phonics before connect books to them
        $tables = ['genres', 'phonics', 'books', 'book_genre', 'book_phonic'];

        // loop thorugh db
        foreach ($tables as $table) {
            $filePath = $dir . '/' . $table . '.csv';

            if (!File::exists($filePath)) {
                $this->command->warn("Skipping {$table} (No CSV file found).");
                continue;
            }

            $this->command->info("Restoring {$table}");

            // read
            $file = fopen($filePath, 'r');
            // get the first row
            $headers = fgetcsv($file);
            $data = [];

            while (($row = fgetcsv($file)) !== false) {
                // combine headers with row data
                $rowData = array_combine($headers, $row);

                // fix excel issue where empty cells are saved as "" instead of null
                foreach ($rowData as $key => $value) {
                    if ($value === '') {
                        
                        // if title or author is blank, give it a fallback and not NULL so it doesnt crash
                        if ($table === 'books' && $key === 'title') {
                            $rowData[$key] = 'Unknown Title';
                        } elseif ($table === 'books' && $key === 'author') {
                            $rowData[$key] = 'Unknown Author';
                        } else {
                            // everything else is null
                            $rowData[$key] = null;
                        }
                    }
                }

                $data[] = $rowData;

                // insert in 200s
                if (count($data) >= 200) {
                    DB::table($table)->insert($data);
                    $data = []; // clear
                }
            }

            // insert remaining rows
            if (count($data) > 0) {
                DB::table($table)->insert($data);
            }

            fclose($file);
        }

        $this->command->info('All books and connections fully restored');
    }
}