<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">案件一覧</h2>
            <a href="{{ route('projects.create') }}"
               class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                ＋ リード追加
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="bg-green-50 border border-green-200 text-green-800 text-sm rounded p-3">
                    {{ session('status') }}
                </div>
            @endif

            <form method="GET" action="{{ route('projects.index') }}"
                  class="bg-white shadow-sm rounded-md p-4 grid grid-cols-1 md:grid-cols-12 gap-3">
                <div class="md:col-span-6">
                    <label class="block text-xs text-gray-500 mb-1">キーワード（案件名・作業内容・場所・スキル）</label>
                    <input type="text" name="q" value="{{ $filters['q'] }}"
                           class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                </div>
                <div class="md:col-span-3">
                    <label class="block text-xs text-gray-500 mb-1">ステータス</label>
                    <select name="status"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">全て</option>
                        @foreach ($statuses as $s)
                            <option value="{{ $s }}" @selected($filters['status'] === $s)>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs text-gray-500 mb-1">ソート</label>
                    <select name="sort"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="created_at" @selected($filters['sort'] === 'created_at')>登録日</option>
                        <option value="unit_price" @selected($filters['sort'] === 'unit_price')>単価</option>
                        <option value="case_name" @selected($filters['sort'] === 'case_name')>案件名</option>
                    </select>
                </div>
                <div class="md:col-span-1 flex items-end">
                    <button class="w-full bg-gray-800 text-white text-sm rounded-md py-2 hover:bg-gray-900">検索</button>
                </div>
            </form>

            <div class="bg-white shadow-sm rounded-md overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-medium text-gray-500 uppercase">
                            <th class="px-4 py-2">ステータス</th>
                            <th class="px-4 py-2">案件名</th>
                            <th class="px-4 py-2">場所</th>
                            <th class="px-4 py-2">単価</th>
                            <th class="px-4 py-2">必須スキル</th>
                            <th class="px-4 py-2">取引先</th>
                            <th class="px-4 py-2">登録日</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        @forelse ($projects as $p)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2">
                                    <span @class([
                                        'inline-block text-xs px-2 py-1 rounded',
                                        'bg-gray-200 text-gray-700' => $p->status === '未対応',
                                        'bg-blue-100 text-blue-700' => $p->status === '検討中',
                                        'bg-yellow-100 text-yellow-800' => $p->status === '提案済',
                                        'bg-green-100 text-green-800' => $p->status === '成約',
                                        'bg-red-100 text-red-700' => $p->status === '見送り',
                                    ])>{{ $p->status }}</span>
                                </td>
                                <td class="px-4 py-2">
                                    <a class="text-indigo-600 hover:underline" href="{{ route('projects.show', $p) }}">{{ $p->case_name }}</a>
                                </td>
                                <td class="px-4 py-2 text-gray-600">{{ $p->location }}</td>
                                <td class="px-4 py-2 text-gray-600">{{ $p->unit_price }}</td>
                                <td class="px-4 py-2 text-gray-600">
                                    @foreach ($p->requiredSkills as $s)
                                        <span class="inline-block bg-indigo-50 text-indigo-700 text-xs px-2 py-0.5 rounded mr-1">{{ $s->name }}</span>
                                    @endforeach
                                </td>
                                <td class="px-4 py-2 text-gray-600">{{ optional($p->client)->name }}</td>
                                <td class="px-4 py-2 text-gray-500 whitespace-nowrap">{{ $p->created_at->format('Y/m/d') }}</td>
                                <td class="px-4 py-2 text-right">
                                    <a class="text-xs text-gray-600 hover:text-gray-900" href="{{ route('projects.edit', $p) }}">編集</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                                    案件はまだ登録されていません。
                                    <a href="{{ route('projects.create') }}" class="text-indigo-600 hover:underline">リードを追加</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>{{ $projects->links() }}</div>
        </div>
    </div>
</x-app-layout>
