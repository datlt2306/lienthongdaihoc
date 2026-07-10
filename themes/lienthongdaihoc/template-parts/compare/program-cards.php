<?php
/**
 * Program Comparison — Mobile Cards View
 *
 * @package lienthongdaihoc
 */
if ( ! defined( 'ABSPATH' ) ) exit;

// Item cards at top
?>
<!-- Item Headers -->
<div class="bg-white rounded shadow-sm border border-slate-100 p-4">
	<div class="grid grid-cols-<?php echo min( count( $items ), 3 ); ?> gap-3">
		<?php foreach ( $items as $item ) :
			$school_name = $item['school'] ? $item['school']['title'] : '';
		?>
		<div class="text-center">
			<img src="<?php echo esc_url( $item['thumbnail'] ); ?>"
				 alt="<?php echo esc_attr( $item['title'] ); ?>"
				 class="h-20 w-full object-cover rounded-lg border border-slate-200 mb-2">
			<a href="<?php echo esc_url( $item['permalink'] ); ?>" class="font-bold text-slate-900 text-xs leading-tight hover:text-brand-primary line-clamp-2 block">
				<?php echo esc_html( $item['title'] ); ?>
			</a>
			<?php if ( $school_name ) : ?>
				<span class="text-[10px] text-slate-400 mt-0.5 block"><?php echo esc_html( $school_name ); ?></span>
			<?php endif; ?>
		</div>
		<?php endforeach; ?>
	</div>
</div>

<?php
// Attribute sections
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

foreach ( $sections as $section_name => $attrs ) :
?>
<div class="bg-white rounded shadow-sm border border-slate-100 overflow-hidden">
	<div class="bg-brand-primary/5 px-4 py-2 border-b border-brand-primary/10">
		<h3 class="text-xs font-bold text-brand-primary uppercase tracking-wider"><?php echo esc_html( $section_name ); ?></h3>
	</div>
	<?php foreach ( $attrs as $attr ) :
		$highlight_key = $attr['highlight'] ?? '';
	?>
	<div class="px-4 py-3 border-b border-slate-100 last:border-0">
		<h4 class="text-xs font-bold text-slate-500 uppercase mb-2"><?php echo esc_html( $attr['label'] ); ?></h4>
		<div class="space-y-2">
			<?php foreach ( $items as $item ) :
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
			<div class="flex items-start gap-2 <?php echo $is_best ? 'bg-emerald-50 -mx-1 px-1 py-1 rounded-lg' : ''; ?>">
				<img src="<?php echo esc_url( $item['thumbnail'] ); ?>"
					 class="h-6 w-6 rounded object-cover shrink-0 mt-0.5"
					 alt="">
				<div class="text-sm text-slate-600 min-w-0 flex-1">
					<?php echo $value; ?>
					<?php if ( $is_best ) : ?>
						<?php echo ltdh_compare_badge( $highlights[ $highlight_key ]['label'] ); ?>
					<?php endif; ?>
				</div>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php endforeach; ?>
</div>
<?php endforeach; ?>
