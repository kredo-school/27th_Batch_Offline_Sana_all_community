<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class JobsController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('jobs');

        if ($search = trim((string) $request->query('search'))) {
            $query->where(function ($query) use ($search) {
                $query->where('queue', 'like', "%{$search}%")
                    ->orWhere('payload', 'like', "%{$search}%");
            });
        }

        $sorts = [
            'latest' => ['created_at', 'desc'],
            'oldest' => ['created_at', 'asc'],
            'queue_asc' => ['queue', 'asc'],
            'queue_desc' => ['queue', 'desc'],
        ];
        [$column, $direction] = $sorts[$request->query('sort', 'latest')] ?? $sorts['latest'];

        $records = $query->orderBy($column, $direction)
            ->paginate(10)
            ->withQueryString();

        $title = 'Queue Jobs';
        $columns = [
            'ID' => 'id',
            'Queue' => 'queue',
            'Attempts' => 'attempts',
            'Reserved' => 'reserved_at',
            'Available At' => 'available_at',
            'Created' => 'created_at',
        ];

        return view('admin.sections.index', compact('title', 'columns', 'records'));
    }

    public function processNext(): RedirectResponse
    {
        Artisan::call('queue:work', [
            '--once' => true,
            '--stop-when-empty' => true,
        ]);

        return redirect()->route('admin.jobs')
            ->with('status', 'The next available queued job was processed.');
    }

    public function destroy(int $jobId): RedirectResponse
    {
        abort_unless(DB::table('jobs')->where('id', $jobId)->delete(), 404);

        return redirect()->route('admin.jobs')
            ->with('status', "Job #{$jobId} deleted successfully.");
    }
}