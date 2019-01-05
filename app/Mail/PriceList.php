<?php

namespace App\Mail;

use App\Platforms\Bitmex;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class PriceList extends Mailable
{
    use Queueable, SerializesModels;

    protected $priceList;
    protected $rateList;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct()
    {
        $bitMex = Bitmex::instance();
        $arr = ['XBTUSD','XBTH19','XBTM19'];
        $priceList = [];
        foreach ($arr as $symbol) {
            $priceList[$symbol] = $bitMex->getTicker($symbol)['last'];
        }
        $this->priceList = $priceList;
        $this->rateList = $bitMex->getFunding('XBTUSD');
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('view.emails.priceList');
    }
}
