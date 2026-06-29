<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}
delete_option( 'gdpr_popup_text' );
delete_option( 'gdpr_accept_text' );
delete_option( 'gdpr_reject_text' );
delete_option( 'google_analytics_code' );
