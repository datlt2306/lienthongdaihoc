/**
 * Pure Logic Eligibility Rules Engine (2026–2027 Regulations)
 * Standalone Client-side Module
 *
 * Compliance:
 * - Circular 06/2026/TT-BGDĐT: General University Admission Regulations
 * - Circular 95/2026/TT-BGDĐT: Secondary Vocational (THN) & Cultural High School Knowledge Standards
 * - Circular 56/2026/TT-BGDĐT: Articulation Programs, Schedule & Modality (Article 5)
 * - Circular 52/2026/TT-BGDĐT: Credit Exemption & Equivalent Tuition Standard
 *
 * @package lienthongdaihoc
 */

(function(root, factory) {
	if (typeof define === 'function' && define.amd) {
		define([], factory);
	} else if (typeof module === 'object' && module.exports) {
		module.exports = factory();
	} else {
		root.LtdhEligibilityEngine = factory();
	}
}(typeof self !== 'undefined' ? self : this, function() {
	'use strict';

	var Engine = {};

	/**
	 * Calculate months elapsed from graduation date to reference date.
	 * Default reference date is November 2026 (urgent intake period).
	 */
	Engine.calculateMonthsSinceGraduation = function(gradMonth, gradYear, refMonth, refYear) {
		if (!gradYear || isNaN(gradYear) || parseInt(gradYear, 10) <= 0) {
			return 0;
		}
		var gYear = parseInt(gradYear, 10);
		var gMonth = (gradMonth && !isNaN(gradMonth) && parseInt(gradMonth, 10) >= 1 && parseInt(gradMonth, 10) <= 12)
			? parseInt(gradMonth, 10)
			: 6; // default mid-year

		var rYear = (refYear && !isNaN(refYear)) ? parseInt(refYear, 10) : 2026;
		var rMonth = (refMonth && !isNaN(refMonth)) ? parseInt(refMonth, 10) : 11; // November 2026

		var months = (rYear - gYear) * 12 + (rMonth - gMonth);
		return Math.max(0, months);
	};

	/**
	 * Core Decision Tree Evaluation.
	 *
	 * @param {Object} input Form input values
	 * @param {Object} context Page context (school, major, training type)
	 * @return {Object} Evaluated decision object
	 */
	Engine.evaluateDecisionTree = function(input, context) {
		input = input || {};
		context = context || {};

		var education       = input.education || '';
		var thptStatus      = input.thpt_status || '';
		var gradMonth       = input.grad_month ? parseInt(input.grad_month, 10) : null;
		var gradYear        = (input.grad_year || input.graduation) ? parseInt(input.grad_year || input.graduation, 10) : null;
		var gradGrade       = input.graduation_grade || 'kha';
		var majorRelation   = input.major_relation || 'dung-nganh';
		var timePreference  = input.study_time_preference || 'online-100';
		var desiredMajor    = input.desired_major ? parseInt(input.desired_major, 10) : 0;

		var monthsSinceGrad = Engine.calculateMonthsSinceGraduation(gradMonth, gradYear);

		// Arts / Music specialty check (special 24-month exemption)
		var isArtsSpecialty = false;
		if (context.major_slug) {
			var artsSlugs = ['am-nhac', 'thanh-nhac', 'bieu-dien-nhac-cu', 'nghe-thuat-bieu-dien', 'hoi-hoa', 'dien-vien'];
			if (artsSlugs.indexOf(context.major_slug) !== -1) {
				isArtsSpecialty = true;
			}
		}

		// RULE 4: Filter Suitable Training Types based on Study Time Preference (TT 56/2026)
		var suitableTrainingTypes = [];
		var timeFilterNote = '';
		var excludeChinhQuy = false;

		if (timePreference === 'ngoai-gio-cuoi-tuan' || timePreference === 'online-100') {
			excludeChinhQuy = true;
			suitableTrainingTypes = ['vua-hoc-vua-lam', 'tu-xa'];
			timeFilterNote = 'Theo Điều 5 Thông tư 56/2026/TT-BGDĐT, hệ Chính quy liên thông bắt buộc học trực tiếp tại trường trong giờ hành chính trên 50% khối lượng chương trình. Do bạn có quỹ thời gian ngoài giờ/online, hệ thống tự động loại bỏ hệ Chính quy và gợi ý tối ưu hệ Vừa làm vừa học hoặc Đào tạo từ xa.';
		} else {
			suitableTrainingTypes = ['chinh-quy', 'vua-hoc-vua-lam', 'tu-xa'];
			timeFilterNote = 'Quỹ thời gian của bạn phù hợp với cả 3 hệ: Chính quy tập trung ban ngày tại trường, Vừa làm vừa học (buổi tối/cuối tuần) và Đào tạo từ xa (100% online).';
		}

		var creditTransferNote = 'Theo Thông tư 52/2026/TT-BGDĐT & Thông tư 56/2026/TT-BGDĐT, tất cả các hệ đào tạo (Chính quy, Vừa học vừa làm, Đào tạo từ xa) đều học chung khung chương trình chuẩn, giá trị văn bằng và đơn giá học phí tương đương; sinh viên được Hội đồng nhà trường xét miễn trừ tín chỉ cá nhân hóa theo bảng điểm gốc.';

		var legalBasis = [
			'Thông tư 06/2026/TT-BGDĐT (Quy chế tuyển sinh đại học)',
			'Thông tư 95/2026/TT-BGDĐT (Chuẩn đầu vào khối lượng văn hóa THPT & Trung học nghề)',
			'Thông tư 56/2026/TT-BGDĐT (Quy chế đào tạo liên thông & hình thức đào tạo)',
			'Thông tư 52/2026/TT-BGDĐT (Công nhận văn bằng & miễn trừ tín chỉ cá nhân hóa)'
		];

		var decision = {
			education: education,
			thpt_status: thptStatus,
			grad_month: gradMonth,
			grad_year: gradYear,
			months_since_grad: monthsSinceGrad,
			graduation_grade: gradGrade,
			major_relation: majorRelation,
			study_time_preference: timePreference,
			suitable_training_types: suitableTrainingTypes,
			exclude_chinh_quy: excludeChinhQuy,
			time_filter_note: timeFilterNote,
			credit_transfer_note: creditTransferNote,
			legal_basis: legalBasis,
			decision_status: 'eligible', // 'eligible' | 'warning_urgent' | 'not_eligible_2027'
			badge_color: 'emerald',
			badge_text: 'Đủ điều kiện xét tuyển',
			status_label: 'Đủ điều kiện',
			decision_title: 'Hồ sơ đủ điều kiện tuyển sinh liên thông',
			decision_summary: '',
			urgent_deadline: null,
			remedy_solution: '',
			priority_level: 'standard', // 'urgent_dec2026' | 'standard' | 'remedy_needed'
			is_2027_blocked: false,
			urgent_2026_available: false,
			context_conflict: null
		};

		// ----------------------------------------------------
		// RULE 1: Nhóm CHƯA có bằng THPT & CHƯA có Giấy hoàn thành văn hóa THPT
		// ----------------------------------------------------
		if ((education === 'trung-cap' || education === 'cao-dang') && thptStatus === 'chua-co-giay-van-hoa-thpt') {
			if (education === 'trung-cap') {
				decision.decision_status       = 'not_eligible';
				decision.badge_color           = 'rose';
				decision.badge_text            = 'Chưa đủ điều kiện xét tuyển';
				decision.status_label          = 'Chưa đủ điều kiện';
				decision.priority_level        = 'standard';
				decision.urgent_deadline       = null;
				decision.is_2027_blocked       = true;
				decision.urgent_2026_available = false;
				decision.decision_title        = 'Chưa đủ điều kiện xét tuyển liên thông Đại học';
				decision.decision_summary      = 'Theo quy định chuẩn đầu vào của Bộ GD&ĐT (Thông tư 95/2026/TT-BGDĐT), người tốt nghiệp Trung cấp bắt buộc phải có bằng tốt nghiệp THPT hoặc Giấy chứng nhận hoàn thành đủ khối lượng kiến thức văn hóa THPT mới đủ điều kiện đăng ký xét tuyển liên thông lên đại học.';
				decision.remedy_solution       = '';
			} else if (education === 'cao-dang') {
				if (monthsSinceGrad >= 24 || isArtsSpecialty) {
					decision.decision_status  = 'eligible';
					decision.badge_color      = 'emerald';
					decision.badge_text       = 'Đủ điều kiện xét tuyển (Đã tốt nghiệp ≥ 24 tháng)';
					decision.status_label     = 'Đủ điều kiện';
					decision.priority_level   = 'standard';
					decision.urgent_deadline  = null;
					decision.decision_title   = 'Đủ điều kiện dự tuyển liên thông Đại học';
					decision.decision_summary = 'Bằng Cao đẳng của bạn đã được cấp ' + (monthsSinceGrad > 0 ? ('khoảng ' + monthsSinceGrad + ' tháng') : 'trên 24 tháng') + ' (đạt mốc ≥ 24 tháng theo quy chế tuyển sinh của Bộ GD&ĐT)' + (isArtsSpecialty ? ' hoặc thuộc nhóm ngành Âm nhạc/Nghệ thuật' : '') + '. Bạn đủ điều kiện đăng ký xét tuyển liên thông đại học bình thường.';
					decision.remedy_solution  = '';
				} else {
					decision.decision_status       = 'not_eligible';
					decision.badge_color           = 'rose';
					decision.badge_text            = 'Chưa đủ mốc 24 tháng theo quy chế';
					decision.status_label          = 'Chưa đủ điều kiện';
					decision.priority_level        = 'standard';
					decision.urgent_deadline       = null;
					decision.is_2027_blocked       = true;
					decision.urgent_2026_available = false;
					decision.decision_title        = 'Chưa đủ điều kiện xét tuyển (Chưa đủ mốc 24 tháng)';
					decision.decision_summary      = 'Theo quy chế tuyển sinh của Bộ GD&ĐT, người tốt nghiệp Cao đẳng nhưng chưa có bằng tốt nghiệp THPT bắt buộc phải có thời gian từ khi công nhận tốt nghiệp đến khi dự tuyển từ đủ 24 tháng trở lên (bằng của bạn hiện mới cấp khoảng ' + monthsSinceGrad + ' tháng). Bạn cần chờ đủ thời gian 24 tháng hoặc bổ sung Giấy chứng nhận hoàn thành đủ khối lượng kiến thức văn hóa THPT để đủ điều kiện xét tuyển.';
					decision.remedy_solution       = '';
				}
			}
		}
		// ----------------------------------------------------
		// RULE 2: Nhóm tốt nghiệp TRUNG HỌC NGHỀ (THN) (Thông tư 95/2026 & 52/2026)
		// ----------------------------------------------------
		else if (education === 'trung-hoc-nghe') {
			var isGoodGrade = (gradGrade === 'xuat-sac-gioi' || gradGrade === 'kha');
			var isSameMajor = (majorRelation === 'dung-nganh');

			if (isSameMajor && isGoodGrade) {
				decision.decision_status  = 'eligible';
				decision.badge_color      = 'emerald';
				decision.badge_text       = 'Đủ điều kiện xét tuyển THN (Thông tư 95/2026)';
				decision.status_label     = 'Đủ điều kiện';
				decision.priority_level   = 'standard';
				decision.decision_title   = 'Đủ điều kiện dự tuyển liên thông Đại học từ 01/01/2027';
				decision.decision_summary = 'Chúc mừng! Hồ sơ Trung học nghề (THN) của bạn đáp ứng đầy đủ và đồng thời 2 tiêu chuẩn cốt lõi theo Thông tư 95/2026/TT-BGDĐT: <b>1) Đúng nhóm ngành đào tạo</b> VÀ <b>2) Học lực xếp loại từ Khá trở lên</b> (áp dụng cho chương trình đào tạo nghề 3 năm). Bạn hoàn toàn đủ điều kiện nộp hồ sơ xét tuyển liên thông thẳng lên Đại học.';
				decision.remedy_solution  = 'Khuyến nghị: Chuẩn bị sớm bằng tốt nghiệp THN, học bạ và bảng điểm toàn khóa để tham gia xét tuyển ngay từ đợt tuyển sinh đầu năm 2027.';
			} else {
				decision.decision_status  = 'not_eligible_2027';
				decision.badge_color      = 'rose';
				decision.badge_text       = 'Chưa đạt chuẩn đầu vào THN (Thông tư 95/2026)';
				decision.status_label     = 'Chưa đủ điều kiện';
				decision.priority_level   = 'remedy_needed';
				decision.is_2027_blocked  = true;
				decision.decision_title   = 'Chưa đạt ngưỡng đầu vào chuẩn liên thông Đại học từ Trung học nghề';

				var reasonDetails = [];
				if (majorRelation === 'trai-nganh') reasonDetails.push('đăng ký trái nhóm ngành');
				if (!isGoodGrade) reasonDetails.push('xếp loại tốt nghiệp ở mức Trung bình / Trung bình khá');
				var reasonsStr = reasonDetails.join(' và ');

				decision.decision_summary = 'Theo quy định tại Thông tư 95/2026/TT-BGDĐT, thí sinh tốt nghiệp Trung học nghề (THN) muốn liên thông trực tiếp lên Đại học từ năm 2027 bắt buộc phải đáp ứng đồng thời 2 điều kiện: Cùng nhóm ngành đào tạo VÀ Xếp loại tốt nghiệp từ Khá trở lên. Hồ sơ của bạn hiện ' + reasonsStr + ' nên chưa đạt ngưỡng tuyển sinh chuẩn.';
				decision.remedy_solution  = 'Giải pháp khắc phục tối ưu:<br>1. <b>Chọn học đúng nhóm ngành:</b> Chuyển nguyện vọng sang ngành đúng với chuyên môn Trung học nghề để đủ điều kiện xét tuyển thẳng.<br>2. <b>Lộ trình qua Cao đẳng:</b> Học liên thông lấy bằng Cao đẳng (thời gian khoảng 1 năm) trước khi liên thông Đại học để được mở rộng ngành nghề và không bị giới hạn xếp loại.<br>3. <b>Bổ sung kiến thức văn hóa THPT:</b> Học lấy Giấy chứng nhận hoàn thành đủ khối lượng văn hóa THPT để xét tuyển diện thí sinh tự do.';
			}
		}
		// ----------------------------------------------------
		// RULE 3: Nhóm ĐÃ CÓ bằng THPT (hoặc Giấy hoàn thành văn hóa THPT) + Trung cấp / Cao đẳng
		// ----------------------------------------------------
		else if (thptStatus === 'co-bang-thpt' || thptStatus === 'co-giay-van-hoa-thpt') {
			var isUnrelatedOrLowGrade = (majorRelation === 'trai-nganh' || gradGrade === 'trung-binh');

			if (isUnrelatedOrLowGrade) {
				decision.decision_status  = 'warning_urgent';
				decision.badge_color      = 'amber';
				decision.badge_text       = 'Đủ điều kiện • Cần kiểm tra điểm sàn & học bổ sung';
				decision.status_label     = 'Đủ điều kiện có lưu ý';
				decision.priority_level   = 'standard';
				decision.decision_title   = 'Đủ điều kiện dự tuyển liên thông (Có lưu ý xét tuyển)';

				var reasons = [];
				if (majorRelation === 'trai-nganh') reasons.push('đăng ký học khác nhóm ngành (trái ngành)');
				if (gradGrade === 'trung-binh') reasons.push('bằng xếp loại Trung bình / Trung bình khá');
				var reasonsText = reasons.join(' và ');

				decision.decision_summary = 'Về mặt pháp lý văn bằng, bạn HOÀN TOÀN ĐỦ ĐIỀU KIỆN đăng ký xét tuyển liên thông đại học. Tuy nhiên do hồ sơ của bạn ' + reasonsText + ', cần kiểm tra ngưỡng điểm sàn đầu vào của từng trường và có thể phải học bổ sung kiến thức nền tảng (theo Thông tư 95/2026 & Thông tư 52/2026/TT-BGDĐT).';
				decision.remedy_solution  = 'Gợi ý: Hãy gửi kèm bảng điểm gốc khi đăng ký tư vấn để Ban tuyển sinh thẩm định số môn cần học bổ sung và hướng dẫn chọn trường có chính sách miễn giảm môn học ưu đãi nhất.';
			} else {
				decision.decision_status  = 'eligible';
				decision.badge_color      = 'emerald';
				decision.badge_text       = 'Đủ điều kiện xét tuyển ưu tiên 100%';
				decision.status_label     = 'Đủ điều kiện';
				decision.priority_level   = 'standard';
				decision.decision_title   = 'Hồ sơ đáp ứng hoàn hảo tiêu chuẩn tuyển sinh liên thông';
				decision.decision_summary = 'Hồ sơ học vấn của bạn hoàn toàn đủ điều kiện dự tuyển vào tất cả các chương trình liên thông đại học theo quy chế mới của Bộ GD&ĐT. Với trình độ hiện tại cùng bằng THPT/chứng nhận văn hóa và học lực tốt, bạn thuộc diện được ưu tiên xét tuyển hồ sơ sớm và tối đa hóa số tín chỉ được xét miễn giảm.';
			}
		}

		// ----------------------------------------------------
		// CONTEXT-AWARE CONFLICT RESOLUTION & CROSS-RECOMMENDATIONS
		// ----------------------------------------------------
		Engine.applyContextResolution(decision, input, context);

		return decision;
	};

	/**
	 * Apply Context-aware checks (Single Program Page & Specific University Rules)
	 */
	Engine.applyContextResolution = function(decision, input, context) {
		if (!context) return;

		var schoolSlug  = (context.school_slug || '').toLowerCase();
		var schoolCode  = (context.school_code || '').toUpperCase();
		var schoolTitle = context.school_title || '';
		var systemSlug  = (context.training_type_slug || '').toLowerCase();
		var siblings    = context.sibling_programs || [];
		var timePref    = input.study_time_preference || 'online-100';
		var thptStatus  = input.thpt_status || '';
		var education   = input.education || '';
		var months      = decision.months_since_grad || 0;

		var isUtc = (
			schoolSlug.indexOf('utc') !== -1 ||
			schoolSlug.indexOf('giao-thong-van-tai') !== -1 ||
			schoolCode === 'UTC' ||
			schoolCode === 'GHA' ||
			schoolTitle.toLowerCase().indexOf('giao thông vận tải') !== -1
		);

		// Conflict 1: Trang Chính quy nhưng học viên chọn học ngoài giờ / Online 100% (Điều 5 TT 56/2026)
		if (systemSlug === 'chinh-quy' && (timePref === 'ngoai-gio-cuoi-tuan' || timePref === 'online-100')) {
			var suggestedLink = '';
			var suggestedName = '';

			for (var i = 0; i < siblings.length; i++) {
				var sSlug = (siblings[i].type_slug || '').toLowerCase();
				if (sSlug.indexOf('tu-xa') !== -1 || sSlug.indexOf('vua-hoc-vua-lam') !== -1) {
					suggestedLink = siblings[i].permalink || '';
					suggestedName = siblings[i].type_name || (sSlug.indexOf('tu-xa') !== -1 ? 'Đào tạo từ xa' : 'Vừa làm vừa học');
					if (timePref === 'online-100' && sSlug.indexOf('tu-xa') !== -1) {
						break;
					}
				}
			}

			decision.context_conflict = {
				type: 'schedule_mismatch',
				severity: 'warning',
				title: 'Lưu ý về hình thức đào tạo của trang bạn đang xem',
				message: 'Chương trình bạn đang xem thuộc <b>Hệ Chính quy</b>. Theo Điều 5 Thông tư 56/2026/TT-BGDĐT, hệ Chính quy liên thông bắt buộc học trực tiếp tại trường trong giờ hành chính (>50% khối lượng chương trình), không phù hợp với quỹ thời gian ngoài giờ/online của bạn.',
				action_text: suggestedName ? ('Chuyển sang xem hệ ' + suggestedName + ' cùng ngành') : 'Xem các hệ đào tạo linh hoạt',
				action_url: suggestedLink || '#training-modes',
				recommendation: 'Bạn hoàn toàn đủ điều kiện học đại học nhưng nên chuyển sang hệ Vừa làm vừa học hoặc Đào tạo từ xa để phù hợp với thời gian đi làm.'
			};
		}
	};

	Engine.getEducationLabel = function(slug) {
		var map = {
			'cao-dang':  'Cao đẳng',
			'trung-cap': 'Trung cấp'
		};
		return map[slug] || slug;
	};

	Engine.getThptStatusLabel = function(slug) {
		var map = {
			'co-bang-thpt':              'Đã có bằng tốt nghiệp THPT',
			'co-giay-van-hoa-thpt':       'Chưa có bằng THPT, nhưng đã có Giấy chứng nhận hoàn thành đủ khối lượng kiến thức văn hóa THPT',
			'chua-co-giay-van-hoa-thpt': 'Chưa có bằng THPT và chưa có Giấy chứng nhận hoàn thành kiến thức văn hóa THPT'
		};
		return map[slug] || slug;
	};

	Engine.getGradGradeLabel = function(slug) {
		var map = {
			'xuat-sac-gioi': 'Xuất sắc / Giỏi',
			'kha':           'Khá',
			'trung-binh':    'Trung bình khá / Trung bình'
		};
		return map[slug] || slug;
	};

	Engine.getMajorRelationLabel = function(slug) {
		var map = {
			'dung-nganh': 'Đúng ngành / Cùng nhóm ngành',
			'nganh-gan':  'Ngành gần',
			'trai-nganh': 'Khác nhóm ngành (Trái ngành)'
		};
		return map[slug] || slug;
	};

	Engine.getTimePrefLabel = function(slug) {
		var map = {
			'gio-hanh-chinh':      'Học tập trung giờ hành chính ban ngày tại trường',
			'ngoai-gio-cuoi-tuan': 'Chỉ học được ngoài giờ hành chính / Cuối tuần',
			'online-100':          'Học trực tuyến (Online 100% qua LMS, thi tập trung)'
		};
		return map[slug] || slug;
	};

	return Engine;
}));
