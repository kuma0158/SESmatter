<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $project->case_name }}</h2>
            <div class="flex gap-2">
                <a href="{{ route('projects.edit', $project) }}"
                   class="px-3 py-2 bg-gray-800 text-white text-sm rounded-md hover:bg-gray-900">編集</a>
                <a href="{{ route('projects.index') }}"
                   class="px-3 py-2 bg-gray-100 text-gray-700 text-sm rounded-md hover:bg-gray-200">一覧へ</a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="bg-green-50 border border-green-200 text-green-800 text-sm rounded p-3">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white shadow-sm rounded-md p-4 flex items-center gap-3">
                <form method="POST" action="{{ route('projects.status', $project) }}" class="flex items-center gap-2">
                    @csrf
                    @method('PATCH')
                    <label class="text-xs text-gray-500">ステータス</label>
                    <select name="status" onchange="this.form.submit()"
                            class="border-gray-300 rounded-md shadow-sm text-sm">
                        @foreach ($statuses as $s)
                            <option value="{{ $s }}" @selected($project->status === $s)>{{ $s }}</option>
                        @endforeach
                    </select>
                </form>
                <div class="text-sm text-gray-500">
                    取引先: {{ optional($project->client)->name ?: '—' }} ／ 登録者: {{ $project->user->name }}
                    ／ 登録日: {{ $project->created_at->format('Y/m/d H:i') }}
                </div>
            </div>

            <div class="bg-white shadow-sm rounded-md p-4 grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                @php $rows = [
                    '作業内容' => $project->work_content,
                    '就業場所' => $project->location,
                    '就業期間' => $project->period,
                    '単価' => $project->unit_price,
                    '精算幅' => $project->settlement,
                    '面談回数' => $project->interview_count,
                    '商流制限' => $project->flow_limit,
                    '契約形態' => $project->contract_type,
                    '年齢制限' => $project->age_limit,
                    '外国籍可否' => $project->foreigner_ok,
                    '個人事業主可否' => $project->freelance_ok,
                ]; @endphp
                @foreach ($rows as $label => $value)
                    <div>
                        <div class="text-xs text-gray-500">{{ $label }}</div>
                        <div class="text-gray-800 whitespace-pre-wrap">{{ $value !== null && $value !== '' ? $value : '—' }}</div>
                    </div>
                @endforeach
                <div class="md:col-span-2">
                    <div class="text-xs text-gray-500">必須スキル</div>
                    <div>
                        @forelse ($project->requiredSkills as $s)
                            <span class="inline-block bg-indigo-50 text-indigo-700 text-xs px-2 py-0.5 rounded mr-1">{{ $s->name }}</span>
                        @empty <span class="text-gray-400">—</span>
                        @endforelse
                    </div>
                </div>
                <div class="md:col-span-2">
                    <div class="text-xs text-gray-500">尚可スキル</div>
                    <div>
                        @forelse ($project->preferredSkills as $s)
                            <span class="inline-block bg-gray-100 text-gray-700 text-xs px-2 py-0.5 rounded mr-1">{{ $s->name }}</span>
                        @empty <span class="text-gray-400">—</span>
                        @endforelse
                    </div>
                </div>
                <div class="md:col-span-2">
                    <div class="text-xs text-gray-500">特記事項</div>
                    <div class="text-gray-800 whitespace-pre-wrap">{{ $project->memo ?: '—' }}</div>
                </div>
            </div>

            @if ($project->raw_text)
                <details class="bg-white shadow-sm rounded-md p-4">
                    <summary class="cursor-pointer text-sm text-gray-600">元のリード本文を表示</summary>
                    <pre class="mt-2 text-xs text-gray-700 whitespace-pre-wrap font-mono">{{ $project->raw_text }}</pre>
                </details>
            @endif
        </div>
    </div>
</x-app-layout>
