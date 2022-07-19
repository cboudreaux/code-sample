<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemImage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'content',
        'item_id',
        'pic_url',
        'type',
        'width',
        'is_open_house_rider',
    ];

    public static function isDualStatus($id)
    {
        return self::where('id', $id)->where('type', 'dual_status')->exists();
    }

	public static function isDirectional()
	{
		if (str_starts_with($this->type, 'dir'))
			return true;
		else
			return false;
	}

    public function items()
    {
        return $this->hasMany(\App\Models\Item::class);
    }
}
