<?php

namespace App\Console\Commands;

use App\Services\DssdImportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ProcessDssdImports extends Command
{
    protected $signature = 'dssd:process-imports';

    protected $description = 'Process all DSSD import files in storage/app/imports directory';

    public function handle(DssdImportService $service)
    {
        $importsDir = storage_path('app/imports');
        $processedDir = storage_path('app/imports/processed');

        if (!File::exists($importsDir)) {
            File::makeDirectory($importsDir, 0755, true);
        }
        if (!File::exists($processedDir)) {
            File::makeDirectory($processedDir, 0755, true);
        }

        $files = File::files($importsDir);
        $importableExtensions = ['csv', 'txt', 'xlsx', 'xls'];
        
        $processedCount = 0;

        foreach ($files as $file) {
            $extension = strtolower($file->getExtension());
            if (in_array($extension, $importableExtensions)) {
                $this->info("Processing file: " . $file->getFilename());
                
                try {
                    $result = $service->processFile($file->getRealPath(), $extension, $file->getFilename());
                    $this->info("Successfully imported {$result['count']} rows from " . $file->getFilename());
                    if ($result['empty_produsen'] > 0) {
                        $this->warn("Peringatan: {$result['empty_produsen']} baris tanpa Produsen Data.");
                    }
                    
                    File::move($file->getRealPath(), $processedDir . '/' . $file->getFilename());
                    $processedCount++;
                } catch (\Exception $e) {
                    $this->error("Failed to process " . $file->getFilename() . ": " . $e->getMessage());
                }
            }
        }

        if ($processedCount === 0) {
            $this->info('No files found to process.');
        } else {
            $this->info("Finished processing {$processedCount} file(s).");
        }
    }
}
