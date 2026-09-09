<?php
declare(strict_types=1);
if (!defined('ABSPATH')) { exit; }
get_header();
?>
<main id="main" class="site-main" tabindex="-1">
  <section class="hero" aria-labelledby="account-title">
    <h1 id="account-title">حساب کاربری</h1>
    <?php if (is_user_logged_in()) : ?>
      <p class="muted">خودروها، مکانیک‌های ذخیره‌شده، یادآوری‌ها و اعلان‌های شما.</p>
      <div class="grid grid--two">
        <article class="card"><h2>مکانیک‌های ذخیره‌شده</h2><p class="muted">از Account API بارگذاری می‌شود.</p></article>
        <article class="card"><h2>خودروهای من</h2><p class="muted">اطلاعات خودروها خصوصی و متعلق به حساب شماست.</p></article>
      </div>
    <?php else : ?>
      <p class="muted">برای مشاهده حساب کاربری وارد شوید.</p>
    <?php endif; ?>
  </section>
</main>
<?php get_footer(); ?>
