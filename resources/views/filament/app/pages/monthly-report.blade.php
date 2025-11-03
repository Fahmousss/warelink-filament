<x-filament-panels::page>
    <form wire:submit.prevent="generateReport" class="space-y-6">
        <div class="flex items-end space-x-4">
            <div class="flex-1">
                <x-filament::input.wrapper>
                    <x-filament::input.select wire:model="selectedMonth" label="Month">
                        <option value="1">January</option>
                        <option value="2">February</option>
                        <option value="3">March</option>
                        <option value="4">April</option>
                        <option value="5">May</option>
                        <option value="6">June</option>
                        <option value="7">July</option>
                        <option value="8">August</option>
                        <option value="9">September</option>
                        <option value="10">October</option>
                        <option value="11">November</option>
                        <option value="12">December</option>
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </div>

            <div class="flex-1">
                <x-filament::input.wrapper>
                    <x-filament::input.select wire:model="selectedYear" label="Year">
                        @foreach (range(2020, now()->year) as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </div>

            <x-filament::button type="submit">
                Generate Report
            </x-filament::button>
        </div>
    </form>

    <div class="grid grid-cols-1 gap-6 mt-6">
        {{-- Inventory Valuation Section --}}
        <x-filament::section>
            <x-slot name="heading">
                Inventory Valuation Report
            </x-slot>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-4 py-2">Product Code</th>
                            <th class="px-4 py-2">Product Name</th>
                            <th class="px-4 py-2 text-right">End Stock</th>
                            <th class="px-4 py-2 text-right">Unit Price</th>
                            <th class="px-4 py-2 text-right">Total Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($valuationData as $item)
                            <tr class="border-t">
                                <td class="px-4 py-2">{{ $item['product_code'] }}</td>
                                <td class="px-4 py-2">{{ $item['product_name'] }}</td>
                                <td class="px-4 py-2 text-right">{{ number_format($item['end_stock']) }}</td>
                                <td class="px-4 py-2 text-right">
                                    {{ \Illuminate\Support\Number::currency($item['unit_price'], 'IDR') }}
                                </td>
                                <td class="px-4 py-2 text-right">
                                    {{ \Illuminate\Support\Number::currency($item['total_value'], 'IDR') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-filament::section>

        {{-- Goods Receipts Section --}}
        <x-filament::section>
            <x-slot name="heading">
                Goods Receipts Summary
            </x-slot>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-4 py-2">GRN Number</th>
                            <th class="px-4 py-2">Supplier</th>
                            <th class="px-4 py-2">Receipt Date</th>
                            <th class="px-4 py-2 text-right">Total Items</th>
                            <th class="px-4 py-2 text-right">Total Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($receiptsData as $receipt)
                            <tr class="border-t">
                                <td class="px-4 py-2">{{ $receipt['grn_number'] }}</td>
                                <td class="px-4 py-2">{{ $receipt['supplier_name'] }}</td>
                                <td class="px-4 py-2">{{ $receipt['receipt_date'] }}</td>
                                <td class="px-4 py-2 text-right">{{ number_format($receipt['total_items']) }}</td>
                                <td class="px-4 py-2 text-right">
                                    {{ \Illuminate\Support\Number::currency($receipt['total_value'], 'IDR') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-filament::section>

        {{-- Payables Summary Section --}}
        <x-filament::section>
            <x-slot name="heading">
                Payables Summary by Supplier
            </x-slot>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-4 py-2">Supplier</th>
                            <th class="px-4 py-2 text-right">Total Receipts</th>
                            <th class="px-4 py-2 text-right">Total Items</th>
                            <th class="px-4 py-2 text-right">Total Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($payablesData as $payable)
                            <tr class="border-t">
                                <td class="px-4 py-2">{{ $payable['supplier_name'] }}</td>
                                <td class="px-4 py-2 text-right">{{ number_format($payable['total_receipts']) }}</td>
                                <td class="px-4 py-2 text-right">{{ number_format($payable['total_items']) }}</td>
                                <td class="px-4 py-2 text-right">
                                    {{ \Illuminate\Support\Number::currency($payable['total_value'], 'IDR') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-filament::section>

        {{-- Stock Movement Section --}}
        <x-filament::section>
            <x-slot name="heading">
                Stock Movement Report
            </x-slot>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-4 py-2">Product Code</th>
                            <th class="px-4 py-2">Product Name</th>
                            <th class="px-4 py-2 text-right">Opening Stock</th>
                            <th class="px-4 py-2 text-right">Stock In</th>
                            <th class="px-4 py-2 text-right">Stock Out</th>
                            <th class="px-4 py-2 text-right">Closing Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($movementData as $movement)
                            <tr class="border-t">
                                <td class="px-4 py-2">{{ $movement['product_code'] }}</td>
                                <td class="px-4 py-2">{{ $movement['product_name'] }}</td>
                                <td class="px-4 py-2 text-right">{{ number_format($movement['opening_stock']) }}</td>
                                <td class="px-4 py-2 text-right">{{ number_format($movement['stock_in']) }}</td>
                                <td class="px-4 py-2 text-right">{{ number_format($movement['stock_out']) }}</td>
                                <td class="px-4 py-2 text-right">{{ number_format($movement['closing_stock']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
