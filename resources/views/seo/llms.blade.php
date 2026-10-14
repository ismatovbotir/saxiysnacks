@php use App\Support\Storefront; @endphp
# {{ $settings->get('brand_name') }} Premium Snacks

> {{ $settings->text('tagline', 'en') }} {{ $settings->text('legal_name', 'en') }} has produced kurt (dried milk snack), roasted nuts and suluguni cheese in Uzbekistan since {{ $settings->get('founded_year') }}. Products are sold wholesale (B2B) to shops, cafés, HoReCa and fuel stations.

- Address: {{ $settings->text('address', 'en') }}
- Phone: {{ collect($settings->get('phones'))->implode(', ') }}
@if ($settings->get('email'))
- Email: {{ $settings->get('email') }}
@endif
- Languages: Uzbek ({{ Storefront::route('home', [], 'uz') }}), Russian ({{ Storefront::route('home', [], 'ru') }}), English ({{ Storefront::route('home', [], 'en') }})
- Prices and ordering: available to anyone who registers in the Telegram bot{{ $telegramBotUrl ? ' ('.$telegramBotUrl.')' : '' }}. Resellers assigned by SAXIY get separate reseller prices. A price list can also be requested by phone or Telegram{{ $settings->get('telegram_contact') ? ' (https://t.me/'.$settings->get('telegram_contact').')' : '' }}.

## Catalog

@foreach ($categories as $category)
### [{{ $category->translate('name', 'en') }}]({{ Storefront::route('category', $category, 'en') }})

{{ $category->translate('description', 'en') }}

@foreach ($category->products as $product)
- [{{ $product->translate('name', 'en') }}, {{ $product->weight_grams }} g]({{ Storefront::route('product', [$category, $product], 'en') }}){{ $product->box_quantity ? ' — '.$product->box_quantity.' pcs per box' : '' }}
@endforeach

@endforeach
## Pages

- [Catalog]({{ Storefront::route('catalog', [], 'en') }})
- [Contact and price list request]({{ Storefront::route('contact', [], 'en') }})
- [Sitemap]({{ route('sitemap') }})
