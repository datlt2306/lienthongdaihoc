<?php
/**
 * Program Comparison — Mobile Stacked Cards View
 *
 * @package lienthongdaihoc
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$hotline_val = isset( $hotline ) ? $hotline : ( function_exists( 'ltdh_compare_get_global_hotline' ) ? ltdh_compare_get_global_hotline() : '0338615497' );
$zalo_val    = isset( $zalo ) ? $zalo : ( function_exists( 'ltdh_compare_get_zalo_url' ) ? ltdh_compare_get_zalo_url() : 'https://zalo.me' );

$sections = [
	'Học phí & Thời gian' => [
		['label' => 'Học phí', 'key' => 'tuition_fee', 'highlight' => 'tuition_fee'],
		['label' => 'Thời gian', 'key' => 'duration', 'highlight' => 'duration'],
		['label' => 'Hạn tuyển sinh', 'key' => 'enrollment_period', 'highlight' => 'enrollment_period'],
	],
	'Thông tin chương trình' => [
		['label' => 'Ngành học', 'key' => 'major', 'render' => 'major'],
		['label' => 'Hệ đào tạo', 'key' => 'training_type'],
		['label' => 'Cơ sở', 'key' => 'campus_info'],
		['label' => 'Bằng cấp', 'key' => 'degree_type'],
		['label' => 'Lịch học', 'key' => 'schedule'],
		['label' => 'Hình thức', 'key' => 'learning_mode'],
	],
	'Tuyển sinh' => [
		['label' => 'Điều kiện', 'key' => 'admission_requirements'],
		['label' => 'Hồ sơ', 'key' => 'required_documents'],
		['label' => 'Đối tượng', 'key' => 'target_students'],
	],
	'Nghề nghiệp' => [
		['label' => 'Việc làm', 'key' => 'career_opportunities'],
		['label' => 'Giá trị bằng', 'key' => 'diploma_value'],
		['label' => 'Ưu điểm', 'key' => 'advantages'],
		['label' => 'Nhược điểm', 'key' => 'disadvantages'],
	],
];
?>

<div class="space-y-6">
	<?php foreach ( $items as $item ) :
		$school_name = $item['school'] ? $item['school']['title'] : '';
		$school_logo = $item['school'] ? ( $item['school']['logo'] ?? '' ) : '';
		$item_hotline = ! empty( $item['hotline'] ) ? $item['hotline'] : $hotline_val;
	?>
		<div class="bg-white rounded-xl shadow-sm border border-slate-100 p-5 space-y-4">
			
			<!-- Card Header: Title + Image + School -->
			<div class="flex items-start gap-4">
				<img src="<?php echo esc_url( $item['thumbnail'] ); ?>"
					 alt="<?php echo esc_attr( $item['title'] ); ?>"
					 class="h-16 w-24 object-cover rounded-lg border border-slate-200 shrink-0">
				<div class="min-w-0 flex-1">
					<?php if ( $school_name ) : ?>
						<div class="flex items-center gap-1.5 mb-1">
							<?php if ( $school_logo ) : ?>
								<img src="<?php echo esc_url( $school_logo ); ?>"
									 alt="<?php echo esc_attr( $school_name ); ?>"
									 class="h-4 w-4 object-cover rounded border border-slate-200">
							<?php endif; ?>
							<span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block truncate"><?php echo esc_html( $school_name ); ?></span>
						</div>
					<?php endif; ?>
					<h3 class="font-extrabold text-slate-900 text-sm leading-snug line-clamp-2">
						<a href="<?php echo esc_url( $item['permalink'] ); ?>" class="hover:text-brand-primary transition-colors">
							<?php echo esc_html( $item['title'] ); ?>
						</a>
					</h3>
					<div class="flex items-center gap-1.5 mt-1.5">
						<?php 
						if ( function_exists( 'ltdh_get_training_type_badge_html' ) && ! empty( $item['training_type'] ) ) {
							echo ltdh_get_training_type_badge_html( $item['training_type'] );
						}
						?>
					</div>
				</div>
			</div>

			<!-- Card Body: Attributes list -->
			<div class="divide-y divide-slate-100 border-t border-slate-100 pt-2">
				<?php foreach ( $sections as $section_name => $attrs ) : ?>
					<div class="py-3">
						<h4 class="text-xs font-black text-brand-primary uppercase tracking-wider mb-2.5 bg-brand-accent/5 -mx-5 px-5 py-1"><?php echo esc_html( $section_name ); ?></h4>
						<div class="space-y-3">
							<?php foreach ( $attrs as $attr ) :
								$highlight_key = $attr['highlight'] ?? '';
								
								// Resolve value
								if ( $attr['key'] === 'major' ) {
									$value = $item['major'] ? esc_html( $item['major']['title'] . ' (' . $item['major']['code'] . ')' ) : '<span class="text-slate-300 italic text-xs">Chưa cập nhật</span>';
								} elseif ( in_array( $attr['key'], [ 'admission_requirements', 'required_documents', 'target_students', 'career_opportunities', 'diploma_value', 'advantages', 'disadvantages' ], true ) ) {
									$val = $item[ $attr['key'] ] ?? '';
									$value = ltdh_compare_field( wp_strip_all_tags( $val ) );
								} else {
									$val = $item[ $attr['key'] ] ?? '';
									$value = $val ? esc_html( $val ) : '<span class="text-slate-300 italic text-xs">Chưa cập nhật</span>';
								}

								$is_best = $highlight_key ? ltdh_compare_is_best( $highlights, $highlight_key, $item['id'] ) : false;
							?>
								<div class="flex flex-col gap-1 py-1 <?php echo $is_best ? 'bg-emerald-50 -mx-3 px-3 rounded-lg border border-emerald-100' : ''; ?>">
									<span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider"><?php echo esc_html( $attr['label'] ); ?></span>
									<div class="text-sm font-medium text-slate-700 leading-relaxed min-w-0">
										<?php echo $value; ?>
										<?php if ( $is_best ) : ?>
											<span class="mt-1 block"><?php echo ltdh_compare_badge( $highlights[ $highlight_key ]['label'] ); ?></span>
										<?php endif; ?>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>

			<!-- Card Footer: Call to Actions -->
			<div class="flex gap-2 pt-3 border-t border-slate-100">
				<a href="tel:<?php echo esc_attr( preg_replace( '/\D/', '', $item_hotline ) ); ?>"
				   class="flex-1 inline-flex items-center justify-center gap-1 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 text-emerald-700 text-xs font-bold py-3 px-1 rounded-lg transition-all min-h-[44px]">
					<span>📞 Gọi ngay</span>
				</a>
				<a href="<?php echo esc_url( $zalo_val ); ?>" target="_blank" rel="noopener"
				   class="flex-1 inline-flex items-center justify-center gap-1 bg-blue-50 hover:bg-blue-100 border border-blue-200 text-blue-700 text-xs font-bold py-3 px-1 rounded-lg transition-all min-h-[44px]">
					<span>💬 Chat Zalo</span>
				</a>
				<a href="<?php echo esc_url( home_url( '/dang-ky-tu-van/?program_id=' . $item['id'] ) ); ?>"
				   class="flex-1 inline-flex items-center justify-center gap-1 bg-brand-primary hover:bg-brand-darkBlue text-white text-xs font-bold py-3 px-1 rounded-lg transition-all min-h-[44px]">
					<span>📝 Đăng ký</span>
				</a>
			</div>

		</div>
	<?php endforeach; ?>
</div>
