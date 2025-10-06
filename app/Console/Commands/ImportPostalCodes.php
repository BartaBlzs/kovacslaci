<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Shuchkin\SimpleXLS;
use App\Models\County;
use App\Models\City;
use App\Models\PostalCode;

class ImportPostalCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:postal-codes {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = $this->argument('file');
        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return 1;
        }

        $this->info('Starting import...');
        
        if ($xlsx = SimpleXLS::parse($filePath)) {
            $rows = $xlsx->rows();
            
            // Első sor header, második üres
            $dataRows = array_slice($rows, 2);
            
            $bar = $this->output->createProgressBar(count($dataRows));
            $bar->start();
            
            foreach ($dataRows as $row) {
                if (count($row) < 3) continue;
                
                [$postalCode, $cityName, $countyName] = $row;
                
                // Megye létrehozása vagy lekérdezése
                if ($countyName == "")
                    $countyName = "Budapest";
                $county = County::firstOrCreate(['name' => $countyName]);
                
                // Település létrehozása vagy lekérdezése
                $city = City::firstOrCreate(
                    ['name' => $cityName, 'county_id' => $county->id]
                );
                
                // Irányítószám létrehozása
                PostalCode::firstOrCreate([
                    'code' => str_pad($postalCode, 4, '0', STR_PAD_LEFT),
                    'city_id' => $city->id
                ]);
                
                $bar->advance();
            }
            
            $bar->finish();
            $this->newLine();
            $this->info('Import completed successfully!');
            
            return 0;
        } else {
            $this->error(SimpleXLSX::parseError());
            return 1;
        }
    }
}