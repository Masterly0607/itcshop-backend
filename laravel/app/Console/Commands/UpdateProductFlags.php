<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\OrderItem;
use Carbon\Carbon;

class UpdateProductFlags extends Command
{
    protected $signature = 'products:update-flags';
    protected $description = 'Update is_best_selling and is_new flags based on logic';

    public function handle()
    {
        // --- BEST SELLING LOGIC ---
        $threshold = 10;

        $bestSelling = OrderItem::selectRaw('product_id, COUNT(*) as total_sold')
            ->groupBy('product_id')
            ->having('total_sold', '>', $threshold)
            ->pluck('product_id');

        Product::query()->update(['is_best_selling' => false]);
        Product::whereIn('id', $bestSelling)->update(['is_best_selling' => true]);

        $this->info('Best selling products updated!');

        // --- NEW PRODUCT LOGIC ---
        $newDays = 7;
        $cutoff = Carbon::now()->subDays($newDays);

        Product::query()->update(['is_new' => false]);
        Product::where('created_at', '>=', $cutoff)->update(['is_new' => true]);

        $this->info('New products updated!');
    }
}
