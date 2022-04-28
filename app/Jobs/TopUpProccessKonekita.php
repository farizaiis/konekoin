<?php

namespace App\Jobs;

use App\Models\Transaction;
use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TopUpProccessKonekita implements ShouldQueue
{
    protected $record, $user;

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Transaction $record, User $user)
    {
        $this->record = $record;
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $client = new Client();

        $data_login = [
            'email' => env('KONEKITA_EMAIL'),
            'password' => env('KONEKITA_PASSWORD')
        ];

        $login_konekoin = $client->post(env('KONEKITA_URL').'pekarya_login', [
            'headers' => [
                'Accept' => 'application/json',
            ],
            'json' => $data_login
        ]);

        $login_response = json_decode($login_konekoin->getBody());

        $client = new Client([
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.$login_response->token,
                'Content-Type' => 'application/json'
            ]
        ]);

        $get_order = $client->request('GET', env('KONEKITA_URL').'bank_orders/'.$this->record->konekita_order_id);

        $data_order = json_decode($get_order->getBody());

        $data_status = [
            'status' => 'Lunas'
        ];

        $client->request('PUT', env('KONEKITA_URL').'bank_orders/'.$this->record->konekita_order_id, [
            'body' => json_encode($data_status)
        ]);

        $get_user = $client->request('GET', env('KONEKITA_URL').'users/'.$this->record->user_konekita_id);

        $user_konekita = json_decode($get_user->getBody());

        Log::info($user_konekita->data);

        $data_amount = [
            'balance' => $user_konekita->data->balance + $data_order->data->amount
        ];

        $client->request('PUT', env('KONEKITA_URL').'users/'.$this->user->user_konekita_id, [
            'body' => json_encode($data_amount)
        ]);
    }
}
