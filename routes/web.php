<?php

use App\Models\GoodsReceipt;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/download-pod/{grn}', function (GoodsReceipt $grn) {
    if (! $grn->pod_scan_path) {
        // return 'success';
        abort(404);
    }

    return Storage::disk('private')->download($grn->pod_scan_path);
})->name('download-pod')->middleware('auth');

Route::get('/print-po/{record}', function (PurchaseOrder $record) {
    if (! $record) {
        abort(404);
    }

    return view('filament.resources.purchase-orders.pages.print-purchase-order', ['record' => $record]);
})->name('print-po')->middleware('auth')->can('');
