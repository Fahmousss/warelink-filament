<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('goods_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('grn_number')->unique();
            $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
            $table->string('delivery_order_number');
            $table->dateTime('receipt_date');
            $table->foreignId('received_by')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['Pending', 'Verified', 'Completed'])->default('Pending');
            $table->string('pod_scan_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('grn_number');
            $table->index('purchase_order_id');
            $table->index('delivery_order_number');
            $table->index('status');
            $table->index('receipt_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('good_receipts');
    }
};
