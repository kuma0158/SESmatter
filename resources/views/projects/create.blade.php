<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">リード追加 / 解析</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4"
             x-data="leadCreate(@js($statuses), @js($prefill))">

            <div class="bg-white shadow-sm rounded-md p-4 space-y-3">
                <h3 class="text-sm font-medium text-gray-700">1. リード本文を貼り付けて解析</h3>
                <textarea x-model="rawText" rows="10"
                          class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                          placeholder="【案件名】：...&#10;【必須スキル】：...&#10;..."></textarea>
                <div class="flex items-center gap-2">
                    <button type="button" @click="parse()" :disabled="parsing"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 disabled:opacity-50">
                        <span x-show="!parsing">解析する</span>
                        <span x-show="parsing">解析中...</span>
                    </button>
                    <button type="button" @click="manual()"
                            class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-200">
                        手動入力で登録
                    </button>
                    <p class="text-sm text-red-600" x-text="error"></p>
                </div>
            </div>

            <form method="POST" action="{{ route('projects.store') }}"
                  x-show="showForm" x-cloak
                  class="bg-white shadow-sm rounded-md p-4 space-y-4">
                @csrf

                <h3 class="text-sm font-medium text-gray-700">2. 内容を確認・修正して登録</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-xs text-gray-500 mb-1">案件名 <span class="text-red-500">*</span></label>
                        <input type="text" name="case_name" required x-model="form.case_name"
                               class="w-full border-gray-300 rounded-md shadow-sm" />
                        @error('case_name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">取引先</label>
                        <input type="text" name="client_name" x-model="form.client_name"
                               class="w-full border-gray-300 rounded-md shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">ステータス</label>
                        <select name="status" x-model="form.status"
                                class="w-full border-gray-300 rounded-md shadow-sm">
                            <template x-for="s in statuses" :key="s">
                                <option :value="s" x-text="s"></option>
                            </template>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs text-gray-500 mb-1">作業内容</label>
                        <textarea name="work_content" rows="4" x-model="form.work_content"
                                  class="w-full border-gray-300 rounded-md shadow-sm"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">必須スキル（カンマ区切り）</label>
                        <input type="text" :value="form.required_skills.join(', ')"
                               @input="form.required_skills = splitCsv($event.target.value)"
                               class="w-full border-gray-300 rounded-md shadow-sm" />
                        <template x-for="s in form.required_skills" :key="s">
                            <input type="hidden" name="required_skills[]" :value="s" />
                        </template>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">尚可スキル（カンマ区切り）</label>
                        <input type="text" :value="form.preferred_skills.join(', ')"
                               @input="form.preferred_skills = splitCsv($event.target.value)"
                               class="w-full border-gray-300 rounded-md shadow-sm" />
                        <template x-for="s in form.preferred_skills" :key="s">
                            <input type="hidden" name="preferred_skills[]" :value="s" />
                        </template>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">就業場所</label>
                        <input type="text" name="location" x-model="form.location" class="w-full border-gray-300 rounded-md shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">就業期間</label>
                        <input type="text" name="period" x-model="form.period" class="w-full border-gray-300 rounded-md shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">単価</label>
                        <input type="text" name="unit_price" x-model="form.unit_price" class="w-full border-gray-300 rounded-md shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">精算幅</label>
                        <input type="text" name="settlement" x-model="form.settlement" class="w-full border-gray-300 rounded-md shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">面談回数</label>
                        <input type="number" name="interview_count" min="0" max="9" x-model.number="form.interview_count" class="w-full border-gray-300 rounded-md shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">商流制限</label>
                        <input type="text" name="flow_limit" x-model="form.flow_limit" class="w-full border-gray-300 rounded-md shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">契約形態</label>
                        <input type="text" name="contract_type" x-model="form.contract_type" class="w-full border-gray-300 rounded-md shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">年齢制限</label>
                        <input type="text" name="age_limit" x-model="form.age_limit" class="w-full border-gray-300 rounded-md shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">外国籍可否</label>
                        <input type="text" name="foreigner_ok" x-model="form.foreigner_ok" class="w-full border-gray-300 rounded-md shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">個人事業主可否</label>
                        <input type="text" name="freelance_ok" x-model="form.freelance_ok" class="w-full border-gray-300 rounded-md shadow-sm" />
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs text-gray-500 mb-1">特記事項</label>
                        <textarea name="memo" rows="3" x-model="form.memo" class="w-full border-gray-300 rounded-md shadow-sm"></textarea>
                    </div>
                </div>

                <input type="hidden" name="raw_text" :value="rawText" />

                <div class="flex items-center gap-2 pt-2 border-t">
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700">
                        登録する
                    </button>
                    <button type="button" @click="reset()"
                            class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-200">
                        破棄
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function leadCreate(statuses, prefill) {
        const empty = {
            case_name: '', client_name: '', work_content: '',
            required_skills: [], preferred_skills: [],
            location: '', period: '', unit_price: '', settlement: '',
            interview_count: null, flow_limit: '', contract_type: '',
            age_limit: '', foreigner_ok: '', freelance_ok: '', memo: '',
            status: statuses[0] || '未対応',
        };
        return {
            statuses,
            rawText: '',
            parsing: false,
            error: '',
            showForm: Object.keys(prefill || {}).length > 0,
            form: Object.assign({}, empty, prefill || {}),
            async parse() {
                this.error = '';
                if (!this.rawText || this.rawText.length < 10) {
                    this.error = '本文を貼り付けてください';
                    return;
                }
                this.parsing = true;
                try {
                    const res = await fetch('{{ route('leads.parse') }}', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ raw_text: this.rawText }),
                    });
                    const data = await res.json();
                    if (!res.ok) {
                        this.error = data.message || '解析に失敗しました';
                        if (data.fallback) { this.showForm = true; }
                        return;
                    }
                    this.form = Object.assign({}, empty, data.data || {});
                    this.showForm = true;
                } catch (e) {
                    this.error = '通信エラー: ' + e.message;
                    this.showForm = true;
                } finally {
                    this.parsing = false;
                }
            },
            manual() {
                this.form = Object.assign({}, empty);
                this.showForm = true;
            },
            reset() {
                this.form = Object.assign({}, empty);
                this.showForm = false;
            },
            splitCsv(v) {
                return (v || '').split(',').map(s => s.trim()).filter(Boolean);
            },
        };
    }
    </script>
</x-app-layout>
