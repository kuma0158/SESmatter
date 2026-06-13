<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">案件を編集</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <form method="POST" action="{{ route('projects.update', $project) }}"
                  class="bg-white shadow-sm rounded-md p-4 space-y-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-xs text-gray-500 mb-1">案件名 <span class="text-red-500">*</span></label>
                        <input type="text" name="case_name" required value="{{ old('case_name', $project->case_name) }}"
                               class="w-full border-gray-300 rounded-md shadow-sm" />
                        @error('case_name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">取引先</label>
                        <input type="text" name="client_name" value="{{ old('client_name', optional($project->client)->name) }}"
                               class="w-full border-gray-300 rounded-md shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">ステータス</label>
                        <select name="status" class="w-full border-gray-300 rounded-md shadow-sm">
                            @foreach ($statuses as $s)
                                <option value="{{ $s }}" @selected(old('status', $project->status) === $s)>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs text-gray-500 mb-1">作業内容</label>
                        <textarea name="work_content" rows="4" class="w-full border-gray-300 rounded-md shadow-sm">{{ old('work_content', $project->work_content) }}</textarea>
                    </div>

                    @php
                        $req = old('required_skills', $project->requiredSkills->pluck('name')->all());
                        $pref = old('preferred_skills', $project->preferredSkills->pluck('name')->all());
                    @endphp

                    <div>
                        <label class="block text-xs text-gray-500 mb-1">必須スキル（カンマ区切り）</label>
                        <input type="text" id="required_csv" value="{{ implode(', ', $req) }}"
                               oninput="this.nextElementSibling.querySelectorAll('input').forEach(e => e.remove()); this.value.split(',').map(s=>s.trim()).filter(Boolean).forEach(v=>{ const i=document.createElement('input'); i.type='hidden'; i.name='required_skills[]'; i.value=v; this.nextElementSibling.appendChild(i); })"
                               class="w-full border-gray-300 rounded-md shadow-sm" />
                        <div>
                            @foreach ($req as $r)<input type="hidden" name="required_skills[]" value="{{ $r }}" />@endforeach
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">尚可スキル（カンマ区切り）</label>
                        <input type="text" id="preferred_csv" value="{{ implode(', ', $pref) }}"
                               oninput="this.nextElementSibling.querySelectorAll('input').forEach(e => e.remove()); this.value.split(',').map(s=>s.trim()).filter(Boolean).forEach(v=>{ const i=document.createElement('input'); i.type='hidden'; i.name='preferred_skills[]'; i.value=v; this.nextElementSibling.appendChild(i); })"
                               class="w-full border-gray-300 rounded-md shadow-sm" />
                        <div>
                            @foreach ($pref as $r)<input type="hidden" name="preferred_skills[]" value="{{ $r }}" />@endforeach
                        </div>
                    </div>

                    <div><label class="block text-xs text-gray-500 mb-1">就業場所</label><input type="text" name="location" value="{{ old('location', $project->location) }}" class="w-full border-gray-300 rounded-md shadow-sm" /></div>
                    <div><label class="block text-xs text-gray-500 mb-1">就業期間</label><input type="text" name="period" value="{{ old('period', $project->period) }}" class="w-full border-gray-300 rounded-md shadow-sm" /></div>
                    <div><label class="block text-xs text-gray-500 mb-1">単価</label><input type="text" name="unit_price" value="{{ old('unit_price', $project->unit_price) }}" class="w-full border-gray-300 rounded-md shadow-sm" /></div>
                    <div><label class="block text-xs text-gray-500 mb-1">精算幅</label><input type="text" name="settlement" value="{{ old('settlement', $project->settlement) }}" class="w-full border-gray-300 rounded-md shadow-sm" /></div>
                    <div><label class="block text-xs text-gray-500 mb-1">面談回数</label><input type="number" name="interview_count" min="0" max="9" value="{{ old('interview_count', $project->interview_count) }}" class="w-full border-gray-300 rounded-md shadow-sm" /></div>
                    <div><label class="block text-xs text-gray-500 mb-1">商流制限</label><input type="text" name="flow_limit" value="{{ old('flow_limit', $project->flow_limit) }}" class="w-full border-gray-300 rounded-md shadow-sm" /></div>
                    <div><label class="block text-xs text-gray-500 mb-1">契約形態</label><input type="text" name="contract_type" value="{{ old('contract_type', $project->contract_type) }}" class="w-full border-gray-300 rounded-md shadow-sm" /></div>
                    <div><label class="block text-xs text-gray-500 mb-1">年齢制限</label><input type="text" name="age_limit" value="{{ old('age_limit', $project->age_limit) }}" class="w-full border-gray-300 rounded-md shadow-sm" /></div>
                    <div><label class="block text-xs text-gray-500 mb-1">外国籍可否</label><input type="text" name="foreigner_ok" value="{{ old('foreigner_ok', $project->foreigner_ok) }}" class="w-full border-gray-300 rounded-md shadow-sm" /></div>
                    <div><label class="block text-xs text-gray-500 mb-1">個人事業主可否</label><input type="text" name="freelance_ok" value="{{ old('freelance_ok', $project->freelance_ok) }}" class="w-full border-gray-300 rounded-md shadow-sm" /></div>

                    <div class="md:col-span-2">
                        <label class="block text-xs text-gray-500 mb-1">特記事項</label>
                        <textarea name="memo" rows="3" class="w-full border-gray-300 rounded-md shadow-sm">{{ old('memo', $project->memo) }}</textarea>
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-2 border-t">
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700">
                        更新する
                    </button>
                    <a href="{{ route('projects.show', $project) }}"
                       class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-200">
                        キャンセル
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
