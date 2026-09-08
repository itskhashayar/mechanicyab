<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}
get_header();
?>
<main id="main" class="my-site-shell" tabindex="-1">
    <p><?php echo esc_html__('MechanicYab presentation foundation is active.', 'mechanicyab'); ?></p>
</main>
<?php get_footer();
