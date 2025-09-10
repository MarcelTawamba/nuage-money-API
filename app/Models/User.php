<?php

namespace App\Models;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use App\Notifications\PasswordReset;


/**
 * App\Models\User
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $account_type
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property mixed $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Client> $clients
 * @property-read int|null $clients_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Passport\Token> $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 * @property string $business_name
 * @property string $country_code
 * @property string $phone_number
 * @property string $business_type
 * @property string $website
 * @property boolean $is_admin
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBusinessName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereBusinessType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCountryCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereWebsite($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereIsAdmin($value)
 * @mixin \Eloquent
 */

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    public $table = 'users';

    public $fillable = [
        'name',
        'email',
        'country_code',
        'phone_number',
        'password',
        "is_admin",
        "account_type"
    ];

    protected $casts = [
        'name' => 'string',
        'email' => 'string',
        'country_code' => 'string',
        'phone_number' => 'string',
        "is_admin"=>"boolean",
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public static array $rules = [
        'name' => 'required|min:3',
        'email' => 'required|email|unique:App\Models\User',
        'country_code' => 'required|min:2',
        'phone_number' => 'required|min:8',
        'password' => 'required|min:6',
        "is_admin"=>"nullable",

    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $nuage_env = env("NUAGE_ENV");

        if(strtoupper($nuage_env) === strtoupper("SANDBOX")) {
            $this->connection = env("AUTH_DB_CONNECTION", "mysql");
        }

    }


    public function clients(): HasMany
    {
        return $this->hasMany(Client::class,'user_id');

    }

    public function wallets_nuage(): array
    {
        $wallets= [];

        foreach ($this->clients as $client){
            $wa = ClientWallet::where('client_id',$client->id)->first();

            if ($wa != null){
                $was = Wallet::where('user_type',ClientWallet::class)->where('user_id',$wa->id)->get();
                foreach($was as $wallet){
                    $wallets[]=$wallet;
                }
            }

        }

        return $wallets;

    }

    public function wallet()
    {

        $clients = [];

        foreach ($this->clients as $client){
            $clients[]=$client->id;

        }

        $client_wallets = [];



        $client_wallet= ClientWallet::whereIn('client_id',$clients)->get();

        foreach ($client_wallet as $wa){
            $client_wallets []=$wa->id;

        }

        return Wallet::where("user_type",ClientWallet::class)->whereIn("user_id",$client_wallets);

    }


}
