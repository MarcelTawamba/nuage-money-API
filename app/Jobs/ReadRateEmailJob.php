<?php

namespace App\Jobs;

use App\Models\ExchangeRateMargin;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use KubAT\PhpSimple\HtmlDomParser;
use Webklex\IMAP\Facades\Client;
use Webklex\PHPIMAP\Folder;
use Webklex\PHPIMAP\Message;

class ReadRateEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $client = Client::account('default');
        $client->connect();

        $folders = $client->getFolders();

        foreach ($folders as $folder) {

            try {
                if(!$folder instanceof Folder) {
                    continue;
                }

                $query = $folder->messages();
                $messages = $query->since(Carbon::now())->from("alerts@mg.monierate.com")->get();
                $dom = HtmlDomParser::str_get_html( $messages[0]->getRawBody() );


                $elems = $dom->find("table");
                $elems = $elems[0]->find("h2");
                $txt = $elems[0]->innertext() ;
                $rate = str_replace("$1=   =3D =E2=82=A6","",$txt);
                $rate = (double) str_replace(",","",$rate);

                $margin = ExchangeRateMargin::where("from_currency","USD")->where("to_currency","NGN")->first();
                $margin1 = ExchangeRateMargin::where("from_currency","NGN")->where("to_currency","USD")->first();

                $margin->rate = $rate;
                $margin->save();

                $margin1->rate = 1/$rate ;
                $margin1->save();

                Log::info("mail",["message"=> $rate]);
            }catch (\Exception $e){

            }

        }
    }

}
