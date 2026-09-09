<?php
declare(strict_types=1);
if (!defined('ABSPATH')) { exit; }
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="my-skip-link" href="#main"><?php echo esc_html__('پرش به محتوا', 'mechanicyab'); ?></a>
<header class="site-header">
  <div class="site-header__inner">
    <a class="brand" href="<?php echo esc_url(home_url('/')); ?>" aria-label="مکانیک‌یاب">مکانیک‌یاب</a>
    <?php if (has_nav_menu('primary')) : ?>
      <nav aria-label="ناوبری اصلی"><?php wp_nav_menu(['theme_location' => 'primary', 'container' => false, 'menu_class' => 'nav-list']); ?></nav>
    <?php endif; ?>
    <a class="button" href="<?php echo esc_url(home_url('/search/')); ?>">جستجو</a>
  </div>
</header>
