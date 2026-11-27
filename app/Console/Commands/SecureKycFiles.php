<?php

namespace App\Console\Commands;

use App\Models\Kyc;
use App\Support\KycDocumentPath;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SecureKycFiles extends Command
{
    protected $signature = 'kyc:secure-files {--dry-run : Report legacy public files without moving them}';
    protected $description = 'Move existing public KYC images to private storage and update their database paths';

    public function handle()
    {
        $moved = 0;
        $missing = 0;
        $missingGroups = [];
        $dryRun = (bool) $this->option('dry-run');

        Kyc::orderBy('id')->chunkById(100, function ($applications) use (&$moved, &$missing, &$missingGroups, $dryRun) {
            foreach ($applications as $application) {
                foreach (['frontimg', 'backimg'] as $column) {
                    $rawPath = $application->{$column};
                    $oldPath = KycDocumentPath::normalize($rawPath);
                    if (!$oldPath || strpos($oldPath, 'kyc-documents/') === 0) {
                        continue;
                    }

                    $source = KycDocumentPath::resolve($oldPath);
                    if (!$source) {
                        $missing++;
                        $prefix = strpos($oldPath, '/') === false ? $oldPath : strtok($oldPath, '/');
                        $missingGroups[$prefix] = ($missingGroups[$prefix] ?? 0) + 1;
                        $this->warn("KYC application {$application->id}: {$column} file is missing or has an unsupported legacy path.");
                        continue;
                    }
                    if ($dryRun) {
                        $this->line("Would move KYC application {$application->id}: {$column}");
                        $moved++;
                        continue;
                    }

                    $extension = pathinfo($oldPath, PATHINFO_EXTENSION) ?: 'bin';
                    $newPath = 'kyc-documents/' . $application->user_id . '/' . Str::uuid() . '.' . $extension;
                    $contents = $source['disk'] === 'absolute'
                        ? file_get_contents($source['absolute'])
                        : Storage::disk($source['disk'])->get($source['path']);
                    if ($contents === false) {
                        $missing++;
                        $this->warn("KYC application {$application->id}: {$column} could not be read.");
                        continue;
                    }
                    Storage::disk('local')->put($newPath, $contents);

                    try {
                        DB::transaction(function () use ($application, $column, $newPath) {
                            $locked = Kyc::whereKey($application->id)->lockForUpdate()->firstOrFail();
                            $locked->{$column} = $newPath;
                            $locked->save();
                        });
                        if ($source['disk'] === 'absolute') {
                            @unlink($source['absolute']);
                        } else {
                            Storage::disk($source['disk'])->delete($source['path']);
                        }
                        $moved++;
                    } catch (\Throwable $e) {
                        Storage::disk('local')->delete($newPath);
                        throw $e;
                    }
                }
            }
        });

        $this->info(($dryRun ? 'Would move ' : 'Moved ') . $moved . ' existing KYC image(s) to private storage.');
        if ($missing > 0) {
            $this->warn("Could not locate {$missing} legacy KYC image(s); their database records were left unchanged.");
            foreach ($missingGroups as $prefix => $count) {
                $this->line("Unresolved stored-path group {$prefix}: {$count} image(s). Filenames were not printed.");
            }
        }

        return self::SUCCESS;
    }
}
