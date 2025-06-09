<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Product extends Model
{
    //
    use HasFactory;
    use HasSlug;
    use SoftDeletes; //  The record is not actually removed from the database. Instead, Laravel sets a deleted_at timestamp like: deleted_at = "2025-05-19 11:35:00". So it's "hidden" from your app, but still exists in the table. If u get all products in api, it won't see.
    protected $fillable = [
        'title',
        'slug',
        'description',
        'price',
        'image',
        'image_mime',
        'image_size',
        'category_id',
        'is_flash_sale',
        'flash_sale_start',
        'flash_sale_end',
        'is_best_selling',
        'created_by',
        'updated_by',
    ];



    public function getSlugOptions(): SlugOptions
    {

        // Sluggable = It auto-generates a nice URL slug from a field when saving a model.Ex: title: I-Phone-15-pro => slug: i-phone-15-pro
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug');
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
