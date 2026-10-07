<?php
/**
 * Template Name: Chính sách bảo mật
 *
 * @package lienthongdaihoc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$hotline      = function_exists( 'ltdh_get_hotline' ) ? ltdh_get_hotline() : '0988 991 496';
$email        = function_exists( 'ltdh_get_email' ) ? ltdh_get_email() : 'tuvan@lienthongdaihoc.com';
$address      = function_exists( 'ltdh_get_address' ) ? ltdh_get_address() : 'Số 71 ngõ 32/84 Đỗ Đức Dục, Mễ Trì, Nam Từ Liêm, Hà Nội';
$zalo         = function_exists( 'ltdh_get_zalo_url' ) ? ltdh_get_zalo_url() : 'https://zalo.me';
$company_name = function_exists( 'ltdh_get_company_name' ) ? ltdh_get_company_name() : 'Cổng thông tin Tuyển sinh lienthongdaihoc.com';
?>

<!-- BANNER HERO -->
<section class="relative bg-gradient-to-tr from-[#0E2038] via-[#002B66] to-[#001838] text-white py-16 md:py-20 overflow-hidden border-b border-slate-800">
	<div class="absolute inset-0 opacity-10 pointer-events-none" style="background-image: radial-gradient(white 1.5px, transparent 1.5px); background-size: 24px 24px;"></div>
	<div class="absolute -right-24 -bottom-24 w-80 h-80 bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>

	<div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
		<!-- Breadcrumb -->
		<nav class="flex items-center gap-2 text-xs text-slate-400 mb-4 font-semibold uppercase tracking-wider">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="hover:text-white transition-colors">Trang chủ</a>
			<span>/</span>
			<span class="text-blue-300">Chính sách bảo mật</span>
		</nav>

		<div class="max-w-3xl space-y-3">
			<span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-blue-500/20 text-blue-300 border border-blue-400/30">
				<span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
				Tuân thủ Nghị định 13/2023/NĐ-CP
			</span>
			<h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold font-display tracking-tight text-white leading-tight">
				CHÍNH SÁCH BẢO MẬT THÔNG TIN
			</h1>
			<p class="text-slate-300 text-sm md:text-base leading-relaxed">
				Cam kết bảo vệ tuyệt đối dữ liệu cá nhân, quyền riêng tư của học viên và người truy cập tại hệ thống <span class="text-white font-bold">lienthongdaihoc.com</span>.
			</p>
			<div class="pt-2 flex flex-wrap items-center gap-4 text-xs text-slate-400">
				<span>Cập nhật lần cuối: <strong>Tháng 10/2026</strong></span>
				<span>•</span>
				<span>Phạm vi: Toàn bộ hệ thống lienthongdaihoc.com</span>
			</div>
		</div>
	</div>
</section>

<!-- MAIN CONTENT WRAPPER -->
<main id="primary" class="site-main py-12 bg-slate-50">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
		<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

			<!-- MAIN CONTENT BODY (8/12) -->
			<div class="lg:col-span-8">
				<article class="bg-white rounded-2xl shadow-sm border border-slate-200/90 p-6 sm:p-10 space-y-8">

					<?php
					$has_custom_content = false;
					if ( have_posts() ) {
						while ( have_posts() ) {
							the_post();
							$content = get_the_content();
							if ( ! empty( trim( $content ) ) ) {
								$has_custom_content = true;
								?>
								<div class="prose prose-slate max-w-none text-slate-700 leading-relaxed">
									<?php the_content(); ?>
								</div>
								<?php
							}
						}
					}

					// Render complete legal policy if no custom content in DB
					if ( ! $has_custom_content ) :
					?>

					<!-- Alert Box -->
					<div class="p-4 rounded-xl bg-blue-50/80 border border-blue-200 text-blue-900 text-sm leading-relaxed flex items-start gap-3">
						<svg class="w-5 h-5 text-blue-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
						</svg>
						<div>
							<strong>Thông báo quan trọng:</strong> Khi quý học viên, phụ huynh hoặc người dùng truy cập, đăng ký nhận tư vấn tuyển sinh hoặc sử dụng bất kỳ tiện ích tra cứu nào trên website <strong>lienthongdaihoc.com</strong>, đồng nghĩa với việc quý vị đã đọc kỹ, hiểu rõ và hoàn toàn đồng thuận với toàn bộ các điều khoản trong Chính sách bảo mật này.
						</div>
					</div>

					<!-- Section 1 -->
					<section id="muc-1" class="scroll-mt-24 space-y-3">
						<h2 class="text-xl sm:text-2xl font-black text-slate-900 flex items-center gap-3 border-b border-slate-100 pb-3">
							<span class="flex items-center justify-center w-7 h-7 rounded-lg bg-blue-600 text-white text-xs font-bold shrink-0">1</span>
							Mục đích và phạm vi áp dụng
						</h2>
						<p class="text-slate-600 text-sm sm:text-base leading-relaxed">
							Chính sách bảo mật này quy định cách thức mà Ban quản trị <strong>lienthongdaihoc.com</strong> (sau đây gọi là "Chúng tôi", "Hệ thống" hoặc "Website") thu thập, lưu trữ, xử lý, bảo vệ và sử dụng dữ liệu cá nhân của người dùng, học viên quan tâm đến các chương trình đào tạo liên thông Đại học, Đại học từ xa, Đại học vừa học vừa làm và Văn bằng 2.
						</p>
						<p class="text-slate-600 text-sm sm:text-base leading-relaxed">
							Chính sách này được xây dựng và thực thi trên cơ sở tuân thủ nghiêm ngặt các quy định pháp luật hiện hành của nước Cộng hòa Xã hội Chủ nghĩa Việt Nam, bao gồm:
						</p>
						<ul class="list-disc list-inside space-y-1.5 text-slate-600 text-sm pl-2">
							<li><strong>Nghị định 13/2023/NĐ-CP</strong> của Chính phủ về Bảo vệ dữ liệu cá nhân;</li>
							<li><strong>Luật An toàn thông tin mạng số 86/2015/QH13</strong>;</li>
							<li><strong>Luật An ninh mạng số 24/2018/QH14</strong>;</li>
							<li><strong>Luật Công nghệ thông tin số 67/2006/QH11</strong> và các văn bản hướng dẫn liên quan.</li>
						</ul>
					</section>

					<!-- Section 2 -->
					<section id="muc-2" class="scroll-mt-24 space-y-3">
						<h2 class="text-xl sm:text-2xl font-black text-slate-900 flex items-center gap-3 border-b border-slate-100 pb-3">
							<span class="flex items-center justify-center w-7 h-7 rounded-lg bg-blue-600 text-white text-xs font-bold shrink-0">2</span>
							Loại dữ liệu cá nhân thu thập
						</h2>
						<p class="text-slate-600 text-sm sm:text-base leading-relaxed">
							Tùy thuộc vào hành vi tương tác và nhu cầu tư vấn của quý khách trên website, chúng tôi có thể thu thập các nhóm thông tin sau:
						</p>
						<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
							<div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 space-y-2">
								<h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
									<span class="w-2 h-2 rounded-full bg-blue-600"></span>
									Dữ liệu cá nhân cơ bản
								</h3>
								<ul class="text-xs text-slate-600 space-y-1 leading-relaxed">
									<li>• Họ và tên người có nguyện vọng học</li>
									<li>• Số điện thoại liên hệ (Di động / Zalo)</li>
									<li>• Địa chỉ hòm thư điện tử (Email)</li>
									<li>• Tỉnh / Thành phố đang sinh sống hoặc làm việc</li>
								</ul>
							</div>
							<div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 space-y-2">
								<h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
									<span class="w-2 h-2 rounded-full bg-orange-500"></span>
									Dữ liệu học vấn & nguyện vọng
								</h3>
								<ul class="text-xs text-slate-600 space-y-1 leading-relaxed">
									<li>• Trình độ học vấn hiện tại (THPT, Trung cấp, CĐ, ĐH)</li>
									<li>• Chuyên ngành đã tốt nghiệp trước đó</li>
									<li>• Ngành học đăng ký liên thông / văn bằng 2</li>
									<li>• Trường đại học mong muốn nộp hồ sơ xét tuyển</li>
									<li>• Hình thức học mong muốn (Từ xa 100%, Vừa học vừa làm)</li>
								</ul>
							</div>
						</div>
						<div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 space-y-2 mt-2">
							<h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
								<span class="w-2 h-2 rounded-full bg-slate-500"></span>
								Dữ liệu kỹ thuật số & nhật ký duyệt web (Tự động)
							</h3>
							<p class="text-xs text-slate-600 leading-relaxed">
								Khi quý vị truy cập website, hệ thống máy chủ tự động ghi nhận các thông tin kỹ thuật tiêu chuẩn như: Địa chỉ IP, loại trình duyệt web, hệ điều hành thiết bị, nguồn truy cập (trực tiếp, tìm kiếm hay mạng xã hội), thời gian và các trang nội dung được xem trên website. Dữ liệu này chỉ phục vụ mục đích kiểm soát an ninh hệ thống và phân tích tối ưu hiệu năng trang web.
							</p>
						</div>
					</section>

					<!-- Section 3 -->
					<section id="muc-3" class="scroll-mt-24 space-y-3">
						<h2 class="text-xl sm:text-2xl font-black text-slate-900 flex items-center gap-3 border-b border-slate-100 pb-3">
							<span class="flex items-center justify-center w-7 h-7 rounded-lg bg-blue-600 text-white text-xs font-bold shrink-0">3</span>
							Mục đích thu thập và xử lý dữ liệu
						</h2>
						<p class="text-slate-600 text-sm sm:text-base leading-relaxed">
							Mọi thông tin cá nhân do người dùng cung cấp chỉ được sử dụng cho các mục đích hợp pháp, minh bạch sau đây:
						</p>
						<ul class="space-y-2 text-slate-600 text-sm sm:text-base">
							<li class="flex items-start gap-2.5">
								<span class="text-blue-600 font-bold shrink-0">✓</span>
								<span><strong>Tư vấn tuyển sinh chuyên sâu miễn phí:</strong> Cán bộ tư vấn liên hệ trực tiếp qua điện thoại hoặc Zalo để giải đáp thắc mắc về điều kiện xét tuyển, lộ trình chuyển đổi tín chỉ, thời gian đào tạo và mức học phí chi tiết.</span>
							</li>
							<li class="flex items-start gap-2.5">
								<span class="text-blue-600 font-bold shrink-0">✓</span>
								<span><strong>Hỗ trợ chuẩn bị và tiếp nhận hồ sơ:</strong> Hướng dẫn học viên các biểu mẫu, giấy tờ chứng thực văn bằng cần thiết theo đúng quy chế tuyển sinh của Bộ GD&ĐT và Trường Đại học ứng tuyển.</span>
							</li>
							<li class="flex items-start gap-2.5">
								<span class="text-blue-600 font-bold shrink-0">✓</span>
								<span><strong>Gửi thông báo và lịch khai giảng:</strong> Cung cấp kịp thời thông tin về thời hạn nhận hồ sơ, lịch nhập học, chính sách học bổng hoặc thay đổi kế hoạch đào tạo từ các trường đối tác.</span>
							</li>
							<li class="flex items-start gap-2.5">
								<span class="text-blue-600 font-bold shrink-0">✓</span>
								<span><strong>Nâng cấp trải nghiệm người dùng:</strong> Đánh giá số liệu thống kê ẩn danh về nhu cầu ngành học nhằm tối ưu hóa công cụ tra cứu, bài viết hướng dẫn trên website.</span>
							</li>
							<li class="flex items-start gap-2.5">
								<span class="text-blue-600 font-bold shrink-0">✓</span>
								<span><strong>Bảo vệ an ninh hệ thống:</strong> Phát hiện và ngăn chặn các hành vi giả mạo thông tin, phát tán tin rác (spam) hoặc phá hoại cơ sở dữ liệu.</span>
							</li>
						</ul>
					</section>

					<!-- Section 4 -->
					<section id="muc-4" class="scroll-mt-24 space-y-3">
						<h2 class="text-xl sm:text-2xl font-black text-slate-900 flex items-center gap-3 border-b border-slate-100 pb-3">
							<span class="flex items-center justify-center w-7 h-7 rounded-lg bg-blue-600 text-white text-xs font-bold shrink-0">4</span>
							Phương thức và quy trình thu thập dữ liệu
						</h2>
						<p class="text-slate-600 text-sm sm:text-base leading-relaxed">
							Dữ liệu của quý vị được thu thập thông qua các phương thức minh bạch sau:
						</p>
						<ol class="list-decimal list-inside space-y-2 text-slate-600 text-sm sm:text-base pl-2">
							<li><strong>Người dùng chủ động cung cấp:</strong> Khi điền biểu mẫu "Nhận tư vấn miễn phí", "Kiểm tra điều kiện tuyển sinh", "Đăng ký hồ sơ trực tuyến" hoặc liên hệ trực tiếp qua Hotline, Chat Zalo, Messenger, Email.</li>
							<li><strong>Thu thập gián tiếp qua công nghệ trình duyệt:</strong> Thông qua cookies và công nghệ phân tích người dùng (như Google Analytics) khi quý vị lướt xem các bài viết, chuyên ngành trên website.</li>
						</ol>
					</section>

					<!-- Section 5 -->
					<section id="muc-5" class="scroll-mt-24 space-y-3">
						<h2 class="text-xl sm:text-2xl font-black text-slate-900 flex items-center gap-3 border-b border-slate-100 pb-3">
							<span class="flex items-center justify-center w-7 h-7 rounded-lg bg-blue-600 text-white text-xs font-bold shrink-0">5</span>
							Phạm vi chia sẻ và chuyển giao dữ liệu
						</h2>
						<div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-sm leading-relaxed font-medium">
							<strong>Nguyên tắc vàng:</strong> Chúng tôi <u>CAM KẾT KHÔNG</u> bán, cho thuê, chia sẻ hoặc tiết lộ thông tin cá nhân của người học cho bất kỳ bên thứ ba độc lập nào nhằm mục đích thương mại, quảng cáo ngoài dịch vụ giáo dục đào tạo.
						</div>
						<p class="text-slate-600 text-sm sm:text-base leading-relaxed">
							Thông tin cá nhân chỉ được chia sẻ trong các trường hợp giới hạn sau:
						</p>
						<ul class="list-disc list-inside space-y-1.5 text-slate-600 text-sm pl-2">
							<li><strong>Cán bộ tư vấn và quản lý nội bộ:</strong> Chỉ nhân sự được phân công nhiệm vụ hỗ trợ tuyển sinh mới có quyền tiếp cận thông tin để phục vụ học viên.</li>
							<li><strong>Ban Tuyển sinh / Phòng Đào tạo của Trường Đại học đối tác:</strong> Khi người học có nguyện vọng nộp hồ sơ xét tuyển chính thức vào Trường, dữ liệu liên quan sẽ được chuyển tiếp an toàn để hoàn tất thủ tục nhập học theo đúng thẩm quyền.</li>
							<li><strong>Yêu cầu pháp lý:</strong> Cung cấp cho các cơ quan thực thi pháp luật hoặc cơ quan Nhà nước có thẩm quyền khi có yêu cầu bằng văn bản chính thức theo quy định của pháp luật Việt Nam.</li>
						</ul>
					</section>

					<!-- Section 6 -->
					<section id="muc-6" class="scroll-mt-24 space-y-3">
						<h2 class="text-xl sm:text-2xl font-black text-slate-900 flex items-center gap-3 border-b border-slate-100 pb-3">
							<span class="flex items-center justify-center w-7 h-7 rounded-lg bg-blue-600 text-white text-xs font-bold shrink-0">6</span>
							Thời gian lưu trữ dữ liệu
						</h2>
						<p class="text-slate-600 text-sm sm:text-base leading-relaxed">
							Dữ liệu cá nhân của người dùng sẽ được lưu trữ an toàn trên máy chủ của chúng tôi cho đến khi:
						</p>
						<ul class="list-disc list-inside space-y-1 text-slate-600 text-sm pl-2">
							<li>Đã hoàn thành toàn bộ mục đích tư vấn, xét tuyển và hỗ trợ nhập học của người dùng;</li>
							<li>Người dùng gửi yêu cầu hủy bỏ, xóa hoặc rút lại sự đồng ý xử lý thông tin cá nhân;</li>
							<li>Dữ liệu nhật ký máy chủ (logs) được lưu trữ định kỳ từ 12 đến 24 tháng theo tiêu chuẩn an toàn an ninh mạng.</li>
						</ul>
					</section>

					<!-- Section 7 -->
					<section id="muc-7" class="scroll-mt-24 space-y-3">
						<h2 class="text-xl sm:text-2xl font-black text-slate-900 flex items-center gap-3 border-b border-slate-100 pb-3">
							<span class="flex items-center justify-center w-7 h-7 rounded-lg bg-blue-600 text-white text-xs font-bold shrink-0">7</span>
							Quyền và nghĩa vụ của chủ thể dữ liệu (Nghị định 13/2023/NĐ-CP)
						</h2>
						<p class="text-slate-600 text-sm sm:text-base leading-relaxed">
							Theo Nghị định 13/2023/NĐ-CP, quý học viên và người dùng được hưởng đầy đủ các quyền hợp pháp sau đối với dữ liệu cá nhân của mình:
						</p>
						<div class="space-y-3 pt-1">
							<div class="p-3.5 rounded-lg bg-slate-50 border border-slate-200">
								<strong class="text-slate-900 text-sm block">1. Quyền được biết & Quyền đồng ý:</strong>
								<span class="text-xs text-slate-600">Được thông báo rõ ràng về loại dữ liệu, mục đích xử lý và có quyền tự nguyện đồng ý hoặc từ chối cung cấp dữ liệu.</span>
							</div>
							<div class="p-3.5 rounded-lg bg-slate-50 border border-slate-200">
								<strong class="text-slate-900 text-sm block">2. Quyền truy cập & Yêu cầu chỉnh sửa:</strong>
								<span class="text-xs text-slate-600">Có quyền yêu cầu kiểm tra, tra cứu hoặc đính chính thông tin cá nhân nếu phát hiện có sai sót.</span>
							</div>
							<div class="p-3.5 rounded-lg bg-slate-50 border border-slate-200">
								<strong class="text-slate-900 text-sm block">3. Quyền xóa dữ liệu & Rút lại sự đồng ý:</strong>
								<span class="text-xs text-slate-600">Có quyền yêu cầu xóa toàn bộ thông tin cá nhân của mình hoặc yêu cầu ngừng nhận bất kỳ cuộc gọi, tin nhắn tư vấn nào từ hệ thống.</span>
							</div>
							<div class="p-3.5 rounded-lg bg-slate-50 border border-slate-200">
								<strong class="text-slate-900 text-sm block">4. Quyền phản ánh, khiếu nại:</strong>
								<span class="text-xs text-slate-600">Gửi khiếu nại tới Ban quản trị nếu phát hiện thông tin của mình bị sử dụng sai mục đích cam kết.</span>
							</div>
						</div>
						<div class="p-4 rounded-xl bg-blue-50/60 border border-blue-200 text-slate-700 text-xs sm:text-sm leading-relaxed mt-3">
							<strong>Cách thức thực hiện quyền:</strong> Quý vị chỉ cần gửi yêu cầu qua email <code><?php echo esc_html( $email ); ?></code> hoặc gọi điện trực tiếp đến Hotline <code><?php echo esc_html( $hotline ); ?></code>. Chúng tôi cam kết tiếp nhận và hoàn tất xử lý yêu cầu của quý khách trong vòng <strong>tối đa 72 giờ làm việc</strong>.
						</div>
					</section>

					<!-- Section 8 -->
					<section id="muc-8" class="scroll-mt-24 space-y-3">
						<h2 class="text-xl sm:text-2xl font-black text-slate-900 flex items-center gap-3 border-b border-slate-100 pb-3">
							<span class="flex items-center justify-center w-7 h-7 rounded-lg bg-blue-600 text-white text-xs font-bold shrink-0">8</span>
							Chính sách Cookie và công nghệ theo dõi
						</h2>
						<p class="text-slate-600 text-sm sm:text-base leading-relaxed">
							Cookie là những tệp dữ liệu nhỏ được lưu trữ trên trình duyệt của người dùng khi truy cập website. Chúng tôi sử dụng cookie nhằm:
						</p>
						<ul class="list-disc list-inside space-y-1.5 text-slate-600 text-sm pl-2">
							<li><strong>Cookie chức năng:</strong> Duy trì phiên hoạt động, ghi nhớ tùy chọn bộ lọc ngành học, trường học của người dùng.</li>
							<li><strong>Cookie phân tích hiệu suất (Google Analytics):</strong> Đo lường lưu lượng truy cập tổng thể để không ngừng cải thiện tốc độ tải trang và nội dung bài viết.</li>
						</ul>
						<p class="text-slate-600 text-sm leading-relaxed">
							Quý vị hoàn toàn có thể chủ động tắt hoặc xóa Cookie bất kỳ lúc nào thông qua phần cài đặt bảo mật của trình duyệt web (Chrome, Safari, Firefox, Edge).
						</p>
					</section>

					<!-- Section 9 -->
					<section id="muc-9" class="scroll-mt-24 space-y-3">
						<h2 class="text-xl sm:text-2xl font-black text-slate-900 flex items-center gap-3 border-b border-slate-100 pb-3">
							<span class="flex items-center justify-center w-7 h-7 rounded-lg bg-blue-600 text-white text-xs font-bold shrink-0">9</span>
							Biện pháp an toàn và bảo mật kỹ thuật
						</h2>
						<p class="text-slate-600 text-sm sm:text-base leading-relaxed">
							Để đảm bảo dữ liệu cá nhân không bị mất mát, đánh cắp hoặc truy cập trái phép, chúng tôi áp dụng các giải pháp bảo mật toàn diện:
						</p>
						<ul class="space-y-2 text-slate-600 text-sm sm:text-base">
							<li class="flex items-start gap-2.5">
								<span class="text-emerald-600 font-bold shrink-0">🔒</span>
								<span><strong>Mã hóa SSL/TLS (HTTPS):</strong> Toàn bộ dữ liệu truyền tải giữa thiết bị của quý vị và máy chủ website đều được mã hóa theo tiêu chuẩn an toàn cao nhất.</span>
							</li>
							<li class="flex items-start gap-2.5">
								<span class="text-emerald-600 font-bold shrink-0">🛡️</span>
								<span><strong>Tường lửa máy chủ & Bảo vệ dữ liệu:</strong> Hệ thống lưu trữ được bảo vệ bởi tường lửa chuyên dụng, hệ thống phòng chống xâm nhập và quy trình sao lưu (backup) định kỳ.</span>
							</li>
							<li class="flex items-start gap-2.5">
								<span class="text-emerald-600 font-bold shrink-0">👥</span>
								<span><strong>Kiểm soát quyền truy cập nội bộ:</strong> Áp dụng cơ chế phân quyền nghiêm ngặt theo vai trò; nhân viên phải ký cam kết bảo mật thông tin trước khi tiếp nhận công việc.</span>
							</li>
						</ul>
					</section>

					<!-- Section 10 -->
					<section id="muc-10" class="scroll-mt-24 space-y-3">
						<h2 class="text-xl sm:text-2xl font-black text-slate-900 flex items-center gap-3 border-b border-slate-100 pb-3">
							<span class="flex items-center justify-center w-7 h-7 rounded-lg bg-blue-600 text-white text-xs font-bold shrink-0">10</span>
							Thông tin Đơn vị kiểm soát & xử lý dữ liệu
						</h2>
						<p class="text-slate-600 text-sm sm:text-base leading-relaxed">
							Mọi câu hỏi, thắc mắc hoặc yêu cầu liên quan đến chính sách bảo vệ dữ liệu cá nhân, xin vui lòng liên hệ trực tiếp với chúng tôi:
						</p>
						<div class="p-5 rounded-xl bg-slate-50 border border-slate-200 space-y-2.5 text-sm text-slate-700">
							<p><strong>Đơn vị chủ quản:</strong> <?php echo esc_html( $company_name ); ?></p>
							<p><strong>Cổng thông tin:</strong> <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="text-blue-600 hover:underline font-semibold">lienthongdaihoc.com</a></p>
							<p><strong>Trụ sở tiếp nhận thông tin:</strong> <?php echo esc_html( $address ); ?></p>
							<p><strong>Hotline tiếp nhận & Hỗ trợ:</strong> <a href="tel:<?php echo esc_attr( preg_replace( '/\D/', '', $hotline ) ); ?>" class="text-blue-600 font-bold hover:underline"><?php echo esc_html( $hotline ); ?></a></p>
							<p><strong>Email chuyên trách bảo mật:</strong> <a href="mailto:<?php echo esc_attr( $email ); ?>" class="text-blue-600 font-bold hover:underline"><?php echo esc_html( $email ); ?></a></p>
							<p><strong>Thời gian làm việc:</strong> 08:00 – 21:00 (Thứ 2 đến Chủ Nhật, kể cả ngày lễ)</p>
						</div>
					</section>

					<!-- Section 11 -->
					<section id="muc-11" class="scroll-mt-24 space-y-3">
						<h2 class="text-xl sm:text-2xl font-black text-slate-900 flex items-center gap-3 border-b border-slate-100 pb-3">
							<span class="flex items-center justify-center w-7 h-7 rounded-lg bg-blue-600 text-white text-xs font-bold shrink-0">11</span>
							Điều khoản thi hành và sửa đổi
						</h2>
						<p class="text-slate-600 text-sm sm:text-base leading-relaxed">
							Ban quản trị website có quyền điều chỉnh, bổ sung nội dung Chính sách bảo mật này bất cứ lúc nào nhằm đáp ứng sự thay đổi của pháp luật hoặc cải tiến phương thức hoạt động. Mọi thay đổi sẽ có hiệu lực ngay khi được công bố chính thức tại trang này.
						</p>
						<p class="text-slate-600 text-sm leading-relaxed">
							Chúng tôi khuyến khích người dùng thường xuyên kiểm tra lại chính sách này để luôn nắm bắt được cách thức chúng tôi bảo vệ thông tin cá nhân của bạn.
						</p>
					</section>

					<?php endif; ?>

				</article>
			</div>

			<!-- SIDEBAR NAVIGATION & QUICK ACTIONS (4/12) -->
			<aside class="lg:col-span-4 space-y-6 lg:sticky lg:top-24">

				<!-- Quick TOC Card -->
				<div class="bg-white rounded-2xl shadow-sm border border-slate-200/90 p-5 sm:p-6 space-y-4">
					<h3 class="font-extrabold text-slate-900 text-sm uppercase tracking-wider flex items-center gap-2 pb-3 border-b border-slate-100">
						<span class="w-1.5 h-4 bg-blue-600 rounded-full inline-block"></span>
						Mục lục điều hướng
					</h3>
					<nav class="space-y-1.5 text-xs sm:text-sm">
						<a href="#muc-1" class="block py-1.5 px-2.5 rounded-lg text-slate-600 hover:text-blue-600 hover:bg-blue-50 transition-colors font-medium">1. Mục đích & phạm vi áp dụng</a>
						<a href="#muc-2" class="block py-1.5 px-2.5 rounded-lg text-slate-600 hover:text-blue-600 hover:bg-blue-50 transition-colors font-medium">2. Loại dữ liệu thu thập</a>
						<a href="#muc-3" class="block py-1.5 px-2.5 rounded-lg text-slate-600 hover:text-blue-600 hover:bg-blue-50 transition-colors font-medium">3. Mục đích xử lý dữ liệu</a>
						<a href="#muc-4" class="block py-1.5 px-2.5 rounded-lg text-slate-600 hover:text-blue-600 hover:bg-blue-50 transition-colors font-medium">4. Phương thức thu thập</a>
						<a href="#muc-5" class="block py-1.5 px-2.5 rounded-lg text-slate-600 hover:text-blue-600 hover:bg-blue-50 transition-colors font-medium">5. Phạm vi chia sẻ dữ liệu</a>
						<a href="#muc-6" class="block py-1.5 px-2.5 rounded-lg text-slate-600 hover:text-blue-600 hover:bg-blue-50 transition-colors font-medium">6. Thời gian lưu trữ</a>
						<a href="#muc-7" class="block py-1.5 px-2.5 rounded-lg text-slate-600 hover:text-blue-600 hover:bg-blue-50 transition-colors font-medium">7. Quyền của chủ thể dữ liệu (NĐ 13)</a>
						<a href="#muc-8" class="block py-1.5 px-2.5 rounded-lg text-slate-600 hover:text-blue-600 hover:bg-blue-50 transition-colors font-medium">8. Chính sách Cookie</a>
						<a href="#muc-9" class="block py-1.5 px-2.5 rounded-lg text-slate-600 hover:text-blue-600 hover:bg-blue-50 transition-colors font-medium">9. An toàn & bảo mật kỹ thuật</a>
						<a href="#muc-10" class="block py-1.5 px-2.5 rounded-lg text-slate-600 hover:text-blue-600 hover:bg-blue-50 transition-colors font-medium">10. Đơn vị kiểm soát dữ liệu</a>
						<a href="#muc-11" class="block py-1.5 px-2.5 rounded-lg text-slate-600 hover:text-blue-600 hover:bg-blue-50 transition-colors font-medium">11. Hiệu lực & sửa đổi</a>
					</nav>
				</div>

				<!-- Legal Switch Card -->
				<div class="bg-gradient-to-br from-slate-900 to-[#002B66] text-white rounded-2xl p-6 shadow-sm space-y-4">
					<span class="text-xs font-bold text-orange-400 uppercase tracking-wider block">Văn bản liên quan</span>
					<h4 class="font-extrabold text-base leading-snug">Điều khoản và Điều kiện Dịch vụ</h4>
					<p class="text-xs text-slate-300 leading-relaxed">
						Tìm hiểu về quy chế hoạt động, quyền lợi và nghĩa vụ của người dùng khi sử dụng dịch vụ tra cứu tuyển sinh.
					</p>
					<a href="<?php echo esc_url( home_url( '/dieu-khoan/' ) ); ?>" class="inline-flex items-center justify-center w-full py-2.5 px-4 rounded-xl bg-white text-slate-900 font-bold text-xs uppercase tracking-wider hover:bg-slate-100 transition-colors shadow-sm">
						Xem Điều khoản dịch vụ →
					</a>
				</div>

				<!-- Support Box -->
				<div class="bg-white rounded-2xl shadow-sm border border-slate-200/90 p-6 space-y-4 text-center">
					<div class="w-12 h-12 rounded-full bg-blue-50 text-blue-600 mx-auto flex items-center justify-center">
						<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
						</svg>
					</div>
					<div>
						<h4 class="font-black text-slate-900 text-sm">Cần hỗ trợ về quyền riêng tư?</h4>
						<p class="text-xs text-slate-500 mt-1">Đội ngũ chuyên viên tuyển sinh sẵn sàng hỗ trợ quý học viên 24/7.</p>
					</div>
					<div class="pt-2 space-y-2">
						<a href="tel:<?php echo esc_attr( preg_replace( '/\D/', '', $hotline ) ); ?>" class="block w-full py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-extrabold text-xs uppercase tracking-wider transition-colors shadow-sm">
							Gọi Hotline: <?php echo esc_html( $hotline ); ?>
						</a>
						<a href="<?php echo esc_url( $zalo ); ?>" target="_blank" rel="noopener noreferrer" class="block w-full py-2.5 rounded-xl bg-blue-500 hover:bg-blue-600 text-white font-extrabold text-xs uppercase tracking-wider transition-colors shadow-sm">
							Nhắn tin qua Zalo
						</a>
					</div>
				</div>

			</aside>

		</div>
	</div>
</main>

<?php
get_footer();
