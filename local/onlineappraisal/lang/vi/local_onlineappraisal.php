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

// Checkins
$string['appraisee_checkin_title'] = 'Section 6. Check-in';
$string['checkins_intro'] = 'Trong suốt một năm, chúng tôi mong rằng người được đánh giá và người đánh giá sẽ cần trao đổi về  tình hình thực hiện so với Kế Hoạch Hành Động Theo Thỏa Thuận, Kế Hoạch Phát Triển, hành động và thể hiện. Người được đánh giá và/ hoặc người đánh giá có thể sử dụng phần dưới đây để ghi chép lại tiến trình. Tần suất trao đổi là tùy thuộc vào các bạn nhưng đề xuất là ít nhất một lần một năm';

// Feedback contribution
$string['confidential_label_text'] = 'Đánh dấu vào ô này để bảo mật nhận xét của bạn. Nếu bạn không đánh dấu, người được đánh giá sẽ thấy nhận xét của bạn.';

// Feedback Email - APPRAISEE
$string['email:subject:appraiseefeedback'] = 'Yêu cầu phản hồi cho bản đánh giá của tôi';

// Feedback Email - APPRAISER
$string['email:subject:appraiserfeedback'] = 'Yêu cầu phản hồi cho bản đánh giá của  {{appraisee_fullname}}';

// PDF Strings
$string['pdf:form:summaries:appraisee'] = 'Nhận xét của người được đánh giá';
$string['pdf:form:summaries:appraiser'] = 'Tóm tắt của người đánh giá về biểu hiện chung của người được đánh giá';
$string['pdf:form:summaries:signoff'] = 'Tổng kết sign off';
$string['pdf:form:summaries:recommendations'] = 'Những hành động đã thống nhất';

// END FORM

// START OVERVIEW CONTENT

// Overview page APPRAISEE Content.
$string['overview:content:appraisee:1'] = ''; // Never seen...

$string['overview:content:appraisee:2:3'] = 'Người đánh giá đã đề nghị thay đổi lên bản thảo<br /><br />
<strong>Bước tiếp theo</strong>
<ul class="m-b-20">
    <li>Thay đổi như đề nghị của người đánh giá (vui lòng xem nhật ký hoạt động để biết thêm về những điều được đề nghị)</li>
    <li>Share bản thảo với {$a->styledappraisername}.</li>
</ul>';

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
$string['overview:content:appraisee:8'] = $string['overview:content:appraisee:7']; // For legacy where there was a six month status.
$string['overview:content:appraisee:9'] = $string['overview:content:appraisee:7']; // When Groupleader added summary.

// Overview page APPRAISER Content.
$string['overview:content:appraiser:1'] = ''; // Never seen...
$string['overview:content:appraiser:2'] = 'Bản đánh giá hiện đang được {$a->styledappraiseename} soạn thảo. Bạn sẽ nhận được thông báo xem xét bản đánh giá khi nó hoàn tất.<br /><br />
<div class="alert alert-danger" role="alert"><strong>Chú ý:</strong> Bạn sẽ không thể xem bản đánh giá cho đến khi nó được share với bạn</div>';

$string['overview:content:appraiser:2:3'] = 'Bạn đã trả lại bản đánh giá cho {$a->styledappraiseename} để thay đổi. Bạn sẽ nhận được thông báo khi bản đánh giá được cập nhật, và sẵn sàng để bạn xem lại lần nữa.<br /><br />
<div class="alert alert-danger" role="alert"><strong>Chú ý:</strong> Bạn vẫn có thể thay đổi những phần của mình</div>';

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

$string['overview:content:signoff:8'] = $string['overview:content:signoff:7']; // For legacy where there was a six month status.
$string['overview:content:signoff:9'] = $string['overview:content:signoff:7']; // When groupleader added summary.

// Overview page GROUP LEADER Content.
$string['overview:content:groupleader:1'] = ''; // Never seen...

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

// 2017 : Updates and additions.
$string['addreceivedfeedback'] = 'Thêm Phản hồi nhận được';
$string['admin:allstaff:assigned'] = 'Phân công cho đợt đánh giá này';
$string['admin:allstaff:assigned:none'] = 'Không có người sử dụng được phân công cho đợt đánh giá này';
$string['admin:allstaff:button:lock'] = 'Phân công người sử dụng cho đợt đánh giá';
$string['admin:allstaff:button:start'] = 'Bắt đầu đợt đánh giá';
$string['admin:allstaff:button:update'] = 'Cập nhật ngày đến hạn mặc định';
$string['admin:allstaff:notassigned'] = 'Chưa phân công cho đợt đánh giá này';
$string['admin:allstaff:notassigned:none'] = 'Tất cả người sử dụng đã được phân công cho đợt đánh giá này';
$string['admin:allstaff:nousers'] = 'Không có người sử dụng active trong nhóm này';
$string['admin:appraisalcycle:assign'] = 'Phân công';
$string['admin:appraisalcycle:assign:tooltip'] = 'Phân công người sử dụng cho đợt đánh giá';
$string['admin:appraisalcycle:closed'] = 'Đợt đánh giá này đã kết thúc, tất cả bản đánh giá của đợt này đã được lưu';
$string['admin:appraisalcycle:unassign'] = 'Hủy phân công';
$string['admin:appraisalcycle:unassign:tooltip'] = 'Hủy phân công người sử dụng cho đợt đánh giá';
$string['admin:appraisalnotrequired:noreason'] = 'Chưa có lý do';
$string['admin:appraisalvip'] = 'Bản đánh giá VIP';
$string['admin:confirm:lock'] = 'Bạn có chắc chắn phân công những người sử dụng được đánh dấu và khóa danh sách người sử dụng?';
$string['admin:confirm:start'] = 'Bạn có chắc chắn mình muốn bắt đầu làm bản đánh giá không?';
$string['admin:duedate:default'] = 'Ngày đến hạn mặc định';
$string['admin:leaver'] = 'Người sử dụng không còn là nhân viên active nữa';
$string['admin:lockingdots'] = 'Phân công …';
$string['admin:requiresappraisal'] = 'Yêu cầu làm bản đánh giá';
$string['admin:start'] = 'Bắt đầu đợt đánh giá';
$string['admin:startingdots'] = 'Bắt đầu…';
$string['admin:toptext:allstaff:closed'] = '<div class="alert alert-danger">Đợt đánh giá {$a} đã kết thúc.</div>
Đợt đánh giá này đã kết thúc và không thể chỉnh sửa được nữa.';
$string['admin:toptext:allstaff:notclosed'] = '<div class="alert alert-success">Đợt đánh giá {$a} đã mở</div><p>Danh sách sau đây thể hiện tất cả người sử dụng có trong cost centre trên đây trong Moodle. Nếu có khác biệt nào trong danh sách, vui lòng liên hệ BP Nhân sự để kiểm tra dữ liệu trong TAPS.</p><p>Sử dụng danh sách phân công và hủy phân công dưới đây để thêm hoặc bớt người sử dụng từ đợt đánh giá hiện tại. Những nhân viên mới sẽ không được tự động thêm vào và cần phải được phân công nếu như họ có yêu cầu làm bản đánh giá. Nhân viên nghỉ việc với bản đánh giá active(assigned) sẽ hiển thị màu xám trừ khi bạn xóa chúng khỏi đợt đánh già này. Để tạo bản đánh giá, vui lòng sử dụng tab "Initialise" trong navigation box.</p>';
$string['admin:toptext:allstaff:notlocked'] = '<div class="alert alert-warning">Người sử dụng cho đợt đánh giá mới {$a} chưa được phân công. </div><p>Danh sách sau đây thể hiện tất cả người sử dụng có trong cost centre trên đây trong Moodle. Nếu có khác biệt nào trong danh sách, vui lòng liên hệ BP Nhân sự để kiểm tra dữ liệu trong TAPS.</p><p>Vui lòng kiểm tra và đánh dấu những người sử dụng có cần làm bản đánh giá hay không cho đợt đánh giá này trước khi nhấn vào nút "phân công người dử dụng cho đợt đánh giá" ở phía dưới trang nhằm kích hoạt quá trình làm bản đánh giá.(Ghi chú: việc này có thể được điều chỉnh bất cứ khi nào trên trang Tất cả nhân viên khi chọn người sử dụng cho đợt đánh giá hiện tại).</p>';
$string['admin:toptext:allstaff:notstarted'] = '<div class="alert alert-warning">Đợt đánh giá mới {$a}  chưa bắt đầu </div>Bắt đầu một đợt đánh giá mới sẽ lưu trữ tất cả các bản đánh giá hiện tại cho nhóm này. Một khi đã lưu trữ, bạn sẽ có thể sắp xếp ai cần phải làm bản đánh giá trong đợt này trước khi chuyển sang trang initialise để bắt đầu khởi động bản đánh giá. Vui lòng thêm vào ngày đến hạn mặc định cho bản đánh giá của bạn trước khi nhân nút "Bắt đầu đợt đánh giá" để bắt đầu.';
$string['admin:updatingdots'] = 'Cập nhật …';
$string['admin:usercount:assigned'] = '({$a} người sử dụng)';
$string['appraisee_feedback_savedraft_error'] = 'Xuất hiện lỗi khi lưu bản thảo';
$string['appraisee_feedback_savedraft_success'] = 'Bản thảo phản hồi đã được lưu';
$string['appraisee_feedback_viewrequest_text'] = 'Email yêu cầu xem';
$string['appraisee_welcome'] = 'Bản đánh giá của bạn là 1 cơ hội để bạn và người đánh giá của mình trao đổi về thể hiện của bạn, hướng phát triển nghề nghiệp cũng như đóng góp cho công ty. Chúng tôi mong muốn đây là cuộc trao đổi mang tính xây dựng, cụ thể và hữu ích cho từng cá nhân.<br /><br />
Mục đích công cụ trực tuyến là giúp bạn có được các ghi nhận trong khi trao đổi mà bạn có thể xem lại bất cứ lúc nào trong năm. Thông tin thêm về quá trình đánh giá có thể tìm thấy <a href="https://moodle.arup.com/appraisal/essentials" target="_blank"> ở đây</a>';
$string['appraisee_welcome_info'] = 'Hạn chót bản đánh giá của bạn cho năm này là {$a}.';
$string['email:body:appraiseefeedback'] = '{{emailmsg}}
<br>
<hr>
<p>Vui lòng nhấp {{link}}  để đóng góp phản hồi. </p>
<p>Appraisal Name {{appraisee_fullname}}<br>
Tên bản đánh giá  <span class="placeholder">{{held_date}}</span></p>
<p>
Bản đánh giá của tôi vào ngày {{appraisee_fullname}} đến {{firstname}} {{lastname}}.</p>
<p>
Nếu đường dẫn bên trên không hoạt động, vui lòng chép đường dẫn sau vào trình duyệt của bạn để đăng nhập vào bản đánh giá:<br />{{linkurl}}</p>';
$string['email:body:appraiseefeedbackmsg'] = '<span class="placeholder bind_firstname">{{firstname}}</span> thân mến,</p>
<p>
Buổi họp đánh giá của tôi sẽ được sắp xếp vào <span class="placeholder">{{held_date}}</span>. Người đánh giá của tôi là <span class="placeholder">{{appraiser_fullname}}</span>. Vì bạn và tôi đã làm việc mật thiết với nhau trong năm vừa qua, tôi rất mong nhận được phản hồi của bạn cho những mặt mà bạn đánh giá cao đóng góp của tôi cũng như những lĩnh vực mà bạn thấy tôi có thể làm hiệu quả hơn. Nếu bạn thấy muốn đóng góp, vui lòng nhấp vào đường dẫn sau đây để phản hồi. </p> <p>

Tôi rất cám ơn nếu bạn có thể phản hồi trước ngày họp đánh giá của tôi.</p>
<p class="ignoreoncopy">Dưới đây là các ý kiến bổ sung từ <span class="placeholder">{{appraisee_fullname}}</span>:<br /> <span>{{emailtext}}</span></p>
<p>Trân trọng,<br />
<span class="placeholder">{{appraisee_fullname}}</span></p>';
$string['email:body:appraiserfeedback'] = '{{emailmsg}}
<br>
<hr>
<p>Vui lòng nhấp {{link}} để đóng góp phản hồi.</p>
<p>Tên bản đánh giá {{appraisee_fullname}}<br>
Bản đánh giá của họ vào ngày <span class="placeholder">{{held_date}}</span></p>
<p>Đây là email tự động gửi từ {{appraiser_fullname}} đến {{firstname}} {{lastname}}.</p>
<p>Nếu đường dẫn bên trên không hoạt động, vui lòng chép đường dẫn sau vào trình duyệt của bạn để đăng nhập vào bản đánh giá:<br />{{linkurl}}</p>';
$string['email:body:appraiserfeedbackmsg'] = '<p><span class="placeholder bind_firstname">{{firstname}}</span> thân mến,</p>
<p>Buổi họp đánh giá cho <span class="placeholder">{{appraisee_fullname}}</span> đã được thu xếp vào ngày <span class="placeholder">{{held_date}}</span>. Vì chúng ta gần đây đã làm việc mật thiết với nhau, tôi rất mong nhận được phản hồi của bạn về những mặt mà bạn đánh giá cao đóng góp của tôi cũng như những lĩnh vực mà bạn thấy tôi có thể làm hiệu quả hơn. Nếu bạn thấy muốn đóng góp, vui lòng nhấp vào đường dẫn sau đây để phản hồi.</p> <p>Tôi rất cám ơn nếu bạn có thể phản hồi trước ngày họp đánh giá của tôi.</p>
<p class="ignoreoncopy">Dưới đây là các ý kiến bổ sung từ <span class="placeholder">{{appraiser_fullname}}</span>:<br /> <span>{{emailtext}}</span></p>
<p>Trân trọng, <br /> <span class="placeholder">{{appraiser_fullname}}</span></p>';
$string['email:body:myfeedback'] = '<p>{{recipient}} thân mến,</p> <p>Bạn đã gửi phản hồi {{confidential}} sau đây cho {{appraisee}}:</p> <div>{{feedback}}</div> <div>{{feedback_2}}</div>';
$string['email:subject:myfeedback'] = 'Phản hồi đánh giá của bạn cho {{appraisee}}';
$string['error:appraisalcycle:groupcohort'] = 'Nhóm không đúng hoặc thông tin đợt đánh giá đã được đệ trình';
$string['error:cohortold'] = 'Đợt đánh giá được chọn không còn active nữa và chưa bao giờ được sắp xếp cho nhóm này <br><a href="{$a}">Go to current appraisal cycle</a>';
$string['error:cohortuser'] = 'Người được đánh giá không cần làm bản đánh giá cho đợt đánh giá hiện tại';
$string['error:noappraisal'] = 'Xãy ra lỗi - Bạn không có bản đánh giá trong hệ thống. Vui lòng liên hệ BP Quản lý liệt kê dưới đây để được trợ giúp nếu bạn cần lập bản đánh giá: {$a}';
$string['error:permission:appraisalcycle:lock'] = 'Bạn không được phép phân công người sử dụng cho đợt đánh giá';
$string['error:permission:appraisalcycle:start'] = 'Bạn không được phép bắt đầu một đợt đánh giá mới.';
$string['error:permission:appraisalcycle:update'] = 'Bạn không được phép cập nhật đợt đánh giá.';
$string['error:toggleassign:confirm:assign'] = 'Việc này sẽ phân công người sử dụng cho đợt đánh giá hiện tạu và đánh dấu là yêu cầu làm bản đánh giá.<br />Nếu người sử dụng đã có bản đánh giá được lưu trước đây trong đợt đánh giá này thì nó sẽ được kích hoạt lại, nếu không nó sẽ có sẵn để bắt đầu trong trang initialise.<br />Bạn có chắc chắn muốn tiến hành?<br />{$a->yes} {$a->no}';
$string['error:toggleassign:confirm:unassign'] = 'Người sử dụng sẽ được hủy phân công trong đợt đánh giá hiện tại và được đánh dấu là không yêu cầu làm bản đánh giá và sẽ cần phải cung cấp lý do cho việc xác nhận dưới đây.<br />Bạn có chắc chắn muốn tiến hành?<br />{$a->yes} {$a->no}';
$string['error:toggleassign:confirm:unassign:appraisalexists'] = 'Cảnh báo: Có 1 bản đánh giá được khởi động trong hệ thống cho người sử dụng này.<br />Nếu tiếp tục, bạn sẽ lưu (nếu có nội dung) hoặc xóa (nếu chưa bắt đầu) bản đánh giá của họ tùy theo tình trạng (có nghĩa là họ sẽ không thể chỉnh sửa được).<br />Người sử dụng sẽ được hủy phân công trong đợt đánh giá hiện tại và được đánh dấu là không yêu cầu làm bản đánh giá và sẽ cần phải cung cấp lý do cho việc xác nhận dưới đây.<br />Bạn có chắc chắn muốn tiến hành?<br />{$a->yes} {$a->no}';
$string['error:toggleassign:reason'] = 'Vui lòng xác nhận lý do người sử dụng sau đây không yêu cầu làm appraisal .

{$a->reasonfield} {$a->continue} {$a->cancel}';
$string['error:toggleassign:reason:cancel'] = 'Hủy bỏ';
$string['error:toggleassign:reason:continue'] = 'Tiếp tục';
$string['error:togglerequired:confirmnotrequired'] = 'Thay đổi người sử dụng sang trạng thái không yêu cầu làm bản đánh giá sẽ hủy phân công những người này khỏi đợt đánh giá hiện tại nếu họ đã được phân công trong đó.<br />
Người sử dụng này hiện tại không có bản đánh giá active trong đợt đánh giá hiện tại.<br />
Bạn có chắc chắn muốn tiến hành? <br />{$a->yes} {$a->no}';
$string['error:togglerequired:confirmnotrequired:appraisalexists'] = 'Cảnh báo: Có 1 bản đánh giá hiện tại đã được khởi động trong hệ thống cho người sử dụng này.<br />
 
Nếu tiếp tục, bạn sẽ lưu (nếu có nội dung) hoặc xóa (nếu chưa bắt đầu) bản đánh giá của họ tùy theo tình trạng (có nghĩa là họ sẽ không thể chỉnh sửa được).<br /> Người sử dụng sẽ được hủy phân công trong đợt đánh giá liên quan. <br /> Bạn có chắc chắn muốn tiến hành? <br />{$a->yes} {$a->no}';
$string['error:togglerequired:confirmrequired'] = 'Thay đổi người sử dụng sang trạng thái yêu cầu làm bản đánh giá sẽ phân công những người này trong đợt đánh giá hiện tại.<br /> Nếu người sử dụng đã có bản đánh giá được lưu trước đây trong đợt đánh giá này thì nó sẽ được kích hoạt lại, nếu không nó sẽ có sẵn để bắt đầu trong trang initialise. <br /> Bạn có chắc chắn muốn tiến hành?
    <br />{$a->yes} {$a->no}';
$string['error:togglerequired:reason'] = 'Vui lòng xác nhận lý do người sử dụng sau đây không yêu cầu làm appraisal .

{$a->reasonfield} {$a->continue} {$a->cancel}';
$string['error:togglerequired:reason:cancel'] = 'Hủy bỏ';
$string['error:togglerequired:reason:continue'] = 'Tiếp tục';
$string['feedback_header'] = 'Đã đưa ra phản hồi cho {$a->appraisee_fullname} (Người đánh giá: {$a->appraiser_fullname} - Ngày đánh giá: {$a->facetofacedate})';
$string['feedback_intro'] = 'Vui lòng chọn 3 hoặc hơn 3 đồng nghiệp gửi phản hồi cho bản đánh giá của bạn. Ở hầu hết các khu vực, bản đánh giá có thể là nội bộ hoặc bên ngoài. Vui lòng xem khu vực của bạn để có hướng dẫn cụ thể. <br/><br/> Đối với người đóng góp phản hồi nội bộ, bạn nên cân nhắc thu thập phản hồi từ quan điểm "360 độ", có nghĩa là người cùng cấp, cấp cao hơn và cấp thấp hơn. Bạn phải lựa chọn thành phần đa dạng. <br/><br/><div data-visible-regions="UKMEA, EUROPE, AUSTRALASIA">Một trong những người đóng góp của bạn có thể là khách hàng hoặc công tác viên bên ngoài mà biết rõ về bạn.</div> <div data-visible-regions="East Asia"><br /><div class="alert alert-warning">FĐối với Khu vực Đông Á, chúng tôi mong rằng phản hồi chỉ từ nguồn nội bộ. Ý kiến từ khách hàng hoặc cộng tác viên bên ngoài sẽ được tiếp thu và phải hồi qua người trong nội bộ. </div></div> <br /><div class="alert alert-danger"> Ghi chú: Phản hồi của người đóng góp ý kiến sẽ được đăng tải ở đây ngay khi nhận được trừ khi phản hồi được người đánh giá yêu cầu. Trong trường hợp này, người đánh giá của bạn sẽ gửi bản đánh giá của bạn để lấy ý kiến sau cùng của bạn (giai đoạn 3) đối với phản hồi đã đưa ra.</div>';
$string['feedbackrequests:paneltitle:requestmail'] = 'Email yêu cầu phản hồi';
$string['form:addfeedback:addfeedback'] = 'Vui lòng mô tả 3 lĩnh vực mà bạn đánh giá cao đóng góp của người được đánh giá trong 12 tháng quá.';
$string['form:addfeedback:addfeedback_2'] = 'Vui lòng cung cấp chi tiết 3 lĩnh vực mà bạn cảm thấy họ có thể làm hiệu quả hơn. Hãy trung thực nhưng góp ý mang tính xây dựng vì phản hồi này có thể giúp đồng nghiệp của bạn xử lý các vấn đề hiệu quả hơn.';
$string['form:addfeedback:addfeedback_2help'] = '<div
class="well well-sm">Điều quan trọng là tất cả thành viên đều nhận được phản hồi cân bằng, có giá trị bao gồm cả ý kiến tích cực cũng như phê bình. <br>Để xem hướng dẫn vui lòng nhấp <a href="https://moodle.arup.com/scorm/_assets/ArupAppraisalGuidanceFeedback.pdf"
target="_blank">vào đây.</a></div>';
$string['form:addfeedback:addfeedback_help'] = 'Vui lòng chỉ copy và paste phản hồi của bạn vào hộp "đóng góp có giá trị" trừ khi  bạn có thể tách ra giữa "có giá trị" và "hiệu quả hơn".';
$string['form:addfeedback:addfeedbackhelp'] = '<div
class="well well-sm">Điều quan trọng là tất cả thành viên đều nhận được phản hồi cân bằng, có giá trị bao gồm cả ý kiến tích cực cũng như phê bình. <br>Để xem hướng dẫn vui lòng nhấp <a href="https://moodle.arup.com/scorm/_assets/ArupAppraisalGuidanceFeedback.pdf"
target="_blank">vào đây.</a></div>';
$string['form:addfeedback:firstname'] = 'Tên người đưa ra phản hồi';
$string['form:addfeedback:lastname'] = 'Họ người đưa ra phản hồi';
$string['form:addfeedback:saveddraft'] = 'Bạn đã lưu bản thảo phản hồi của mình. Người đánh giá và người được đánh giá sẽ không nhìn thấy bản thảo này trừ khi bạn gửi phản hồi đi.';
$string['form:addfeedback:savedraftbtn'] = 'Lưu bản thảo';
$string['form:addfeedback:savedraftbtntooltip'] = 'Lưu bản thảo để hoàn chỉnh sau. Bản này sẽ không được gửi cho người đánh giá và người được đánh giá';
$string['form:addfeedback:savefeedback'] = 'Lưu Phản hồi';
$string['form:development:comments'] = 'Ý kiến của người đánh giá';
$string['form:development:commentshelp'] = '<div class="well well-sm"><em>Do người đánh giá điền vào</em></div>';
$string['form:feedback:editemail'] = 'Soạn thảo';
$string['form:feedback:providefirstnamelastname'] = 'Vui lòng nhập họ và tên người nhận trước khi nhấn vào nút soạn thảo.';
$string['form:lastyear:cardinfo:performancelink'] = 'Kế hoạch tác động năm trước';
$string['form:lastyear:printappraisal'] = '<a href="{$a}" target="_blank">Bản đánh giá năm trước </a>có sẵn để xem (PDF - mở cửa sổ mới).';
$string['form:summaries:grpleader'] = '5.5 Tóm tắt của nhóm trưởng';
$string['form:summaries:grpleadercaption'] = 'Hoàn tất bởi {$a->fullname}{$a->date}';
$string['form:summaries:grpleaderhelp'] = '<div class="well well-sm"><em>Do nhân sự cấp cao hoàn tất với vai trò người phê duyệt sau cùng.</em></div>';
$string['introduction:video'] = '<img src="https://moodle.arup.com/scorm/_assets/ArupAppraisal.png" alt="Arup Appraisal logo"/>';
$string['leadersignoff'] = 'Giám đốc phê duyệt sau cùng';
$string['modal:printconfirm:cancel'] = 'Không, được rồi';
$string['modal:printconfirm:content'] = 'Bạn có thật sự cần in tài liệu này không?';
$string['modal:printconfirm:continue'] = 'Vâng, tiến hành';
$string['modal:printconfirm:title'] = 'Hãy cân nhắc trước khi in';
$string['overview:content:appraisee:2'] = 'Vui lòng hoàn tất bản đánh giá bản thân của bạn.<br /><br /> <strong>Các bước tiếp theo:</strong> <ul class="m-b-20"> <li>Điền ngày sẽ gặp mặt trực tiếp </li> <li>Yêu cầu đưa ra phản hồi</li> <li>Phản ánh và nhận xét về Thể hiện và Phát triển của bạn trong năm ngoái</li> <li>Điền vào Định Hướng Nghề Nghiệp, Tác Động và Kế Hoạch Phát Triển để thảo luận trong buổi gặp mặt trực tiếp</li> <li>Share bản thảo cho {$a->styledappraisername}, người đánh giá bạn</li> </ul> Vui lòng gửi bản thảo cho người đánh giá bạn ít nhất <strong><u>một tuần</u></strong> trước ngày gặp trực tiếp. Bạn vẫn có thể tiếp tục bổ sung thêm sau khi share.<br /><br /> <div class="alert alert-danger" role="alert"><strong>Chú ý:</strong> Người đánh giá bạn sẽ không thể thấy bản thảo cho đến khi bạn share với họ.</div>';
$string['overview:content:appraisee:3'] = 'Hiện tại bạn đã nộp bản thảo đánh giá của mình cho {$a->styledappraisername} xem xét. <br /><br /> <strong>Bước tiếp theo: </strong> <ul class="m-b-20"><li>Họp trực tiếp gặp mặt - trước khi họp bạn có thể mong muốn:</li> <ul class="m-b-0"> <li><a class="oa-print-confirm" href="{$a->printappraisalurl}">Tải Bản đánh giá</a></li> <li><a href="https://moodle.arup.com/appraisal/reference" target="_blank">Tải Hướng dẫn Tham khảo nhanh</a></li> </ul> <li>Sau cuộc họp, người đánh giá sẽ trả lại bản đánh giá cho bạn. Bạn sẽ được yêu cầu chỉnh sửa như đã thống nhất trong buổi họp trực tiếp gặp mặt hoặc viết các ý kiến sau cùng.</li> </ul> <div class="alert alert-danger" role="alert"><strong>Ghi chú:</strong> Bạn có thể tiếp tục soạn thảo bản đánh giá trong khi nó đang ở chỗ người đanh giá tuy nhiên đề nghị bạn sử dụng Activity log để báo các điểm mà bạn đã thay đổi.</div>';
$string['overview:content:appraisee:7:groupleadersummary'] = 'Bản đánh giá của bạn hiện nay đã hoàn tất và chờ giám đốc xem xét và tóm tắt. Bạn sẽ được thông báo khi được phê duyệt xong';
$string['overview:content:appraiser:3'] = '{$a->styledappraiseename} đã nộp bản thảo để chuẩn bị cho buổi họp trực tiếp gặp mặt. <br /><br /> <strong>Các bước tiếp theo:</strong> <ul class="m-b-20"> <li>Vui lòng xem xét bản đánh giá để chuẩn bị cho buổi họp. Nếu bạn thấy cần thêm thông tin bổ sung thì trả lại cho người được đánh giá.</li> <li>Trước khi họp bạn nên</li> <ul class="m-b-0"> <li><a class="oa-print-confirm" href="{$a->printappraisalurl}">Tải bản đánh giá</a></li> <li><a class="oa-print-confirm" href="{$a->printfeedbackurl}">Tải các phản hồi nhận được</a></li> <li>Bạn cũng có thể muốn <a href="https://moodle.arup.com/appraisal/reference" target="_blank">tải hướng dẫn tham khảo nhanh </a></li></ul> <li>Tiếp theo buổi họp trực tiếp gặp mặt, vui lòng </li> <ul class="m-b-0"> <li>Đánh dấu buổi họp trực tiếp gặp mặt đã được tiến hành trong Mục Thông tin Người được đánh giá </li> <li>Thêm các ý kiến vào từng mục </li> <li>Viết tóm tắt và các hành động thống nhất vào mục Tóm tắt </li> (Nếu cần thiết bạn có thể  chuyển cho người được đánh giá bổ sung trước khi bạn bổ sung ý kiến của mình) </ul> <li>Gửi cho người được đánh giá xem ý kiến của bạn, xem các phản hồi và bổ sung ý kiến sau cùng của họ </li> </ul>';
$string['overview:content:appraiser:7:groupleadersummary'] = 'Bản đánh giá của bạn hiện nay đã hoàn tất và chờ giám đốc xem xét và tóm tắt. Bạn sẽ được thông báo khi được phê duyệt xong';
$string['overview:content:groupleader:2'] = 'Bản đánh giá đang thực hiện';
$string['overview:content:groupleader:3'] = 'Bản đánh giá đang thực hiện';
$string['overview:content:groupleader:4'] = 'Bản đánh giá đang thực hiện';
$string['overview:content:groupleader:5'] = 'Bản đánh giá đang thực hiện';
$string['overview:content:groupleader:6'] = 'Bản đánh giá đang thực hiện';
$string['overview:content:groupleader:7'] = 'Bản đánh giá đã hoàn tất và được phê duyệt';
$string['overview:content:groupleader:7:groupleadersummary'] = 'Bản đánh giá đã hoàn tất và chờ bạn xem xét tóm tắt.<br /><br /> <strong> Các bước tiếp theo:</strong> <ul class="m-b-20"> <li>Vui lòng thêm Tóm tắt của Giám đốc vào mục Tóm tắt.</li> <li>Nhấp vào nút Sign off</li> <li>Người được đánh giá, người đánh giá và người sign off sẽ nhận được thông báo khi bạn thêm ý kiến nhận xét.</li> </ul>';
$string['overview:content:groupleader:7:groupleadersummary:generic'] = 'Bản đánh giá này đã hoàn tất và chờ giám đốc nhận xét tóm tắt';
$string['overview:content:signoff:7:groupleadersummary'] = 'Bản đánh giá này đã hoàn tất và chờ giám đốc nhận xét tóm tắt. Bạn sẽ được thông báo khi tóm tắt xong.';
$string['overview:content:special:archived'] = '<div class="alert alert-danger" role="alert">Bản đánh giá này đã được lưu.<br />Hiện nay chỉ<a class="oa-print-confirm" href="{$a->printappraisalurl}">có thể tải xuống</a>.</div>';
$string['overview:content:special:archived:appraisee'] = '<div class="alert alert-danger" role="alert">Bản đánh giá này đã được lưu.<br /> <a class="oa-print-confirm" href="{$a->printappraisalurl}">Hiện nay chỉ có thể tải xuống </a>.</div>';
$string['overview:content:special:archived:groupleader:2'] = '<div class="alert alert-danger" role="alert">Bản đánh giá này đã được lưu. <br />
Bạn không được đăng nhập để chỉnh sửa nữa.</div>';
$string['overview:lastsaved'] = 'Lưu lần cuối: {$a}';
$string['overview:lastsaved:never'] = 'Chưa bao giờ';
$string['pdf:feedback:confidentialhelp:appraisee'] = '# Chú ý Phản hồi bảo mật bạn không thể xem';
$string['pdf:feedback:notyetavailable'] = 'Chưa xem được';
$string['pdf:feedback:requestedfrom'] = 'Người xem xét {$a->firstname} {$a->lastname}{$a->appraiserflag}{$a->confidentialflag}:';
$string['pdf:feedback:requestedhelp'] = '* Chú ý phản hồi do Người đánh giá yêu cầu chưa cho bạn xem được';
$string['pdf:form:summaries:grpleader'] = 'Tóm tắt của nhóm trưởng';
$string['pdf:header:warning'] = 'Tải xuống bởi: {$a->who} vào {$a->when}<br>Vui lòng không lưu nhưng chỗ không an toàn.';
$string['status:7:leadersignoff'] = 'Giám đốc ký phê duyệt';

$string['overview:content:groupleader:8'] = $string['overview:content:groupleader:7']; // For legacy where there was a six month status.
$string['overview:content:groupleader:9'] = $string['overview:content:groupleader:7'];