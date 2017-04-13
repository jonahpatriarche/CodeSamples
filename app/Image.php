<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Image extends Model
{

    /**
     * Define attributes that are mass-assignable
     *
     * @var array
     */
    public $fillable = ['path', 'imageable_id', 'imageable_type'];

    /**
     * Define polymorphic relationship
     *  - this allows us to associate multiple images with any model and store the all the urls
     *    in a single table
     *
     * @see \App\Product
     * @see \App\Package
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function imageable()
    {
        return $this->morphTo();
    }

    /**
     * Helper function to transform image path using base path of app's filesystem
     *
     * @see \config\filesystems.php
     *
     * @return string
     */
    public function getUrl()
    {
        return Storage::url($this->path);
    }
}
