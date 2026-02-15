<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class SystemLogsController extends Controller
{
    public function index()
    {
        $title = "System Logs";
        $logPath = storage_path('logs');
        $logs = [];

        if (File::exists($logPath)) {
            $files = File::files($logPath);
            
            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'log') {
                    $logs[] = [
                        'name' => $file->getFilename(),
                        'path' => $file->getPathname(),
                        'size' => $this->formatBytes($file->getSize()),
                        'modified' => date('Y-m-d H:i:s', $file->getMTime()),
                        'timestamp' => $file->getMTime(),
                    ];
                }
            }

            // Sort by most recent first
            usort($logs, function($a, $b) {
                return $b['timestamp'] - $a['timestamp'];
            });
        }

        return view('admin.system-logs.index', compact('title', 'logs'));
    }

    public function view($filename)
    {
        $title = "View Log: " . $filename;
        $logPath = storage_path('logs/' . $filename);

        if (!File::exists($logPath)) {
            return redirect()->route('admin.system-logs.index')
                ->with('message', 'Log file not found.');
        }

        $content = File::get($logPath);
        $lines = explode("\n", $content);
        
        // Parse log entries
        $logEntries = [];
        $currentEntry = null;

        foreach ($lines as $line) {
            // Check if line starts with a log level pattern [YYYY-MM-DD HH:MM:SS]
            if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\].*?(ERROR|WARNING|INFO|DEBUG|CRITICAL|ALERT|EMERGENCY|NOTICE):(.*)/', $line, $matches)) {
                // Save previous entry if exists
                if ($currentEntry !== null) {
                    $logEntries[] = $currentEntry;
                }

                // Start new entry
                $currentEntry = [
                    'timestamp' => $matches[1],
                    'level' => $matches[2],
                    'message' => trim($matches[3]),
                    'stack' => ''
                ];
            } elseif ($currentEntry !== null && !empty(trim($line))) {
                // Append to stack trace
                $currentEntry['stack'] .= $line . "\n";
            }
        }

        // Add the last entry
        if ($currentEntry !== null) {
            $logEntries[] = $currentEntry;
        }

        // Reverse to show newest first
        $logEntries = array_reverse($logEntries);

        return view('admin.system-logs.view', compact('title', 'filename', 'logEntries'));
    }

    public function download($filename)
    {
        $logPath = storage_path('logs/' . $filename);

        if (!File::exists($logPath)) {
            return redirect()->route('admin.system-logs.index')
                ->with('message', 'Log file not found.');
        }

        return response()->download($logPath);
    }

    public function delete($filename)
    {
        $logPath = storage_path('logs/' . $filename);

        if (!File::exists($logPath)) {
            return redirect()->route('admin.system-logs.index')
                ->with('message', 'Log file not found.');
        }

        File::delete($logPath);

        return redirect()->route('admin.system-logs.index')
            ->with('success', 'Log file deleted successfully.');
    }

    public function clear()
    {
        $logPath = storage_path('logs');
        
        if (File::exists($logPath)) {
            $files = File::files($logPath);
            
            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'log') {
                    File::delete($file);
                }
            }
        }

        return redirect()->route('admin.system-logs.index')
            ->with('success', 'All log files cleared successfully.');
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
