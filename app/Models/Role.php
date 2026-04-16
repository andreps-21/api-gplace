<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Guard;

class Role extends \Spatie\Permission\Models\Role
{
    use HasFactory;

    public static function create(array $attributes = [])
    {
        $attributes['guard_name'] = $attributes['guard_name'] ?? Guard::getDefaultName(static::class);

        if (!array_key_exists('tenant_id', $attributes) &&  session()->has('tenant')) {
            $attributes['tenant_id'] = session('tenant')['id'];
        }

        return static::query()->create($attributes);
    }

    /**
     * The rules that belong to the Role
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function rules(): BelongsToMany
    {
        return $this->belongsToMany(Rule::class);
    }
}
