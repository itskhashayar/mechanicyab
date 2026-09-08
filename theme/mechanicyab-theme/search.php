<?php
declare(strict_types=1);
if (!defined('ABSPATH')) { exit; }
get_header();
$query = isset($_GET['q']) ? sanitize_text_field(wp_unslash((string) $_GET['q'])) : '';
?>
<main id="main" class="site-main">
  <section class="hero" aria-labelledby="search-title">
    <h1 id="search-title">جستجوی مکانیک</h1>
    <p class="muted">نتایج را بر اساس محل، خدمت و نیازت پیدا کن.</p>
    <form class="search-panel" role="search" action="<?php echo esc_url(home_url('/search/')); ?>" method="get">
      <label><span class="screen-reader-text">جستجو</span><input name="q" type="search" value="<?php echo esc_attr($query); ?>" placeholder="مکانیک، خدمت یا خودرو" /></label>
      <label><span class="screen-reader-text">محل</span><input name="city" type="text" placeholder="شهر یا محله" /></label>
      <button class="button" type="submit">جستجو</button>
    </form>
  </section>
  <section class="card" aria-live="polite"><h2>نتایج جستجو</h2><p class="muted">نتایج از Search API بارگذاری می‌شوند.</p></section>
</main>
<?php get_footer(); ?>
