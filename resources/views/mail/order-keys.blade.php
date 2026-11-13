<!doctype html><html lang="en"><body style="font-family:Arial,sans-serif;color:#251052;max-width:620px;margin:auto;padding:28px">
<h1>Your next adventure is ready.</h1><p>Thank you! Order <strong>{{ $receipt['number'] }}</strong> is complete.</p>
@foreach($receipt['items'] as $item)
<h2>{{ $item['title'] }}</h2><p>{{ $item['platform'] }} · {{ $item['region'] }} · Sold by {{ $item['seller'] }}</p>
@foreach($item['keys'] as $key)<p style="padding:14px;background:#f0eaf9;font-family:monospace;word-break:break-all">{{ $key }}</p>@endforeach
@endforeach
<p>These are simulated digital keys for an internship demonstration. They cannot be activated. No real payment was collected.</p>
<p><a href="{{ $receipt['url'] }}">View your order</a> in the browser used to purchase, or sign in if you attached this order to an account.</p>
<p>Eneba-style marketplace demo · This email contains your keys; keep it private.</p>
</body></html>
