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
}
