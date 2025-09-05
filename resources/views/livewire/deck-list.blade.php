<div class="max-w-4xl mx-auto py-10">
    <h1 class="text-3xl font-bold mb-6">📚 Your Decks</h1>

    @if($decks->isEmpty())
        <p class="text-gray-500">No decks available</p>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
            @foreach($decks as $deck)
                <div class="p-6 bg-white shadow rounded-xl hover:shadow-lg transition">
                    <h2 class="text-xl font-semibold">{{ $deck->title }}</h2>
                    <p class="text-sm text-gray-600">{{ $deck->description }}</p>
                    <p class="mt-2 text-gray-800">{{ $deck->cards_count }} cards</p>
{{--                    <a href="{{ route('decks.show', $deck) }}"--}}
{{--                       class="inline-block mt-4 px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">--}}
{{--                        Open Deck--}}
{{--                    </a>--}}
                </div>
            @endforeach
        </div>
    @endif
</div>
