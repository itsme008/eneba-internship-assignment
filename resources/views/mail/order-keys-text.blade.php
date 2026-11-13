Your next adventure is ready.
Order {{ $receipt['number'] }} is complete.
@foreach($receipt['items'] as $item)
{{ $item['title'] }} — {{ $item['platform'] }} / {{ $item['region'] }}
Sold by {{ $item['seller'] }}
@foreach($item['keys'] as $key)
{{ $key }}
@endforeach
@endforeach
These are simulated digital keys for an internship demonstration. They cannot be activated. No real payment was collected.
View order: {{ $receipt['url'] }}
Use the purchasing browser, or sign in if you attached the order to an account. Keep this email private.
