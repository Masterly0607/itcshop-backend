<?php

namespace App\Observers;

use App\Models\Product;
use Carbon\Carbon;

class ProductObserver
{
    public function created(Product $product): void
    {
        // Automatically flag is_new based on creation date
        $product->is_new = $product->created_at >= Carbon::now()->subDays(7);
        $product->saveQuietly(); // Save silently to avoid infinite loop
    }
}
