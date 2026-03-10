<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'no_hp',
        'email',
        'password',
        'status',
        'role',
    ];

    public function clients()
    {
        return $this->belongsToMany(Client::class, 'user_clients', 'user_id', 'client_id');
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function menus()
    {
        return $this->belongsToMany(Menu::class, 'user_has_menu', 'user_id', 'menu_id');
    }

    public function hasMenu($menuName)
    {
        return $this->menus()->where('name', $menuName)->exists();
    }

    public function isAdminWMS()
    {
        return $this->role === 'Admin WMS';
    }

    public function isClientUser()
    {
        return $this->role === 'Client User';
    }

    /**
     * Get IDs of clients this user can access
     */
    public function getAccessibleClientIds()
    {
        if ($this->isAdminWMS()) {
            return Client::pluck('id')->toArray();
        }
        return $this->clients()->pluck('client_id')->toArray();
    }
}
