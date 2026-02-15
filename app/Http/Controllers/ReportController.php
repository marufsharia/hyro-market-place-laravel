<?php

namespace App\Http\Controllers;

use App\Models\Plugin;
use App\Models\Report;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function store(Request $request, Plugin $plugin)
    {
        $validated = $request->validate([
            'reason' => 'required|in:spam,inappropriate,broken,copyright,security,other',
            'description' => 'nullable|string|max:1000'
        ]);

        // Check if user already reported this plugin
        $existingReport = Report::where('user_id', $request->user()->id)
            ->where('plugin_id', $plugin->id)
            ->where('status', 'pending')
            ->first();

        if ($existingReport) {
            return back()->with('error', 'You have already reported this plugin.');
        }

        Report::create([
            'user_id' => $request->user()->id,
            'plugin_id' => $plugin->id,
            'reason' => $validated['reason'],
            'description' => $validated['description'] ?? null,
        ]);

        return back()->with('success', 'Thank you for your report. We will review it shortly.');
    }
}
