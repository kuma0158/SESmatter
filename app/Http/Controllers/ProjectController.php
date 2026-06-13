<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Models\Client;
use App\Models\Project;
use App\Models\Skill;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        $sort = $request->string('sort', 'created_at')->toString();
        $direction = $request->string('direction', 'desc')->toString();
        if (!in_array($sort, ['created_at', 'unit_price', 'case_name'], true)) {
            $sort = 'created_at';
        }
        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'desc';
        }

        $projects = Project::query()
            ->with(['client', 'requiredSkills', 'preferredSkills'])
            ->search($request->string('q')->toString() ?: null)
            ->status($request->string('status')->toString() ?: null)
            ->orderBy($sort, $direction)
            ->paginate(20)
            ->withQueryString();

        return view('projects.index', [
            'projects' => $projects,
            'statuses' => Project::STATUSES,
            'filters' => [
                'q' => $request->string('q')->toString(),
                'status' => $request->string('status')->toString(),
                'sort' => $sort,
                'direction' => $direction,
            ],
        ]);
    }

    public function board(Request $request): View
    {
        $grouped = [];
        foreach (Project::STATUSES as $s) {
            $grouped[$s] = Project::query()
                ->with(['client'])
                ->where('status', $s)
                ->orderByDesc('created_at')
                ->limit(50)
                ->get();
        }
        return view('projects.board', [
            'grouped' => $grouped,
            'statuses' => Project::STATUSES,
        ]);
    }

    public function create(Request $request): View
    {
        $prefill = session('lead_prefill', []);
        return view('projects.create', [
            'prefill' => $prefill,
            'statuses' => Project::STATUSES,
        ]);
    }

    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $project = DB::transaction(function () use ($validated, $request) {
            $clientId = null;
            if (!empty($validated['client_name'])) {
                $client = Client::firstOrCreate(['name' => trim($validated['client_name'])]);
                $clientId = $client->id;
            }

            $rawText = $validated['raw_text'] ?? null;
            $hash = $rawText ? hash('sha256', $rawText) : null;

            $project = Project::create([
                'user_id' => $request->user()->id,
                'client_id' => $clientId,
                'case_name' => $validated['case_name'],
                'work_content' => $validated['work_content'] ?? null,
                'location' => $validated['location'] ?? null,
                'period' => $validated['period'] ?? null,
                'unit_price' => $validated['unit_price'] ?? null,
                'settlement' => $validated['settlement'] ?? null,
                'interview_count' => $validated['interview_count'] ?? null,
                'flow_limit' => $validated['flow_limit'] ?? null,
                'contract_type' => $validated['contract_type'] ?? null,
                'age_limit' => $validated['age_limit'] ?? null,
                'foreigner_ok' => $validated['foreigner_ok'] ?? null,
                'freelance_ok' => $validated['freelance_ok'] ?? null,
                'memo' => $validated['memo'] ?? null,
                'status' => $validated['status'] ?? '未対応',
                'raw_text' => $rawText,
                'raw_text_hash' => $hash,
            ]);

            $this->syncSkills($project, $validated['required_skills'] ?? [], 'required');
            $this->syncSkills($project, $validated['preferred_skills'] ?? [], 'preferred');

            return $project;
        });

        return redirect()
            ->route('projects.show', $project)
            ->with('status', '案件を登録しました');
    }

    public function show(Project $project): View
    {
        $project->load(['client', 'user', 'requiredSkills', 'preferredSkills']);
        return view('projects.show', [
            'project' => $project,
            'statuses' => Project::STATUSES,
        ]);
    }

    public function edit(Project $project): View
    {
        $project->load(['client', 'requiredSkills', 'preferredSkills']);
        return view('projects.edit', [
            'project' => $project,
            'statuses' => Project::STATUSES,
        ]);
    }

    public function update(StoreProjectRequest $request, Project $project): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($project, $validated) {
            $clientId = $project->client_id;
            if (array_key_exists('client_name', $validated)) {
                if (filled($validated['client_name'])) {
                    $client = Client::firstOrCreate(['name' => trim($validated['client_name'])]);
                    $clientId = $client->id;
                } else {
                    $clientId = null;
                }
            }

            $project->update([
                'client_id' => $clientId,
                'case_name' => $validated['case_name'],
                'work_content' => $validated['work_content'] ?? null,
                'location' => $validated['location'] ?? null,
                'period' => $validated['period'] ?? null,
                'unit_price' => $validated['unit_price'] ?? null,
                'settlement' => $validated['settlement'] ?? null,
                'interview_count' => $validated['interview_count'] ?? null,
                'flow_limit' => $validated['flow_limit'] ?? null,
                'contract_type' => $validated['contract_type'] ?? null,
                'age_limit' => $validated['age_limit'] ?? null,
                'foreigner_ok' => $validated['foreigner_ok'] ?? null,
                'freelance_ok' => $validated['freelance_ok'] ?? null,
                'memo' => $validated['memo'] ?? null,
                'status' => $validated['status'] ?? $project->status,
            ]);

            $this->syncSkills($project, $validated['required_skills'] ?? [], 'required', replace: true);
            $this->syncSkills($project, $validated['preferred_skills'] ?? [], 'preferred', replace: true);
        });

        return redirect()
            ->route('projects.show', $project)
            ->with('status', '案件を更新しました');
    }

    public function updateStatus(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:' . implode(',', Project::STATUSES)],
        ]);
        $project->update(['status' => $validated['status']]);

        if ($request->wantsJson()) {
            return redirect()->back()->with('status', 'ステータスを更新しました');
        }
        return redirect()->back()->with('status', 'ステータスを更新しました');
    }

    private function syncSkills(Project $project, array $names, string $requirement, bool $replace = false): void
    {
        if ($replace) {
            $project->skills()->wherePivot('requirement', $requirement)->detach();
        }
        $attach = [];
        foreach ($names as $name) {
            $name = trim((string) $name);
            if ($name === '') continue;
            $skill = Skill::firstOrCreate(['name' => $name]);
            $attach[$skill->id] = ['requirement' => $requirement];
        }
        if (!empty($attach)) {
            $project->skills()->syncWithoutDetaching($attach);
        }
    }
}
