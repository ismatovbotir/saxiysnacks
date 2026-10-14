<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">
@foreach ($pages as $page)
@foreach ($page['alternates'] as $url)
  <url>
    <loc>{{ $url }}</loc>
@foreach ($page['alternates'] as $alternateLocale => $alternateUrl)
    <xhtml:link rel="alternate" hreflang="{{ $alternateLocale }}" href="{{ $alternateUrl }}"/>
@endforeach
    <xhtml:link rel="alternate" hreflang="x-default" href="{{ $page['alternates'][\App\Support\Storefront::DEFAULT_LOCALE] }}"/>
@if ($page['lastmod'])
    <lastmod>{{ $page['lastmod'] }}</lastmod>
@endif
    <priority>{{ $page['priority'] }}</priority>
  </url>
@endforeach
@endforeach
</urlset>
