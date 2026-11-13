<?php

namespace App\Services;

use App\Mail\OrderKeys;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class OrderDelivery
{
    public function status(string $order): array
    {
        $row = DB::table('order_email_deliveries')->where('order_id', $order)->first();

        $expired = $row && $row->status === 'sending' && $row->lease_until && now()->gte($row->lease_until);

        return ['status' => $expired ? 'pending' : ($row?->status ?? 'unavailable'), 'can_retry' => $row && ($expired || in_array($row->status, ['pending', 'failed', 'unconfigured'])) && $row->attempts < 10];
    }

    public function send(string $order): array
    {
        $lease = (string) Str::uuid();
        $admitted = DB::transaction(function () use ($order, $lease) {
            $row = DB::table('order_email_deliveries')->where('order_id', $order)->lockForUpdate()->first();
            if (! $row || $row->status === 'sent' || $row->attempts >= 10 || ($row->lease_until && now()->lt($row->lease_until)) || ($row->last_attempt_at && now()->diffInSeconds($row->last_attempt_at, absolute: true) < 30)) {
                return false;
            }
            $driver = config('orders.email_driver');
            $configured = ($driver === 'brevo' && config('orders.brevo_api_key')) || ($driver === 'smtp' && config('mail.mailers.smtp.host'));
            if (! $configured) {
                DB::table('order_email_deliveries')->where('id', $row->id)->update(['status' => 'unconfigured', 'failure_code' => 'PROVIDER_NOT_CONFIGURED', 'updated_at' => now()]);

                return false;
            }
            DB::table('order_email_deliveries')->where('id', $row->id)->update(['status' => 'sending', 'lease_token' => $lease, 'lease_until' => now()->addMinutes(2), 'last_attempt_at' => now(), 'attempts' => $row->attempts + 1, 'updated_at' => now()]);

            return true;
        }, 3);
        if (! $admitted) {
            return $this->status($order);
        }
        try {
            $o = DB::table('orders')->where('id', $order)->first();
            $items = DB::table('order_items')->where('order_id', $order)->get()->map(function ($i) {
                $p = json_decode($i->product_snapshot, true);

                return ['title' => $p['title'], 'platform' => $p['platform']['name'], 'region' => $p['region']['name'], 'seller' => $i->seller_name_snapshot, 'keys' => DB::table('order_keys')->where('order_item_id', $i->id)->orderBy('unit_number')->pluck('key_ciphertext')->map(fn ($key) => Crypt::decryptString($key))->all()];
            })->all();
            $receipt = ['number' => $o->order_number, 'items' => $items, 'url' => rtrim(config('app.url'), '/').'/orders/'.$o->id];
            if (config('orders.email_driver') === 'brevo') {
                Http::acceptJson()->withHeaders(['api-key' => config('orders.brevo_api_key')])->connectTimeout(5)->timeout(15)->post('https://api.brevo.com/v3/smtp/email', ['sender' => ['email' => config('mail.from.address'), 'name' => config('mail.from.name')], 'to' => [['email' => $o->buyer_email_snapshot]], 'subject' => 'Your demo keys · '.$o->order_number, 'htmlContent' => view('mail.order-keys', ['receipt' => $receipt])->render()])->throw();
            } else {
                Mail::mailer('smtp')->to($o->buyer_email_snapshot)->send(new OrderKeys($receipt));
            }
            DB::table('order_email_deliveries')->where('order_id', $order)->where('lease_token', $lease)->update(['status' => 'sent', 'sent_at' => now(), 'failure_code' => null, 'lease_token' => null, 'lease_until' => null, 'updated_at' => now()]);
        } catch (\Throwable) {
            // Never log exception bodies: provider requests can include keys and API credentials.
            DB::table('order_email_deliveries')->where('order_id', $order)->where('lease_token', $lease)->update(['status' => 'failed', 'failure_code' => 'DELIVERY_FAILED', 'lease_token' => null, 'lease_until' => null, 'updated_at' => now()]);
        }

        return $this->status($order);
    }
}
