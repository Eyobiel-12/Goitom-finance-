<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\TimeEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Project;
use Inertia\Response;

final class TimeEntryController extends Controller
{
    public function index(Request $request): Response
    {
        $entries = TimeEntry::with(['project','user'])->latest('work_date')->paginate(20)->withQueryString();
        $projects = Project::select('id','name')->orderBy('name')->get();
        return Inertia::render('TimeEntries/Index', [
            'entries' => $entries,
            'projects' => $projects,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'project_id' => ['required','exists:projects,id'],
            'work_date' => ['required','date'],
            'hours' => ['required','numeric','min:0.25','max:24'],
            'rate' => ['nullable','numeric','min:0'],
            'description' => ['nullable','string','max:255'],
        ]);

        TimeEntry::create($data + ['user_id' => $request->user()->id]);
        return back()->with('success', 'Time entry saved.');
    }
}


