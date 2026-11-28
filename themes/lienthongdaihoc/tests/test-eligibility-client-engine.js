/**
 * Node.js Unit Test for Client-Side Eligibility Engine (eligibility-engine.js)
 * Tests pure business logic parity between PHP and JS engines.
 */

const assert = require('assert');
const Engine = require('../assets/js/eligibility-engine.js');

let passed = 0;
let failed = 0;

function it(title, fn) {
	try {
		fn();
		passed++;
		console.log('  \x1b[32m[PASS]\x1b[0m ' + title);
	} catch (err) {
		failed++;
		console.log('  \x1b[31m[FAIL]\x1b[0m ' + title);
		console.error('        ' + err.message);
	}
}

console.log('\n===================================================================');
console.log('TEST SUITE: Client-Side Engine (assets/js/eligibility-engine.js)');
console.log('===================================================================');

// 1. Month calculation
it('Calculates correct elapsed months from graduation date', () => {
	// Reference is 11/2026. Graduated 06/2024 -> (2026-2024)*12 + (11-6) = 24 + 5 = 29 months
	const months = Engine.calculateMonthsSinceGraduation(6, 2024, 11, 2026);
	assert.strictEqual(months, 29);
});

// 2. Rule 1: TC without THPT
it('Rule 1: Trung cấp without THPT -> not_eligible under TT 95/2026', () => {
	const res = Engine.evaluateDecisionTree({
		education: 'trung-cap',
		thpt_status: 'chua-co-giay-van-hoa-thpt',
		grad_year: 2024,
		graduation_grade: 'kha',
		major_relation: 'dung-nganh',
		study_time_preference: 'online-100'
	});
	assert.strictEqual(res.decision_status, 'not_eligible');
	assert.strictEqual(res.urgent_deadline, null);
	assert.strictEqual(res.is_2027_blocked, true);
	assert.strictEqual(res.remedy_solution, '');
});

// 3. Rule 1: CĐ without THPT < 24 months
it('Rule 1: Cao đẳng without THPT < 24 months -> not_eligible until >= 24 months', () => {
	const res = Engine.evaluateDecisionTree({
		education: 'cao-dang',
		thpt_status: 'chua-co-giay-van-hoa-thpt',
		grad_month: 6,
		grad_year: 2025, // 17 months
		graduation_grade: 'kha',
		major_relation: 'dung-nganh',
		study_time_preference: 'online-100'
	});
	assert.strictEqual(res.decision_status, 'not_eligible');
	assert.strictEqual(res.urgent_deadline, null);
	assert.strictEqual(res.is_2027_blocked, true);
	assert.strictEqual(res.remedy_solution, '');
	assert.ok(res.months_since_grad < 24);
});

// 4. Rule 1: CĐ without THPT >= 24 months
it('Rule 1: Cao đẳng without THPT >= 24 months -> eligible for both 2026 & 2027', () => {
	const res = Engine.evaluateDecisionTree({
		education: 'cao-dang',
		thpt_status: 'chua-co-giay-van-hoa-thpt',
		grad_month: 6,
		grad_year: 2024, // 29 months
		graduation_grade: 'kha',
		major_relation: 'dung-nganh',
		study_time_preference: 'online-100'
	});
	assert.strictEqual(res.decision_status, 'eligible');
	assert.strictEqual(res.is_2027_blocked, false);
	assert.ok(res.months_since_grad >= 24);
});

// 5. Rule 2: THN
it('Rule 2: THN same major + grade Khá -> eligible', () => {
	const res = Engine.evaluateDecisionTree({
		education: 'trung-hoc-nghe',
		graduation_grade: 'kha',
		major_relation: 'dung-nganh',
		study_time_preference: 'online-100'
	});
	assert.strictEqual(res.decision_status, 'eligible');
});

it('Rule 2: THN wrong major -> not_eligible_2027', () => {
	const res = Engine.evaluateDecisionTree({
		education: 'trung-hoc-nghe',
		graduation_grade: 'kha',
		major_relation: 'trai-nganh',
		study_time_preference: 'online-100'
	});
	assert.strictEqual(res.decision_status, 'not_eligible_2027');
	assert.strictEqual(res.is_2027_blocked, true);
});

// 6. Rule 4: Study Time Preference (TT 56 Article 5)
it('Rule 4: Outside hours/Online excludes Chính quy and cites Article 5 TT 56', () => {
	const res = Engine.evaluateDecisionTree({
		education: 'cao-dang',
		thpt_status: 'co-bang-thpt',
		graduation_grade: 'kha',
		major_relation: 'dung-nganh',
		study_time_preference: 'online-100'
	});
	assert.strictEqual(res.exclude_chinh_quy, true);
	assert.ok(!res.suitable_training_types.includes('chinh-quy'));
	assert.ok(res.suitable_training_types.includes('tu-xa'));
	assert.ok(res.suitable_training_types.includes('vua-hoc-vua-lam'));
	assert.ok(res.time_filter_note.includes('Điều 5 Thông tư 56/2026/TT-BGDĐT'));
});

// 7. Context Conflict 1: Chính quy vs Outside hours
it('Context Conflict 1: Chính quy program + outside hours -> schedule_mismatch conflict & sibling link', () => {
	const context = {
		program_id: 101,
		school_title: 'ĐH Giao thông Vận tải',
		training_type_slug: 'chinh-quy',
		sibling_programs: [
			{
				permalink: '/lien-thong-cong-nghe-thong-tin-tu-xa-utc/',
				type_name: 'Đào tạo từ xa',
				type_slug: 'tu-xa'
			}
		]
	};
	const res = Engine.evaluateDecisionTree({
		education: 'cao-dang',
		thpt_status: 'co-bang-thpt',
		graduation_grade: 'kha',
		major_relation: 'dung-nganh',
		study_time_preference: 'online-100'
	}, context);

	assert.ok(res.context_conflict);
	assert.strictEqual(res.context_conflict.type, 'schedule_mismatch');
	assert.strictEqual(res.context_conflict.action_url, '/lien-thong-cong-nghe-thong-tin-tu-xa-utc/');
});

// 8. Context Conflict 2: Do NOT suggest other schools
it('Context Conflict 2: UTC + student without THPT -> Does NOT suggest other schools', () => {
	const context = {
		school_slug: 'utc',
		school_code: 'UTC',
		school_title: 'Trường Đại học Giao thông Vận tải',
		training_type_slug: 'tu-xa'
	};
	const res = Engine.evaluateDecisionTree({
		education: 'trung-cap',
		thpt_status: 'chua-co-giay-van-hoa-thpt',
		grad_year: 2024,
		graduation_grade: 'kha',
		major_relation: 'dung-nganh',
		study_time_preference: 'online-100'
	}, context);

	assert.strictEqual(res.context_conflict, null);
});

// 9. Context Conflict suppressed when not_eligible
it('Context Conflict: Chính quy program + not_eligible -> does NOT attach schedule_mismatch recommendation', () => {
	const context = {
		program_id: 101,
		school_title: 'ĐH Giao thông Vận tải',
		training_type_slug: 'chinh-quy',
		sibling_programs: [
			{
				permalink: '/lien-thong-cong-nghe-thong-tin-tu-xa-utc/',
				type_name: 'Đào tạo từ xa',
				type_slug: 'tu-xa'
			}
		]
	};
	const res = Engine.evaluateDecisionTree({
		education: 'cao-dang',
		thpt_status: 'chua-co-giay-van-hoa-thpt',
		grad_month: 6,
		grad_year: 2026, // 5 months < 24
		graduation_grade: 'kha',
		major_relation: 'dung-nganh',
		study_time_preference: 'online-100'
	}, context);

	assert.strictEqual(res.decision_status, 'not_eligible');
	assert.strictEqual(res.context_conflict, null);
});

// 10. Education Labels: only Cao đẳng and Trung cấp (no suffixes)
it('Education labels: only returns Cao đẳng and Trung cấp without suffixes', () => {
	assert.strictEqual(Engine.getEducationLabel('cao-dang'), 'Cao đẳng');
	assert.strictEqual(Engine.getEducationLabel('trung-cap'), 'Trung cấp');
});

console.log('\n===================================================================');
console.log(`SUMMARY: ${passed} PASSED, ${failed} FAILED`);
console.log('===================================================================\n');

if (failed > 0) {
	process.exit(1);
} else {
	process.exit(0);
}
