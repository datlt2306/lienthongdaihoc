<?php
/**
 * Program Admission Status Notice
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$program_id       = isset( $args['program_id'] ) ? (int) $args['program_id'] : get_the_ID();
$admission_status = isset( $args['admission_status'] ) 
	? $args['admission_status'] 
	: ( function_exists( 'ltdh_get_program_admission_status' ) 
		? ltdh_get_program_admission_status( $program_id ) 
		: ( get_post_meta( $program_id, 'admission_status', true ) ?: 'tuyen-sinh' ) );

if ( $admission_status === 'tam-ngung' ) :
?>
	<div class="bg-red-50 text-red-800 rounded-2xl p-4 flex items-start gap-3 shadow-2xs">
		<svg class="w-5 h-5 text-red-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
		<div>
			<p class="font-bold text-red-900 text-sm">Đã hết chỉ tiêu tuyển sinh năm nay</p>
			<p class="text-xs text-red-700 mt-1">Chương trình tuyển sinh Liên thông của trường hiện đã nhận đủ chỉ tiêu cho đợt này. Quý học viên vui lòng tham khảo các chương trình liên quan hoặc để lại thông tin đăng ký tư vấn để được hướng dẫn lộ trình phù hợp.</p>
		</div>
	</div>
<?php elseif ( $admission_status === 'sap-mo' ) : ?>
	<div class="bg-blue-50 text-blue-800 rounded-2xl p-4 flex items-start gap-3 shadow-2xs">
		<svg class="w-5 h-5 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
		<div>
			<p class="font-bold text-blue-900 text-sm">Chương trình chuẩn bị mở tuyển sinh đợt mới</p>
			<p class="text-xs text-blue-700 mt-1">Đợt tuyển sinh tiếp theo đang chuẩn bị mở tiếp nhận hồ sơ. Quý học viên có thể để lại thông tin để nhận thông báo sớm nhất ngay khi mở cổng đăng ký.</p>
		</div>
	</div>
<?php endif; ?>
