<?php
declare(strict_types=1);
if (!defined('ABSPATH')) { exit; }
get_header();
$slug = sanitize_title((string) get_query_var('mechanicyab_slug'));
?>
<main id="main" class="site-main" tabindex="-1">
  <article class="card" data-mechanic-profile data-mechanic-slug="<?php echo esc_attr($slug); ?>" aria-labelledby="mechanic-title">
    <div data-profile-content><h1 id="mechanic-title">پروفایل مکانیک</h1><p class="muted">در حال بارگذاری اطلاعات معتبر مکانیک…</p></div>
  </article>
</main>
<?php get_footer(); ?>
