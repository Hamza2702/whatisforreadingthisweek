<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\School;

class ImportSchools extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schools:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import schools from CSVs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // CSV file paths
        $files = [
            base_path('database/data/AllStateFunded.csv'),
        ];

        // Clear existing data
        School::truncate();

        $this->info("Importing schools");

        // Import data from each file
        foreach ($files as $file) {
            // Check if file exists
            if (!file_exists($file)){
                $this->error("File missing: $file");
                continue;
            }

            // Open and read CSV
            $handle = fopen($file, 'r');
            $header = fgetcsv($handle); // Read header row
            
            // Read each row
            while (($row = fgetcsv($handle)) !== false){
                $data = array_combine($header, $row);

                // Only allow primary schools
                $phase = $data['PhaseOfEducation (name)'] ?? null;
                
                // If not primary, skip
                if ($phase !== 'Primary') {
                    continue;
                }

                // Create or update school
                School::updateOrCreate(
                    ['urn' => $data['URN']],
                    [
                        'name' => $data['EstablishmentName'] ?? null,
                        'phase' => $phase,
                        'town' => $data['Town'] ?? null,
                        'postcode' => $data['Postcode'] ?? null,
                    ]
                );

            }

            fclose($handle);
        }

        $this->info("Import complete");
    }
}