<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">ボード</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-3 bg-green-50 border border-green-200 text-green-800 text-sm rounded p-3">
                    {{ session('status') }}
                </div>
            @endif
            <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
                @foreach ($statuses as $s)
                    <div class="bg-gray-50 border border-gray-200 rounded-md p-2">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-sm font-semibold text-gray-700">{{ $s }}</h3>
                            <span class="text-xs text-gray-500">{{ count($grouped[$s]) }}</span>
                        </div>
                        <div class="space-y-2">
                            @foreach ($grouped[$s] as $p)
                                <div class="bg-white shadow-sm rounded p-2 text-xs">
                                    <a class="text-indigo-600 hover:underline text-sm font-medium block mb-1" href="{{ route('projects.show', $p) }}">{{ $p->case_name }}</a>
                                    @if ($p->location)<div class="text-gray-500">{{ $p->location }}</div>@endif
                                    @if ($p->unit_price)<div class="text-gray-500">{{ $p->unit_price }}</div>@endif
                                    <form method="POST" action="{{ route('projects.status', $p) }}" class="mt-1">
                                        @csrf
                                        @method('PATCH')
                                        <select name="status" onchange="this.form.submit()"
                                                class="w-full text-xs border-gray-200 rounded">
                                            @foreach ($statuses as $opt)
                                                <option value="{{ $opt }}" @selected($p->status === $opt)>{{ $opt }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                </div>
                            @endforeach
                            @if (count($grouped[$s]) === 0)
                                <div class="text-xs text-gray-400 text-center py-4">なし</div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
