<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Models\Achat;
use App\Models\Client;
use App\Models\Company;
use App\Models\Order;
use App\Models\SystemLedger;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user = Auth::user();
        if(Auth::user()->is_admin){
            $companies = Company::all();
            $clients = Client::where("user_id","!=",null)->get();
            $users = User::all();
            $system = SystemLedger::whereName("system")->first();
            $system_fee = SystemLedger::whereName("system fee")->first();

            $wallet_system = Wallet::whereUserType(SystemLedger::class)->where("user_id",$system->id)->get();
            $wallet_fee = Wallet::whereUserType(SystemLedger::class)->where("user_id",$system_fee->id)->get();

            $wallets_id = [];

            foreach ($wallet_system as $wallet){
                $wallets_id[] = $wallet->id;

            }

            foreach ($wallet_fee as $wallet){
                $wallets_id[] = $wallet->id;

            }

            $wallets = Wallet::whereIn("id",$wallets_id)->get();

            $achat = Achat::all();
            $trans = Achat::where("status",PaymentStatus::SUCCESSFUL)
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('sum(amount) as amount'),"currency")
                ->groupBy('date',"currency")
                ->get();

            $value = [];

            foreach ($trans as $val){
                $value[] = $val->toArrayStat();
            }

            $trans = json_encode($value);


            $stat = Achat::select("status", DB::raw('count(id) as total'))
                ->groupBy('status')
                ->get();

            $value = [];

            foreach ($stat as $val){
                $value[] = [
                    "total"=>$val->total,
                    "status"=>$val->status
                ] ;
            }


            $stat = json_encode($value);

            $transactions = Transaction::orderBy('id',"desc")->limit(10)->get();


        }else{
            $companies = Company::whereUserId($user->id)->get();
            $clients = Client::where("user_id",$user->id)->get();
            $client_id = [];

            foreach ($clients as $client){
                $client_id[]= $client->id;
            }
            $wallets =  $user->wallets_nuage();
            $achat = Achat::whereIn("client_id",$client_id)->get();
            $trans = Achat::whereIn("client_id",$client_id)->where("status",PaymentStatus::SUCCESSFUL)
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('sum(amount) as amount'),"currency")
                ->groupBy('date',"currency")
                ->get();

            $value = [];

            foreach ($trans as $val){
                $value[] = $val->toArrayStat();
            }

            $trans = json_encode($value);


            $stat = Achat::whereIn("client_id",$client_id)->select("status", DB::raw('count(id) as total'))
                ->groupBy('status')
                ->get();

            $value = [];

            foreach ($stat as $val){
                $value[] = [
                    "total"=>$val->total,
                    "status"=>$val->status
                ] ;
            }


            $stat = json_encode($value);
            $wallet_id= [];

            foreach ($wallets as $wallet){
                $wallet_id [] =$wallet->id;
            }
            $transactions = Transaction::whereIn("wallet_id",$wallet_id)->where("refund",false)->where("amount",'!=',0)->orderBy('id',"desc")->limit(10)->get();
            $users = null;
        }

        $wallet = WalletType::all();

        $wall = [];
        foreach ($wallet as $wal){
            $wall[$wal->id]=$wal->name;
        }

        return view('home')->with("companies",$companies)
            ->with("clients",$clients)
            ->with("users",$users)
            ->with("wallets",$wallets)
            ->with("achat",$achat)
            ->with("transactions",$transactions)
            ->with("trans",$trans)
            ->with('trans_stat',$stat)
            ->with("walls" , $wall);
    }
}
