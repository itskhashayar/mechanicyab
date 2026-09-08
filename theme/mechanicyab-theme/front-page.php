<?php
declare(strict_types=1);
if (!defined('ABSPATH')) { exit; }
get_header();
?>
<main id="main" class="site-main">
  <section class="hero" aria-labelledby="hero-title">
    <p class="muted">مکانیک‌یاب</p>
    <h1 id="hero-title">بهترین مکانیک محله‌ات کجاست؟</h1>
    <p>مکانیک‌های اطرافت رو پیدا کن، نظر بقیه رو بخون و راحت‌تر انتخاب کن.</p>
    <form class="search-panel" role="search" action="<?php echo esc_url(home_url('/search/')); ?>" method="get">
      <label><span class="screen-reader-text">جستجو</span><input name="q" type="search" placeholder="مکانیک، خدمت یا خودرو رو جستجو کن" /></label>
      <label><span class="screen-reader-text">شهر</span><input name="city" type="text" placeholder="شهر یا محله" /></label>
      <button class="button" type="submit">پیدا کردن مکانیک</button>
    </form>
  </section>
  <section aria-labelledby="how-title" class="grid grid--two">
    <article class="card"><h2 id="how-title">ساده و قابل اعتماد</h2><p class="muted">جستجو کن، بررسی کن و با خیال راحت تماس بگیر یا مسیر بگیر.</p></article>
    <article class="card"><h2>برای مکانیک‌ها</h2><p class="muted">پروفایل حرفه‌ای تعمیرگاهت را مدیریت کن و بهتر دیده شو.</p></article>
  </section>
</main>
<?php get_footer(); ?>
