<?php
/**
 * UI & Presentation Domain Helpers.
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LTDH_UI_Helpers {

	/**
	 * Get the global hotline number.
	 */
	public static function get_hotline(): string {
		$hotline = '';
		if ( function_exists( 'get_field' ) ) {
			$hotline = get_field( 'global_hotline', 'options' );
		}
		return $hotline ?: ( function_exists( 'ltdh_default' ) ? ltdh_default( 'contact', 'hotline' ) : '' );
	}

	/**
	 * Get the global Zalo URL.
	 */
	public static function get_zalo_url(): string {
		$url = '';
		if ( function_exists( 'get_field' ) ) {
			$url = get_field( 'global_zalo_url', 'options' );
		}
		return $url ?: ( function_exists( 'ltdh_default' ) ? ltdh_default( 'contact', 'zalo_url' ) : '' );
	}

	/**
	 * Get the global Messenger URL.
	 */
	public static function get_messenger_url(): string {
		$url = '';
		if ( function_exists( 'get_field' ) ) {
			$url = get_field( 'global_messenger_url', 'options' );
		}
		return $url ?: ( function_exists( 'ltdh_default' ) ? ltdh_default( 'contact', 'messenger_url' ) : '' );
	}

	/**
	 * Get the global contact email.
	 */
	public static function get_email(): string {
		$email = '';
		if ( function_exists( 'get_field' ) ) {
			$email = (string) get_field( 'global_email', 'options' );
		}
		return trim( $email ) ?: ( function_exists( 'ltdh_default' ) ? ltdh_default( 'contact', 'email' ) : '' );
	}

	/**
	 * Get the global address.
	 */
	public static function get_address(): string {
		$address = '';
		if ( function_exists( 'get_field' ) ) {
			$address = (string) get_field( 'global_address', 'options' );
		}
		return trim( $address ) ?: ( function_exists( 'ltdh_default' ) ? ltdh_default( 'contact', 'address' ) : '' );
	}

	/**
	 * Get the company name.
	 */
	public static function get_company_name(): string {
		$name = '';
		if ( function_exists( 'get_field' ) ) {
			$name = (string) get_field( 'global_company_name', 'options' );
		}
		return trim( $name ) ?: ( function_exists( 'ltdh_default' ) ? ltdh_default( 'contact', 'company_name' ) : '' );
	}

	/**
	 * Format and sanitize contact information HTML for display.
	 *
	 * @param string $contact_html Raw contact HTML.
	 * @param int    $school_id    Optional school ID context.
	 * @return string Formatted HTML.
	 */
	public static function format_contact_info( string $contact_html, int $school_id = 0 ): string {
		$new_address_html = '<p class="text-slate-800 leading-relaxed font-normal"><strong>✅ Địa chỉ nộp hồ sơ (trực tiếp hoặc chuyển phát nhanh):</strong> 📍 Tầng 2 – Khu Hiệu bộ, 306B Kim Mã, phường Giảng Võ, Hà Nội (Trong khuôn viên Trường Lê Duẩn, đối diện Trường ĐH GTVT).</p>';

		if ( empty( trim( strip_tags( $contact_html ) ) ) ) {
			return $new_address_html;
		}

		$pattern = '/<h[1-6][^>]*>[\s\S]*?(?:địa\s*điểm\s*khai\s*giảng|khai\s*giảng\s*&(?:amp;)?\s*thi)[\s\S]*?<\/h[1-6]>\s*(?:<p[^>]*>[\s\S]*?<\/p>|<ul[^>]*>[\s\S]*?<\/ul>)?/ui';

		if ( preg_match( $pattern, $contact_html ) ) {
			$contact_html = preg_replace( $pattern, $new_address_html, $contact_html, 1 );
		}

		$heading_only_pattern = '/<h[1-6][^>]*>[\s\S]*?(?:địa\s*điểm\s*khai\s*giảng|khai\s*giảng\s*&(?:amp;)?\s*thi)[\s\S]*?<\/h[1-6]>/ui';
		$contact_html         = preg_replace( $heading_only_pattern, '', $contact_html );

		$legacy_address_pattern = '/<p[^>]*>[\s\S]*?Phòng\s+Khảo\s+thí[\s\S]*?Cầu\s+Giấy[\s\S]*?<\/p>/ui';
		if ( preg_match( $legacy_address_pattern, $contact_html ) ) {
			$contact_html = preg_replace( $legacy_address_pattern, $new_address_html, $contact_html, 1 );
		}

		return trim( (string) $contact_html );
	}

	/**
	 * Get the CF7 shortcode for a given context.
	 *
	 * @param string $context
	 * @return string
	 */
	public static function get_form_shortcode( string $context = 'consultation' ): string {
		$option_map = [
			'consultation' => 'cf7_consultation_form_id',
			'contact'      => 'cf7_contact_form_id',
			'program'      => 'cf7_program_form_id',
			'evaluation'   => 'cf7_evaluation_form_id',
			'eligibility'  => 'cf7_evaluation_form_id',
		];

		$form_id    = '';
		$field_name = $option_map[ $context ] ?? '';

		// 1. Check ACF Options Page
		if ( $field_name && function_exists( 'get_field' ) ) {
			$form_id = get_field( $field_name, 'options' );
		}
		if ( empty( $form_id ) && $field_name ) {
			$form_id = get_option( 'options_' . $field_name ) ?: get_option( $field_name );
		}

		// 2. Check defaults configuration
		if ( empty( $form_id ) && function_exists( 'ltdh_default' ) ) {
			$form_id = ltdh_default( 'forms', $context . '_form_id' );
		}

		// 3. Auto-detect published CF7 form by slug/title if still empty
		if ( empty( $form_id ) && post_type_exists( 'wpcf7_contact_form' ) ) {
			$slugs = [
				'contact'      => [ 'form-lien-he', 'lien-he', 'contact-form-1' ],
				'consultation' => [ 'form-tu-van', 'tu-van', 'consultation-form' ],
				'program'      => [ 'form-tu-van', 'form-chuong-trinh', 'consultation-form' ],
				'evaluation'   => [ 'form-tham-dinh-ho-so', 'form-tham-dinh', 'tham-dinh', 'evaluation-form' ],
				'eligibility'  => [ 'form-tham-dinh-ho-so', 'form-tham-dinh', 'tham-dinh', 'evaluation-form' ],
			];
			$candidates = $slugs[ $context ] ?? [ 'form-tu-van', 'form-lien-he' ];
			foreach ( $candidates as $candidate_slug ) {
				$found = get_page_by_path( $candidate_slug, OBJECT, 'wpcf7_contact_form' );
				if ( $found && 'publish' === $found->post_status ) {
					$form_id = $found->ID;
					break;
				}
			}
		}

		// 4. Fallback to cf7_default_form_id if not evaluation/contact
		if ( empty( $form_id ) && ! in_array( $context, [ 'evaluation', 'eligibility' ], true ) ) {
			if ( function_exists( 'get_field' ) ) {
				$form_id = get_field( 'cf7_default_form_id', 'options' );
			}
			if ( empty( $form_id ) ) {
				$form_id = get_option( 'options_cf7_default_form_id' ) ?: get_option( 'cf7_default_form_id' );
			}
		}

		if ( ! empty( $form_id ) ) {
			$form_post = get_post( $form_id );
			if ( $form_post && 'wpcf7_contact_form' === $form_post->post_type && 'publish' === $form_post->post_status ) {
				return do_shortcode( '[contact-form-7 id="' . esc_attr( $form_id ) . '"]' );
			}
		}

		return '';
	}

	/**
	 * Render an evaluation / eligibility form with automatic fallback.
	 *
	 * @param array $context_hidden_fields Optional hidden fields to inject.
	 */
	public static function render_evaluation_form( array $context_hidden_fields = [] ): void {
		$GLOBALS['ltdh_cf7_context_hidden_fields'] = $context_hidden_fields;

		$shortcode = self::get_form_shortcode( 'evaluation' );
		if ( ! empty( $shortcode ) && strpos( $shortcode, 'wpcf7-contact-form-not-found' ) === false ) {
			echo $shortcode; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			$GLOBALS['ltdh_cf7_context_hidden_fields'] = [];
			return;
		}
		$GLOBALS['ltdh_cf7_context_hidden_fields'] = [];
		self::render_native_form( 'evaluation', $context_hidden_fields );
	}

	/**
	 * Render a consultation form with automatic fallback to native HTML form.
	 *
	 * @param array $context_hidden_fields Optional hidden fields to inject.
	 */
	public static function render_consultation_form( array $context_hidden_fields = [] ): void {
		$GLOBALS['ltdh_cf7_context_hidden_fields'] = $context_hidden_fields;

		$shortcode = self::get_form_shortcode( 'consultation' );
		if ( ! empty( $shortcode ) ) {
			echo $shortcode; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			$GLOBALS['ltdh_cf7_context_hidden_fields'] = [];
			return;
		}
		$GLOBALS['ltdh_cf7_context_hidden_fields'] = [];
		self::render_native_form( 'consultation', $context_hidden_fields );
	}

	/**
	 * Render a contact form with automatic fallback.
	 *
	 * @param array $context_hidden_fields Optional hidden fields to inject.
	 */
	public static function render_contact_form( array $context_hidden_fields = [] ): void {
		$GLOBALS['ltdh_cf7_context_hidden_fields'] = $context_hidden_fields;

		$shortcode = self::get_form_shortcode( 'contact' );
		if ( ! empty( $shortcode ) ) {
			echo $shortcode; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			$GLOBALS['ltdh_cf7_context_hidden_fields'] = [];
			return;
		}
		$GLOBALS['ltdh_cf7_context_hidden_fields'] = [];
		self::render_native_form( 'contact', $context_hidden_fields );
	}

	/**
	 * Render a native HTML form fallback.
	 *
	 * @param string $type 'consultation', 'contact', or 'evaluation'.
	 * @param array  $hidden_fields
	 */
	public static function render_native_form( string $type = 'consultation', array $hidden_fields = [] ): void {
		static $form_instance = 0;
		$form_instance++;
		$prefix = "ltdh-lead-{$type}-{$form_instance}";
		?>
		<form action="" method="POST" class="space-y-3 lg:space-y-4">
			<?php wp_nonce_field( 'ltdh_native_lead_submit_action', 'ltdh_native_lead_nonce' ); ?>
			<?php if ( isset( $_GET['submit_success'] ) && '1' === $_GET['submit_success'] ) : ?>
				<div class="p-3 lg:p-4 mb-3 lg:mb-4 text-xs lg:text-sm text-green-800 rounded-xl lg:rounded-lg bg-green-50 border border-green-200" role="alert">
					<span class="font-bold">Gửi thông tin thành công!</span> Đội ngũ tư vấn tuyển sinh sẽ liên hệ với bạn trong thời gian sớm nhất.
				</div>
			<?php endif; ?>

			<?php foreach ( $hidden_fields as $name => $value ) : ?>
				<input type="hidden" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>">
			<?php endforeach; ?>
			<?php if ( function_exists( 'ltdh_render_referral_hidden_input' ) ) { ltdh_render_referral_hidden_input(); } ?>

			<div class="hidden" style="display:none !important;" aria-hidden="true">
				<label for="<?php echo esc_attr( $prefix . '-hp' ); ?>">Website URL</label>
				<input type="text" name="hp_website" id="<?php echo esc_attr( $prefix . '-hp' ); ?>" tabindex="-1" autocomplete="off" value="">
			</div>

			<div>
				<label for="<?php echo esc_attr( $prefix . '-name' ); ?>" class="block text-xs lg:text-sm font-semibold text-slate-700 lg:text-slate-600 mb-1"><?php esc_html_e( 'Họ và tên *', 'ltdh' ); ?></label>
				<input type="text" id="<?php echo esc_attr( $prefix . '-name' ); ?>" name="your-name" required autocomplete="name" aria-required="true" class="w-full border border-slate-200 rounded-xl lg:rounded-lg px-3.5 py-2.5 lg:py-3 text-sm focus:border-brand-primary focus:outline-none transition-all" placeholder="<?php esc_attr_e( 'Họ và tên của bạn', 'ltdh' ); ?>">
			</div>
			<div>
				<label for="<?php echo esc_attr( $prefix . '-phone' ); ?>" class="block text-xs lg:text-sm font-semibold text-slate-700 lg:text-slate-600 mb-1"><?php esc_html_e( 'Số điện thoại *', 'ltdh' ); ?></label>
				<input type="tel" id="<?php echo esc_attr( $prefix . '-phone' ); ?>" name="your-phone" required autocomplete="tel" aria-required="true" pattern="^(0|\+84)(3|5|7|8|9)[0-9]{8}$" title="Vui lòng nhập số điện thoại Việt Nam hợp lệ (10 chữ số)" class="w-full border border-slate-200 rounded-xl lg:rounded-lg px-3.5 py-2.5 lg:py-3 text-sm focus:border-brand-primary focus:outline-none transition-all" placeholder="<?php esc_attr_e( 'Số điện thoại liên hệ (10 số)' ); ?>">
			</div>

			<?php if ( 'consultation' === $type || 'contact' === $type ) : ?>
				<div>
					<div class="flex items-center justify-between mb-1">
						<label for="<?php echo esc_attr( $prefix . '-email' ); ?>" class="block text-xs lg:text-sm font-semibold text-slate-700 lg:text-slate-600"><?php esc_html_e( 'Địa chỉ Email', 'ltdh' ); ?></label>
						<span class="text-[10px] text-slate-400"><?php esc_html_e( 'Nhận lộ trình & xác nhận', 'ltdh' ); ?></span>
					</div>
					<input type="email" id="<?php echo esc_attr( $prefix . '-email' ); ?>" name="your-email" autocomplete="email" class="w-full border border-slate-200 rounded-xl lg:rounded-lg px-3.5 py-2.5 lg:py-3 text-sm focus:border-brand-primary focus:outline-none transition-all" placeholder="Ví dụ: hoten@gmail.com">
				</div>
			<?php endif; ?>

			<?php if ( 'evaluation' === $type ) : ?>
				<div class="grid grid-cols-1 md:grid-cols-3 gap-3">
					<div>
						<label for="<?php echo esc_attr( $prefix . '-email' ); ?>" class="block text-xs lg:text-sm font-semibold text-slate-700 lg:text-slate-600 mb-1"><?php esc_html_e( 'Email nhận kết quả', 'ltdh' ); ?></label>
						<input type="email" id="<?php echo esc_attr( $prefix . '-email' ); ?>" name="your-email" autocomplete="email" class="w-full border border-slate-200 rounded-xl lg:rounded-lg px-3.5 py-2.5 lg:py-3 text-sm focus:border-brand-primary focus:outline-none transition-all" placeholder="Ví dụ: name@example.com">
					</div>
					<div>
						<label for="<?php echo esc_attr( $prefix . '-school' ); ?>" class="block text-xs lg:text-sm font-semibold text-slate-700 lg:text-slate-600 mb-1"><?php esc_html_e( 'Trường đã tốt nghiệp', 'ltdh' ); ?></label>
						<input type="text" id="<?php echo esc_attr( $prefix . '-school' ); ?>" name="previous_school" class="w-full border border-slate-200 rounded-xl lg:rounded-lg px-3.5 py-2.5 lg:py-3 text-sm focus:border-brand-primary focus:outline-none transition-all" placeholder="Ví dụ: CĐ Kinh Tế...">
					</div>
					<div>
						<label for="<?php echo esc_attr( $prefix . '-grad' ); ?>" class="block text-xs lg:text-sm font-semibold text-slate-700 lg:text-slate-600 mb-1"><?php esc_html_e( 'Năm tốt nghiệp', 'ltdh' ); ?></label>
						<select id="<?php echo esc_attr( $prefix . '-grad' ); ?>" name="graduation" class="w-full border border-slate-200 rounded-xl lg:rounded-lg px-3.5 py-2.5 lg:py-3 text-sm focus:border-brand-primary focus:outline-none transition-all bg-white">
							<option value="">-- Chọn năm --</option>
							<?php
							$current_y = (int) date( 'Y' );
							for ( $y = $current_y; $y >= $current_y - 25; $y-- ) :
							?>
								<option value="<?php echo esc_attr( $y ); ?>"><?php echo esc_html( $y ); ?></option>
							<?php endfor; ?>
						</select>
					</div>
				</div>
			<?php endif; ?>

			<div>
				<div class="flex items-center justify-between mb-1">
					<label for="<?php echo esc_attr( $prefix . '-message' ); ?>" class="block text-xs lg:text-sm font-semibold text-slate-700 lg:text-slate-600"><?php esc_html_e( 'Nội dung cần tư vấn', 'ltdh' ); ?></label>
					<span class="text-[10px] text-slate-400 lg:hidden">Không bắt buộc</span>
				</div>
				<textarea id="<?php echo esc_attr( $prefix . '-message' ); ?>" name="your-message" rows="2" class="w-full border border-slate-200 rounded-xl lg:rounded-lg px-3.5 py-2 lg:py-3 text-sm focus:border-brand-primary focus:outline-none transition-all lg:h-24 resize-none" placeholder="<?php esc_attr_e( 'Nhập câu hỏi hoặc yêu cầu cụ thể...', 'ltdh' ); ?>"></textarea>
			</div>

			<?php if ( 'contact' !== $type ) : ?>
				<div class="pt-0.5">
					<label for="<?php echo esc_attr( $prefix . '-group' ); ?>" class="flex items-start gap-2.5 p-2.5 sm:p-3 rounded-xl bg-amber-50/80 border border-amber-200/90 cursor-pointer hover:bg-amber-100/60 transition-colors select-none">
						<input type="checkbox" id="<?php echo esc_attr( $prefix . '-group' ); ?>" name="is_group_reg" value="1" class="mt-0.5 w-4 h-4 rounded border-amber-300 text-brand-primary focus:ring-brand-primary">
						<span class="text-xs sm:text-sm text-slate-700 leading-snug">
							🎁 <strong class="font-bold text-amber-900">Đăng ký theo nhóm:</strong> Tư vấn cho tôi suất học bổng đồng hành (khi cùng bạn bè tìm hiểu từ 2 người trở lên)
						</span>
					</label>
				</div>
			<?php endif; ?>

			<?php if ( function_exists( 'ltdh_render_recaptcha_field' ) ) : ?>
				<?php ltdh_render_recaptcha_field( 'native_lead' ); ?>
			<?php endif; ?>

			<button type="submit" class="w-full bg-[#0071E3] hover:bg-[#0077ED] text-white py-3 lg:py-3.5 rounded-full text-sm font-semibold tracking-tight shadow-none hover:shadow-xs active:scale-[0.98] transition-all mt-1 lg:mt-2 min-h-[44px] flex items-center justify-center gap-2 cursor-pointer">
				<span><?php echo 'contact' === $type ? esc_html__( 'Gửi Liên Hệ' ) : ( 'evaluation' === $type ? esc_html__( 'GỬI YÊU CẦU ĐỐI CHIẾU HỒ SƠ & TƯ VẤN' ) : esc_html__( 'Gửi Thông Tin Ngay' ) ); ?></span>
			</button>

			<div class="flex flex-col sm:flex-row items-center justify-center gap-1.5 sm:gap-3 text-[11px] text-slate-400 pt-1 text-center">
				<span class="inline-flex items-center gap-1 font-medium">
					<svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd"/></svg>
					Bảo mật theo Nghị định 13/2023/NĐ-CP
				</span>
				<span class="hidden sm:inline text-slate-300">•</span>
				<span class="inline-flex items-center gap-1 font-medium">
					<svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
					Phản hồi trong 15 phút
				</span>
			</div>
		</form>
		<?php
	}

	/**
	 * Automatically resolve optimized image URL (e.g. serving optimized WebP replacements from assets/images/).
	 *
	 * @param string      $url     Original image URL.
	 * @param string|bool $variant Variant name ('mobile', '1400w') or bool for mobile.
	 * @return string
	 */
	public static function get_optimized_image_url( string $url, $variant = false ): string {
		if ( empty( $url ) ) {
			return '';
		}

		// Preserve offloaded ImageKit / Cloudflare R2 / S3 / external CDN URLs
		if ( strpos( $url, 'ik.imagekit.io' ) !== false || strpos( $url, 'r2.dev' ) !== false || strpos( $url, 'cloudflarestorage.com' ) !== false || ( function_exists( 'advmo_get_provider_credential' ) && ( $cdn_domain = advmo_get_provider_credential( 'cloudflare_r2', 'domain' ) ) && strpos( $url, parse_url( $cdn_domain, PHP_URL_HOST ) ?: $cdn_domain ) !== false ) ) {
			return $url;
		}

		$parsed_path = parse_url( $url, PHP_URL_PATH );
		if ( empty( $parsed_path ) ) {
			return $url;
		}

		$filename         = basename( $parsed_path );
		$name_without_ext = pathinfo( $filename, PATHINFO_FILENAME );
		$ext              = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );

		$is_mobile = ( true === $variant || 'mobile' === $variant );
		$is_1400w  = ( '1400w' === $variant );

		$theme_dir = get_template_directory();
		$theme_uri = 'https://ik.imagekit.io/cdnplus/assets/images';

		// 1. Check for specific variant in theme assets/images/
		if ( $is_1400w && file_exists( $theme_dir . '/assets/images/' . $name_without_ext . '-1400w.webp' ) ) {
			return $theme_uri . '/' . $name_without_ext . '-1400w.webp';
		}

		if ( $is_mobile && file_exists( $theme_dir . '/assets/images/' . $name_without_ext . '-mobile.webp' ) ) {
			return $theme_uri . '/' . $name_without_ext . '-mobile.webp';
		}

		// 2. Check for standard .webp in theme assets/images/
		if ( file_exists( $theme_dir . '/assets/images/' . $name_without_ext . '.webp' ) ) {
			return $theme_uri . '/' . $name_without_ext . '.webp';
		}

		// 3. If image is in wp-content/uploads/, check for sibling .webp
		if ( in_array( $ext, [ 'jpg', 'jpeg', 'png' ], true ) && strpos( $parsed_path, '/wp-content/uploads/' ) !== false ) {
			$upload_dir = wp_upload_dir();
			$rel_path   = ltrim( strstr( $parsed_path, '/wp-content/uploads/' ), '/' );
			$rel_path   = preg_replace( '#^wp-content/uploads/#', '', $rel_path );
			$dir_part   = dirname( $rel_path );
			$webp_rel   = ( '.' === $dir_part ? '' : $dir_part . '/' ) . $name_without_ext . '.webp';
			$webp_file  = $upload_dir['basedir'] . '/' . $webp_rel;

			if ( file_exists( $webp_file ) ) {
				return $upload_dir['baseurl'] . '/' . $webp_rel;
			}
		}

		return $url;
	}

	/**
	 * Get site logo data (URL, width, height) with Customizer -> ACF fallback.
	 *
	 * @return array
	 */
	public static function get_logo_data(): array {
		$data    = [ 'url' => '', 'width' => 0, 'height' => 0 ];
		$logo_id = get_theme_mod( 'custom_logo' );
		if ( $logo_id ) {
			$img_data = wp_get_attachment_image_src( $logo_id, 'full' );
			if ( $img_data && ! empty( $img_data[0] ) ) {
				$data = [
					'url'    => $img_data[0],
					'width'  => (int) $img_data[1],
					'height' => (int) $img_data[2],
				];
			}
		}

		if ( empty( $data['url'] ) && function_exists( 'get_field' ) ) {
			$acf_logo = get_field( 'global_logo', 'options' );
			if ( $acf_logo ) {
				if ( is_numeric( $acf_logo ) ) {
					$img_data = wp_get_attachment_image_src( (int) $acf_logo, 'full' );
					if ( $img_data && ! empty( $img_data[0] ) ) {
						$data = [
							'url'    => $img_data[0],
							'width'  => (int) $img_data[1],
							'height' => (int) $img_data[2],
						];
					}
				} elseif ( is_array( $acf_logo ) && isset( $acf_logo['url'] ) ) {
					$data = [
						'url'    => $acf_logo['url'],
						'width'  => (int) ( $acf_logo['width'] ?? 200 ),
						'height' => (int) ( $acf_logo['height'] ?? 60 ),
					];
				} elseif ( is_string( $acf_logo ) ) {
					$data = [
						'url'    => $acf_logo,
						'width'  => 200,
						'height' => 60,
					];
				}
			}
		}

		if ( ! empty( $data['url'] ) ) {
			$optimized = self::get_optimized_image_url( $data['url'] );
			if ( ! empty( $optimized ) && $optimized !== $data['url'] ) {
				$data['url'] = $optimized;
			}
		}

		return $data;
	}

	/**
	 * Get site logo URL with Customizer -> ACF fallback.
	 */
	public static function get_logo_url(): string {
		$data = self::get_logo_data();
		return $data['url'] ?? '';
	}

	/**
	 * Output site logo HTML with explicit dimensions & aspect-ratio (Zero CLS).
	 */
	public static function site_logo( int $max_height = 48 ): void {
		$logo_data = self::get_logo_data();
		if ( ! empty( $logo_data['url'] ) ) :
			$site_name = get_bloginfo( 'name' );
			$orig_w    = $logo_data['width'] ?: 200;
			$orig_h    = $logo_data['height'] ?: 60;
			$calc_w    = $orig_h > 0 ? round( ( $orig_w / $orig_h ) * $max_height ) : $orig_w;
			$logo_url  = $logo_data['url'];
			if ( strpos( $logo_url, 'ik.imagekit.io' ) !== false || strpos( $logo_url, 'r2.dev' ) !== false ) {
				$clean_logo = preg_replace( '#\?tr=.*$#', '', $logo_url );
				$logo_url   = $clean_logo . '?tr=w-' . round( $calc_w * 2 ) . ',h-' . round( $max_height * 2 );
			}
		?>
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="flex items-center" aria-label="<?php echo esc_attr( $site_name ); ?>">
				<img src="<?php echo esc_url( $logo_url ); ?>"
					alt="<?php echo esc_attr( $site_name ); ?>"
					width="<?php echo esc_attr( $calc_w ); ?>"
					height="<?php echo esc_attr( $max_height ); ?>"
					class="site-logo custom-logo skip-lazy no-lazy h-auto object-contain"
					loading="eager"
					fetchpriority="high"
					decoding="async"
					data-no-lazy="1"
					style="max-height: <?php echo esc_attr( $max_height ); ?>px; width: auto; aspect-ratio: <?php echo esc_attr( $calc_w ); ?> / <?php echo esc_attr( $max_height ); ?>;">
			</a>
		<?php else : ?>
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="flex items-center gap-2 font-display font-black text-2xl text-brand-primary" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
				<div class="flex flex-col leading-none">
					<span class="text-sm font-semibold text-slate-400 tracking-wider">LIÊN THÔNG</span>
					<span class="text-xl font-extrabold text-brand-primary">ĐẠI HỌC</span>
				</div>
			</a>
		<?php endif;
	}

	/**
	 * Output site logo for mobile with explicit dimensions & aspect-ratio (Zero CLS).
	 */
	public static function site_logo_mobile( int $max_height = 36 ): void {
		$logo_data = self::get_logo_data();
		if ( ! empty( $logo_data['url'] ) ) :
			$site_name = get_bloginfo( 'name' );
			$orig_w    = $logo_data['width'] ?: 200;
			$orig_h    = $logo_data['height'] ?: 60;
			$calc_w    = $orig_h > 0 ? round( ( $orig_w / $orig_h ) * $max_height ) : $orig_w;
			$logo_url  = $logo_data['url'];
			if ( strpos( $logo_url, 'ik.imagekit.io' ) !== false || strpos( $logo_url, 'r2.dev' ) !== false ) {
				$clean_logo = preg_replace( '#\?tr=.*$#', '', $logo_url );
				$logo_url   = $clean_logo . '?tr=w-' . round( $calc_w * 2 ) . ',h-' . round( $max_height * 2 );
			}
		?>
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="flex items-center" aria-label="<?php echo esc_attr( $site_name ); ?>">
				<img src="<?php echo esc_url( $logo_url ); ?>"
					alt="<?php echo esc_attr( $site_name ); ?>"
					width="<?php echo esc_attr( $calc_w ); ?>"
					height="<?php echo esc_attr( $max_height ); ?>"
					class="site-logo custom-logo skip-lazy no-lazy h-auto object-contain"
					loading="eager"
					decoding="async"
					data-no-lazy="1"
					style="max-height: <?php echo esc_attr( $max_height ); ?>px; width: auto; aspect-ratio: <?php echo esc_attr( $calc_w ); ?> / <?php echo esc_attr( $max_height ); ?>;">
			</a>
		<?php else : ?>
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="flex items-center gap-2 font-display font-black text-2xl text-brand-primary" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
				<div class="flex flex-col leading-none">
					<span class="text-xs font-semibold text-slate-400 tracking-wider">LIÊN THÔNG</span>
					<span class="text-lg font-extrabold text-brand-primary">ĐẠI HỌC</span>
				</div>
			</a>
		<?php endif;
	}

	/**
	 * Breadcrumbs trail renderer.
	 */
	public static function breadcrumb(): void {
		// 1. Primary: Rank Math breadcrumb engine
		if ( function_exists( 'rank_math_the_breadcrumbs' ) ) {
			if ( class_exists( '\RankMath\Helper' ) && ! \RankMath\Helper::is_breadcrumbs_enabled() ) {
				return;
			}

			ob_start();
			rank_math_the_breadcrumbs();
			$html = trim( (string) ob_get_clean() );

			if ( ! empty( $html ) ) {
				echo '<div class="ltdh-breadcrumb bg-slate-50/60 border-b border-slate-100">';
				echo '<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2.5 text-xs sm:text-sm text-slate-500 overflow-x-auto whitespace-nowrap scrollbar-none flex items-center gap-1.5">';
				echo $html;
				echo '</div>';
				echo '</div>';
				return;
			}
		}

		// 2. Fallback: Minimal native breadcrumbs
		if ( is_front_page() ) {
			return;
		}

		$crumbs = [ [ 'label' => 'Trang chủ', 'url' => home_url( '/' ) ] ];

		if ( is_singular( 'program' ) ) {
			$program_id   = (int) get_the_ID();
			$program_name = function_exists( 'ltdh_get_program_banner_title' ) ? ltdh_get_program_banner_title( $program_id ) : get_the_title( $program_id );
			$major_id     = function_exists( 'ltdh_get_program_major_id' ) ? ltdh_get_program_major_id( $program_id ) : 0;
			if ( $major_id > 0 ) {
				$terms = get_the_terms( $major_id, 'major_cat' );
				if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
					$cat_link = get_term_link( $terms[0] );
					if ( ! is_wp_error( $cat_link ) ) {
						$crumbs[] = [ 'label' => $terms[0]->name, 'url' => $cat_link ];
					}
				}
				$crumbs[] = [ 'label' => get_the_title( $major_id ), 'url' => get_permalink( $major_id ) ];
			} else {
				$crumbs[] = [ 'label' => 'Liên thông Đại học', 'url' => home_url( '/hinh-thuc-dao-tao/' ) ];
			}
			$crumbs[] = [ 'label' => $program_name, 'url' => '' ];
		} elseif ( is_singular( 'school' ) ) {
			$crumbs[] = [ 'label' => 'Trường đối tác', 'url' => home_url( '/truong-doi-tac/' ) ];
			$crumbs[] = [ 'label' => get_the_title(), 'url' => '' ];
		} elseif ( is_singular( 'major' ) ) {
			$terms = get_the_terms( get_the_ID(), 'major_cat' );
			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
				$crumbs[] = [ 'label' => $terms[0]->name, 'url' => get_term_link( $terms[0] ) ];
			}
			$crumbs[] = [ 'label' => get_the_title(), 'url' => '' ];
		} elseif ( is_singular( 'post' ) || is_home() || is_category() ) {
			$crumbs[] = [ 'label' => 'Tin tức', 'url' => home_url( '/tin-tuc/' ) ];
			if ( is_category() ) {
				$cat = get_queried_object();
				if ( $cat ) {
					$crumbs[] = [ 'label' => $cat->name, 'url' => '' ];
				}
			} elseif ( is_singular( 'post' ) ) {
				$crumbs[] = [ 'label' => get_the_title(), 'url' => '' ];
			}
		} elseif ( is_tax( 'training_type' ) ) {
			$crumbs[] = [ 'label' => 'Liên thông Đại học', 'url' => home_url( '/hinh-thuc-dao-tao/' ) ];
			$term     = get_queried_object();
			if ( $term ) {
				$crumbs[] = [ 'label' => $term->name, 'url' => '' ];
			}
		} else {
			$crumbs[] = [ 'label' => wp_title( '', false ) ?: 'Nội dung', 'url' => '' ];
		}

		echo '<div class="ltdh-breadcrumb bg-slate-50/60 border-b border-slate-100">';
		echo '<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2.5 text-xs sm:text-sm text-slate-500 overflow-x-auto whitespace-nowrap scrollbar-none flex items-center gap-1.5">';
		echo '<nav aria-label="breadcrumbs" class="rank-math-breadcrumb"><p>';
		$count = count( $crumbs );
		foreach ( $crumbs as $idx => $c ) {
			if ( ! empty( $c['url'] ) && $idx < $count - 1 ) {
				echo '<a href="' . esc_url( $c['url'] ) . '">' . esc_html( $c['label'] ) . '</a>';
				echo '<span class="separator"> - </span>';
			} else {
				echo '<span class="last">' . esc_html( $c['label'] ) . '</span>';
			}
		}
		echo '</p></nav>';
		echo '</div>';
		echo '</div>';
	}

	/**
	 * Transient query caching.
	 */
	public static function get_cached_query( string $transient_key, array $query_args, int $expiration = HOUR_IN_SECONDS ) {
		$cached_results = get_transient( $transient_key );
		if ( false !== $cached_results ) {
			return $cached_results;
		}
		$query = new WP_Query( $query_args );
		set_transient( $transient_key, $query, $expiration );
		return $query;
	}

	/**
	 * Cached filter dropdown options for Homepage & Search Bar.
	 */
	public static function get_cached_filter_options(): array {
		$cache_key = 'ltdh_filter_options';
		$cached    = get_transient( $cache_key );
		if ( false !== $cached && is_array( $cached ) ) {
			return $cached;
		}

		$schools = get_posts( [
			'post_type'      => 'school',
			'posts_per_page' => 100,
			'post_status'    => 'publish',
			'orderby'        => 'title',
			'order'          => 'ASC',
			'fields'         => 'ids',
			'no_found_rows'  => true,
		] );

		$school_list = [];
		foreach ( $schools as $sid ) {
			$school_list[] = [
				'slug'  => get_post_field( 'post_name', $sid ),
				'title' => get_the_title( $sid ),
			];
		}

		$majors = get_posts( [
			'post_type'      => 'major',
			'posts_per_page' => 100,
			'post_status'    => 'publish',
			'orderby'        => 'title',
			'order'          => 'ASC',
			'fields'         => 'ids',
			'no_found_rows'  => true,
		] );

		$major_list = [];
		foreach ( $majors as $mid ) {
			$major_list[] = [
				'slug'  => get_post_field( 'post_name', $mid ),
				'title' => get_the_title( $mid ),
			];
		}

		$types_terms = get_terms( [
			'taxonomy'   => defined( 'LTDH_TAX_TRAINING_TYPE' ) ? LTDH_TAX_TRAINING_TYPE : 'training_type',
			'hide_empty' => false,
		] );

		$type_list = [];
		if ( ! is_wp_error( $types_terms ) && ! empty( $types_terms ) ) {
			foreach ( $types_terms as $tt ) {
				$type_list[] = [
					'slug' => $tt->slug,
					'name' => $tt->name,
				];
			}
		}

		$data = [
			'schools' => $school_list,
			'majors'  => $major_list,
			'types'   => $type_list,
		];

		set_transient( $cache_key, $data, 12 * HOUR_IN_SECONDS );
		return $data;
	}

	/**
	 * Flush all content transients across the theme.
	 */
	public static function flush_all_content_transients(): void {
		$featured_schools_key = defined( 'LTDH_TRANSIENT_FEATURED_SCHOOLS' ) ? LTDH_TRANSIENT_FEATURED_SCHOOLS : 'ltdh_featured_schools_data';
		delete_transient( $featured_schools_key );
		delete_transient( 'ltdh_featured_schools_data_v18' );
		delete_transient( 'ltdh_featured_schools_data_v11' );
		delete_transient( 'ltdh_featured_schools_data_v10' );
		delete_transient( 'ltdh_featured_schools_data' );
		delete_transient( 'ltdh_archive_school_featured_v2' );
		delete_transient( 'ltdh_archive_school_featured' );
		delete_transient( 'ltdh_mega_menu_majors_data' );
		delete_transient( 'ltdh_hot_majors_data' );
		delete_transient( 'ltdh_combinations_data' );
		delete_transient( 'ltdh_filter_options' );
		delete_transient( 'ltdh_training_type_counts' );
		delete_transient( 'ltdh_homepage_news' );

		global $wpdb;
		if ( $wpdb && ! empty( $wpdb->options ) ) {
			$wpdb->query(
				"DELETE FROM {$wpdb->options} 
				 WHERE option_name LIKE '_transient_ltdh_featured_%' 
				    OR option_name LIKE '_transient_timeout_ltdh_featured_%'
				    OR option_name LIKE '_transient_ltdh_archive_school_%'
				    OR option_name LIKE '_transient_timeout_ltdh_archive_school_%'
				    OR option_name LIKE '_transient_ltdh_major_s_%'
				    OR option_name LIKE '_transient_timeout_ltdh_major_s_%'
				    OR option_name LIKE '_transient_ltdh_search_%'
				    OR option_name LIKE '_transient_timeout_ltdh_search_%'"
			);
		}
	}

	/**
	 * Clear transients on saving relevant CPT posts.
	 */
	public static function clear_transients_on_save( $post_id ): void {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		$post_type    = get_post_type( $post_id );
		$cpt_prog     = defined( 'LTDH_CPT_PROGRAM' ) ? LTDH_CPT_PROGRAM : 'program';
		$cpt_school   = defined( 'LTDH_CPT_SCHOOL' ) ? LTDH_CPT_SCHOOL : 'school';
		$cpt_major    = defined( 'LTDH_CPT_MAJOR' ) ? LTDH_CPT_MAJOR : 'major';
		if ( in_array( $post_type, [ $cpt_prog, $cpt_school, $cpt_major, 'post' ], true ) ) {
			self::flush_all_content_transients();
		}
	}

	/**
	 * Format standard label for program select dropdowns.
	 *
	 * @param int|WP_Post $program Program ID or post object.
	 * @return string Formatted program label.
	 */
	public static function format_program_select_label( $program ): string {
		$prog_id = is_object( $program ) ? $program->ID : absint( $program );
		if ( ! $prog_id ) {
			return '';
		}

		$prog_name = '';

		// 1. Chuyên ngành đào tạo cụ thể (nếu có)
		$spec_title = function_exists( 'get_field' ) ? get_field( 'specialization_title', $prog_id ) : '';
		if ( empty( $spec_title ) ) {
			$spec_title = get_post_meta( $prog_id, 'specialization_title', true );
		}
		if ( empty( $spec_title ) ) {
			$spec_title = function_exists( 'get_field' ) ? get_field( 'chuyen_nganh', $prog_id ) : '';
			if ( empty( $spec_title ) ) {
				$spec_title = get_post_meta( $prog_id, 'chuyen_nganh', true );
			}
		}
		if ( ! empty( $spec_title ) && is_string( $spec_title ) ) {
			$prog_name = $spec_title;
		}

		// 2. Tên ngành đào tạo liên kết (major_relationship)
		if ( empty( $prog_name ) ) {
			$major_id = function_exists( 'ltdh_get_program_major_id' ) ? ltdh_get_program_major_id( $prog_id ) : 0;
			if ( $major_id ) {
				$prog_name = get_the_title( $major_id );
			}
		}

		// 3. Fallback: Tiêu đề chương trình đã làm sạch qua helper chuẩn
		if ( empty( $prog_name ) ) {
			$prog_name = function_exists( 'ltdh_get_program_discipline_name' ) ? ltdh_get_program_discipline_name( $prog_id ) : get_the_title( $prog_id );
		}

		$prog_name = preg_replace( '/^(?:ngành|chuyên ngành|cử nhân|kỹ sư)\s+/iu', '', trim( (string) $prog_name ) );
		$prog_name = preg_replace( '/\s*\([^)]*\)$/u', '', $prog_name );
		$prog_name = trim( $prog_name, " -–—\t\n\r\0\x0B" );
		if ( ! empty( $prog_name ) ) {
			$prog_name = mb_strtoupper( mb_substr( $prog_name, 0, 1, 'UTF-8' ), 'UTF-8' ) . mb_substr( $prog_name, 1, null, 'UTF-8' );
		}

		$terms      = wp_get_post_terms( $prog_id, 'training_type' );
		$type_name  = ( ! empty( $terms ) && ! is_wp_error( $terms ) ) ? trim( (string) $terms[0]->name ) : '';
		$clean_type = preg_replace( '/^(?:hệ\s+|đào\s+tạo\s+)/iu', '', $type_name );
		if ( ! empty( $clean_type ) ) {
			$clean_type = mb_strtoupper( mb_substr( $clean_type, 0, 1, 'UTF-8' ), 'UTF-8' ) . mb_substr( $clean_type, 1, null, 'UTF-8' );
		}

		$school_id   = function_exists( 'ltdh_get_program_school_id' ) ? ltdh_get_program_school_id( $prog_id ) : 0;
		$school_code = $school_id ? ( function_exists( 'ltdh_get_school_code' ) ? ltdh_get_school_code( $school_id ) : get_post_meta( $school_id, 'school_code', true ) ) : '';
		if ( empty( $school_code ) && $school_id ) {
			$school_title = get_the_title( $school_id );
			$school_code  = preg_replace( '/^(?:trường\s+)?đại\s+học\s+/iu', 'ĐH ', trim( (string) $school_title ) );
		}

		$parts = array_filter( [ $prog_name, $clean_type, $school_code ] );
		return ! empty( $parts ) ? implode( ' - ', $parts ) : get_the_title( $prog_id );
	}

	/**
	 * CF7 dynamic options for programs/majors dropdown.
	 */
	public static function cf7_dynamic_programs( $tag, $replace ) {
		if ( 'current_program_id' === $tag['name'] || 'program_id' === $tag['name'] || 'current_major_id' === $tag['name'] || 'major_id' === $tag['name'] ) {
			$cpt_major = defined( 'LTDH_CPT_MAJOR' ) ? LTDH_CPT_MAJOR : 'major';
			$majors = get_posts( [
				'post_type'   => $cpt_major,
				'post_status' => 'publish',
				'numberposts' => -1,
				'orderby'     => 'title',
				'order'       => 'ASC',
			] );
			$tag['raw_values'] = [];
			$tag['values']     = [];
			$tag['labels']     = [];

			$tag['raw_values'][] = '';
			$tag['values'][]     = '';
			$tag['labels'][]     = '-- Chọn ngành học --';

			$curr_id = 0;
			if ( ! empty( $GLOBALS['ltdh_cf7_context_hidden_fields']['current_major_id'] ) ) {
				$curr_id = (int) $GLOBALS['ltdh_cf7_context_hidden_fields']['current_major_id'];
			} elseif ( ! empty( $GLOBALS['ltdh_cf7_context_hidden_fields']['major_id'] ) ) {
				$curr_id = (int) $GLOBALS['ltdh_cf7_context_hidden_fields']['major_id'];
			} elseif ( ! empty( $GLOBALS['ltdh_cf7_context_hidden_fields']['current_program_id'] ) ) {
				$prog_id = (int) $GLOBALS['ltdh_cf7_context_hidden_fields']['current_program_id'];
				$curr_id = function_exists( 'ltdh_get_program_major_id' ) ? ltdh_get_program_major_id( $prog_id ) : (int) get_post_meta( $prog_id, 'major_relationship', true );
			} elseif ( ! empty( $GLOBALS['ltdh_cf7_context_hidden_fields']['program_id'] ) ) {
				$prog_id = (int) $GLOBALS['ltdh_cf7_context_hidden_fields']['program_id'];
				$curr_id = function_exists( 'ltdh_get_program_major_id' ) ? ltdh_get_program_major_id( $prog_id ) : (int) get_post_meta( $prog_id, 'major_relationship', true );
			} elseif ( function_exists( 'is_singular' ) && is_singular( 'major' ) ) {
				$curr_id = get_the_ID();
			} elseif ( function_exists( 'is_singular' ) && is_singular( 'program' ) ) {
				$prog_id = get_the_ID();
				$curr_id = function_exists( 'ltdh_get_program_major_id' ) ? ltdh_get_program_major_id( $prog_id ) : (int) get_post_meta( $prog_id, 'major_relationship', true );
			}

			$items = [];
			foreach ( $majors as $m ) {
				$items[] = [
					'id'    => (string) $m->ID,
					'label' => $m->post_title,
				];
			}

			usort( $items, function( $a, $b ) {
				return strcmp( mb_strtolower( $a['label'], 'UTF-8' ), mb_strtolower( $b['label'], 'UTF-8' ) );
			} );

			$selected_idx = 0;
			$idx          = 1;
			foreach ( $items as $item ) {
				$idx++;
				$tag['raw_values'][] = $item['id'];
				$tag['values'][]     = $item['id'];
				$tag['labels'][]     = $item['label'];

				if ( $curr_id && (int) $item['id'] === $curr_id ) {
					$selected_idx = $idx;
				}
			}

			if ( $selected_idx > 0 ) {
				if ( ! isset( $tag['options'] ) || ! is_array( $tag['options'] ) ) {
					$tag['options'] = [];
				}
				$tag['options'][] = 'default:' . $selected_idx;
			}
		}
		return $tag;
	}

	/**
	 * Get fallback image URL.
	 *
	 * @param string $context
	 * @param string $size
	 * @return string
	 */
	public static function get_fallback_image( string $context = 'program', string $size = 'medium' ): string {
		$theme_uri = get_template_directory_uri();
		$theme_dir = get_template_directory();

		if ( 'school' === $context ) {
			if ( ( 'medium' === $size || 'thumbnail' === $size ) && file_exists( $theme_dir . '/assets/images/banner-fallback-300x180.webp' ) ) {
				return $theme_uri . '/assets/images/banner-fallback-300x180.webp';
			}
			if ( file_exists( $theme_dir . '/assets/images/banner-school.webp' ) ) {
				return $theme_uri . '/assets/images/banner-school.webp';
			}
			return $theme_uri . '/assets/images/banner-school.jpg';
		}

		if ( 'contact' === $context ) {
			$contact_img = home_url( '/wp-content/uploads/2026/07/banner-contact.png' );
			return self::get_optimized_image_url( $contact_img );
		}

		$custom_share_image = null;
		if ( function_exists( 'get_field' ) ) {
			$custom_share_image = get_field( 'global_share_image', 'options' ) ?: get_field( 'global_share_image', 'option' );
		}
		if ( empty( $custom_share_image ) ) {
			$raw_opt = get_option( 'options_global_share_image' ) ?: get_option( 'global_share_image' );
			if ( ! empty( $raw_opt ) ) {
				if ( is_numeric( $raw_opt ) ) {
					$custom_share_image = wp_get_attachment_image_url( (int) $raw_opt, 'full' );
				} elseif ( is_string( $raw_opt ) && filter_var( $raw_opt, FILTER_VALIDATE_URL ) ) {
					$custom_share_image = $raw_opt;
				}
			}
		}
		if ( ! empty( $custom_share_image ) ) {
			if ( is_array( $custom_share_image ) && ! empty( $custom_share_image['url'] ) ) {
				$custom_share_image = $custom_share_image['url'];
			} elseif ( is_numeric( $custom_share_image ) ) {
				$custom_share_image = wp_get_attachment_image_url( (int) $custom_share_image, 'full' );
			}
			if ( ! empty( $custom_share_image ) ) {
				return self::get_optimized_image_url( $custom_share_image );
			}
		}

		if ( ( 'medium' === $size || 'thumbnail' === $size ) && file_exists( $theme_dir . '/assets/images/banner-fallback-300x180.webp' ) ) {
			return $theme_uri . '/assets/images/banner-fallback-300x180.webp';
		}
		if ( file_exists( $theme_dir . '/assets/images/banner-fallback.webp' ) ) {
			return $theme_uri . '/assets/images/banner-fallback.webp';
		}
		if ( file_exists( $theme_dir . '/assets/images/banner-default.jpg' ) ) {
			return $theme_uri . '/assets/images/banner-default.jpg';
		}
		if ( file_exists( $theme_dir . '/assets/images/banner-fallback.png' ) ) {
			return $theme_uri . '/assets/images/banner-fallback.png';
		}
		return $theme_uri . '/assets/images/banner-program.jpg';
	}

	/**
	 * Get post thumbnail URL or fallback.
	 *
	 * @param mixed  $post
	 * @param string $size
	 * @return string
	 */
	public static function get_post_thumbnail_url( $post = null, string $size = 'medium' ): string {
		$post_id = $post ? ( is_object( $post ) ? $post->ID : (int) $post ) : get_the_ID();
		if ( $post_id && has_post_thumbnail( $post_id ) ) {
			$thumb = get_the_post_thumbnail_url( $post_id, $size );
			if ( ! empty( $thumb ) ) {
				return self::get_optimized_image_url( $thumb );
			}
		}

		if ( $post_id && 'program' === get_post_type( $post_id ) ) {
			$major_id = function_exists( 'ltdh_get_program_major_id' ) ? ltdh_get_program_major_id( $post_id ) : 0;
			if ( $major_id ) {
				$major_thumb = get_the_post_thumbnail_url( $major_id, $size );
				if ( ! empty( $major_thumb ) ) {
					return self::get_optimized_image_url( $major_thumb );
				}
			}
		}

		$fallback = self::get_fallback_image( 'post' );
		return self::get_optimized_image_url( $fallback );
	}

	/**
	 * Get major banner image URL.
	 *
	 * @param int    $major_id
	 * @param string $size
	 * @return string
	 */
	public static function get_major_banner_url( int $major_id, string $size = 'full' ): string {
		if ( ! $major_id ) {
			return '';
		}

		$banner_url = '';

		if ( function_exists( 'get_field' ) ) {
			$raw = get_field( 'major_banner', $major_id ) ?: get_field( 'page_banner', $major_id );
			if ( is_array( $raw ) ) {
				$banner_url = $raw['url'] ?? '';
			} elseif ( is_numeric( $raw ) && (int) $raw > 0 ) {
				$banner_url = wp_get_attachment_image_url( (int) $raw, $size ) ?: '';
			} elseif ( is_string( $raw ) && ! empty( $raw ) ) {
				$banner_url = $raw;
			}
		}

		if ( empty( $banner_url ) ) {
			$raw_meta = get_post_meta( $major_id, 'major_banner', true ) ?: get_post_meta( $major_id, 'page_banner', true );
			if ( is_numeric( $raw_meta ) && (int) $raw_meta > 0 ) {
				$banner_url = wp_get_attachment_image_url( (int) $raw_meta, $size ) ?: '';
			} elseif ( is_string( $raw_meta ) && ! empty( $raw_meta ) ) {
				$banner_url = $raw_meta;
			}
		}

		if ( empty( $banner_url ) && has_post_thumbnail( $major_id ) ) {
			$banner_url = get_the_post_thumbnail_url( $major_id, $size ) ?: '';
		}

		if ( ! empty( $banner_url ) ) {
			$banner_url = self::get_optimized_image_url( $banner_url );
		}

		return $banner_url ?: '';
	}

	/**
	 * Filter default HTML for post thumbnails when no featured image is set.
	 */
	public static function filter_default_post_thumbnail_html( $html, $post_id, $post_thumbnail_id, $size, $attr ) {
		if ( empty( $html ) && ! is_admin() ) {
			$fallback_url = self::get_fallback_image( 'post' );
			$alt          = esc_attr( get_the_title( $post_id ) );
			$class        = 'wp-post-image';
			if ( is_array( $attr ) && ! empty( $attr['class'] ) ) {
				$class .= ' ' . esc_attr( $attr['class'] );
			} elseif ( is_string( $attr ) && ! empty( $attr ) ) {
				$class .= ' ' . esc_attr( $attr );
			}
			return sprintf( '<img src="%s" alt="%s" class="%s" loading="lazy" />', esc_url( $fallback_url ), $alt, $class );
		}
		return $html;
	}

	/**
	 * Get category thumbnail URL for major_cat taxonomy.
	 *
	 * @param mixed  $term
	 * @param string $size
	 * @return string
	 */
	public static function get_major_cat_thumbnail_url( $term = null, string $size = 'medium_large' ): string {
		if ( empty( $term ) ) {
			return '';
		}
		$term_id = is_object( $term ) ? $term->term_id : (int) $term;
		if ( ! $term_id ) {
			return '';
		}

		if ( function_exists( 'get_field' ) ) {
			$thumb = get_field( 'category_thumbnail', 'term_' . $term_id )
				?: get_field( 'category_thumbnail', 'major_cat_' . $term_id );
			if ( ! empty( $thumb ) ) {
				if ( is_numeric( $thumb ) ) {
					$url = wp_get_attachment_image_url( (int) $thumb, $size );
					if ( ! empty( $url ) ) {
						return $url;
					}
				} elseif ( is_array( $thumb ) && ! empty( $thumb['url'] ) ) {
					return $thumb['url'];
				} elseif ( is_string( $thumb ) ) {
					return $thumb;
				}
			}
		}

		$meta_thumb_id = get_term_meta( $term_id, 'category_thumbnail', true )
			?: get_term_meta( $term_id, 'thumbnail_id', true );
		if ( ! empty( $meta_thumb_id ) && is_numeric( $meta_thumb_id ) ) {
			$url = wp_get_attachment_image_url( (int) $meta_thumb_id, $size );
			if ( ! empty( $url ) ) {
				return $url;
			}
		}

		return '';
	}

	/**
	 * Get thumbnail image URL for Major card.
	 *
	 * @param mixed  $post_id
	 * @param string $size
	 * @return string
	 */
	public static function get_major_card_thumbnail_url( $post_id = null, string $size = 'medium_large' ): string {
		$pid = $post_id ? ( is_object( $post_id ) ? $post_id->ID : (int) $post_id ) : get_the_ID();
		if ( ! $pid ) {
			return get_stylesheet_directory_uri() . '/assets/images/banner-fallback.webp';
		}

		if ( has_post_thumbnail( $pid ) ) {
			$thumb = get_the_post_thumbnail_url( $pid, $size );
			if ( ! empty( $thumb ) ) {
				return $thumb;
			}
		}

		$terms = get_the_terms( $pid, 'major_cat' );
		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $t ) {
				$cat_thumb = self::get_major_cat_thumbnail_url( $t->term_id, $size );
				if ( ! empty( $cat_thumb ) ) {
					return $cat_thumb;
				}
			}
		}

		if ( file_exists( get_template_directory() . '/assets/images/banner-hero-02.webp' ) ) {
			return get_template_directory_uri() . '/assets/images/banner-hero-02.webp';
		}
		return get_template_directory_uri() . '/assets/images/banner-fallback.webp';
	}

	/**
	 * Training mode badge HTML.
	 *
	 * @param string $type_name
	 * @param string $type_slug
	 * @return string
	 */
	public static function get_training_type_badge_html( string $type_name, string $type_slug = '' ): string {
		if ( ! $type_name ) {
			return '';
		}
		$name_lower = mb_strtolower( trim( $type_name ) );
		$slug_lower = sanitize_title( $type_slug ?: $type_name );

		if ( in_array( $slug_lower, [ 'tu-xa', 'dao-tao-tu-xa', 'online' ], true ) || false !== strpos( $name_lower, 'từ xa' ) || false !== strpos( $name_lower, 'online' ) ) {
			$badge_class = 'bg-sky-50 text-sky-700 border-sky-200';
		} elseif ( in_array( $slug_lower, [ 'vua-hoc-vua-lam', 'vhvl', 'vua-lam-vua-hoc' ], true ) || false !== strpos( $name_lower, 'vừa học vừa làm' ) || false !== strpos( $name_lower, 'vừa làm vừa học' ) ) {
			$badge_class = 'bg-amber-50 text-amber-700 border-amber-200';
		} elseif ( in_array( $slug_lower, [ 'chinh-quy', 'chinh-quy-tap-trung' ], true ) || false !== strpos( $name_lower, 'chính quy' ) ) {
			$badge_class = 'bg-emerald-50 text-emerald-700 border-emerald-200';
		} else {
			$badge_class = 'bg-blue-50 text-[#00308b] border-blue-200';
		}

		return sprintf(
			'<span class="%s text-xs font-extrabold px-2.5 py-0.5 rounded-full uppercase tracking-wide border shadow-xs inline-block leading-normal">%s</span>',
			esc_attr( $badge_class ),
			esc_html( $type_name )
		);
	}

	/**
	 * Format verification date into Vietnamese standard DD/MM/YYYY.
	 *
	 * @param mixed $date_raw
	 * @return string
	 */
	public static function format_verification_date( $date_raw ): string {
		if ( empty( $date_raw ) || ! is_string( $date_raw ) ) {
			return '';
		}
		$trimmed = trim( $date_raw );
		if ( '' === $trimmed ) {
			return '';
		}

		// 1. Format: DD/MM/YYYY
		if ( preg_match( '#^(\d{1,2})/(\d{1,2})/(\d{4})$#', $trimmed, $m ) ) {
			return sprintf( '%02d/%02d/%04d', (int) $m[1], (int) $m[2], (int) $m[3] );
		}

		// 2. Format: YYYYMMDD (ACF datepicker raw)
		if ( preg_match( '#^(\d{4})(\d{2})(\d{2})$#', $trimmed, $m ) ) {
			return sprintf( '%02d/%02d/%04d', (int) $m[3], (int) $m[2], (int) $m[1] );
		}

		// 3. Format: YYYY-MM-DD
		if ( preg_match( '#^(\d{4})-(\d{1,2})-(\d{1,2})$#', $trimmed, $m ) ) {
			return sprintf( '%02d/%02d/%04d', (int) $m[3], (int) $m[2], (int) $m[1] );
		}

		// 4. Try strtotime for standard ISO formats
		$ts = strtotime( $trimmed );
		if ( false !== $ts && $ts > 0 ) {
			return date_i18n( 'd/m/Y', $ts );
		}

		return '';
	}

	/**
	 * Retrieve verified status, date, source and internal notes for a program.
	 *
	 * @param int $program_id Program post ID.
	 * @return array Verification data.
	 */
	public static function get_program_verification( int $program_id ): array {
		$status_unverified = defined( 'LTDH_VERIF_STATUS_UNVERIFIED' ) ? LTDH_VERIF_STATUS_UNVERIFIED : 'unverified';
		$status_verified   = defined( 'LTDH_VERIF_STATUS_VERIFIED' ) ? LTDH_VERIF_STATUS_VERIFIED : 'verified';
		$status_partial    = defined( 'LTDH_VERIF_STATUS_PARTIAL' ) ? LTDH_VERIF_STATUS_PARTIAL : 'partial';

		if ( ! $program_id || 'program' !== get_post_type( $program_id ) ) {
			return [
				'status'            => $status_unverified,
				'status_label'      => 'Chưa xác minh',
				'is_verified'       => false,
				'is_partial'        => false,
				'is_unverified'     => true,
				'last_verified_at'  => '',
				'last_verified_raw' => '',
				'source_url'        => '',
				'source_title'      => '',
				'notes'             => '',
			];
		}

		$meta_status = defined( 'LTDH_META_VERIFICATION_STATUS' ) ? LTDH_META_VERIFICATION_STATUS : '_ltdh_verification_status';
		$status      = get_post_meta( $program_id, $meta_status, true );
		if ( empty( $status ) && function_exists( 'get_field' ) ) {
			$status = get_field( $meta_status, $program_id );
		}
		$allowed_statuses = [
			$status_verified,
			$status_partial,
			$status_unverified,
		];
		if ( ! in_array( $status, $allowed_statuses, true ) ) {
			$status = $status_unverified;
		}

		$status_labels = [
			$status_verified   => 'Đã xác minh',
			$status_partial    => 'Đã xác minh một phần',
			$status_unverified => 'Chưa xác minh',
		];

		$meta_date = defined( 'LTDH_META_LAST_VERIFIED_AT' ) ? LTDH_META_LAST_VERIFIED_AT : '_ltdh_last_verified_at';
		$raw_date  = get_post_meta( $program_id, $meta_date, true );
		if ( empty( $raw_date ) && function_exists( 'get_field' ) ) {
			$raw_date = get_field( $meta_date, $program_id, false );
		}
		$formatted_date = self::format_verification_date( $raw_date );

		$meta_source = defined( 'LTDH_META_VERIFICATION_SOURCE' ) ? LTDH_META_VERIFICATION_SOURCE : '_ltdh_verification_source';
		$raw_url     = get_post_meta( $program_id, $meta_source, true );
		if ( empty( $raw_url ) && function_exists( 'get_field' ) ) {
			$raw_url = get_field( $meta_source, $program_id );
		}
		$clean_url = '';
		if ( ! empty( $raw_url ) && is_string( $raw_url ) ) {
			$candidate_url = esc_url_raw( trim( $raw_url ) );
			if ( ! empty( $candidate_url ) && filter_var( $candidate_url, FILTER_VALIDATE_URL ) && preg_match( '#^https?://#i', $candidate_url ) ) {
				$clean_url = $candidate_url;
			}
		}

		$meta_source_title = defined( 'LTDH_META_VERIFICATION_SOURCE_TITLE' ) ? LTDH_META_VERIFICATION_SOURCE_TITLE : '_ltdh_verification_source_title';
		$source_title      = get_post_meta( $program_id, $meta_source_title, true );
		if ( empty( $source_title ) && function_exists( 'get_field' ) ) {
			$source_title = get_field( $meta_source_title, $program_id );
		}
		$clean_title = ! empty( $source_title ) && is_string( $source_title ) ? trim( sanitize_text_field( $source_title ) ) : '';

		$meta_notes = defined( 'LTDH_META_VERIFICATION_NOTES' ) ? LTDH_META_VERIFICATION_NOTES : '_ltdh_verification_notes';
		$notes      = get_post_meta( $program_id, $meta_notes, true );
		if ( empty( $notes ) && function_exists( 'get_field' ) ) {
			$notes = get_field( $meta_notes, $program_id );
		}
		$clean_notes = ! empty( $notes ) && is_string( $notes ) ? trim( sanitize_textarea_field( $notes ) ) : '';

		return [
			'status'            => $status,
			'status_label'      => $status_labels[ $status ] ?? 'Chưa xác minh',
			'is_verified'       => ( $status_verified === $status ),
			'is_partial'        => ( $status_partial === $status ),
			'is_unverified'     => ( $status_unverified === $status ),
			'last_verified_at'  => $formatted_date,
			'last_verified_raw' => is_string( $raw_date ) ? trim( $raw_date ) : '',
			'source_url'        => $clean_url,
			'source_title'      => $clean_title,
			'notes'             => $clean_notes,
		];
	}

	/**
	 * Get verification badge HTML for a program.
	 *
	 * @param int    $program_id Program post ID.
	 * @param string $context    'detail' for single page, 'card' for listing cards.
	 * @return string HTML output or empty string.
	 */
	public static function get_program_verification_badge_html( int $program_id, string $context = 'detail' ): string {
		$data = self::get_program_verification( $program_id );

		if ( 'card' === $context ) {
			if ( ! empty( $data['is_verified'] ) ) {
				return '<span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200/80 px-2 py-0.5 rounded-md shadow-2xs select-none" title="Thông tin chương trình đã được xác minh">'
					. '<svg class="w-3 h-3 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>'
					. '<span>Đã xác minh</span>'
					. '</span>';
			}
			return '';
		}

		if ( ! empty( $data['is_verified'] ) ) {
			$source_label = ! empty( $data['source_title'] ) ? $data['source_title'] : 'Xem nguồn chính thức';

			$meta_items = [];
			if ( ! empty( $data['last_verified_at'] ) ) {
				$meta_items[] = '<span class="inline-flex items-center gap-1.5">'
					. '<span class="text-emerald-700/80">Cập nhật xác minh:</span>'
					. '<strong class="font-bold text-emerald-950">' . esc_html( $data['last_verified_at'] ) . '</strong>'
					. '</span>';
			}
			if ( ! empty( $data['source_url'] ) ) {
				$meta_items[] = '<span class="inline-flex items-center gap-1.5">'
					. '<span class="text-emerald-700/80">Nguồn thông tin:</span>'
					. '<a href="' . esc_url( $data['source_url'] ) . '" target="_blank" rel="noopener noreferrer nofollow" class="font-bold text-emerald-800 hover:text-emerald-950 underline underline-offset-2 transition-colors inline-flex items-center gap-0.5">'
					. '<span>' . esc_html( $source_label ) . '</span>'
					. '<svg class="w-3 h-3 ml-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>'
					. '</a>'
					. '</span>';
			}

			$meta_html = '';
			if ( ! empty( $meta_items ) ) {
				$meta_html = '<div class="mt-2.5 pt-2.5 border-t border-emerald-200/70 flex flex-wrap items-center gap-x-5 gap-y-1.5 text-xs text-emerald-800">'
					. implode( '<span class="text-emerald-300 hidden sm:inline select-none" aria-hidden="true">•</span>', $meta_items )
					. '</div>';
			}

			return '<div class="ltdh-verification-badge mb-5 rounded-2xl border border-emerald-200/90 bg-emerald-50/70 p-3.5 sm:p-4 text-emerald-950 shadow-2xs">'
				. '<div class="flex items-center gap-2.5 min-w-0">'
				. '<span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-emerald-600 text-white text-xs font-black shrink-0 shadow-xs" aria-hidden="true">✓</span>'
				. '<span class="font-extrabold text-sm sm:text-base text-emerald-950 tracking-tight leading-snug">Thông tin chương trình đã được xác minh</span>'
				. '</div>'
				. $meta_html
				. '</div>';
		}

		if ( ! empty( $data['is_partial'] ) ) {
			return '<div class="ltdh-verification-partial mb-5 rounded-2xl border border-amber-200/90 bg-amber-50/70 p-3.5 sm:p-4 text-amber-950 shadow-2xs">'
				. '<div class="flex items-center gap-2.5">'
				. '<span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-amber-500 text-white text-xs font-bold shrink-0" aria-hidden="true">!</span>'
				. '<span class="font-semibold text-xs sm:text-sm text-amber-900 leading-snug">Một số thông tin chương trình chưa được xác minh đầy đủ.</span>'
				. '</div>'
				. '</div>';
		}

		return '';
	}

	/**
	 * Output verification badge HTML.
	 */
	public static function render_program_verification_badge( int $program_id, string $context = 'detail' ): void {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo self::get_program_verification_badge_html( $program_id, $context );
	}
}

// ----------------------------------------------------------------------------
// Action & Filter Hooks for UI Domain
// ----------------------------------------------------------------------------

add_filter( 'acf/format_value/name=contact_info', function( $value, $post_id, $field ) {
	if ( ! empty( $value ) && is_string( $value ) ) {
		return LTDH_UI_Helpers::format_contact_info( $value, (int) $post_id );
	}
	return $value;
}, 20, 3 );

add_filter( 'wpcf7_autop_or_not', '__return_false' );

add_filter( 'wpcf7_form_hidden_fields', function( $hidden_fields ) {
	if ( ! empty( $GLOBALS['ltdh_cf7_context_hidden_fields'] ) && is_array( $GLOBALS['ltdh_cf7_context_hidden_fields'] ) ) {
		$hidden_fields = array_merge( $hidden_fields, $GLOBALS['ltdh_cf7_context_hidden_fields'] );
	}
	return $hidden_fields;
} );

add_action( 'save_post', [ 'LTDH_UI_Helpers', 'clear_transients_on_save' ], 20 );
add_action( 'deleted_post', [ 'LTDH_UI_Helpers', 'flush_all_content_transients' ], 20 );

add_action( 'litespeed_purged_all', [ 'LTDH_UI_Helpers', 'flush_all_content_transients' ] );
add_action( 'litespeed_purged_all_lscache', [ 'LTDH_UI_Helpers', 'flush_all_content_transients' ] );

add_filter( 'wpcf7_form_tag', [ 'LTDH_UI_Helpers', 'cf7_dynamic_programs' ], 10, 2 );

add_filter( 'wpcf7_form_elements', function( $content ) {
	if ( false !== strpos( $content, 'consult-program' ) ) {
		$content = str_replace( 'Chương trình quan tâm', 'Ngành học quan tâm', $content );
	}
	return $content;
} );

add_filter( 'post_thumbnail_html', [ 'LTDH_UI_Helpers', 'filter_default_post_thumbnail_html' ], 20, 5 );

add_filter( 'the_content', function( $content ) {
	if ( empty( $content ) || ! is_string( $content ) ) {
		return $content;
	}

	// Remove <br /> directly inside <a> tags
	$content = preg_replace_callback( '/<a\b[^>]*>(.*?)<\/a>/is', function( $matches ) {
		$inner = preg_replace( '/<br\s*\/?>/i', ' ', $matches[1] );
		return str_replace( $matches[1], $inner, $matches[0] );
	}, $content );

	// Remove <br /> between sibling <a> tags (e.g. inside flex button groups)
	$content = preg_replace( '/(<\/a>)\s*<br\s*\/?>\s*(<a\b)/i', '$1 $2', $content );

	// Unwrap inline-flex buttons from accidental <p> tags created by wpautop
	$content = preg_replace( '/<p>\s*(<a\b[^>]*class="[^"]*inline-flex[^"]*"[^>]*>.*?<\/a>)\s*<\/p>/is', '$1', $content );

	// Clean up empty <p></p> or <p><br /></p>
	$content = preg_replace( '/<p>\s*(?:<br\s*\/?>)?\s*<\/p>/i', '', $content );

	return $content;
}, 99 );
