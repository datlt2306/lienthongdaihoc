<?php
/**
 * Pricing & Fee Domain Helpers.
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LTDH_Pricing_Helpers {

	/**
	 * Get dynamic program tuition display with fallback.
	 *
	 * @param int  $program_id
	 * @param bool $include_year
	 * @return string
	 */
	public static function get_tuition_display( int $program_id, bool $include_year = false ): string {
		$amount        = function_exists( 'get_field' ) ? get_field( 'tuition_amount', $program_id ) : get_post_meta( $program_id, 'tuition_amount', true );
		$unit          = function_exists( 'get_field' ) ? get_field( 'tuition_unit', $program_id ) : get_post_meta( $program_id, 'tuition_unit', true );
		$academic_year = function_exists( 'get_field' ) ? get_field( 'tuition_academic_year', $program_id ) : get_post_meta( $program_id, 'tuition_academic_year', true );

		if ( ! empty( $amount ) ) {
			$unit_label = 'đ';
			if ( 'tin-chi' === $unit ) {
				$unit_label = 'đ/tín chỉ';
			} elseif ( 'hoc-ky' === $unit ) {
				$unit_label = 'đ/học kỳ';
			} elseif ( 'nam' === $unit ) {
				$unit_label = 'đ/năm';
			}

			$year_suffix = ( $include_year && ! empty( $academic_year ) ) ? ' · ' . $academic_year : '';
			return number_format( (float) $amount, 0, ',', '.' ) . ' ' . $unit_label . $year_suffix;
		}

		// Fallback to original tuition_fee
		$legacy_tuition = function_exists( 'get_field' ) ? get_field( 'tuition_fee', $program_id ) : get_post_meta( $program_id, 'tuition_fee', true );
		return ! empty( $legacy_tuition ) ? $legacy_tuition : 'Liên hệ';
	}

	public static function get_program_tuition_display( int $program_id, bool $include_year = false ): string {
		return self::get_tuition_display( $program_id, $include_year );
	}

	/**
	 * Format admission fee into display string and flag.
	 *
	 * @param mixed $fee
	 * @return array
	 */
	public static function format_admission_fee( $fee ): array {
		if ( '' === $fee || null === $fee || false === $fee ) {
			return [ 'display' => '', 'is_free' => false ];
		}
		$fee_str = trim( (string) $fee );
		if ( '' === $fee_str ) {
			return [ 'display' => '', 'is_free' => false ];
		}

		$fee_lower = mb_strtolower( $fee_str, 'UTF-8' );
		$is_free   = in_array( $fee_lower, [ '0', '0đ', '0 đ', '0d', '0 d', 'miễn phí', 'mien phi', 'không thu', 'khong thu', 'miễn lệ phí' ], true );

		if ( $is_free ) {
			return [
				'display' => 'Miễn phí / hồ sơ',
				'is_free' => true,
			];
		}

		// If already contains "hồ sơ" or "ho so"
		if ( preg_match( '/\b(hồ sơ|ho so|hs)\b/iu', $fee_str ) ) {
			return [
				'display' => $fee_str,
				'is_free' => false,
			];
		}

		// Check if it's purely digits (e.g. "200000")
		if ( ctype_digit( $fee_str ) ) {
			return [
				'display' => number_format( (float) $fee_str, 0, ',', '.' ) . ' đ / hồ sơ',
				'is_free' => false,
			];
		}

		// Check if string contains numbers with optional separators (e.g. "200.000" or "200.000đ" or "200,000")
		$clean_num = preg_replace( '/[^\d]/', '', $fee_str );
		if ( is_numeric( $clean_num ) && (float) $clean_num > 0 ) {
			if ( preg_match( '/^[\d.,\s]+(?:đ|vnđ|vnd)?$/iu', $fee_str ) ) {
				return [
					'display' => number_format( (float) $clean_num, 0, ',', '.' ) . ' đ / hồ sơ',
					'is_free' => false,
				];
			}
		}

		// Fallback: append " / hồ sơ"
		return [
			'display' => rtrim( $fee_str, ' /' ) . ' / hồ sơ',
			'is_free' => false,
		];
	}

	/**
	 * Lấy lộ trình tăng học phí / ghi chú học phí của chương trình với cơ chế DRY fallback về trường.
	 *
	 * @param int $program_id
	 * @return string
	 */
	public static function get_tuition_roadmap( int $program_id ): string {
		if ( ! $program_id ) {
			return '';
		}

		$roadmap = function_exists( 'get_field' ) ? get_field( 'tuition_increase_roadmap', $program_id ) : '';
		if ( empty( $roadmap ) ) {
			$roadmap = get_post_meta( $program_id, 'tuition_increase_roadmap', true );
		}

		if ( ! empty( $roadmap ) && ! empty( trim( (string) $roadmap ) ) ) {
			return trim( (string) $roadmap );
		}

		// Fallback to school tuition increase roadmap (DRY)
		$school_id = function_exists( 'ltdh_get_program_school_id' ) ? ltdh_get_program_school_id( $program_id ) : ( class_exists( 'LTDH_Program_Helpers' ) ? LTDH_Program_Helpers::get_school_id( $program_id ) : 0 );
		if ( $school_id > 0 ) {
			$school_roadmap = function_exists( 'get_field' ) ? get_field( 'tuition_increase_roadmap', $school_id ) : '';
			if ( empty( $school_roadmap ) ) {
				$school_roadmap = get_post_meta( $school_id, 'tuition_increase_roadmap', true );
			}
			if ( ! empty( $school_roadmap ) && ! empty( trim( (string) $school_roadmap ) ) ) {
				return trim( (string) $school_roadmap );
			}
		}

		return '';
	}

	public static function get_program_tuition_roadmap( int $program_id ): string {
		return self::get_tuition_roadmap( $program_id );
	}

	/**
	 * Lấy lệ phí xét tuyển của chương trình với cơ chế DRY fallback về trường.
	 *
	 * @param int $program_id
	 * @return mixed
	 */
	public static function get_admission_fee( int $program_id ) {
		if ( ! $program_id ) {
			return '';
		}

		$fee = function_exists( 'get_field' ) ? get_field( 'admission_fee', $program_id ) : '';
		if ( '' === $fee || null === $fee || false === $fee ) {
			$fee = get_post_meta( $program_id, 'admission_fee', true );
		}

		if ( ! empty( $fee ) || ( is_numeric( $fee ) && (int) $fee === 0 ) ) {
			return $fee;
		}

		// Fallback to school default
		$school_id = function_exists( 'ltdh_get_program_school_id' ) ? ltdh_get_program_school_id( $program_id ) : ( class_exists( 'LTDH_Program_Helpers' ) ? LTDH_Program_Helpers::get_school_id( $program_id ) : 0 );
		if ( $school_id > 0 ) {
			$default_fee = function_exists( 'get_field' ) ? get_field( 'default_admission_fee', $school_id ) : '';
			if ( '' === $default_fee || null === $default_fee || false === $default_fee ) {
				$default_fee = get_post_meta( $school_id, 'default_admission_fee', true );
			}
			if ( ! empty( $default_fee ) || ( is_numeric( $default_fee ) && (int) $default_fee === 0 ) ) {
				return $default_fee;
			}
		}

		return '';
	}

	public static function get_program_admission_fee( int $program_id ) {
		return self::get_admission_fee( $program_id );
	}

	/**
	 * Lấy mức tạm thu học phí kỳ 1 của chương trình với cơ chế DRY fallback về trường.
	 *
	 * @param int $program_id
	 * @return string
	 */
	public static function get_initial_tuition( int $program_id ): string {
		if ( ! $program_id ) {
			return '';
		}

		$tuition = function_exists( 'get_field' ) ? get_field( 'initial_tuition', $program_id ) : '';
		if ( '' === $tuition || null === $tuition || false === $tuition ) {
			$tuition = get_post_meta( $program_id, 'initial_tuition', true );
		}

		if ( ! empty( $tuition ) || ( is_numeric( $tuition ) && (float) $tuition > 0 ) ) {
			return trim( (string) $tuition );
		}

		// Fallback to school default
		$school_id = function_exists( 'ltdh_get_program_school_id' ) ? ltdh_get_program_school_id( $program_id ) : ( class_exists( 'LTDH_Program_Helpers' ) ? LTDH_Program_Helpers::get_school_id( $program_id ) : 0 );
		if ( $school_id > 0 ) {
			$default_tuition = function_exists( 'get_field' ) ? get_field( 'default_initial_tuition', $school_id ) : '';
			if ( '' === $default_tuition || null === $default_tuition || false === $default_tuition ) {
				$default_tuition = get_post_meta( $school_id, 'default_initial_tuition', true );
			}
			if ( empty( $default_tuition ) ) {
				$default_tuition = get_post_meta( $school_id, 'initial_tuition', true );
			}

			if ( ! empty( $default_tuition ) || ( is_numeric( $default_tuition ) && (float) $default_tuition > 0 ) ) {
				return trim( (string) $default_tuition );
			}

			// School-specific fallback for TMU (Trường Đại học Thương mại)
			$school_code = function_exists( 'ltdh_get_school_code' ) ? ltdh_get_school_code( $school_id ) : get_post_meta( $school_id, 'school_code', true );
			if ( empty( $school_code ) && class_exists( 'LTDH_School_Helpers' ) ) {
				$school_code = LTDH_School_Helpers::get_code( $school_id );
			}
			$school_title = get_the_title( $school_id );

			if ( 'TMU' === strtoupper( trim( (string) $school_code ) ) || false !== mb_stripos( (string) $school_title, 'Thương mại' ) ) {
				return '9.000.000 đ';
			}
		}

		return '';
	}

	public static function get_program_initial_tuition( int $program_id ): string {
		return self::get_initial_tuition( $program_id );
	}

	/**
	 * Format initial tuition into formatted string and raw numeric value.
	 *
	 * @param mixed $tuition
	 * @return array
	 */
	public static function format_initial_tuition( $tuition ): array {
		if ( '' === $tuition || null === $tuition || false === $tuition ) {
			return [ 'display' => '', 'raw' => '' ];
		}
		$str = trim( (string) $tuition );
		if ( '' === $str ) {
			return [ 'display' => '', 'raw' => '' ];
		}

		// Separate extra parenthetical notes if present: e.g. "(15 tín chỉ)"
		$extra = '';
		$main  = $str;
		if ( preg_match( '/^(.*?)\s*(\(.*?\))\s*$/u', $str, $matches ) ) {
			$main  = trim( $matches[1] );
			$extra = ' ' . trim( $matches[2] );
		}

		// Handle "9tr", "9 tr", "9 triệu"
		if ( preg_match( '/^(\d+(?:[.,]\d+)?)\s*(?:tr|triệu|trieu)\b/iu', $main, $m ) ) {
			$val = (float) str_replace( ',', '.', $m[1] ) * 1000000;
			$formatted = number_format( $val, 0, ',', '.' ) . ' đ' . $extra;
			return [
				'display' => $formatted,
				'raw'     => $val,
			];
		}

		// Pure digits e.g. "9000000"
		if ( ctype_digit( $main ) ) {
			return [
				'display' => number_format( (float) $main, 0, ',', '.' ) . ' đ' . $extra,
				'raw'     => (float) $main,
			];
		}

		// Formatted numbers like "9.000.000" or "9.000.000đ" or "9.000.000 đ"
		$clean_num = preg_replace( '/[^\d]/', '', $main );
		if ( is_numeric( $clean_num ) && (float) $clean_num > 0 ) {
			if ( preg_match( '/^[\d.,\s]+(?:đ|vnđ|vnd)?$/iu', $main ) ) {
				return [
					'display' => number_format( (float) $clean_num, 0, ',', '.' ) . ' đ' . $extra,
					'raw'     => (float) $clean_num,
				];
			}
		}

		return [
			'display' => $str,
			'raw'     => $clean_num ?: $str,
		];
	}
}
