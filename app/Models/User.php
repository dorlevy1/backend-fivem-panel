<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{

    use HasFactory, Notifiable;


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $fillable = ['discord_id', 'username', 'global_name', 'avatar'];
    protected $hidden = [
        'remember_token',
    ];
    private string $avatar;
    private int $id;


    public function avatar(): Attribute
    {
        return new Attribute(
            get: function ($avatar) {
                return "https://cdn.discordapp.com/avatars/{$this->attributes['discord_id']}/{$avatar}";
            });
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

    public function mainUser()
    {
        return $this->belongsTo('User');
    }
}
