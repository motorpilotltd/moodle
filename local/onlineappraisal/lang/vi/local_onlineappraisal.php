<?php
// This file is part of the Arup online appraisal system
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Language pack for local_onlineappraisal
 * @copyright   2016 Motorpilot Ltd / Sonsbeekmedia.nl
 * @author      Bas Brands, Simon Lewis
 *
 * @package    local_onlineappraisal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// General alerts.
$string['alert:language:notdefault'] = '<strong>Cảnh báo</strong>: Bạn đang sử dụng ngôn ngữ không phải mặc định để xem bản đánh giá. Vui lòng chắc chắn là bạn trả lời bằng ngôn ngữ thích hợp cho tất cả các bên liên quan';

// Error alerts
$string['error:noaccess'] = 'Bạn không được phép xem tài liệu này.';
$string['error:pagedoesnotexist'] = 'Bạn không được phép xem trang này.';

// Userinfo.
$string['form:userinfo:title'] = 'Appraisee Info';
$string['form:userinfo:intro'] = 'Vui lòng hoàn tất các chi tiết dưới đây. Một số chỗ đã được cài đặt sẵn sử dụng dữ liệu cá nhân trên TAPS. Nếu bạn thấy thông tin nào không chính xác, vui lòng liên hệ BP nhân sự';
$string['form:userinfo:name'] = 'Tên người được đánh giá';
$string['form:userinfo:staffid'] = 'Mã số nhân viên';
$string['form:userinfo:grade'] = 'Bậc';
$string['form:userinfo:jobtitle'] = 'Chức danh';
$string['form:userinfo:operationaljobtitle'] = 'Chức danh giao dịch';
$string['form:userinfo:facetoface'] = 'Ngày gặp mặt trực tiếp';
$string['form:userinfo:facetofaceheld'] = 'Buổi gặp mặt trực tiếp đã thực hiện';
$string['form:userinfo:setf2f'] = 'Set your face to face meeting time and date';
// ERROR: missing translation

// Feedback.
$string['form:addfeedback:alert:cancelled'] = 'Sending cancelled, your appraisal feedback has not been sent.';
$string['form:addfeedback:alert:error'] = 'Sorry, there was an error sending your appraisal feedback.';
$string['form:addfeedback:alert:saved'] = 'Thank you, your appraisal feedback has been successfully sent.';
$string['form:addfeedback:notfound'] = 'Không có Yêu cầu phản hồi nào';
$string['form:addfeedback:sendemailbtn'] = 'Gửi phản hồi Bản đánh giá';
$string['form:addfeedback:title'] = 'Feedback Contribution';
$string['form:addfeedback:closed'] = 'Cửa sổ gửi phản hồi hiện nay đang  đóng';
$string['form:addfeedback:submitted'] = 'Phản hồi đã gửi';
$string['form:addfeedback:addfeedback'] = 'Vui lòng mô tả ba lĩnh vực mà bạn đã đánh giá cao sự đóng góp của người được đánh giá trong 12 tháng qua. Sau đó cung cấp các chi tiết lĩnh vực mà bạn cảm thấy thực tế họ đã có thể  đạt được hiệu quả hơn. Hãy trung thực, phê bình mang tính chất xây dựng, vì thông tin phản hồi này sẽ giúp đồng nghiệp của bạn giải quyết vấn đề hiệu quả hơn.';

$string['form:feedback:alert:cancelled'] = 'Lệnh gửi đi đã được hủy bỏ, Yêu cầu phản hồi bản đánh giá của bạn chưa được gửi đi';
$string['form:feedback:alert:error'] = 'Rất tiếc, xãy ra lỗi khi gửi Yêu cầu phản hồi của bạn';
$string['form:feedback:alert:saved'] = 'Yêu cầu phản hồi bản đánh giá của bạn đã gửi thành công';
$string['form:feedback:email'] = 'Địa chỉ email';
$string['form:feedback:firstname'] = 'Tên';
$string['form:feedback:lastname'] = 'Họ';
$string['form:feedback:language'] = 'Lựa chọn ngôn ngữ cho email phản hồi';
$string['form:feedback:sendemailbtn'] = 'Send email to Contributor';
$string['form:feedback:title'] = 'Feedback - Add a new Contributor';

// START FORM
// Introduction Page
$string['appraisee_heading'] = 'Chào mừng đến với Bản Đánh Giá Trực Tuyến';
$string['appraisee_welcome'] = 'Bản đánh giá là cơ hội tốt để bạn và người đánh giá có thể trao đổi với nhau về sự thể hiện và phát triển của bạn.<br /><br />
Mục đích của công cụ trực tuyến này là giúp lưu lại buổi thảo luận và dùng nó để đối chiếu trong suốt năm.<br /><br />
Bấm vào hình bên phải để xem thông điệp giới thiệu của Gregory Hodkinson.<br /><br />
Để biết thêm thông tin về quá trình làm bản đánh giá, bạn có thể tìm thấy  <a href="https://moodle.arup.com/appraisal/essentials" target="_blank">ở đây </a>';

$string['introduction:video'] = '<img src="https://moodle.arup.com/scorm/_assets/Gregory_Hodkinson.jpg" alt="Changes to Appraisal" onclick="window.open(\'https://moodle.arup.com/scorm/_assets/intro.pdf\', \'_blank\');"/>';

// Request Feedback
$string['feedback_intro'] = 'Vui lòng chọn ba người đồng nghiệp hoặc nhiều hơn để đóng góp ý kiến phản hồi cho bản đánh giá của bạn. Ở hầu hết các khu vực, phản hồi này có thể là người nội bộ hoặc bên ngoài. <br/><br/>  Vui lòng tham chiếu khu vực của bạn để có hướng dẫn cụ thể.
Đối với người góp ý kiến phản hồi nội bộ, bạn nên thu thập phản hồi với góc nhìn “360 độ”, VD: đồng nghiệp, người nào đó bậc cao hơn bạn và người nào đó có bậc thấp hơn bạn. Bạn phải lựa chọn đa dạng người góp ý.
<br/><br/>Một trong những người nhận xét phản hồi có thể là khách hàng hoặc người cộng tác mà biết bạn rất rõ.<div data-visible-regions="East Asia"><br />Đối với khu vực Đông Á, chúng tôi mong muốn phản hồi chỉ đến từ nguồn nội bộ. Nhận xét từ khách hàng hay đối tác bên ngoài được hiểu và phản hồi qua người ngoài .</div> <br /><br /> <div class="alert alert-danger"> Ghi chú: Phản hồi của người đóng góp mà bạn lựa chọn sẽ được đăng ở đây sau buổi gặp mặt trực tiếp trừ khi họ muốn bảo mật.</div>';

// Last Year Review
$string['form:lastyear:title'] = 'Section 1: Review of last year';
$string['form:lastyear:nolastyear'] = 'Ghi chú: Chú ý là bạn không có bản đánh giá lần trước lưu trên hệ thống. Vui lòng tải lên bản đánh giá sau cùng dưới định dạng word hay pdf dưới đây';
$string['form:lastyear:intro'] = 'Ở mục này, cả người đánh giá và người được đánh giá thảo luận những gì đã đạt được trong 12 tháng qua và cách thức đã thực hiện.<a href="https://moodle.arup.com/appraisal/guide" target="_blank">Hướng dẫn làm Bản đánh giá</a> cung cấp thêm thông tin về bản chất của buổi thảo luận.';
$string['form:lastyear:upload'] = 'Tải lên Bản đánh giá';
$string['form:lastyear:appraiseereview'] = '1.1 Người được đánh giá xem lại thể hiện trong năm qua';
$string['form:lastyear:appraiseereviewhelp'] = '<div class="well well-sm"> <em>Nhìn chung, kể từ bản đánh giá gần nhất, bạn đã thế hiện tốt chưa, xét trên khía cạnh dự án, con người và khách hàng?</em>
    <ul class="m-b-0">
        <li><em>Bạn đã cộng tác và chia sẻ thông tin cũng như chuyên môn như thế nào? Kết quả là gì?</em></li>
        <li><em>Có phần nào trong phần thể hiện của bạn thấp hơn mong đợi?</em></li>
        <li><em>Nếu bạn phải chịu trách nhiệm về người khác, bạn đã quản lý tốt phần thể hiện và hành xử của họ chưa, cả hành xử tốt và xấu?</em></li>
        <li><em>Bạn đã sử dụng công nghệ để giúp mình hiệu quả hơn như thế nào?</em></li>
    </ul>
</div>';
$string['form:lastyear:appraiserreview'] = '1.2 Người đánh giá xem lại thể hiện trong năm qua';
$string['form:lastyear:appraiserreviewhelp'] = '<div class="well well-sm">
    <em>Vui lòng nhận xét cho phần đánh giá thể hiện của người được đánh giá kể từ lần làm đánh giá gần nhất.</em>
    <ul class="m-b-0">
        <li><em>Có tiến bộ nào không?</em></li>
        <li><em>Tóm tắt bất kỳ phản hồi nào mà người được đánh giá nhận được từ người nhận xét được chỉ định.</em></li>
    </ul>
    <em>Nếu có bất kỳ phần nào trong sự thể hiện hay ứng xử thấp hơn mong đợi thì  <strong>phải</strong> được thảo luận và ghi chép lại trong phần này. Nó có thể liên quan đến dự án, nhóm làm việc, khách hàng hoặc những người khác nói chung.</em>
</div>';
$string['form:lastyear:appraiseedevelopment'] = '1.3 Người được đánh giá xem lại sự phát triển trong năm qua';
$string['form:lastyear:appraiseedevelopmenthelp'] = '<div class="well well-sm">
    <em>Vui lòng nhận xét về sự phát triển của bản thân kể từ lần làm bản đánh giá gần nhất:</em>
    <ul class="m-b-0">
        <li><em>Bạn đã phát triển kỹ năng, kiến thức hay hành xử như thế nào?</em></li>
        <li><em>Kế hoạch phát triển nào trong năm qua vẫn chưa thực hiện được?</em></li>
    </ul>
</div>';
$string['form:lastyear:appraiseefeedback'] = '1.4 Có điều gì có thể tác động hoặc thúc đẩy sự thể hiện của nhóm bạn hay không';
$string['form:lastyear:appraiseefeedbackhelp'] = '<div class="well well-sm"><em>Phải được người làm bản đánh giá hoàn tất</em></div>';

// Career Direction
$string['form:careerdirection:title'] = 'Section 2: Career Direction';
$string['form:careerdirection:intro'] = 'Mục đích của phần này là để giúp người được đánh giá xem xét nguyện vọng nghề nghiệp và trao đổi điều này một cách thực tế với người đánh giá. Đối với nhân viên cấp dưới, giới hạn tầm nhìn khi trao đổi sẽ khoảng từ 1-3 năm. Đối với nhân viên cấp trên, chúng tôi mong rằng sẽ từ 3 – 5 năm';
$string['form:careerdirection:progress'] = '2.1 Bạn muốn nghề nghiệp của mình tiến triển thế nào?';
$string['form:careerdirection:progresshelp'] = '<div class="well well-sm">
<em>Bạn nên xem xét:</em>
    <ul class="m-b-0">
        <li><em>Bạn muốn làm loại công việc gì và mức độ trách nhiệm ra sao?</em></li>
        <li><em>Trong một vài năm tới, điều gì trong công việc của bạn là quan trọng với bạn. Ví dụ:  chiều rộng, chiều sâu, chuyên môn hóa, tổng quát hóa, điều động, thiết kế, trách nhiệm với mọi người, v.v…?</em></li>
        <li><em>Bạn muốn được bố trí làm việc ở đâu?</em></li>
    </ul>
</div>';
$string['form:careerdirection:comments'] = '2.2 Nhận xét của người đánh giá';
$string['form:careerdirection:commentshelp'] = '<div class="well well-sm">
    <em>Bạn nên xem xét:</em>
    <ul class="m-b-0">
        <li><em>Tính thực tế, thách thức và tham vọng của những nguyện vọng của người được đánh giá như thế nào?</em></li>
        <li><em>Vai trò, dự án, và những cơ hội công việc nào có thể cung cấp kinh nghiệm, kỹ năng và sự phát triển ứng xử theo yêu cầu?</em></li>
    </ul>
</div>';

// Agreed Impact Plan
$string['form:impactplan:title'] = 'Section 3: Agreed Impact Plan';
$string['form:impactplan:intro'] = 'Kế Hoạch Tác Động Theo Thỏa Thuận thể hiện người được đánh giá muốn tạo nên sự khác biệt trong những năm sắp tới như thế nào, xét trên khía cạnh công việc họ làm, và tác động của họ đến toàn công ty. Kế hoạch nên bao gồm cách thức người được đánh giá sẽ cải tiến công việc của mình/ hay dự án/ nhóm làm việc/ văn phòng/ công ty. Điều này có nghĩa là cung cấp những chi tiết về mốc thời gian, chất lượng, ngân sách, thiết kế/ cải tiến và tác động lên con người, khách hàng và công việc.<br /><br /> <a href="https://moodle.arup.com/appraisal/contribution" target="_blank">Hướng Dẫn Góp Ý </a> và <a href="https://moodle.arup.com/appraisal/guide" target="_blank">Hướng Dẫn Làm Bản Đánh Giá </a> sẽ đưa ra những đề xuất làm thế nào để những tiến bộ này có thể thực hiện';

$string['form:impactplan:impact'] = '3.1 Mô tả tác động mà bạn muốn có đối với dự án, khách hàng, nhóm làm việc hoặc công ty trong năm tới:';
$string['form:impactplan:impacthelp'] = '<div class="well well-sm"><em>Trong phần nêu lên của mình bạn phải bao gồm:</em>
    <ul class="m-b-0">
        <li><em>Lĩnh vực mà bạn tập trung</em></li>
        <li><em>Tại sao chúng quan trọng</em></li>
        <li><em>Làm thế nào để đạt được chúng</em></li>
        <li><em>Bạn sẽ cộng tác với ai</em></li>
        <li><em>Khung thời gian trung bình: : 3/6/12/18 tháng hoặc lâu hơn</em></li>
        <li><em>Kế Hoạch Tác Động Theo Thỏa Thuận phù hợp và hỗ trợ tiến trình phát triển nghề nghiệp theo mong muốn của bạn như thế nào.</em></li>
    </ul>
</div>';
$string['form:impactplan:support'] = '3.2 Bạn cần Arup hỗ trợ gì để đạt được điều này?';
$string['form:impactplan:supporthelp'] = '<div class="well well-sm">
    <em>Bạn có thể xem xét:</em>
    <ul class="m-b-0">
        <li><em>Hỗ trợ từ người khác</em></li>
        <li><em>Giám sát</em></li>
        <li><em>Các nguồn lực (thời gian, ngân sách, thiết bị)</em></li>
        <li><em>Sự phát triển cá nhân</em></li>
        <li><em>Công cụ (phần mềm, phần cứng)</em></li>
    </ul>
</div>';
$string['form:impactplan:comments'] = '3.3 Nhận xét của người đánh giá';
$string['form:impactplan:commentshelp'] = '<div class="well well-sm"><em>Phải được người đánh giá hoàn tất</em></div>';

// Development Plan
$string['form:development:title'] = 'Section 4: Development Plan';
$string['form:development:intro'] = 'Kế hoạch phát triển chỉ ra những kỹ năng cá nhân, kiến thức hoặc những thay đổi về hành xử nào cần được hỗ trợ cho sự phát triển nghề nghiệp của người được đánh giá và Kế Hoạch Tác Động Theo Thỏa Thuận.<br /><br />
Bạn cần phát triển thế nào trong 12-18 tháng tới để đạt được điều này? Bạn cần hỗ trợ gì và khi nào bạn sẽ thực hiện phát triển?<br /><br />
<div class="well well-sm">Tại Arup, chúng ta sử dụng nguyên tắc “70-20-10” trong sự phát triển cá nhân. Điều này có nghĩa là với hầu hết mọi người, 70% sự phát triển sẽ là “từ công việc” và học được từ kinh nghiệm, 20% sẽ từ người khác, có thể thông qua huấn luyện hoặc cố vấn. 10% cuối cùng sẽ từ những phương pháp học tập chính thức, như từ tham dự các lớp học hoặc học trực tuyến. Dĩ nhiên, tỷ lệ phần trăm trên chỉ là hướng dẫn tham khảoc</div>';
$string['form:development:seventy'] = 'Việc học diễn ra trong quá trình làm việc của bạn – khoảng 70%';
$string['form:development:seventyhelp'] = '<div class="well well-sm"> <em>Ví dụ:</em>
    <ul class="m-b-0">
        <li><em>Bổ nhiệm làm việc cho dự án</em></li>
        <li><em>Bổ nhiệm nhóm làm việc </em></li>
        <li><em>Điều động</em></li>
        <li><em>Thảo luận về công việc và phản hồi</em></li>
        <li><em>Kiểm tra dự án, trao đổi thiết kế</em></li>
        <li><em>Đọc</em></li>
        <li><em>Nghiên cứu</em></li>
    </ul>
</div>';
$string['form:development:twenty'] = 'Học hỏi từ người khác  - khoảng 20%';
$string['form:development:twentyhelp'] = '<div class="well well-sm"> <em>Ví dụ:</em>
    <ul class="m-b-0">
        <li><em>Thành viên trong nhóm làm việc</em></li>
        <li><em>Chuyên gia</em></li>
        <li><em>Khách hàng</em></li>
        <li><em>Cộng tác viên</em></li>
        <li><em>Hội thảo</em></li>
        <li><em>Huấn luyện</em></li>
        <li><em>Cố vấn</em></li>
    </ul>
</div>';
$string['form:development:ten'] = 'Học từ những khóa học chính thức – trực tiếp hoặc trực tuyến – khoảng 10%';
$string['form:development:tenhelp'] = '<div class="well well-sm">
    <em>Ví dụ:</em>
    <ul class="m-b-0">
        <li><em>Lớp học</em></li>
        <li><em>Khóa học trực tuyến</em></li>
        <li><em>Mô hình lớp học ảo</em></li>
    </ul>
</div>';

//APPRAISEE EMAIL
//$string['email:subject:summaries:groupleaderemail:appraisee'] = 'Nhận xét của Leader đã được thêm vào bản đánh giá của bạn';
//$string['email:body:summaries:groupleaderemail:appraisee'] = '<p>{{appraiseefirstname}} thân mến,</p>Tôi đã xem và nhận xét vào bản đánh giá của bạn.<br><p>Bạn có thể xem bản đánh giá hoàn chỉnh bằng cách bấm vào <a href="{{appraisalurl}}">đây</a>.</p><p>Trân trọng,<br /><br>{{groupleadername}}';

//APPRAISER EMAIL
//$string['email:subject:summaries:groupleaderemail:appraiser'] = 'Bản đánh giá {{appraiseefirstname}} {{appraiseelastname}} đã được cập nhập';
//$string['email:body:summaries:groupleaderemail:appraiser'] = '<p>{{appraiserfirstname}} thân mến,</p><p>Tôi đã nhận xét cho bản đánh giá của {{appraiseefirstname}} {{appraiseelastname}}.</p><p>Bạn có thể xem bản đánh giá hoàn chỉnh bằng cách bấm vào <a href="{{appraisalurl}}">đây</a>.</p>Trân trọng,<br><br>{{groupleadername}}';

//SIGN OFF EMAIL
//$string['email:subject:summaries:groupleaderemail:signoff'] = 'Bản đánh giá  {{appraiseefirstname}} {{appraiseelastname}} đã được cập nhập';
//$string['email:body:summaries:groupleaderemail:signoff'] = '<p>{{signofffirstname}} thân mến,</p><p>Tôi đã nhận xét cho bản đánh giá của {{appraiseefirstname}} {{appraiseelastname}}.</p><p>Bạn có thể xem bản đánh giá hoàn chỉnh bằng cách bấm vào <a href="{{appraisalurl}}">đây</a>.</p>Trân trọng,<br><br>{{groupleadername}}';

// Summaries
$string['form:summaries:title'] = 'Section 5: Summaries';
$string['form:summaries:intro'] = 'Mục đích của phần này là tóm tắt nội dung của bản đánh giá để bất kỳ ai sau này liên quan đến việc ra quyết định lương, nâng bậc hay phát triển có thể tham khảo.';
$string['form:summaries:appraiser'] = '5.1 Tóm tắt của người đánh giá về biểu hiện chung của người được đánh giá';
$string['form:summaries:appraiserhelp'] = '<div class="well well-sm">
    <em>Người đánh giá phải đưa ra tóm tắt rõ ràng, chính xác về biểu hiện của người được đánh giá để người khác có thể dễ dàng hiểu khi đưa ra các quyết định về lương/thăng bậc/phát triển trong tương lai. Cụ thể là, người đánh giá phải chỉ rõ khi thấy biểu hiện chung thấp hơn hay cao hơn mong đợi.</em>
</div>';
$string['form:summaries:recommendations'] = '5.2 Những hành động đã thống nhất';
$string['form:summaries:recommendationshelp'] = '<div class="well well-sm">
    <em>Được người đánh giá hoàn tât</em><br/>
    <em>Hoạt động gì cần xảy ra bây giờ? Ví dụ:</em>
    <ul>
        <li><em>Phát triển</em></li>
        <li><em>Điều động</em></li>
        <li><em>Bổ nhiệm</em></li>
        <li><em>Hỗ trợ thực hiện</em></li>
    </ul>
</div>';
$string['form:summaries:appraisee'] = '5.3 Nhận xét của người được đánh giá';
$string['form:summaries:appraiseehelp'] = '<div class="well well-sm"><em>Do người được đánh giá hoàn tất</em></div>';
$string['form:summaries:signoff'] = '5.4 Tổng kết sign off';
$string['form:summaries:signoffhelp'] = '<div class="well well-sm"><em>Do người trưởng nhóm/ người được chỉ định hoàn tất</em></div>';
$string['form:summaries:grpleader'] = '5.5 Tóm tắt của Leader';
$string['form:summaries:grpleaderhelp'] = '<div class="well well-sm"><em>Do Senior leader hoàn tất.</em></div>';
$string['form:summaries:grpleadercaption'] = 'Hoàn tất bởi {$a->fullname}{$a->date}';

// Checkins
$string['appraisee_checkin_title'] = 'Section 6. Check-in';
$string['checkins_intro'] = 'Trong suốt một năm, chúng tôi mong rằng người được đánh giá và người đánh giá sẽ cần trao đổi về  tình hình thực hiện so với Kế Hoạch Hành Động Theo Thỏa Thuận, Kế Hoạch Phát Triển, hành động và thể hiện. Người được đánh giá và/ hoặc người đánh giá có thể sử dụng phần dưới đây để ghi chép lại tiến trình. Tần suất trao đổi là tùy thuộc vào các bạn nhưng đề xuất là ít nhất một lần một năm';

// Feedback contribution
$string['feedback_header'] = 'Đưa ra ý kiến phản hồi cho {$a->appraisee_fullname}';
$string['form:addfeedback:addfeedback'] = 'Vui lòng mô tả ba lĩnh vực mà bạn đã đánh giá cao sự đóng góp của người được đánh giá trong 12 tháng qua. Sau đó cung cấp các chi tiết lĩnh vực mà bạn cảm thấy thực tế họ đã có thể  đạt được hiệu quả hơn – có thể lên đến ba lĩnh vực. Hãy trung thực, phê bình mang tính chất xây dựng, vì thông tin phản hồi này sẽ giúp đồng nghiệp của bạn giải quyết vấn đề hiệu quả hơn.';
$string['confidential_label_text'] = 'Đánh dấu vào ô này để bảo mật nhận xét của bạn. Nếu bạn không đánh dấu, người được đánh giá sẽ thấy nhận xét của bạn.';

// Feedback Email - APPRAISEE
$string['email:subject:appraiseefeedback'] = 'Yêu cầu phản hồi cho bản đánh giá của tôi';
$string['email:body:appraiseefeedbackmsg'] = '<p>Kính gửi <span class="placeholder bind_firstname">{{firstname}}</span>,</p>
<p>Bản đánh giá của tôi sắp được gửi đến cho bạn. Vì trong năm qua, chúng ta đã làm việc mật thiết với nhau, tôi rất cảm kích nếu bạn cho ý kiến phản hồi về lĩnh vực mà bạn đánh giá cao những đóng góp của tôi, và về lĩnh vực mà bạn cảm thấy tôi có thể làm tốt hơn. Nếu bạn chấp thuận, vui lòng bấm vào đường dẫn bên dưới để đóng góp ý kiến phản hồi của bạn.</p> <p>Ngày đánh giá của tôi vào ngày <span class="placeholder">{{held_date}}</span>, xin vui lòng phản hồi trước ngày này.</p>
<p>Phản hồi của bạn sẽ được chia sẻ với tôi sau buổi gặp trực tiếp, trừ khi bạn đánh dấu vào ô bảo mật khi bạn gửi đi.</p>
<p>Dưới đây là bất kỳ nhận xét bổ sung từ <span class="placeholder">{{appraisee_fullname}}</span>:<br /> <span>{{emailtext}}</span></p>
<p>Trân trọng,<br />
<span class="placeholder">{{appraisee_fullname}}</span></p>';

// Feedback Email - APPRAISER
$string['email:subject:appraiserfeedback'] = 'Yêu cầu phản hồi cho bản đánh giá của  {{appraisee_fullname}}';
$string['email:body:appraiserfeedbackmsg'] = '<p>Kính gửi <span class="placeholder bind_firstname">{{firstname}}</span>,</p>
<p>Tôi đang làm bản đánh giá cho <span class="placeholder">{{appraisee_fullname}}</span>, vì bạn đã làm việc mật thiết với người được đánh giá trên, tôi mong muốn bạn sẽ  đưa ra một số phản hồi về những đóng góp của họ. Tôi rất cảm kích nếu phản hồi nhận xét ở lĩnh vực mà bạn đánh giá cao và lĩnh vực mà bạn cảm thấy họ có thể làm tốt hơn. Nếu bạn chấp thuận, vui lòng bấm vào đường dẫn bên dưới để đóng góp ý kiến phản hồi của bạn.</p>
<p>Ngày đánh giá của họ vào ngày <span class="placeholder">{{held_date}}</span>, xin vui lòng phản hồi trước ngày này.</p>
<p>Phản hồi của bạn sẽ được chia sẻ với <span class="placeholder">{{appraisee_fullname}}</span> sau buổi gặp trực tiếp, trừ khi bạn đánh dấu vào ô bảo mật khi bạn gửi đi.</p>
<p>Dưới đây là bất kỳ nhận xét bổ sung từ <span class="placeholder">{{appraiser_fullname}}</span>:<br /> <span>{{emailtext}}</span></p>
<p>Trân trọng,<br /> <span class="placeholder">{{appraiser_fullname}}</span></p>';

// PDF Strings
$string['pdf:form:summaries:appraisee'] = 'Nhận xét của người được đánh giá';
$string['pdf:form:summaries:appraiser'] = 'Tóm tắt của người đánh giá về biểu hiện chung của người được đánh giá';
$string['pdf:form:summaries:signoff'] = 'Tổng kết sign off';
$string['pdf:form:summaries:grpleader'] = 'Tóm tắt của Leader';
$string['pdf:form:summaries:recommendations'] = 'Những hành động đã thống nhất';

// END FORM

// START OVERVIEW CONTENT

// Overview page APPRAISEE Content.
$string['overview:content:appraisee:1'] = ''; // Never seen...
$string['overview:content:appraisee:2'] = 'Vui lòng hoàn tất bản đánh giá bản thân của bạn.<br /><br />
<strong>Các bước tiếp theo:</strong>
    <ul class="m-b-20">
        <li>Điền ngày sẽ gặp mặt trực tiếp </li>
        <li>Yêu cầu đưa ra phản hồi</li>
        <li>Phản ánh và nhận xét về Thể hiện và Phát triển của bạn trong năm ngoái</li>
        <li>Điền vào Định Hướng Nghề Nghiệp, Tác Động và Kế Hoạch Phát Triển để thảo luận trong buổi gặp mặt trực tiếp</li>
        <li>Share bản thảo cho {$a->styledappraisername}], người đánh giá bạn</li>
    </ul>
Vui lòng gửi bản thảo cho người đánh giá bạn ít nhất <strong><u>một tuần</u></strong> trước ngày gặp trực tiếp. Bạn vẫn có thể tiếp tục bổ sung thêm sau khi share.<br /><br />
<div class="alert alert-danger" role="alert"><strong>Chú ý:</strong> Người đánh giá bạn sẽ không thể thấy bản thảo cho đến khi bạn share với họ.</div>';

$string['overview:content:appraisee:2:3'] = 'Người đánh giá đã đề nghị thay đổi lên bản thảo<br /><br />
<strong>Bước tiếp theo</strong>
<ul class="m-b-20">
    <li>Thay đổi như đề nghị của người đánh giá (vui lòng xem nhật ký hoạt động để biết thêm về những điều được đề nghị)</li>
    <li>Share bản thảo với {$a->styledappraisername}.</li>
</ul>';

$string['overview:content:appraisee:3'] = 'Bạn đã nộp bản thảo đánh giá cho {$a->styledappraisername} để xem lại.<br /><br />
<strong>Bước tiếp theo:</strong>
<ul class="m-b-20">
    <li>Có một buổi gặp trực tiếp – trước buổi gặp, bạn có thể mong muốn:</li>
    <ul class="m-b-0">
        <li><a href="{$a->printappraisalurl}" target="_blank">Tải bản đánh giá</a></li>
        <li><a href="https://moodle.arup.com/appraisal/reference" target="_blank" target="_blank">Tải Hướng Dẫn Tham Khảo </a></li>
    </ul>
    <li>Tiếp sau buổi gặp mặt, người đánh giá sẽ trả bản đánh giá cho bạn. Bạn có thể được yêu cầu chỉnh sửa theo những thay đổi đã được thống nhất trong buổi gặp trực tiếp hoặc viết nhận xét cuối cùng của bạn.</li>
</ul>
<div class="alert alert-danger" role="alert"><strong>Chú ý:</strong> Bạn có thể tiếp tục chỉnh sửa bản đánh giá trong khi người đánh giá đang xem, nhưng đề nghị bạn nên sử dụng nhật ký hoạt động để làm nổi bật bất kỳ thay đổi nào đã thực hiện.</div>';

$string['overview:content:appraisee:3:4'] = 'Bạn đã trả bản đánh giá cho {$a->styledappraisername} để thực hiện thay đổi.<br /><br /> Bạn sẽ nhận được thông báo khi họ cập nhật lên bản đánh giá, và sẵn sàng cho bạn xem lại lần nữa.<br /><br /> <div class="alert alert-danger" role="alert"><strong>Ghi chú:</strong> Bạn có thể tiếp tục chỉnh sữa bản đánh giá khi người đánh giá đang xem nhưng đề nghị bạn sử dụng Nhật ký hoạt động/Activity log để làm nổi bật các thay đổi.</div>';

$string['overview:content:appraisee:4'] = '{$a->styledappraisername} đã điền nhận xét của họ lên bản đánh giá và bản đánh giá đã trở về lại với bạn.<br /><br />
<strong>Bước tiếp theo:</strong>
<ul class="m-b-20">
    <li>Vui lòng xem lại nhận xét và tóm tắt của người đánh giá. Nếu cần thì gửi lại bản đánh giá cho người đánh giá nếu bạn có yêu cầu bất kỳ thay đổi nào.</li>
    <li>Viết nhận xét của bạn lên mục Tóm tắt</li>
    <li>Gửi cho người đánh giá để xem lại lần cuối trước khi chấp nhận. Một khi đã nộp, bạn sẽ không thể điều chỉnh bản đánh giá.</li>
</ul>
<div class="alert alert-danger" role="alert"><strong>Chú ý:</strong> bạn có thể tiếp tục điều chỉnh lên các phần của mình trong bản đánh giá nhưng đề xuất bạn nên sử dụng nhật ký hoạt động để làm nổi bật những thay đổi cho người đánh giá biết.</div>';

$string['overview:content:appraisee:5'] = 'Bạn đã nộp bản đánh đánh giá hoàn chỉnh cho {$a->styledappraisername} để xem xét lần cuối.<br /><br /> <strong>Bước tiếp theo:</strong> <ul class="m-b-20"> <li>•	Người đánh giá của bạn sẽ gửi bản đánh giá đến {$a->styledsignoffname} để được sign off</li> </ul> <div class="alert alert-danger" role="alert"><strong>Chú ý:</strong> bạn sẽ không thể thực hiện bất kỳ thay đổi nào lên bản đánh giá nữa trừ khi người đánh giá trả lại nó cho bạn để bổ sung thêm</div>';

$string['overview:content:appraisee:6'] = 'Bản đánh giá của bạn đã được gửi đến {$a->styledsignoffname} để xem lại và viết tóm tắt của họ.<br /><br />
<div class="alert alert-danger" role="alert"><strong>Chú ý:</strong> bản đánh giá đã bị khóa và không thể bổ sung thêm được nữa.</div>';

$string['overview:content:appraisee:7'] = 'Bản đánh giá của bạn đã hoàn tất. Bạn có thể tải bản PDF bất cứ lúc nào bằng cách bấm vào “Tải bản đánh giá”';
$string['overview:content:appraisee:7:groupleadersummary'] = 'Bản đánh giá của bạn hiện nay đã hoàn tất và chờ leader xem và tóm tắt. Bạn sẽ được thông báo khi việc này được thực hiện.';
$string['overview:content:appraisee:8'] = $string['overview:content:appraisee:7']; // For legacy where there was a six month status.
$string['overview:content:appraisee:9'] = $string['overview:content:appraisee:7']; // When Groupleader added summary.

// Overview page APPRAISER Content.
$string['overview:content:appraiser:1'] = ''; // Never seen...
$string['overview:content:appraiser:2'] = 'Bản đánh giá hiện đang được {$a->styledappraiseename} soạn thảo. Bạn sẽ nhận được thông báo xem xét bản đánh giá khi nó hoàn tất.<br /><br />
<div class="alert alert-danger" role="alert"><strong>Chú ý:</strong> Bạn sẽ không thể xem bản đánh giá cho đến khi nó được share với bạn</div>';

$string['overview:content:appraiser:2:3'] = 'Bạn đã trả lại bản đánh giá cho {$a->styledappraiseename} để thay đổi. Bạn sẽ nhận được thông báo khi bản đánh giá được cập nhật, và sẵn sàng để bạn xem lại lần nữa.<br /><br />
<div class="alert alert-danger" role="alert"><strong>Chú ý:</strong> Bạn vẫn có thể thay đổi những phần của mình</div>';

$string['overview:content:appraiser:3'] = '{$a->styledappraiseename} đã nộp bản thảo để chuẩn bị cho buổi gặp trực tiếp<br /><br />
<strong>Bước tiếp theo:</strong>
<ul class="m-b-20">
    <li>Vui lòng xem lại bản đánh giá để chuẩn bị cho buổi gặp. Nếu cần thì hoàn trả lại bản đánh giá cho người được đánh giá nếu bạn thấy cần bổ sung thêm thông tin.</li>
    <li>Trước buổi gặp bạn nên</li>
    <ul class="m-b-0">
        <li><a href="{$a->printappraisalurl}" target="_blank">Tải bản đánh giá</a></li>
        <li><a href="{$a->printfeedbackurl}" target="_blank">Tải bất kỳ phản hồi nào nhận được</a></li>
        <li>Bạn có thể mong muốn <a href="https://moodle.arup.com/appraisal/reference" target="_blank">tải hướng dẫn tham khảo nhanh</a></li>
    </ul>
    <li>Sau buổi gặp, vui lòng</li>
    <ul class="m-b-0">
        <li>Đánh dấu là buổi gặp trực tiếp đã diễn ra lên mục Thông Tin Người Được Đánh Giá</li>
        <li>Thêm nhận xét cho mỗi mục</li>
        <li>Viết bản tóm tắt và các hành động đã thống nhất trong mục Tóm Tắt</li>
        (Nếu cần, bạn có thể trả bản đánh giá cho người được đánh giá để chỉnh sửa trước khi bạn thêm nhận xét của mình vào).
    </ul>
    <li>Gửi cho người được đánh giá để xem lại nhận xét của bạn, xem phản hồi và họ sẽ thêm nhận xét cuối cùng của mình vào.</li>
</ul>';

$string['overview:content:appraiser:3:4'] = '{$a->styledappraiseename} đã đề nghị một số thay đổi lên bản đánh giá của họ<br /><br />
<strong>Bước tiếp theo:</strong>
<ul class="m-b-20">
    <li>Thực hiện thay đổi theo đề nghị của người được đánh giá (vui lòng xem nhật ký hoạt động để biết thêm về những thông tin được yêu cầu)</li>
    <li>Share bản đánh giá với {$a->styledappraiseename} để nhận xét sau cùng.</li>
</ul>';


$string['overview:content:appraiser:4'] = 'Bạn vừa bổ sung nhận xét và tóm tắt của mình và bản đánh giá đã trả lại cho {$a->styledappraiseename} để bổ sung nhận xét cuối cùng của họ. Bạn sẽ nhận được thông báo khi bản đánh giá đã sẵn sàng để bạn xem lại lần cuối cùng.<br /><br />
<div class="alert alert-danger" role="alert"><strong>Chú ý:</strong> bạn có thể tiếp tục bổ sung lên các phần của mình trong bản đánh giá nhưng đề xuất bạn nên sử dụng nhật ký hoạt động để làm nổi bất những thay đổi cho người được đánh giá biết.</div>';

$string['overview:content:appraiser:5'] = '{$a->styledappraiseename} đã bổ sung nhận xét sau cùng của họ.<br /><br />
<strong>Bước tiếp theo:</strong>
<ul class="m-b-20">
    <li>Vui lòng xem lại bản đánh giá hoàn chỉnh đã sẵn sàng được sign off</li>
    <li>Gửi cho {$a->styledsignoffname} để xem lại và bổ sung tóm tắt của họ</li>
    <li>Bạn và người được đánh giá sẽ được thông báo khi bản đánh giá được hoàn tất.</li>
</ul>
<div class="alert alert-danger" role="alert"><strong>Chú ý:</strong> bạn sẽ không thể thực hiện bất kỳ thay đổi nào lên bản đánh giá nữa trừ khi bạn trả lại nó cho người được đánh giá</div>';

$string['overview:content:appraiser:6'] = 'Bạn đã nộp bản đánh giá cho {$a->styledsignoffname} để hoàn tất.<br /><br />
    <div class="alert alert-danger" role="alert"><strong>Chú ý:</strong> bản đánh giá đã bị khóa và không thể bổ sung thêm được nữa.</div>';

$string['overview:content:appraiser:7'] = 'Bản đánh giá đã hoàn tất và được sign off';
$string['overview:content:appraiser:7:groupleadersummary'] = 'Bản đánh giá  hiện nay đã hoàn tất và chờ leader xem và tóm tắt. Bạn sẽ được thông báo khi việc này được thực hiện.';

$string['overview:content:appraiser:8'] = $string['overview:content:appraiser:7']; // For legacy where there was a six month status.
$string['overview:content:appraiser:9'] = $string['overview:content:appraiser:7']; // When Groupleader added summary.

// Overview page SIGN OFF Content.
$string['overview:content:signoff:1'] = ''; // Never seen...
$string['overview:content:signoff:2'] = 'Bản đánh giá đang thực hiện.<br /><br /><div class="alert alert-danger" role="alert"><strong>Ghi chú:</strong>Bạn sẽ được thông báo khi bản đánh giá đã sẵn sàng để xem xét và sign off</div>';
$string['overview:content:signoff:3'] = 'Bản đánh giá đang thực hiện.<br /><br /><div class="alert alert-danger" role="alert"><strong>Ghi chú:</strong>Bạn sẽ được thông báo khi bản đánh giá đã sẵn sàng để xem xét và sign off</div>';
$string['overview:content:signoff:4'] = 'Bản đánh giá đang thực hiện.<br /><br /><div class="alert alert-danger" role="alert"><strong>Ghi chú:</strong>Bạn sẽ được thông báo khi bản đánh giá đã sẵn sàng để xem xét và sign off</div>';
$string['overview:content:signoff:5'] = 'Bản đánh giá đang thực hiện.<br /><br /><div class="alert alert-danger" role="alert"><strong>Ghi chú:</strong>Bạn sẽ được thông báo khi bản đánh giá đã sẵn sàng để xem xét và sign off</div>';
$string['overview:content:signoff:6'] = 'Bản đánh giá của {$a->styledappraiseename} đã được gửi đến bạn để xem xét .<br /><br />
<strong>Các bước tiếp theo:</strong>
<ul class="m-b-20">
    <li>Vui lòng xem xét bản đánh giá</li>
    <li>Viết tóm tắt trong Mục Tóm tắt</li>
    <li>Bấm vào nút  Sign Off để hoàn tất bản đánh giá</li>
</ul>';

$string['overview:content:signoff:7'] = 'Bản đánh giá này đã hoàn tất và được sign off';
$string['overview:content:signoff:7:groupleadersummary'] = 'Bản đánh giá  hiện nay đã hoàn tất và chờ leader xem và tóm tắt. Bạn sẽ được thông báo khi việc này được thực hiện';

$string['overview:content:signoff:8'] = $string['overview:content:signoff:7']; // For legacy where there was a six month status.
$string['overview:content:signoff:9'] = $string['overview:content:signoff:7']; // When groupleader added summary.

// Overview page GROUP LEADER Content.
$string['overview:content:groupleader:1'] = ''; // Never seen...
$string['overview:content:groupleader:2'] = 'Bản đánh giá đang trong quá trình thực hiện';
$string['overview:content:groupleader:3'] = 'Bản đánh giá đang trong quá trình thực hiện';
$string['overview:content:groupleader:4'] = 'Bản đánh giá đang trong quá trình thực hiện';
$string['overview:content:groupleader:5'] = 'Bản đánh giá đang trong quá trình thực hiện';
$string['overview:content:groupleader:6'] = 'Bản đánh giá đang trong quá trình thực hiện';
$string['overview:content:groupleader:7'] = 'Bản đánh giá này đã hoàn tất và được sign off';
$string['overview:content:groupleader:7:groupleadersummary'] = 'Bản đánh giá này đã hoàn tất và chờ bạn xem và tóm tắt<br /><br />
<strong>Các bước tiếp theo:</strong>
<ul class="m-b-20">
    <li>Vui lòng thêm tóm tắt của Leader vào Mục Tóm tắt và lưu lại</li>
    <li>Người được đánh giá, người đánh giá và người sign off sẽ được thông báo khi bạn thêm vào nhận xét của mình</li>
</ul>';
$string['overview:content:groupleader:8'] = $string['overview:content:groupleader:7']; // For legacy where there was a six month status.
$string['overview:content:groupleader:9'] = $string['overview:content:groupleader:7'];

// Overview page buttons.
$string['overview:button:appraisee:2:extra'] = 'Bắt đầu thực hiện Bản đánh giá';
$string['overview:button:appraisee:2:submit'] = 'Share với {$a->plainappraisername}';

$string['overview:button:appraisee:4:return'] = 'Trả lại cho {$a->plainappraisername} để thực hiện thay đổi';
$string['overview:button:appraisee:4:submit'] = 'Nộp bản đánh giá hoàn tất cho {$a->plainappraisername}';

$string['overview:button:appraiser:3:return'] = 'Yêu cầu thêm thông tin từ {$a->plainappraiseename}';
$string['overview:button:appraiser:3:submit'] = 'Gửi cho {$a->plainappraiseename} để xem lại lần cuối';

$string['overview:button:appraiser:5:return'] = 'Yêu cầu điền thêm nội dung trước khi chấp nhận';
$string['overview:button:appraiser:5:submit'] = 'Gửi đến {$a->plainsignoffname} để chấp nhận';

$string['overview:button:signoff:6:submit'] = 'Sign Off';

$string['overview:button:returnit'] = 'Return';
$string['overview:button:submitit'] = 'Send';

// START V string translations - spreadsheet
$string['startappraisal'] = 'Bắt đầu làm Bản đánh giá online/ trực tuyến';
$string['continueappraisal'] = 'Tiếp tục làm Bản đánh giá online/ trực tuyến';
$string['appraisee_feedback_edit_text'] = 'Điền vào';
$string['appraisee_feedback_resend_text'] = 'Gửi lại';
$string['appraisee_feedback_view_text'] = 'Xem';
$string['feedback_setface2face'] = 'Bạn phải đặt hẹn ngày gặp trực tiếp trước khi bạn bổ sung yêu cầu đưa ra phản hồi. Có thể tìm thấy điều này trên trang Thông Tin Của Người Được Đánh Giá';
$string['feedback_comments_none'] = 'Không có nhận xét bổ sung nào';
$string['actionrequired'] = 'Yêu cầu phải có hành động';
$string['actions'] = 'Hành động';
$string['admin:bulkactions'] = 'Những Hành Động Chính';
$string['admin:duedate'] = 'Ngày Hết Hạn';
$string['admin:email'] = 'Email cho người được đánh giá';
$string['admin:initialise'] = 'Tạo Bản Đánh Giá';
$string['admin:nousers'] = 'Không tìm thấy người dùng nào phù hợp';
$string['admin:toptext:archived'] = 'Các bản đánh giá đã lưu là một bản lưu trữ các bản đánh giá trước đây và không thể chỉnh sửa.';
$string['admin:toptext:complete'] = 'Bản đánh giá đã hoàn tất sẽ xuất hiện ở đây một khi nó được chấp nhận bởi Người Sign Off. Chỉ trước khi bắt đầu một bản đánh giá mới thì bản đánh giá hiện tại mới cần được lưu lại. Khi bản đánh giá được lưu thì không thể thực hiện thêm bất kỳ thay đổi nào và bản đánh giá sẽ được khóa ở tình trạng hiện tại. Người sử dụng có thể truy cập bản đánh giá trong mục các bản đánh giá đã lưu trên thanh dashboard';
$string['admin:toptext:deleted'] = 'Bản đánh giá đã được xóa trong bản cập nhật quá trình đánh giá nhưng vẫn còn lưu trên hệ thống.';
$string['admin:toptext:initialise'] = 'Để lập bản đánh giá, bạn cần phải điền ngày đến hạn, chọn Người Đánh Giá và Người Sign Off bằng cách sử dụng thanh kéo lên xuống dọc theo phần người sử dụng, và bấm "Tạo Bản Đánh Giá". Quy trình đánh giá sẽ bắt đầu và một email sẽ được gửi đến Người Được Đánh Giá (cc Người Đánh Giá) để thông báo rằng quy trình đã bắt đầu, cung cấp cho họ đường link của văn bản.';
$string['admin:toptext:inprogress'] = 'Người được đánh giá có thểđược theo dõi dưới đây theo danh sách này. Bản đánh giá sẽ được chuyển sang trạng thái Hoàn tất một khi nó được chấp nhận. Các Hành Động trong bảng cho phép bạn thay đổi Người Đánh Giá/ Người Sign Off cũng như là xóa bản đánh giá (chú ý là việc này không thể khôi phục lại được). Sử dụng các lựa chọn và kéo thả tại cuối mỗi trang, bạn có thể gửi email đến người thực hiện để theo dõi tiến độ. Lưu trữ được thực hiện vào cuối năm để giúp cho bạn có thể tạo ra một bản đánh giá mới.';
$string['admin:usercount'] = 'Tổng số nhân viên trong cost centre được lựa chọn: {number}';
$string['appraisals:archived'] = 'Các bản đánh giá đã lưu ';
$string['appraisals:current'] = 'Các bản đánh giá hiện tại';
$string['appraisals:noarchived'] = 'Bạn không có bản đánh giá đã lưu nào';
$string['appraisals:nocurrent'] = 'Hiện tại bạn không có bản đánh giá nào';
$string['group'] = 'Cost centre';
$string['index:togglef2f:complete'] = 'Đánh dấu F2F là Đã diễn ra';
$string['index:togglef2f:notcomplete'] = 'Đánh dấu F2F là Chưa diễn ra';
$string['index:notstarted'] = 'Chưa bắt đầu';
$string['index:notstarted:tooltip'] = 'Người được đánh giá chưa bắt đầu thực hiện bản đánh giá, một khi họ thực hiện, bạn sẽ truy cập được.';
$string['index:printappraisal'] = 'Tải bản đánh giá';
$string['index:printfeedback'] = 'Tải phản hồi';
$string['index:start'] = 'Bắt đầu làm Bản đánh giá';
$string['index:toptext:appraisee'] = 'Bảng dashboard thể hiện các bản đánh giá hiện tại và đã lưu. Có thể truy cập bản đánh giá hiện tại bằng cách sử dụng đường dẫn bên dưới thanh Hành Động (Actions). Có thể tải các bản đánh giá đã lưu bằng cách sử dụng nút Tải Bản Đánh Giá bên dưới.';
$string['index:toptext:appraiser'] = 'Bảng dashboard thể hiện các bản đánh giá hiện tại và đã lưu mà bạn là người đánh giá. Có thể truy cập bất kỳ bản đánh giá hiện tại nào bằng cách sử dụng đường dẫn bên dưới thanh Hành Động (Actions). Người được đánh giá không thể tải  phản hồi cho đến sau khi buổi gặp trực tiếp diễn ra. Bất kỳ phản hồi nào được bảo mật sẽ được ẩn ở tất cả các bước/ giai đoạn. Có thể tải các bản đánh giá đã lưu bằng cách sử dụng nút Tải Bản Đánh Giá bên dưới.';
$string['index:toptext:groupleader'] = 'Bảng dashboard thể hiện các bản đánh giá hiện tại và đã lưu trong cost centre của bạn. Có thể truy cập hoặc tải bản đánh giá hiện tại bằng cách sử dụng đường dẫn bên dưới thanh Hành Động . Có thể tải các bản đánh giá đã lưu bằng cách sử dụng nút Tải Bản Đánh Giá bên dưới.';
$string['index:toptext:hrleader'] = 'Dashboard này thể hiện các bản đánh giá hiện tại và đã lưu trữ trong cost centre của bạn. Bạn có thể truy cập hoặc tải xuống bất cứ bản đánh giá hiện tại nào bằng cách sử dụng các đường dẫn bên dưới nút Actions/Hành động. Bạn có thể tải xuống các bản đánh giá đã lưu trữ bằng cách sử dụng nút Tải Bản đánh giá bên dưới.';
$string['index:toptext:signoff'] = 'Bảng dashboard thể hiện các bản đánh giá hiện tại và đã lưu mà bạn là Người Sign Off. Có thể truy cập bản đánh giá hiện tại bằng cách sử dụng đường dẫn bên dưới thanh Hành Động . Có thể tải các bản đánh giá đã lưu bằng cách sử dụng nút Tải Bản Đánh Giá bên dưới.';
$string['index:view'] = 'Xem Bản Đánh Giá';
$string['success:appraisal:create'] = 'Bản đánh giá đã được tạo thành công';
$string['success:appraisal:delete'] = 'Bản đánh giá đã được xóa thành công';
$string['success:appraisal:update'] = 'Bản đánh giá đã được cập nhật thành công';
$string['error:appraisal:create'] = 'Rất tiếc, xuất hiện lỗi trong quá trình tạo bản đánh giá';
$string['error:appraisal:delete'] = 'Rất tiếc, xuất hiện lỗi trong quá trình xóa bản đánh giá';
$string['error:appraisal:select'] = 'Vui lòng chọn ít nhất một bản đánh giá';
$string['error:appraisal:update'] = 'Rất tiếc, xuất hiện lỗi trong quá trình cập nhật bản đánh giá';
$string['error:appraisalexists'] = 'Người dùng này có một bản đánh giá đang có hiệu lực ';
$string['error:appraiseeassuperior'] = 'Người được đánh giá không thể cũng là người đánh giá hoặc Người Sign Off';
$string['error:appraisernotvalid'] = 'Không có người đánh giá được chọn trong nhóm này ';
$string['error:duedate'] = 'Vui lòng nhập ngày hết hạn';
$string['error:togglef2f:complete'] = 'Không thể đánh dấu F2F là đã diễn ra';
$string['error:togglef2f:notcomplete'] = 'Không thể đánh dấu F2F là chưa diễn ra';
$string['error:selectusers'] = 'Vui lòng lựa chọn người đánh giá và Người Sign Off';
$string['appraisee_feedback_email_success'] = 'Đã gửi email thành công';
$string['appraisee_feedback_email_error'] = 'Không gửi email được';
$string['appraisee_feedback_invalid_edit_error'] = 'Không cung cấp đúng địa chỉ email ';
$string['appraisee_feedback_inuse_edit_error'] = 'Địa chỉ email đang được sử dụng ';
$string['appraisee_feedback_inuse_email_error'] = 'Địa chỉ email đang được sử dụng ';
$string['appraisee_feedback_resend_success'] = 'Đã gửi lại email thành công';
$string['appraisee_feedback_resend_error'] = 'Xuất hiện lỗi, vui lòng gửi email lại';
$string['form:choosedots'] = 'Chọn …';
$string['form:delete'] = 'Xóa';
$string['form:edit'] = 'Điền vào';
$string['form:language'] = 'Ngôn ngữ';
$string['form:addfeedback:alert:cancelled'] = 'Đã hủy "gửi đi", phản hồi cho bản đánh giá của bạn chưa được gửi';
$string['form:addfeedback:alert:error'] = 'Rất tiếc, xuất hiện lỗi khi gửi phản hồi cho bản đánh giá của bạn';
$string['form:addfeedback:alert:saved'] = 'Cảm ơn, phản hồi cho bản đánh giá của bạn đã được được gửi đi ';
$string['form:feedback:alert:cancelled'] = 'Đã hủy "gửi đi", yêu cầu phản hồi cho bản đánh giá của bạn chưa được gửi';
$string['form:feedback:alert:error'] = 'Rất tiếc, xuất hiện lỗi khi gửi yêu cầu phản hồi cho bản đánh giá của bạn';
$string['form:feedback:alert:saved'] = 'Yêu cầu phản hồi cho bản đánh giá của bạn đã được được gửi đi ';
$string['form:lastyear:nolastyear'] = 'Chú ý: Chúng tôi không thấy bản đánh giá trước đây của bạn trên hệ thống. Vui lòng tải bản đánh giá cũ bằng file pdf/ word bên dưới';
$string['form:lastyear:file'] = '<strong>File xem lại đã được người được đánh giá tải lên: <a href="{$a->path}" target="_blank">{$a->filename}</a></strong>';
$string['form:lastyear:cardinfo:developmentlink'] = 'Phát triển đạt được trong năm trước';
$string['form:lastyear:cardinfo:performancelink'] = 'Kết quả thể hiện trong năm trước';
$string['feedbackrequests:description'] = 'Dashboard thể hiện bất kỳ yêu cầu phản hồi nào còn tồn đọng và cho phép bạn truy cập bất kỳ phản hồi nào mà bạn đã đưa ra trong quá khứ';
$string['feedbackrequests:outstanding'] = 'Yêu cầu còn tồn đọng';
$string['feedbackrequests:norequests'] = 'Không có yêu cầu phản hồi nào còn tồn đọng';
$string['feedbackrequests:completed'] = 'Các yêu cầu đã hoàn tất';
$string['feedbackrequests:nocompleted'] = 'Không có yêu cầu phản hồi nào đã hoàn tất';
$string['feedbackrequests:th:actions'] = 'Hành động';
$string['feedbackrequests:emailcopy'] = 'Gửi cho tôi bản sao qua email';
$string['feedbackrequests:submitfeedback'] = 'Nộp phản hồi';
$string['feedbackrequests:received:confidential'] = 'Đã nhận (bảo mật)';
$string['feedbackrequests:received:nonconfidential'] = 'Đã nhận';
$string['feedbackrequests:paneltitle:confidential'] = 'Phản hồi (bảo mật)';
$string['feedbackrequests:paneltitle:nonconfidential'] = 'Phản hồi';
$string['feedbackrequests:legend'] = '* có nghĩa là người đóng góp do người đánh giá thêm vào';
/*
$string['email:subject:myfeedback'] = 'Phản hồi của bạn cho bản đánh giá  của   {{appraisee}}';
$string['email:body:myfeedback'] = 'Kính gửi {{recipient}},
Bạn đã nộp {{confidential}} feedback for {{appraisee}}: {{feedback}} {{feedback_2}}';
*/
$string['feedbackrequests:confidential'] = 'bảo mật';
$string['feedbackrequests:nonconfidential'] = 'không bảo mật';
$string['success:checkin:add'] = 'check-in thành công';
$string['error:checkin:add'] = 'check-in thất bại';
$string['error:checkin:validation'] = 'vui lòng điền nội dung vào';
$string['checkin:deleted'] = 'xóa check-in';
$string['checkin:delete:failed'] = 'không xóa check-in được';
$string['checkin:update'] = 'cập nhật';
$string['checkin:addnewdots'] = 'check-in…';
// END V string translations - spreadsheet

//ADDED STRINGS
// $string['comment:status:7_to_9'] = 'Nhận xét của Leader đã được thêm vào bởi {$a->relateduser}.';

// $string['status:9'] = 'Bản đánh giá hoàn tất';

// ****** WORKFLOW Email6-APPRAISEE ******
//$string['email:body:status:6_to_7:appraisee'] = '<p>{{appraiseefirstname}} thân mến,</p><p>Tôi đã xem và sign off bản đánh giá của bạn.</p>{{groupleaderextra}}{{comment}}<p>Bạn có thể xem bản đánh giá hoàn chỉnh bằng cách bấm vào <a href="{{linkappraisee}}">đây</a>.</p><p>Trân trọng,<br />{{signofffirstname}} {{signofflastname}}</p><br /><hr><p>Further assistance can be found <a href="https://moodle.arup.com/appraisal/help">here</a> alternatively you can contact your local HR group or raise a Service Desk ticket.</p><p>This is an auto generated message sent to {{appraiseeemail}} from {{signoffemail}} by moodle.arup.com - Appraisal status: {{status}} - Email6Appraisee</p><p>Trouble viewing? To view your appraisal online please copy and paste this URL {{linkappraisee}} into your browser.</p>';

//$string['email:body:status:6_to_7:appraisee:groupleaderextra'] = '<p>Bản đánh giá  hiện nay đã hoàn tất và chờ leader xem và tóm tắt. Bạn sẽ được thông báo khi việc này được thực hiện</p>';

//$string['email:subject:status:6_to_7:appraisee'] = 'Your Appraisal is Complete';

// ****** WORKFLOW Email6-APPRAISER ******
//$string['email:body:status:6_to_7:appraiser'] = '<p>{{appraiserfirstname}} thân mến,</p><p>Tôi đã sign off bản đánh giá của {{appraiseefirstname}} {{appraiseelastname}}.</p>{{groupleaderextra}}{{comment}}<p>Bạn có thể xem bản đánh giá hoàn chỉnh bằng cách bấm vào <a href="{{linkappraiser}}">đây</a>.</p><p>Trân trọng,<br />{{signofffirstname}} {{signofflastname}}</p><br /><hr><p>Further assistance can be found <a href="https://moodle.arup.com/appraisal/help">here</a> alternatively you can contact your local HR group or raise a Service Desk ticket.</p><p>This is an auto generated message sent to {{appraiseremail}} from {{signoffemail}} by moodle.arup.com - Appraisal status: {{status}} - Email6Appraiser</p><p>Trouble viewing? To view your appraiser dashboard online please copy and paste this URL {{linkappraiserdashboard}} into your browser.</p>';

//$string['email:body:status:6_to_7:appraiser:groupleaderextra'] = '<p>Bản đánh giá  hiện nay đã hoàn tất và chờ leader xem và tóm tắt. Bạn sẽ được thông báo khi việc này được thực hiện</p>';
//$string['email:subject:status:6_to_7:appraiser'] = 'Appraisal ({{appraiseefirstname}} {{appraiseelastname}}) is complete';

// ****** WORKFLOW Email7-GROUPLEADER ******
//$string['email:body:status:6_to_7:groupleader'] = '<p>Kính gửi {{groupleaderfirstname}},</p><p>Bản đánh giá của {{appraiseefirstname}} {{appraiseelastname}} đã hoàn tất và sẵn sàng để bạn xem xét và viết tóm tắt.</p>{{comment}}<p>Có thể truy cập bản đánh giá bằng cách bấm vào <a href="{{linkgroupleader}}">here</a>.</p><p>Trân trọng,<br />{{signofffirstname}} {{signofflastname}}</p><br /><hr><p>Further assistance can be found <a href="https://moodle.arup.com/appraisal/help">here</a> alternatively you can contact your local HR group or raise a Service Desk ticket.</p><p>This is an auto generated message sent to {{groupleaderemail}} from {{signoffemail}} by moodle.arup.com - Appraisal status: {{status}} - Email7Leader</p><p>Trouble viewing? To view your leader dashboard online please copy and paste this URL {{linkgroupleaderdashboard}} into your browser.</p>';

//$string['email:subject:status:6_to_7:groupleader'] = 'Bản đánh giá ({{appraiseefirstname}} {{appraiseelastname}}) đã sẵn sàng để bạn xem xét';

//$string['email:replacement:comment'] = '<p>Nhận xét của tôi:<br />{$a}</p>';

// ERROR: mising translation - further assistance