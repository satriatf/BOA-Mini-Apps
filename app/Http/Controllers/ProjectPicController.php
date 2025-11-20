<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectPic;
use Illuminate\Http\Request;

class ProjectPicController extends Controller
{
    public function store(Request $request, $project_sk)
    {
        $project = Project::where('sk_project', $project_sk)->firstOrFail();

        $data = $request->validate([
            'sk_user' => 'required|string|exists:users,sk_user',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $pic = ProjectPic::create([
            'sk_project' => $project->sk_project,
            'sk_user' => $data['sk_user'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'] ?? null,
            'created_by' => optional($request->user())->employee_name ?? null,
        ]);

        return back()->with('success', 'PIC added.');
    }

    public function destroy(Request $request, $project_sk, $id)
    {
        $project = Project::where('sk_project', $project_sk)->firstOrFail();
        $pic = ProjectPic::where('sk_project', $project->sk_project)->where('id', $id)->firstOrFail();
        $pic->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'PIC removed.');
    }
}
