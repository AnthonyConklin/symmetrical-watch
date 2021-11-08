<?php

namespace App\Models;

use App\Exceptions\RouteConflictException;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Routing\Exception\InvalidParameterException;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'username',
        'email'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        /**
         * User profile remains at the top level slug, i.e. https://example.com/{username}.
         * Write a test that, during the user account creation, one can not use a username
         * that conflicts with existing predefined fixed route slugs
         * (i.e. /about, /contact, etc.) in a dynamic way.
         */
        static::creating(function ($user) {
            $hasConflict = collect(app(Router::class)->getRoutes())->map(function ($route) {
                return strtolower(trim($route->uri(), '/'));
            })->flip()->has(strtolower($user->username));

            if ($hasConflict) {
                throw new RouteConflictException('Please choose a different username');
            }
            return $user;
        });
    }


    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function scopeOrderByProductsTotalValue($query, $direction = 'desc')
    {
        // Ensure direction sent is supported or revert to desc
        if (!in_array($direction, ['asc', 'desc'])) {
            throw new InvalidParameterException('Direction must be either "desc" or "asc"');
        }

        return $query->withSum('products as total_value', 'price')
            ->orderBy('total_value', $direction);
    }

    public function getOrderByProductsTotalValueCacheKey()
    {
        return 'users.order-by-products-total-value';
    }

    public function orderByProductsTotalValueFromCache()
    {
        $users = new static;

        return Cache::remember(
            $this->getOrderByProductsTotalValueCacheKey(),
            60*45,
            function () use ($users) {
                return $users->orderByProductsTotalValue()->get();
            }
        );
    }
}
