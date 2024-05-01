<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Admin extends Authenticatable implements JWTSubject
{

    use HasFactory, Notifiable, HasDatabase, HasDomains;


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $fillable = ['discord_id', 'username', 'global_name', 'avatar', 'remember_token'];
    protected $hidden = [
        'remember_token',
    ];

    public function avatar(): Attribute
    {
        return new Attribute(
            get: function ($avatar) {
                return "https://cdn.discordapp.com/avatars/{$this->attributes['discord_id']}/{$avatar}";
            });
    }

    public function permissions()
    {
        return $this->hasOne(Permission::class, 'discord_id', 'discord_id');
    }

    public function pending()
    {
        return $this->hasOne(PendingPermission::class, 'discord_id', 'discord_id');
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims(): array
    {
        return ['data' => $this];
    }

    public function getAuthIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Get the password for the secondary user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->discord_id . $this->avatar . $this->remember_token;
    }

    /**
     * Get the token value for the "remember me" session.
     *
     * @return string
     */
    public function getRememberToken()
    {
        return $this->remember_token;
    }

    /**
     * Set the token value for the "remember me" session.
     *
     * @param string $value
     *
     * @return void
     */
    public function setRememberToken($value)
    {
        $this->remember_token = $value;
    }

    /**
     * Get the column name for the "remember me" token.
     *
     * @return string
     */
    public function getRememberTokenName()
    {
        return 'remember_token';
    }


}
