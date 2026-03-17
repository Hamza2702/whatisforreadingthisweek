<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class BackupBooks extends Command
{   
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'books:backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup books from db';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $backupDir = database_path('books');

        $tables = ['books', 'genres', 'phonics', 'book_genre', 'book_phonic'];

        // loop through tables
        foreach ($tables as $table) {
            $this->info("Exporting {$table}");
            
            // get all rows from the table into an array
            $data = DB::table($table)->get()->map(function ($item) {
                return (array) $item;
            })->toArray();

            // filepath
            $filePath = $backupDir . '/' . $table . '.csv';
            $file = fopen($filePath, 'w'); // write

            // if it exists
            if (count($data) > 0) {
                // write column headers
                fputcsv($file, array_keys($data[0]));

                // actual data
                foreach ($data as $row) {
                    fputcsv($file, $row);
                }
            }

            fclose($file);
        }

        $this->newLine();
        $this->info("Finished");
    }
}