<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\OrderItem;
use Carbon\Carbon;

class UpdateProductFlags extends Command
{
    protected $signature = 'products:update-flags';
    protected $description = 'Update is_new, is_best_selling, and is_flash_sale flags';

    public function handle()
    {
        $now = Carbon::now();

        // -------------------------
        // Auto-set is_new (7 days)
        // -------------------------
        $cutoff = $now->copy()->subDays(7);
        Product::query()->update(['is_new' => false]);
        Product::where('created_at', '>=', $cutoff)->update(['is_new' => true]);

        // Auto-set is_best_selling (>10 orders)
        // ---------------------------------
        $threshold = 10;
        $bestSellingIds = OrderItem::selectRaw('product_id, COUNT(*) as total')
            ->groupBy('product_id')
            ->having('total', '>', $threshold)
            ->pluck('product_id');

        Product::query()->update(['is_best_selling' => false]);
        Product::whereIn('id', $bestSellingIds)->update(['is_best_selling' => true]);


        // Auto-set is_flash_sale
        Product::query()->update(['is_flash_sale' => false]);
        Product::where('flash_sale_starts_at', '<=', $now)
            ->where('flash_sale_ends_at', '>=', $now)
            ->update(['is_flash_sale' => true]);

        $this->info('Product flags (new, best-selling, flash sale) updated!');
    }
}
