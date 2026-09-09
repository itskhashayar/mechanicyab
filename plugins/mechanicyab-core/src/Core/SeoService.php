<?php

declare(strict_types=1);

namespace MechanicYab\Core\Core;

final class SeoService
{
    public function canonical(string $url): string { $url=function_exists('esc_url_raw') ? esc_url_raw($url) : filter_var($url,FILTER_SANITIZE_URL); return rtrim($url,'/').'/'; }
    /** @param array<string, mixed> $resource */
    public function schema(string $type, array $resource): array { if (!in_array($type,['LocalBusiness','Service','WebSite'],true)) { throw new \InvalidArgumentException('Unsupported schema type.'); } $clean=static fn(string $v):string=>function_exists('sanitize_text_field')?sanitize_text_field($v):trim(strip_tags($v)); return ['@context'=>'https://schema.org','@type'=>$type,'name'=>$clean((string)($resource['name']??'')),'url'=>$this->canonical((string)($resource['url']??'https://example.test/')),'description'=>$clean((string)($resource['description']??''))]; }
    /** @param list<string> $urls */
    public function sitemapChunk(array $urls): string { return '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.implode('',array_map(static fn(string $url): string=>'<url><loc>'.htmlspecialchars(function_exists('esc_url')?esc_url($url):$url,ENT_XML1).'</loc></url>',array_slice($urls,0,50000))).'</urlset>'; }
}
