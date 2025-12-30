<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ApplicationController extends Controller
{
    public function index()
    {
        $employee = Auth::user();
        $applications = $employee->applications()
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('applications.index', compact('applications'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'req_type' => 'required|string|max:30',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'reason' => 'required|string|max:255',
            'subject' => 'nullable|string|max:255',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $data = $request->only(['req_type', 'start_date', 'end_date']);
        $data['employee_id'] = Auth::user()->emp_id;
        $data['subject'] = $request->input('subject', $request->req_type);
        $data['description'] = $request->input('reason');

        if ($request->hasFile('attachment')) {
            $data['file'] = $request->file('attachment')->store('attachments', 'public');
        }

        Application::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Application submitted successfully!'
        ]);
    }

    public function show(Application $application)
    {
        $this->authorize('view', $application);

        return view('applications.show', compact('application'));
    }

    public function history(Request $request)
    {
        $employee = Auth::user();
        $query = $employee->applications();

        if ($request->filled('type')) {
            $query->where('req_type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('month')) {
            $query->whereMonth('start_date', $request->month);
        }

        $applications = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('applications.history', compact('applications'));
    }

    public function downloadAttachment(Application $application)
    {
        $this->authorize('view', $application);

        if (!$application->file || !Storage::disk('public')->exists($application->file)) {
            abort(404, 'Attachment not found');
        }

        return Storage::disk('public')->download($application->file);
    }
}