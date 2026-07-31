<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessDatabaseBackup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class BackupController extends Controller
{
    public function index()
    {
        $files = [];
        $backupPath = 'backups';

        if (Storage::disk('public')->exists($backupPath)) {
            $rawFiles = Storage::disk('public')->files($backupPath);
            foreach ($rawFiles as $file) {
                $files[] = [
                    'name' => basename($file),
                    'size' => round(Storage::disk('public')->size($file) / 1024 / 1024, 2) . ' MB',
                    'created_at' => date('Y-m-d H:i:s', Storage::disk('public')->lastModified($file)),
                    'url' => Storage::disk('public')->url($file)
                ];
            }
        }

        // Sort by newest first
        usort($files, fn($a, $b) => strcmp($b['created_at'], $a['created_at']));

        return Inertia::render('Backup/Index', [
            'backups' => $files,
            'translations' => trans('backup'),
        ]);
    }

    public function run()
    {
        ProcessDatabaseBackup::dispatchSync();

        return back()->with('success', trans('backup.success_run'));
    }

    public function download($filename)
    {
        $path = 'backups/' . $filename;
        if (!Storage::disk('public')->exists($path)) {
            abort(404);
        }

        return Storage::disk('public')->download($path);
    }

    public function destroy($filename)
    {
        $path = 'backups/' . $filename;
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

        return back()->with('success', trans('backup.success_destroy'));
    }
}
