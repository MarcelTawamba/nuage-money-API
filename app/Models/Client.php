<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Passport\Client as PassportClient;
use Illuminate\Http\Request;
use Laravel\Passport\Token;
use Lcobucci\JWT\Configuration;
use Ramsey\Collection\Collection;


/**
 * App\Models\Client
 *
 * @property string $id
 * @property int|null $user_id
 * @property int $company_id
 * @property boolean $is_live
 * @property string $name
 * @property string $main_wallet
 * @property string|null $secret
 * @property string|null $provider
 * @property string $redirect
 * @property bool $personal_access_client
 * @property bool $password_client
 * @property bool $revoked
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Passport\AuthCode> $authCodes
 * @property-read int|null $auth_codes_count
 * @property-read string|null $plain_secret
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Passport\Token> $tokens
 * @property-read int|null $tokens_count
 * @property-read \App\Models\User|null $user
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Wallet> $wallets
 * @property-read int|null $wallets_count
 * @method static \Laravel\Passport\Database\Factories\ClientFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Client newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Client newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Client query()
 * @method static \Illuminate\Database\Eloquent\Builder|Client whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Client whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Client whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Client wherePasswordClient($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Client wherePersonalAccessClient($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Client whereProvider($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Client whereRedirect($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Client whereRevoked($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Client whereSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Client whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Client whereUserId($value)
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|Client whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Client whereIsLive($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Wallet> $wallets_nuage
 * @property-read int|null $wallets_nuage_count
 * @property-read \App\Models\ClientWallet|null $client
 * @property-read \App\Models\ClientWallet|null $wallet
 * @mixin \Eloquent
 */
class Client extends PassportClient
{


    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $nuage_env = env("NUAGE_ENV");

        if(strtoupper($nuage_env) === strtoupper("SANDBOX")) {
            $this->connection = env("AUTH_DB_CONNECTION", "mysql");
        }

    }

    public static array $rules = [
        'user_id' => 'nullable',
        'name' => 'required|string|max:255',
        'secret' => 'nullable|string|max:100',
        'provider' => 'nullable|string|max:255',
        'redirect' => 'nullable|string|max:65535',
        'personal_access_client' => 'nullable|boolean',
        'password_client' => 'nullable|boolean',
        'revoked' => 'nullable|boolean',
        'created_at' => 'nullable',
        'updated_at' => 'nullable',

    ];



    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class,'company_id');
    }

    public function mainCurrency(): WalletType
    {
        return WalletType::whereName($this->main_wallet)->first();
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public static function findByRequest(Request $request = null) : ?Client
    {


        $bearerToken = request()->bearerToken();
        $tokenId = Configuration::forUnsecuredSigner()->parser()->parse($bearerToken)->claims()->get('jti');
        return Token::find($tokenId)->client;
    }

    public function wallet(): HasOne
    {
        return  $this->hasOne(ClientWallet::class, 'client_id');
    }

    public function wallets()
    {
        $client_wallet = ClientWallet::whereClientId($this->id)->first();
        if($client_wallet instanceof ClientWallet){
            return Wallet::where('user_type',ClientWallet::class)->where('user_id',$client_wallet->id)->get();
        }else{
            return [];
        }

    }

    public function transactions($limit = null)
    {
        $wallet_id = [];
        foreach ($this->wallets() as $wallet){
            $wallet_id[]=$wallet['id'];
        }
        if($limit == null){
            return Transaction::whereIn('wallet_id',$wallet_id)->where("amount","!=",0)->get();
        }else{
            return Transaction::whereIn('wallet_id',$wallet_id)->where("amount","!=",0)->limit($limit)->get();
        }



    }
}
