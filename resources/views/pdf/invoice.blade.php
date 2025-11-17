<x-pdf-layout :record="$record">
    <div class="flex justify-between items-start mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Invoice #{{ $record->code }}</h1>
            <p class="text-sm text-gray-500">Invoice date: {{ $record->created_at->format('d.m.Y') }}</p>
        </div>

        <div class="text-right">
            <p class="text-lg font-semibold">{{ $record->organisation->name }}</p>
            <p class="text-sm text-gray-600">{{ $record->organisation->address }}</p>
            <p class="text-sm text-gray-600">{{ $record->organisation->city }}</p>
            <p class="text-sm text-gray-600">{{ $record->organisation->vat_number }}</p>
        </div>
    </div>

    <div class="mb-6">
        <h2 class="text-lg font-semibold mb-2 text-gray-800">Client</h2>
        <p class="text-gray-700">{{ $record->client->full_name }}</p>
        <p class="text-gray-500 text-sm">{{ $record->client->street }}, {{ $record->client->city }}</p>
        <p class="text-gray-500 text-sm">{{ $record->client->oib }}</p>
    </div>

    <table class="w-full text-sm border-collapse mb-8">
        <thead>
        <tr class="bg-gray-100 border-b border-gray-300 text-gray-700">
            <th class="py-2 px-3 text-left font-medium">Name</th>
            <th class="py-2 px-3 text-right font-medium">Quantity</th>
            <th class="py-2 px-3 text-right font-medium">Price</th>
            <th class="py-2 px-3 text-right font-medium">Vat</th>
            <th class="py-2 px-3 text-right font-medium">Total</th>
        </tr>
        </thead>
        <tbody>
        @foreach($record->invoiceItems as $item)
            <tr class="border-b border-gray-200">
                <td class="py-2 px-3">{{ $item->name }}</td>
                <td class="py-2 px-3 text-right">{{ $item->quantity }}</td>
                <td class="py-2 px-3 text-right">{{ Number::currency($item->price, 'EUR') }}</td>
                <td class="py-2 px-3 text-right">{{ $item->tax_rate }}%</td>
                <td class="py-2 px-3 text-right font-medium">{{ Number::currency($item->total, 'EUR') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="flex justify-end">
        <table class="text-sm w-1/3">
            <tr>
                <td class="py-1 text-gray-500">Base price:</td>
                <td class="py-1 text-right">{{ Number::currency($record->total, 'EUR') }}</td>
            </tr>
            <tr>
                <td class="py-1 text-gray-500">Vat ({{ $record->tax_rate }}%):</td>
                <td class="py-1 text-right">{{ Number::currency($record->total, 'EUR') }}</td>
            </tr>
            <tr class="border-t border-gray-300">
                <td class="py-2 font-semibold text-gray-800">Total to pay:</td>
                <td class="py-2 text-right font-bold text-gray-800 text-lg">
                    {{ Number::currency($record->total, 'EUR') }}
                </td>
            </tr>
        </table>
    </div>

    <p class="text-xs text-gray-500 mt-8">
        {{$record->terms_and_conditions}}
    </p>
</x-pdf-layout>
