<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Top Products per Category
        </x-slot>

        @php($products = $this->getProducts())

        @if ($products->isEmpty())
            <p class="text-sm text-gray-500 dark:text-gray-400">
                No product analytics available yet.
            </p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs uppercase text-gray-500 dark:text-gray-400">
                        <tr>
                            <th class="px-3 py-2">Product</th>
                            <th class="px-3 py-2">Category</th>
                            <th class="px-3 py-2">Rating</th>
                            <th class="px-3 py-2">Reviews</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $product)
                            <tr class="border-t border-gray-100 dark:border-gray-700">
                                <td class="px-3 py-2 font-medium">{{ $product->name }}</td>
                                <td class="px-3 py-2">#{{ $product->category_id }}</td>
                                <td class="px-3 py-2">{{ number_format((float) $product->rating_avg, 2) }}</td>
                                <td class="px-3 py-2">{{ $product->reviews_count }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
