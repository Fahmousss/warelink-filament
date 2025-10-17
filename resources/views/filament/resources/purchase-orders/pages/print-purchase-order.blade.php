<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order - {{ $record->po_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
            background: #fff;
        }

        .container {
            max-width: 210mm;
            margin: 0 auto;
            padding: 20mm;
        }

        /* Header Section */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #2563eb;
        }

        .company-info {
            flex: 1;
        }

        .company-name {
            font-size: 28px;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 8px;
        }

        .company-details {
            font-size: 11px;
            color: #666;
            line-height: 1.6;
        }

        .document-info {
            text-align: right;
        }

        .document-title {
            font-size: 32px;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }

        .po-number {
            font-size: 16px;
            font-weight: bold;
            color: #374151;
            font-family: 'Courier New', monospace;
        }

        /* Info Grid Section */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .info-box {
            background: #f9fafb;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }

        .info-box-title {
            font-size: 10px;
            text-transform: uppercase;
            font-weight: 600;
            color: #6b7280;
            margin-bottom: 10px;
            letter-spacing: 0.5px;
        }

        .info-box-content {
            font-size: 12px;
            color: #111827;
        }

        .info-box-content strong {
            display: block;
            font-size: 14px;
            color: #1f2937;
            margin-bottom: 4px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }

        .info-label {
            color: #6b7280;
            font-size: 11px;
        }

        .info-value {
            font-weight: 600;
            color: #111827;
        }

        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-partial {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-completed {
            background: #d1fae5;
            color: #065f46;
        }

        .status-cancelled {
            background: #fee2e2;
            color: #991b1b;
        }

        /* Table Section */
        .table-container {
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        thead {
            background: #1e40af;
            color: white;
        }

        thead th {
            padding: 12px 8px;
            text-align: left;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        tbody tr {
            border-bottom: 1px solid #e5e7eb;
        }

        tbody tr:hover {
            background: #f9fafb;
        }

        tbody td {
            padding: 12px 8px;
            font-size: 12px;
            color: #374151;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .font-mono {
            font-family: 'Courier New', monospace;
        }

        .font-bold {
            font-weight: 600;
        }

        .text-gray {
            color: #6b7280;
        }

        /* Summary Section */
        .summary-section {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
        }

        .summary-box {
            width: 300px;
            background: #f9fafb;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 15px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .summary-row:last-child {
            border-bottom: none;
            padding-top: 12px;
            border-top: 2px solid #1e40af;
            font-size: 16px;
            font-weight: bold;
            color: #1e40af;
        }

        .summary-label {
            color: #6b7280;
            font-size: 12px;
        }

        .summary-value {
            font-weight: 600;
            color: #111827;
        }

        /* Notes Section */
        .notes-section {
            margin-top: 30px;
            padding: 15px;
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 8px;
        }

        .notes-title {
            font-size: 12px;
            font-weight: 600;
            color: #92400e;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        .notes-content {
            font-size: 11px;
            color: #78350f;
            line-height: 1.6;
        }

        /* Footer Section */
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 2px solid #e5e7eb;
        }

        .signature-section {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 30px;
            margin-top: 50px;
        }

        .signature-box {
            text-align: center;
        }

        .signature-line {
            border-top: 2px solid #374151;
            margin-top: 60px;
            padding-top: 8px;
        }

        .signature-label {
            font-size: 11px;
            color: #6b7280;
            text-transform: uppercase;
            font-weight: 600;
        }

        .signature-name {
            font-size: 12px;
            color: #111827;
            margin-top: 4px;
        }

        .footer-info {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            font-size: 10px;
            color: #9ca3af;
        }

        /* Print Styles */
        @media print {
            body {
                margin: 0;
                padding: 0;
            }

            .container {
                max-width: 100%;
                padding: 15mm;
            }

            .no-print {
                display: none !important;
            }

            .page-break {
                page-break-after: always;
            }

            @page {
                margin: 15mm;
                size: A4;
            }
        }

        /* Print Button */
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #2563eb;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            transition: all 0.2s;
            z-index: 1000;
        }

        .print-button:hover {
            background: #1e40af;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .print-button svg {
            display: inline-block;
            width: 16px;
            height: 16px;
            margin-right: 8px;
            vertical-align: middle;
        }
    </style>
</head>

<body>
    <!-- Print Button -->
    <button onclick="window.print()" class="print-button no-print">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
        </svg>
        Print Document
    </button>

    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="company-info">
                <div class="company-name">Your Company Name</div>
                <div class="company-details">
                    123 Business Street, Suite 100<br>
                    City, State 12345<br>
                    Phone: (555) 123-4567<br>
                    Email: info@yourcompany.com<br>
                    Website: www.yourcompany.com
                </div>
            </div>
            <div class="document-info">
                <div class="document-title">PURCHASE ORDER</div>
                <div class="po-number">{{ $record->po_number }}</div>
                <div style="margin-top: 10px;">
                    <span class="status-badge status-{{ strtolower($record->status->value) }}">
                        {{ $record->status->getLabel() }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Info Grid -->
        <div class="info-grid">
            <!-- Supplier Information -->
            <div class="info-box">
                <div class="info-box-title">Supplier Information</div>
                <div class="info-box-content">
                    <strong>{{ $record->supplier->name }}</strong>
                    @if ($record->supplier->code)
                        <div style="margin-top: 4px; color: #6b7280;">Code: {{ $record->supplier->code }}</div>
                    @endif
                    @if ($record->supplier->email)
                        <div style="margin-top: 8px;">{{ $record->supplier->email }}</div>
                    @endif
                    @if ($record->supplier->phone)
                        <div>{{ $record->supplier->phone }}</div>
                    @endif
                    @if ($record->supplier->address)
                        <div style="margin-top: 8px;">{{ $record->supplier->address }}</div>
                    @endif
                </div>
            </div>

            <!-- Order Information -->
            <div class="info-box">
                <div class="info-box-title">Order Information</div>
                <div class="info-box-content">
                    <div class="info-row">
                        <span class="info-label">Order Date:</span>
                        <span class="info-value">{{ $record->order_date->format('F j, Y') }}</span>
                    </div>
                    @if ($record->expected_delivery_date)
                        <div class="info-row">
                            <span class="info-label">Expected Delivery:</span>
                            <span class="info-value">{{ $record->expected_delivery_date->format('F j, Y') }}</span>
                        </div>
                    @endif
                    <div class="info-row">
                        <span class="info-label">Payment Terms:</span>
                        <span class="info-value">Net 30</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Shipping Method:</span>
                        <span class="info-value">Standard Delivery</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 15%;">Product Code</th>
                        <th style="width: 35%;">Description</th>
                        <th style="width: 10%;" class="text-center">Unit</th>
                        <th style="width: 10%;" class="text-right">Quantity</th>
                        <th style="width: 12%;" class="text-right">Unit Price</th>
                        <th style="width: 13%;" class="text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($record->details as $index => $detail)
                        <tr>
                            <td class="text-center text-gray">{{ $index + 1 }}</td>
                            <td class="font-mono">{{ $detail->product->product_code }}</td>
                            <td>
                                <div class="font-bold">{{ $detail->product->name }}</div>
                                @if ($detail->notes)
                                    <div class="text-gray" style="font-size: 10px; margin-top: 2px;">
                                        {{ $detail->notes }}</div>
                                @endif
                            </td>
                            <td class="text-center">{{ $detail->product->unit }}</td>
                            <td class="text-right font-bold">{{ number_format($detail->quantity_ordered) }}</td>
                            <td class="text-right">Rp {{ number_format($detail->price, 0, ',', '.') }}</td>
                            <td class="text-right font-bold">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Summary -->
        <div class="summary-section">
            <div class="summary-box">
                <div class="summary-row">
                    <span class="summary-label">Total Items:</span>
                    <span class="summary-value">{{ $record->details->count() }}</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Total Quantity:</span>
                    <span class="summary-value">{{ number_format($record->details->sum('quantity_ordered')) }}</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Subtotal:</span>
                    <span class="summary-value">Rp {{ number_format($record->total_amount, 0, ',', '.') }}</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Tax (0%):</span>
                    <span class="summary-value">Rp 0</span>
                </div>
                <div class="summary-row">
                    <span>TOTAL:</span>
                    <span>Rp {{ number_format($record->total_amount, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Notes -->
        @if ($record->notes)
            <div class="notes-section">
                <div class="notes-title">Notes / Special Instructions</div>
                <div class="notes-content">{{ $record->notes }}</div>
            </div>
        @endif

        <!-- Terms and Conditions -->
        <div class="notes-section" style="background: #f3f4f6; border-color: #d1d5db; margin-top: 20px;">
            <div class="notes-title" style="color: #374151;">Terms and Conditions</div>
            <div class="notes-content" style="color: #4b5563;">
                1. Payment is due within 30 days of invoice date.<br>
                2. Please include the PO number on all invoices and correspondence.<br>
                3. Goods must be delivered to the address specified on this order.<br>
                4. Supplier must notify us immediately of any delays in delivery.<br>
                5. All goods are subject to inspection upon receipt.
            </div>
        </div>

        <!-- Footer with Signatures -->
        <div class="footer">
            <div class="signature-section">
                <div class="signature-box">
                    <div class="signature-line">
                        <div class="signature-label">Prepared By</div>
                        <div class="signature-name">{{ auth()->user()->name ?? 'Purchasing Department' }}</div>
                    </div>
                </div>
                <div class="signature-box">
                    <div class="signature-line">
                        <div class="signature-label">Approved By</div>
                        <div class="signature-name">_____________________</div>
                    </div>
                </div>
                <div class="signature-box">
                    <div class="signature-line">
                        <div class="signature-label">Supplier Acknowledgment</div>
                        <div class="signature-name">_____________________</div>
                    </div>
                </div>
            </div>

            <div class="footer-info">
                Document generated on {{ now()->format('F j, Y \a\t g:i A') }}<br>
                This is a computer-generated document. No signature is required.
            </div>
        </div>
    </div>

    <script>
        // Auto-print on load (optional - comment out if not needed)
        // window.onload = function() { window.print(); }
    </script>
</body>

</html>
