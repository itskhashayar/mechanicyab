<?php
declare(strict_types=1);
if (!defined('ABSPATH')) { exit; }
get_header();
?>
<main id="main" class="site-main" tabindex="-1">
  <section class="card" aria-labelledby="page-title">
    <h1 id="page-title"><?php echo esc_html(get_the_title() ?: 'مکانیک‌یاب'); ?></h1>
    <p class="muted">اطلاعات این صفحه از داده‌های معتبر مکانیک‌یاب ارائه می‌شود.</p>
  </section>
</main>
<?php get_footer(); ?>
