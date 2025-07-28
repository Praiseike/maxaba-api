<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\Status;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;


    const TYPE_AGENT = 'agent';
    const TYPE_USER = 'user';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'password',
        'profile_image',
        'ethnicity',
        'address',
        'location',
        'phone_number',
        'first_name',
        'last_name',
        'google_id',
        'account_status',
        'account_type',
        'bio'
    ];

    protected $appends = ['profile_image_url', 'name', 'has_profile'];


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = \Str::uuid();
            }
        });
    }


    public function getRouteKeyName()
    {
        return 'uuid';
    }




    public function getProfileImageUrlAttribute()
    {
        return $this->profile_image ? url("/storage/" . $this->profile_image) : null;
    }


    public function getHasProfileAttribute()
    {
        return $this->first_name || $this->last_name;
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'account_status' => Status::class,
    ];


    public function scopeAgents($query)
    {
        return $query->where('account_type', User::TYPE_AGENT);
    }


    public function getNameAttribute()
    {
        return "$this->first_name $this->last_name";
    }


    public function application()
    {
        return $this->hasOne(AgentApplication::class, foreignKey: 'user_id');
    }

    public function properties()
    {
        return $this->hasMany(Property::class);
    }

    public function favourites()
    {
        return $this->belongsToMany(Property::class, 'favourites')->withTimestamps();
    }

    public function isFavourite(Property $property)
    {
        return $this->favourites()->where('property_id', $property->id)->exists();
    }

    public function following()
    {
        return $this->belongsToMany(User::class, 'user_followers', 'follower_id', 'user_id')->withTimestamps();
    }

    public function followers()
    {
        return $this->belongsToMany(User::class, 'user_followers', 'user_id', 'follower_id')->withTimestamps();
    }

    public function isFollowing(User $user)
    {
        return $this->following()->where('user_id', $user->id)->exists();
    }
    public function follow(User $user)
    {
        if ($this->id !== $user->id ) {
            return $this->following()->attach($user->id);
        }
        throw ClientException::create(
            'You cannot follow yourself or already following this user.',
            400
        );
    }

    public function unfollow(User $user)
    {
        $this->following()->detach($user->id);
    }


    public function roommateRequests()
    {
        return $this->hasOne(RoommateRequest::class);
    }

    public function isAgent()
    {
        return $this->account_type == self::TYPE_AGENT;
    }
}
