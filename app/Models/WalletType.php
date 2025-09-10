<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;


/**
 * App\Models\WalletType
 *
 * @property int $id
 * @property string $name
 * @property int $decimals
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|WalletType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WalletType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WalletType query()
 * @method static \Illuminate\Database\Eloquent\Builder|WalletType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WalletType whereDecimals($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WalletType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WalletType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WalletType whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class WalletType extends \CoreProc\WalletPlus\Models\WalletType
{
    use HasFactory;


}
