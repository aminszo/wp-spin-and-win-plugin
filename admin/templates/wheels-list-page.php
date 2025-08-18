<?php

if (! defined('ABSPATH')) exit;

use SWN_Deluxe\Admin;

?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e('Wheels', 'swn-deluxe'); ?></h1>
    <a href="<?php echo esc_url(admin_url('admin.php?page=' . Admin::MENU_SLUGS['WHEEL_EDIT_PAGE'])); ?>" class="page-title-action">
        <?php esc_html_e('Add New Wheel', 'swn-deluxe'); ?>
    </a>

    <hr class="wp-header-end">

    <form method="post">
        <?php $list_table->display(); ?>
    </form>
</div>