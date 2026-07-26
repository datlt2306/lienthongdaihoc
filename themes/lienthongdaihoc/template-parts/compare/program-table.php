<?php
/**
 * Program Comparison — Desktop Table View
 *
 * @package lienthongdaihoc
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$count = count( $items );
$col_width = $count <= 2 ? 'w-[300px]' : ( $count === 3 ? 'w-[260px]' : 'w-[220px]' );
?>

<div class="bg-white rounded-lg shadow-sm border border-slate-100 overflow-hidden">
	<table class="w-full border-collapse min-w-[600px]">
		<!-- Header Row: Thumbnails + Titles -->
		<thead>
			<tr>
				<th class="w-[180px] bg-slate-50 p-4 text-left text-sm font-bold text-slate-500 uppercase tracking-wide sticky left-0 z-10 border-b border-slate-100">
					Thông tin
				</th>
				<?php foreach ( $items as $item ) :
					$school_name = $item['school'] ? $item['school']['title'] : '';
				?>
				<th class="<?php echo esc_attr( $col_width ); ?> p-4 text-center border-b border-slate-100 bg-slate-50">
					<div class="flex flex-col items-center gap-2">
						<img src="<?php echo esc_url( $item['thumbnail'] ); ?>"
							 alt="<?php echo esc_attr( $item['title'] ); ?>"
							 class="h-16 w-24 object-cover rounded-lg border border-slate-200">
						<?php if ( $item['school'] && $item['school']['logo'] ) : ?>
							<img src="<?php echo esc_url( $item['school']['logo'] ); ?>"
								 alt="<?php echo esc_attr( $school_name ); ?>"
								 class="h-8 w-8 object-cover rounded-lg border border-slate-200 -mt-4 relative z-10">
						<?php endif; ?>
						<div>
							<a href="<?php echo esc_url( $item['permalink'] ); ?>" class="font-bold text-slate-900 text-sm hover:text-brand-primary transition-colors line-clamp-2 leading-snug">
								<?php echo esc_html( $item['title'] ); ?>
							</a>
							<div class="flex items-center justify-center gap-1.5 mt-1.5">
								<?php 
								if ( function_exists( 'ltdh_get_training_type_badge_html' ) && ! empty( $item['training_type'] ) ) {
									echo ltdh_get_training_type_badge_html( $item['training_type'] );
								}
								?>
							</div>
							<?php if ( $school_name ) : ?>
								<span class="text-xs text-slate-400 mt-1 block"><?php echo esc_html( $school_name ); ?></span>
							<?php endif; ?>
						</div>
					</div>
				</th>
				<?php endforeach; ?>
			</tr>
		</thead>

		<tbody>
			<?php
			$rows = [
				[
					'label' => 'Ngành học',
					'key' => 'major',
					'render' => function( $item ) {
						return $item['major'] ? esc_html( $item['major']['title'] . ' (' . $item['major']['code'] . ')' ) : '<span class="text-slate-300 italic">Chưa cập nhật</span>';
					}
				],
				[
					'label' => 'Hệ đào tạo',
					'key' => 'training_type',
					'render' => function( $item ) { return esc_html( $item['training_type'] ?: 'Chưa cập nhật' ); }
				],
				[
					'label' => 'Cơ sở học',
					'key' => 'campus_info',
					'render' => function( $item ) { return esc_html( $item['campus_info'] ?: 'Chưa cập nhật' ); }
				],
				[
					'label' => 'Học phí',
					'key' => 'tuition_fee',
					'render' => function( $item ) { return esc_html( $item['tuition_fee'] ?: 'Chưa cập nhật' ); }
				],
				[
					'label' => 'Thời gian đào tạo',
					'key' => 'duration',
					'render' => function( $item ) { return esc_html( $item['duration'] ?: 'Chưa cập nhật' ); }
				],
				[
					'label' => 'Bằng cấp',
					'key' => 'degree_type',
					'render' => function( $item ) { return esc_html( $item['degree_type'] ?: 'Cử nhân' ); }
				],
				[
					'label' => 'Lịch học',
					'key' => 'schedule',
					'render' => function( $item ) { return esc_html( $item['schedule'] ?: 'Chưa cập nhật' ); }
				],
				[
					'label' => 'Hình thức học',
					'key' => 'learning_mode',
					'render' => function( $item ) { return esc_html( $item['learning_mode'] ); }
				],
				[
					'label' => 'Hạn tuyển sinh',
					'key' => 'enrollment_period',
					'render' => function( $item ) { return esc_html( $item['enrollment_period'] ?: 'Đang nhận hồ sơ' ); }
				],
				[
					'label' => 'Điều kiện xét tuyển',
					'key' => 'admission_requirements',
					'render' => function( $item ) { return ltdh_compare_field( wp_strip_all_tags( $item['admission_requirements'] ) ); },
					'is_html' => false,
				],
				[
					'label' => 'Hồ sơ cần thiết',
					'key' => 'required_documents',
					'render' => function( $item ) { return ltdh_compare_field( wp_strip_all_tags( $item['required_documents'] ) ); },
				],
				[
					'label' => 'Đối tượng tuyển sinh',
					'key' => 'target_students',
					'render' => function( $item ) { return ltdh_compare_field( wp_strip_all_tags( $item['target_students'] ) ); },
				],
				[
					'label' => 'Giá trị bằng cấp',
					'key' => 'diploma_value',
					'render' => function( $item ) { return ltdh_compare_field( wp_strip_all_tags( $item['diploma_value'] ) ); },
				],
				[
					'label' => 'Cơ hội việc làm',
					'key' => 'career_opportunities',
					'render' => function( $item ) { return ltdh_compare_field( wp_strip_all_tags( $item['career_opportunities'] ) ); },
				],
				[
					'label' => 'Ưu điểm',
					'key' => 'advantages',
					'render' => function( $item ) { return ltdh_compare_field( wp_strip_all_tags( $item['advantages'] ) ); },
				],
				[
					'label' => 'Nhược điểm',
					'key' => 'disadvantages',
					'render' => function( $item ) { return ltdh_compare_field( wp_strip_all_tags( $item['disadvantages'] ) ); },
				],
				[
					'label' => 'Hotline',
					'key' => 'hotline',
					'render' => function( $item ) {
						$h = $item['hotline'];
						if ( ! $h ) return '<span class="text-slate-300 italic">Chưa cập nhật</span>';
						return '<a href="tel:' . esc_attr( preg_replace( '/\D/', '', $h ) ) . '" class="text-brand-primary font-bold hover:underline">' . esc_html( $h ) . '</a>';
					},
				],
			];

			// Section labels for grouping
			$sections = [
				'Thông tin cơ bản' => array_slice( $rows, 0, 3 ),
				'Học phí & Thời gian' => array_slice( $rows, 3, 3 ),
				'Lịch học & Hình thức' => array_slice( $rows, 6, 3 ),
				'Tuyển sinh' => array_slice( $rows, 9, 2 ),
				'Nghề nghiệp & Bằng cấp' => array_slice( $rows, 11, 4 ),
				'Liên hệ' => array_slice( $rows, 15, 1 ),
			];

			$row_index = 0;
			foreach ( $sections as $section_name => $section_rows ) :
			?>
				<tr>
					<td colspan="<?php echo esc_attr( $count + 1 ); ?>" class="bg-brand-primary/5 px-4 py-2 text-xs font-bold text-brand-primary uppercase tracking-wider border-y border-brand-primary/10">
						<?php echo esc_html( $section_name ); ?>
					</td>
				</tr>
				<?php foreach ( $section_rows as $row ) :
					$highlight_key = '';
					if ( $row['key'] === 'tuition_fee' ) $highlight_key = 'tuition_fee';
					elseif ( $row['key'] === 'duration' ) $highlight_key = 'duration';
					elseif ( $row['key'] === 'enrollment_period' ) $highlight_key = 'enrollment_period';
				?>
				<tr class="<?php echo $row_index % 2 === 0 ? 'bg-white' : 'bg-slate-50/50'; ?>">
					<td class="w-[180px] bg-slate-50/80 px-4 py-3 text-sm font-bold text-slate-700 align-top sticky left-0 z-10 border-r border-slate-100">
						<?php echo esc_html( $row['label'] ); ?>
					</td>
					<?php foreach ( $items as $item ) :
						$is_best = $highlight_key ? ltdh_compare_is_best( $highlights, $highlight_key, $item['id'] ) : false;
						$value = call_user_func( $row['render'], $item );
					?>
					<td class="<?php echo esc_attr( $col_width ); ?> px-4 py-3 text-sm text-slate-600 align-top border-l border-slate-50 <?php echo $is_best ? 'bg-emerald-50/60 border-l-2 border-l-emerald-400' : ''; ?>">
						<?php echo $value; // escaped by render callback ?>
						<?php if ( $is_best ) : ?>
							<?php echo ltdh_compare_badge( $highlights[ $highlight_key ]['label'] ); ?>
						<?php endif; ?>
					</td>
					<?php endforeach; ?>
				</tr>
				<?php
					$row_index++;
				endforeach;
				endforeach;
			?>
		</tbody>
	</table>
</div>
