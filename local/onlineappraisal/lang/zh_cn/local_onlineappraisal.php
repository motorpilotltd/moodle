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
$string['alert:language:notdefault'] = '<strong>警告</strong>: 你现没有用英语在看评议。为确保每个人的参与，请你用最适合的语言来回答问题';

// Userinfo.
$string['form:userinfo:title'] = 'Appraisee Info';
$string['form:userinfo:intro'] = '请填写下面的详细信息。有些字段的预填信息摘自于TAPS，如果有任何预填信息不正确，请联系人力资源部。';
$string['form:userinfo:name'] = '被评议人名字';
$string['form:userinfo:staffid'] = '员工号';
$string['form:userinfo:grade'] = '级别';
$string['form:userinfo:jobtitle'] = '核心职位';
$string['form:userinfo:operationaljobtitle'] = '业务职位';
$string['form:userinfo:facetoface'] = '拟面谈会议日期';
$string['form:userinfo:facetofaceheld'] = '面谈会议已开';
$string['form:userinfo:setf2f'] = 'Set your face to face meeting time and date';

// START FORM

// Introduction Page
$string['appraisee_heading'] = '欢迎参加在线员工评议';
$string['appraisee_welcome'] = '你的员工评议是次机会，让你和你的评议人关于你的工作绩效和发展有一次宝贵的谈话。<br /><br />
在线评议工具的目的是帮助你记录谈话，并可以全年参阅。<br /><br />
点击右边的图像看Gregory Hodkinson介绍的信息<br /><br />
可以在<a href="https://moodle.arup.com/appraisal/essentials" target="_blank">此处</a>找到有关评议过程的进一步信息';

$string['introduction:video'] = '<img src="https://moodle.arup.com/scorm/_assets/Gregory_Hodkinson.jpg" alt="Changes to Appraisal" onclick="window.open(\'https://moodle.arup.com/scorm/_assets/intro.pdf\', \'_blank\');"/>';

// Last Year Review
$string['form:lastyear:title'] = 'Section 1: Review of last year';
$string['form:lastyear:nolastyear'] = '注：我们注意到你上一次的评议没有保存在系统里。请在下方上传你上一次评议的pdf或word版本。';
$string['form:lastyear:intro'] = '在这一部分被评议人和评议人要讨论在过去的12月取得了哪些成绩并是如何取得的。<a href="https://moodle.arup.com/appraisal/guide" target="_blank">评议指南</a>提供了更多信息有关这次讨论的特性';
$string['form:lastyear:upload'] = '上传评议';
$string['form:lastyear:appraiseereview'] = '1.1 被评议人回顾去年绩效';
$string['form:lastyear:appraiseereviewhelp'] = '<div class="well well-sm"> <em>总体来说，自上次评议以来，在项目、人员和客户而言，你是如何表现的？</em>
    <ul class="m-b-0">
        <li><em>你如何合作、共享信息和专业知识？结果如何？</em></li>
        <li><em>你的业绩有低于预期吗？</em></li>
        <li><em>如果你是负责人，你是否适当地管理他们的表现和行为，包括良好的和糟糕的？</em></li>
        <li><em>你如何使用技术让自己更加有效？</em></li>
    </ul>
    </div>';
$string['form:lastyear:appraiserreview'] = '1.2 评议人回顾去年绩效';
$string['form:lastyear:appraiserreviewhelp'] = '<div class="well well-sm"><em>请自上次评议后对被评议人的绩效回顾发表评语</em>
    <ul class="m-b-0">
        <li><em>他们已经取得了哪些进步？</em></li>
        <li><em>总结收到的反馈评价</em></li>
    </ul>
    <em>如果他们的表现或行为有低于预期的，<strong>必须</strong>讨论并记录在本节中。</em>
</div>';
$string['form:lastyear:appraiseedevelopment'] = '1.3 被评议人回顾去年个人发展';
$string['form:lastyear:appraiseedevelopmenthelp'] = '<div class="well well-sm">
<em>请自上次评议后评论自己的个人发展</em>
    <ul class="m-b-0">
        <li><em>你如何发展个人的技能、知识和行为？</em></li>
        <li><em>去年制定的发展计划到目前还有哪些尚未实现？</em></li>
    </ul>
</div>';
$string['form:lastyear:appraiseefeedback'] = '1.4 有什么会影响或提高你或你团队的表现？';
$string['form:lastyear:appraiseefeedbackhelp'] = '<div class="well well-sm"><em>由被评议人完成</em></div>';
$string['form:lastyear:file'] = '<strong>A review file has been uploaded by the appraisee: <a href="{$a->path}" target="_blank">{$a->filename}</a></strong>';
$string['form:lastyear:cardinfo:heading'] = 'Import from last year';
$string['form:lastyear:cardinfo:title'] = 'Title';
$string['form:lastyear:cardinfo:date'] = 'Date';
$string['form:lastyear:cardinfo:description'] = 'Description';
$string['form:lastyear:cardinfo:heading'] = 'Import from last year';
$string['form:lastyear:cardinfo:competency'] = 'Competency';
$string['form:lastyear:cardinfo:progress'] = 'Progress Required';
$string['form:lastyear:cardinfo:action'] = 'Action Required';
$string['form:lastyear:cardinfo:developmentlink'] = 'Last Year Development';
$string['form:lastyear:cardinfo:performancelink'] = 'Last Year Performance';
$string['form:lastyear:cardinfo:none'] = 'You do not have any information from last year available.';

// Career Direction
$string['form:careerdirection:title'] = 'Section 2: Career Direction';
$string['form:careerdirection:intro'] = '本节的目的是为了让被评议人考虑自己的职业理想，和评议人以切合实际的方式讨论。对于初级员工，这次谈话的范围可以是在 1-3年。对于高级别员工，范围可以是在3-5年。';
$string['form:careerdirection:progress'] = '2.1 你希望自己的职业生涯如何发展？';
$string['form:careerdirection:progresshelp'] = '<div class="well well-sm"> <em>你应该考虑：</em>
    <ul class="m-b-0">
        <li><em>你想做什么样的工作，担负何种程度的责任？</em></li>
        <li><em>在未来几年哪些对你的工作很重要，例如：广度，深度，专业性，普遍性，外派，设计，人员管理，等等？</em></li>
        <li><em>你想在哪里任职？</em></li>
    </ul>
</div>';
$string['form:careerdirection:comments'] = '2.2 评议人评语';
$string['form:careerdirection:commentshelp'] = '<div class="well well-sm">
    <em>你应该考虑：</em>
    <ul class="m-b-0">
        <li><em>被评议人的愿望是切合实际的，具有挑战性和雄心壮志的吗?</em></li>
        <li><em>什么样的角色，项目和其他工作的机会将提供所需要经验，技能和行为的发展？</em></li>
    </ul>
</div>';

// Agreed Impact Plan
$string['form:impactplan:title'] = 'Section 3: Agreed Impact Plan';
$string['form:impactplan:intro'] = '议定的影响计划阐述了被评议人在未来一年，想要如何在他的工作和其对公司的总体影响方面有所作为。该计划应包括被评议人将如何改善他们的工作，或者他们的项目/团队/办公室/团体。实际上，这意味着提供有关时间表，质量，预算，设计/创新的具体内容，以及对人、客户或整体工作的影响。<br /><br /> <a href="https://moodle.arup.com/appraisal/contribution" target="_blank">贡献指南</a>和<a href="https://moodle.arup.com/appraisal/guide" target="_blank">评议指南</a>给予如何改进的建议。';

$string['form:impactplan:impact'] = '3.1 描述明年你想要对你的项目，你的客户，你的团队或公司会有什么影响：';
$string['form:impactplan:impacthelp'] = '<div class="well well-sm">
    <em>在你的描述中可以包含：</em>
    <ul class="m-b-0">
        <li><em>你关注的领域</em></li>
        <li><em>他们为什么重要</em></li>
        <li><em>你将如何实现这些目标</em></li>
        <li><em>你将与谁合作</em></li>
        <li><em>大概的时间表︰ 3/6/12/18 个月或更长时间</em></li>
        <li><em>你议定的影响计划如何配合和支持你职业发展的需要</em></li>
    </ul>
</div>';
$string['form:impactplan:support'] = '3.2 从奥雅纳你需要什么样的支持，以实现这一目标？';
$string['form:impactplan:supporthelp'] = '<div class="well well-sm">
    <em>你可以考虑：</em>
    <ul class="m-b-0">
        <li><em>他人的帮助</em></li>
        <li><em>督导</em></li>
        <li><em>资源（时间，预算，装备）</em></li>
        <li><em>个人发展</em></li>
        <li><em>工具 （软件、 硬件）</em></li>
    </ul>
</div>';
$string['form:impactplan:comments'] = '3.3 评议人评语';
$string['form:impactplan:commentshelp'] = '<div class="well well-sm"><em>由评议人完成</em></div>';

// Development Plan
$string['form:development:title'] = 'Section 4: Development Plan';
$string['form:development:intro'] = '发展计划阐述了个人技能，知识或行为需要什么样改变，以支持被评议人的职业发展和议定的影响计划。<br /><br />
你需要如何在未来12-18个月来实现这一目标？你需要什么帮助，你何时计划进行这方面的发展？<br /><br />
<div class="well well-sm">在奥雅纳我们在个人发展中使用了"70-20-10"的原则。这意味着，对于大多数人来说，70％的发展应该是“在工作”从经验中获取。 20％应该是通过从其他人，也许是通过辅导或指导获得。最后的10％应该是正式的学习方法，如课堂教学或正式的在线学习。这些百分比只是一个参考。</div>';
$string['form:development:seventy'] = '工作过程中的学习 – 约70%';
$string['form:development:seventyhelp'] = '<div class="well well-sm"> <em>例如：</em>
    <ul class="m-b-0">
        <li><em>项目任务</em></li>
        <li><em>团队任务</em></li>
        <li><em>外派</em></li>
        <li><em>工作讨论和反馈</em></li>
        <li><em>项目评审，设计专家研讨会</em></li>
        <li><em>阅读</em></li>
        <li><em>研究</em></li>
    </ul>
</div>';
$string['form:development:twenty'] = '学习他人 – 约20%';
$string['form:development:twentyhelp'] = '<div class="well well-sm"> <em>例如：</em>
    <ul class="m-b-0">
        <li><em>团队成员</em></li>
        <li><em>专家</em></li>
        <li><em>客户</em></li>
        <li><em>合作者</em></li>
        <li><em>会晤</em></li>
        <li><em>指导</em></li>
        <li><em>辅导</em></li>
    </ul>
</div>';
$string['form:development:ten'] = '从正式的课程 - 面对面或在线学习 – 约10%';
$string['form:development:tenhelp'] = '<div class="well well-sm">
    <em>例如：</em>
    <ul class="m-b-0">
        <li><em>课堂教学</em></li>
        <li><em>正式的在线学习</em></li>
        <li><em>虚拟课堂学习</em></li>
    </ul>
</div>';

// Summaries
$string['form:summaries:title'] = 'Section 5: Summaries';
$string['form:summaries:intro'] = '本节的目的是总结评议的内容供以后参与薪酬，晋升或发展决定的人员作参考';
$string['form:summaries:appraiser'] = '5.1 评议人总结工作绩效';
$string['form:summaries:appraiserhelp'] = '<div class="well well-sm"><em>评议人应提供清晰、 简明的绩效总结，以便于参与未来薪酬，晋升，发展决定人员的理解。尤其是需要明确指出哪些绩效未能达到，或超过预期，或达到预期。</em></div>';
$string['form:summaries:recommendations'] = '5.2 议定的行动';
$string['form:summaries:recommendationshelp'] = '<div class="well well-sm">
    <em>由评议人完成</em><br/>
    <em>需要现在做些什么？例如：</em>
    <ul>
        <li><em>发展</em></li>
        <li><em>外派 </em></li>
        <li><em>工作分配</em></li>
        <li><em>业绩支持</em></li>
    </ul>
</div>';
$string['form:summaries:appraisee'] = '5.3 被评议人评语';
$string['form:summaries:appraiseehelp'] = '<div class="well well-sm"><em>由被评议人完成</em></div>';
$string['form:summaries:signoff'] = '5.4 总结签署';
$string['form:summaries:signoffhelp'] = '<div class="well well-sm"><em>由团队负责人或指定人员完成签署</em></div>';
$string['form:summaries:grpleader'] = '5.5 领导总结评语';
$string['form:summaries:grpleaderhelp'] = '<div class="well well-sm"><em>由高层领导完成。</em></div>';
$string['form:summaries:grpleadercaption'] = '由{$a->fullname}{$a->date}完成';

//APPRAISEE EMAIL
//$string['email:subject:summaries:groupleaderemail:appraisee'] = '领导的评语已添加到你的评议';
//$string['email:body:summaries:groupleaderemail:appraisee'] = '<p>尊敬的{{appraiseefirstname}},</p>我已审阅你的评议并添加了评语。<br><p>点击<a href="{{appraisalurl}}">这里</a>可以访问已完成的评议。 .</p><p>亲切的问候，<br /><br>{{groupleadername}}';

//APPRAISER EMAIL
//$string['email:subject:summaries:groupleaderemail:appraiser'] = '{{appraiseefirstname}} {{appraiseelastname}} 的评议已更新';
//$string['email:body:summaries:groupleaderemail:appraiser'] = '<p>尊敬的{{appraiserfirstname}},</p><p>我现已在 {{appraiseefirstname}} {{appraiseelastname}}中添加了我的评语。</p><p>点击<a href="{{appraisalurl}}">这里</a>可以访问已完成的评议。</p>亲切的问候，<br><br>{{groupleadername}}';

//SIGN OFF EMAIL
//$string['email:subject:summaries:groupleaderemail:signoff'] = '{{appraiseefirstname}} {{appraiseelastname}} 的评议已更新';
//$string['email:body:summaries:groupleaderemail:signoff'] = '<p>尊敬的 {{signofffirstname}},</p><p>我现已在 {{appraiseefirstname}} {{appraiseelastname}}中添加了我的评语。</p><p>点击<a href="{{appraisalurl}}">这里</a>可以访问已完成的评议。</p>亲切的问候，<br><br>{{groupleadername}}';

// Check-in
$string['appraisee_checkin_title'] = 'Section 6. Check-in';
$string['checkins_intro'] = '纵观全年，希望被评议人和评议人会对影响计划，发展计划，表现和绩效的进展情况进行讨论。被评议人/或评议人可以使用以下部分来记录进展情况。谈话的次数由你们自己决定，但建议至少每年一次。';
$string['success:checkin:add'] = 'Successfully added check-in';
$string['error:checkin:add'] = 'Failed to add check-in';
$string['error:checkin:validation'] = 'Please provide some text.';
$string['checkin:addnewdots'] = 'Check-in...';
$string['checkin:deleted'] = 'Deleted check-in';
$string['checkin:delete:failed'] = 'Failed to delete check-in';
$string['checkin:update'] = 'Update';

// Feedback contribution
$string['feedback_header'] = '给予被评议人你的反馈{$a->appraisee_fullname}';
$string['confidential_label_text'] = '勾选此框可以保密您的意见。如果不勾选此框，您的意见将于被评议人共享。';

$string['feedback_intro'] = '请选择三个或更多的同事，能够对你的评价反馈。在大多数地区这种反馈可以是内部或外部的。请参阅你所在地区的具体指导。<br/><br/>  对于内部反馈者，你应考虑从"360 度 反馈"的角度来收集反馈意见，即同级，级别比你高和比你低的同事。你必须选择混合人群。<br/><br/>其中有一个反馈者可以是外部客户或非常了解你的合作者。<div data-visible-regions="East Asia"><br />在东亚地区，我们希望反馈来自于公司内部。外部客户和合作者的评语应通过内部员工得以反馈。</div> <br /><br /> <div class="alert alert-danger"> 注：除非反馈者选择了保密，在面谈会议过后你，反馈信息会在这里公布。</div>';

// Feedback email - Appraisee
$string['email:subject:appraiseefeedback'] = '我的员工评议反馈意见请求';
$string['email:body:appraiseefeedbackmsg'] = '<p>尊敬的 <span class="placeholder bind_firstname">{{firstname}}</span>,</p>
<p>我的员工评议即将开始。因为您和我在过去一年一直密切合作，我希望您能给予我在贡献和您觉得我可以更有效方面的反馈。如果您同意，请点击下面的链接提供您的反馈意见。</p> <p>我的评议面谈是在<span class="placeholder">{{held_date}}</span>，请在此日期之前回复。</p>
<p>以下是<span class="placeholder">{{appraisee_fullname}}</span>的其他意见：<br /> <span>{{emailtext}}</span></p>
<p>此致，<br />
<span class="placeholder">{{appraisee_fullname}}</span></p>';

// Feedback email - Appraiser
$string['email:subject:appraiserfeedback'] = '{{appraisee_fullname}}的员工评议反馈意见请求';
$string['email:body:appraiserfeedbackmsg'] = '<p>尊敬的<span class="placeholder bind_firstname">{{firstname}}</span>,</p> <p>我现正在为<span class="placeholder">{{appraisee_fullname}}</span>做员工评议。因为您和他们在过去一年一直密切合作，我希望您能给予他们在贡献和你觉得他们可以更有效方面的反馈。如果您同意，请点击下面的链接提供您的反馈意见。</p> <p>他们的评议是 <span class="placeholder">{{held_date}}</span>，请在此日期之前回复。</p> <p>在面谈会议后你的反馈会与<span class="placeholder">{{appraisee_fullname}}</span>分享，除非你在提交时勾选保密框。</p> <p>以下是<span class="placeholder">{{appraiser_fullname}}</span>的其他意见：<br /> <span>{{emailtext}}</span></p> <p>此致，<br /> <span class="placeholder">{{appraiser_fullname}}</span></p>';

// PDF Strings
$string['pdf:form:summaries:appraisee'] = '被评议人评语';
$string['pdf:form:summaries:appraiser'] = '评议人总结工作绩效';
$string['pdf:form:summaries:signoff'] = '总结签署';
$string['pdf:form:summaries:grpleader'] = '领导总结评语';
$string['pdf:form:summaries:recommendations'] = '议定的行动';

// END FORM

// START OVERVIEW CONTENT
// Overview page APPRAISEE Content.
$string['overview:content:appraisee:1'] = ''; // Never seen...
$string['overview:content:appraisee:2'] = '请开始完成你的员工评议。<br /><br />
<strong>下一步：</strong>
<ul class="m-b-20">
    <li>输入预计面谈会议的日期</li>
    <li>请求反馈</li>
    <li>反映和评论去年的绩效和发展</li>
    <li>填写你面谈会议要讨论的职业方向、 影响和发展计划部分</li>
    <li>与评议人{$a->styledappraisername}共享你的草稿</li>
</ul>
请至少在面谈会议<strong><u>前一周</u></strong>与你的评议人共享你的草稿。在共享后你可以继续修改草稿<br /><br />
<div class="alert alert-danger" role="alert"><strong>注：</strong>只有在你共享后，你的评议人才可以看到你的草稿</div>';

$string['overview:content:appraisee:2:3'] = '评议人已要求对你的评议草稿修改<br /><br />
<strong>下一步：</strong>
<ul class="m-b-20">
    <li>按照评议人的要求修改（请参阅Activity Log获取进一步的信息)</li>
    <li>与{$a->styledappraisername}共享你的草稿</li>
</ul>';

$string['overview:content:appraisee:3'] = '你现在已经提交你的评议草稿到 {$a->styledappraisername} 审阅。<br /><br />
<strong>下一步：</strong>
<ul class="m-b-20">
    <li>在面谈会议前你希望：</li>
    <ul class="m-b-0">
        <li><a href="{$a->printappraisalurl}" target="_blank">下载评议表</a></li>
        <li><a href="https://moodle.arup.com/appraisal/reference" target="_blank">下载快速参考指南</a></li>
    </ul>
    <li>面谈会议后，评议人会返还评议表给你。你会要求按照面谈会议中商定的内容作修改，或填写你最终的评论。</li>
</ul>
<div class="alert alert-danger" role="alert"><strong>注：</strong>评议人在审阅你的评议时你可以继续修改，但是建议你在Activity Log上注明你所做的修改。</div>';

$string['overview:content:appraisee:3:4'] = '你已经将评议回复给了 [评议人姓名]并让他们进行更改。<br /><br /> 当他们更新了评议，你将会收到做再次审阅的通知。<br /><br /> <div class="alert alert-danger" role="alert"><strong>注：</strong> 评议人在审阅你的评议时你可以继续修改，但是建议你在Activity Log上注明你所做的修改。</div>';

$string['overview:content:appraisee:4'] = '{$a->styledappraisername}现已加注他们的评语并已回复给你。<br /><br />
<strong>下一步：</strong>
<ul class="m-b-20">
    <li>请查阅评议人的评语和总结。如有需要更改请回复评议人</li>
    <li>在总结部分填写你的评语</li>
    <li>在签署前发送给评议人做最终审阅。评议一旦提交就不能再修改了。</li>
</ul>
<div class="alert alert-danger" role="alert"><strong>注：</strong>你可以继续修改你自己填写的部分，但是建议你在Activity Log上给被评议人注明你所做的修改。</div>';

$string['overview:content:appraisee:5'] = '你现已提交完成的评议给{$a->styledappraisername}做最终审阅。<br /><br />
<strong>下一步：</strong>
    <ul class="m-b-20">
        <li>你的评议人现将你的评议发送给{$a->styledsignoffname}签署</li>
    </ul>
<div class="alert alert-danger" role="alert"><strong>注：</strong> 除非评议人回复你要进一步修改，否则你不能够再修改评议了。</div>';

$string['overview:content:appraisee:6'] = '你的评议已发送给{$a->styledsignoffname}审阅并写总结。<br /><br />
<div class="alert alert-danger" role="alert"><strong>注：</strong>你的评议已锁定不能再修改。</div>';

$string['overview:content:appraisee:7'] = '你的评议以完成。你可以任何时间通过点击“下载评议表”按键下载PDF格式的评议表。';
$string['overview:content:appraisee:7:groupleadersummary'] = '你的评议已完成，等待领导的审阅和总结评语。你会收到相关通知。';
$string['overview:content:appraisee:8'] = $string['overview:content:appraisee:7']; // For legacy where there was a six month status.
$string['overview:content:appraisee:9'] = $string['overview:content:appraisee:7']; // When Groupleader added summary.

// Overview page APPRAISER Content.
$string['overview:content:appraiser:1'] = ''; // Never seen...
$string['overview:content:appraiser:2'] = '{$a->styledappraiseename}正在起草员工评议。你将会收到可以审阅的通知。<br /><br />
<div class="alert alert-danger" role="alert"><strong>注:</strong>只有在共享时你可以审阅员工评议</div>';

$string['overview:content:appraiser:2:3'] = '你已经将评议回复给了{$a->styledappraiseename}并让他们修改。当他们更新了评议，你将会收做再次审阅的通知。<br /><br />
<div class="alert alert-danger" role="alert"><strong>注：</strong>你仍然可以修改你填写的部分。</div>';

$string['overview:content:appraiser:3'] = '{$a->styledappraiseename}已经提交评议草稿准备面谈会议。<br /><br />
<strong>下一步：</strong>
<ul class="m-b-20">
    <li>请审阅评议草稿准备会议。是否要回复评议草稿给评议人要求补充信息。</li>
    <li>会议前你应该</li>
    <ul class="m-b-0">
        <li><a href="{$a->printappraisalurl}" target="_blank">下载评议表</a></li>
        <li><a href="{$a->printfeedbackurl}" target="_blank">下载收到的反馈</a></li>
        <li>也可以<a href="https://moodle.arup.com/appraisal/reference" target="_blank">下载快速参考指南</a></li>
    </ul>
    <li>面谈会议后请</li>
    <ul class="m-b-0">
        <li>在被评议人信息栏上标注面谈会议已进行</li>
        <li>在每一栏填上你的评语</li>
        <li>填写你的总结和在总结环节中商定的行动计划</li>
        （如有需要可以在你写评语前，回复评议表让评议人做修改）
    </ul>
    <li>发送给被评议人，让他们查阅你的评语，反馈，并做最终评论。</li>
</ul>';

$string['overview:content:appraiser:3:4'] = '{$a->styledappraiseename}已要求修改他们的评议。<br /><br />
<strong>下一步：</strong>
<ul class="m-b-20">
    <li>按照被评议人的要求进行修改（请参阅Activity Log获取进一步的信息）</li>
    <li>与{$a->styledappraiseename}共享你的最终评语</li>
</ul>';

$string['overview:content:appraiser:4'] = '你已加注你的评语和总结，并回复给{$a->styledappraiseename}加注他们最终评语。你将会收到做最终审阅的通知。<br /><br />
<div class="alert alert-danger" role="alert"><strong>注：</strong>你可以继续修改你自己填写的部分，但是建议你在Activity Log上给评议人注明你所做的修改</div>';

$string['overview:content:appraiser:5'] = '{$a->styledappraiseename}现已加注最终评语。<br /><br />
<strong>下一步：</strong>
<ul class="m-b-20">
    <li>请审阅完成的评议准备签署</li>
    <li>发送给{$a->styledsignoffname}做审阅和加注总结</li>
    <lis评议完成即通知你和被评议人。</li>
</ul>
<div class="alert alert-danger" role="alert"><strong>注：</strong>除非你回复被评议人，否则你不能够再修改评议了。</div>';

$string['overview:content:appraiser:6'] = '你现已提交完成评议给 {$a->styledsignoffname}<br /><br />
    <div class="alert alert-danger" role="alert"><strong>注：</strong>你的评议已锁定不能再修改。</div>';

$string['overview:content:appraiser:7'] = '评议表已完成并已签署。';
$string['overview:content:appraiser:7:groupleadersummary'] = '你的评议已完成，等待领导的审阅和总结评语。你会收到相关通知。';

$string['overview:content:appraiser:8'] = $string['overview:content:appraiser:7']; // For legacy where there was a six month status.
$string['overview:content:appraiser:9'] = $string['overview:content:appraiser:7']; // When Groupleader added summary.

// Overview page SIGN OFF Content.
$string['overview:content:signoff:1'] = ''; // Never seen...
$string['overview:content:signoff:2'] = '评议在进行中。<br /><br /><div class="alert alert-danger" role="alert"><strong>注：</strong> 你将会收到可以审阅和签署评议的通知。</div>';
$string['overview:content:signoff:3'] = '评议在进行中。<br /><br /><div class="alert alert-danger" role="alert"><strong>注：</strong> 你将会收到可以审阅和签署评议的通知。</div>';
$string['overview:content:signoff:4'] = '评议在进行中。<br /><br /><div class="alert alert-danger" role="alert"><strong>注：</strong> 你将会收到可以审阅和签署评议的通知。</div>';
$string['overview:content:signoff:5'] = '评议在进行中。<br /><br /><div class="alert alert-danger" role="alert"><strong>注：</strong> 你将会收到可以审阅和签署评议的通知。</div>';
$string['overview:content:signoff:6'] = '{$a->styledappraiseename} 的评议已发送给你审阅了。<br /><br />
<strong>下一步:</strong>
<ul class="m-b-20">
    <li>请审阅评议</li>
    <li>在Summaries处添加你的总结评语</li>
    <li>点击“签署”按键完成评议</li>
</ul>';

$string['overview:content:signoff:7'] = '评议表已完成并已签署。';
$string['overview:content:signoff:7:groupleadersummary'] = '你的评议已完成，等待领导的审阅和总结评语。你会收到相关通知。';

$string['overview:content:signoff:8'] = $string['overview:content:signoff:7']; // For legacy where there was a six month status.
$string['overview:content:signoff:9'] = $string['overview:content:signoff:7']; // When groupleader added summary.

// Overview page GROUP LEADER Content.
$string['overview:content:groupleader:1'] = ''; // Never seen...
$string['overview:content:groupleader:2'] = '评议在进行中。';
$string['overview:content:groupleader:3'] = '评议在进行中。';
$string['overview:content:groupleader:4'] = '评议在进行中。';
$string['overview:content:groupleader:5'] = '评议在进行中。';
$string['overview:content:groupleader:6'] = '评议在进行中。';
$string['overview:content:groupleader:7'] = '评议表已完成并已签署。';
$string['overview:content:groupleader:7:groupleadersummary'] = '评议已完成，等待你的审阅和总结评语。<br /><br />
<strong>下一步:</strong>
<ul class="m-b-20">
    <li>请在Summaries处添加你的领导总结评语并保存。</li>
    <li>被评议人，评议人和签署人都会收到相关通知。</li>
</ul>';
$string['overview:content:groupleader:8'] = $string['overview:content:groupleader:7']; // For legacy where there was a six month status.
$string['overview:content:groupleader:9'] = $string['overview:content:groupleader:7'];

// Overview page buttons.
$string['overview:button:appraisee:2:extra'] = '开始完成你的员工评议。';
$string['overview:button:appraisee:2:submit'] = '与{$a->plainappraisername}共享';

$string['overview:button:appraisee:4:return'] = '回复{$a->plainappraisername}去做修改';
$string['overview:button:appraisee:4:submit'] = '提交完成的评议给{$a->plainappraisername}';

$string['overview:button:appraiser:3:return'] = '要求{$a->plainappraiseename}提供进一步信息';
$string['overview:button:appraiser:3:submit'] = '发送给{$a->plainappraiseename}做最终评语';

$string['overview:button:appraiser:5:return'] = '要求在签署前进一步修改';
$string['overview:button:appraiser:5:submit'] = '发送给{$a->plainsignoffname}签署';

$string['overview:button:signoff:6:submit'] = 'Sign Off';
// ERROR: missing translation

$string['overview:button:returnit'] = 'Return';
$string['overview:button:submitit'] = 'Send';

// END OVERVIEW CONTENT

// START CH string translations - spreadsheet
$string['startappraisal'] = '开始在线评议';
$string['continueappraisal'] = '继续在线评议';
$string['appraisee_feedback_edit_text'] = '编辑修改';
$string['appraisee_feedback_resend_text'] = '重新发送';
$string['appraisee_feedback_view_text'] = '查阅';
$string['feedback_setface2face'] = '你必须设置了面谈会议的日期，才能添加反馈请求。可以在评议人信息页面上找到。';
$string['feedback_comments_none'] = '没有其他评论。';
$string['actionrequired'] = '需要采取的行动';
$string['actions'] = '操作';
$string['admin:bulkactions'] = '批量处理';
$string['admin:duedate'] = '截止日期';
$string['admin:email'] = '发送邮件给被评议人';
$string['admin:initialise'] = '创建评议';
$string['admin:nousers'] = '没有找到匹配的用户';
$string['admin:toptext:archived'] = '存档评议表是往年评议的记录，并不能编辑。';
$string['admin:toptext:complete'] = '一旦签署人签署了评议表，这儿就显示完整的评议表。在创建一套新的评议之前，当前的评议需要存档。当评议存档时，评议将锁定在当前状态不能再继续进行。用户可以通过列表清单上存档评议处访问评议。';
$string['admin:toptext:deleted'] = '删除的评议已经从评议过程中移除，但仍存储在系统中';
$string['admin:toptext:initialise'] = '要建立用户的评议，你需要添加一个到期日期，并使用用户旁的下拉箭头选择评议人和签署人，然后点击创建评议。这将启动评议，触发一封电子邮件给被评议人 (抄送评议人) 通知评议已经开始了，并给他们一个链接到文档。';
$string['admin:toptext:inprogress'] = '评议进程将显示在下列图表。评议一旦签署就会移动至结束。 你可以在表格中变更评议人/签署人，和删除评议 （注：不可再恢复的）。使用选择并下拉至页底，你可以通过电子邮件让用户去追踪进展。到年底通过存档再创建新的评议。';
$string['admin:usercount'] = '在所选的成本中心的员工总数 {人数}';
$string['appraisals:archived'] = '存档的评议';
$string['appraisals:current'] = '当前评议';
$string['appraisals:noarchived'] = '你没有存档的评议';
$string['appraisals:nocurrent'] = '你没有当前评议';
$string['group'] = '成本中心';
$string['index:togglef2f:complete'] = '标注面谈会议已开';
$string['index:togglef2f:notcomplete'] = '标注面谈会议未开';
$string['index:notstarted'] = '未开始';
$string['index:notstarted:tooltip'] = '被评议人未开始评议，一旦评议开始你就可以访问';
$string['index:printappraisal'] = '下载评议表';
$string['index:printfeedback'] = '下载反馈';
$string['index:start'] = '开始评议';
$string['index:toptext:appraisee'] = '此列表清单显示你当前的和所有的存档评议。你可以通过操作下拉列表访问你当前的评议。点击以下下载评议表按键可以下载存档评议表。';
$string['index:toptext:appraiser'] = '此列表清单显示所有你做为评议人当前的和存档的评议。可以通过操作下拉列表访问所有当前评议。反馈下载包含只有在面谈会议后才可以共享给被评议人的反馈。任何保密反馈在任何阶段都被隐藏。点击以下下载评议表按键可以下载存档的评议表。';
$string['index:toptext:groupleader'] = '此列表清单显示你的成本中心里当前的和存档的评议。你可以通过操作下拉列表访问或下载你当前的评议。点击下载评议表按键可以下载存档评议表。';
$string['index:toptext:hrleader'] = '此列表清单显示你的成本中心里当前的和存档的评议。你可以通过操作下拉列表访问或下载你当前的评议。点击下载评议表按键可以下载存档评议表';
$string['index:toptext:signoff'] = '此列表清单显示你所签署的当前的和存档的评议。你可以通过操作下拉列表访问你当前的评议。点击以下下载评议表按键可以下载存档评议表。';
$string['index:view'] = '查阅评议';
$string['success:appraisal:create'] = '评议已成功创建';
$string['success:appraisal:delete'] = '评议已成功删除';
$string['success:appraisal:update'] = '评议已成功更新';
$string['error:appraisal:create'] = '对不起，评议创建时出错';
$string['error:appraisal:delete'] = '对不起，评议删除时出错';
$string['error:appraisal:select'] = '请至少选择一个评议';
$string['error:appraisal:update'] = '对不起，评议更新时出错';
$string['error:appraisalexists'] = '此用户现已有评议';
$string['error:appraiseeassuperior'] = '被评议人不能同时是评议人或签署人';
$string['error:appraisernotvalid'] = '所选的评议人不适用这个组';
$string['error:duedate'] = '请输入一个截止日期';
$string['error:togglef2f:complete'] = '不能标注面谈会议已开';
$string['error:togglef2f:notcomplete'] = '不能标注面谈会议未开';
$string['error:selectusers'] = '请选择一个评议人和一个签署人';
$string['appraisee_feedback_email_success'] = '电子邮件发送成功';
$string['appraisee_feedback_email_error'] = '电子邮件发送失败';
$string['appraisee_feedback_invalid_edit_error'] = '提供的电子邮件地址无效';
$string['appraisee_feedback_inuse_edit_error'] = '电子邮件地址已被使用';
$string['appraisee_feedback_inuse_email_error'] = '电子邮件地址已被使用';
$string['appraisee_feedback_resend_success'] = '电子邮件再次发送成功';
$string['appraisee_feedback_resend_error'] = '错误，尝试重新发送电子邮件';
$string['form:choosedots'] = '选择。。。';
$string['form:delete'] = '删除';
$string['form:edit'] = '编辑修改';
$string['form:language'] = '语言';
$string['form:addfeedback:alert:cancelled'] = '发送取消，你的评议反馈尚未发送';
$string['form:addfeedback:alert:error'] = '对不起，你的评议反馈发送错误';
$string['form:addfeedback:alert:saved'] = '谢谢，你的评议反馈已成功发送';
$string['form:addfeedback:notfound'] = '无反馈请求';
$string['form:addfeedback:sendemailbtn'] = '发送评议反馈';
$string['form:addfeedback:title'] = 'Feedback Contribution';
$string['form:addfeedback:closed'] = '提交您的反馈意见的窗口现已关闭';
$string['form:addfeedback:submitted'] = '反馈意见已提交';
$string['form:addfeedback:addfeedback'] = '请从三个方面评价被评议人在过去12个月内的贡献。再最多从三个方面提议你觉得可以做得更有效的。诚实，但要建设性的评批，因为这些反馈将帮助你的同事更有效地处理问题。';
$string['form:feedback:alert:cancelled'] = '发送取消了，你的评议反馈意见请求未发送。';
$string['form:feedback:alert:error'] = '对不起，发送你的评议反馈意见请求时发生错误。';
$string['form:feedback:alert:saved'] = '你的评议反馈意见请求发送成功。';
$string['form:feedback:email'] = '邮件地址';
$string['form:feedback:firstname'] = '名';
$string['form:feedback:lastname'] = '姓';
$string['form:feedback:language'] = '选择反馈邮件的语言';
$string['form:feedback:sendemailbtn'] = 'Send email to Contributor';
$string['form:feedback:title'] = 'Feedback - Add a new Contributor';
$string['form:lastyear:file'] = '被评议人已上传回顾文件：{文件}';
$string['form:lastyear:cardinfo:developmentlink'] = '去年的发展计划';
$string['form:lastyear:cardinfo:performancelink'] = '去年的绩效';
$string['feedbackrequests:description'] = '此列表清单显示了您未回复的反馈请求，您可以访问您已经回复的反馈。';
$string['feedbackrequests:outstanding'] = '未回复的反馈请求';
$string['feedbackrequests:norequests'] = '没有未回复的反馈请求';
$string['feedbackrequests:completed'] = '已完成的反馈请求';
$string['feedbackrequests:nocompleted'] = '尚未完成的反馈请求';
$string['feedbackrequests:th:actions'] = '操作';
$string['feedbackrequests:emailcopy'] = '发邮件给我一份副本';
$string['feedbackrequests:submitfeedback'] = '提交反馈';
$string['email:subject:myfeedback'] = '你为{{被评议人}}的评议反馈';
$string['email:body:myfeedback'] = '尊敬的{{收件人}},你提交的为{{被评议人}}做的{{机密的}}反馈如下：{{反馈}}';
$string['feedbackrequests:confidential'] = '机密的';
$string['feedbackrequests:nonconfidential'] = '非机密的';
$string['feedbackrequests:received:confidential'] = '收到（保密）';
$string['feedbackrequests:received:nonconfidential'] = '收到';
$string['feedbackrequests:paneltitle:confidential'] = '反馈（保密';
$string['feedbackrequests:paneltitle:nonconfidential'] = '反馈';
$string['feedbackrequests:legend'] = '*表示有评议人添加的反馈者';
$string['success:checkin:add'] = '添加回顾成功';
$string['error:checkin:add'] = '添加回顾失败';
$string['error:checkin:validation'] = '请输入文字';
$string['checkin:deleted'] = '回顾删除';
$string['checkin:delete:failed'] = '回顾删除失败';
$string['checkin:update'] = '更新';
$string['checkin:addnewdots'] = '回顾';
// END CH string translations - spreadsheet

// ADDED Strings

$string['comment:status:7_to_9'] = '领导的评语已 由{$a->relateduser}补充.';

// ****** WORKFLOW Email6-APPRAISEE ******
//$string['email:body:status:6_to_7:appraisee'] = '<p>尊敬的 {{appraiseefirstname}},</p><p>我现已审阅并签署了你的评议。</p>{{groupleaderextra}}{{comment}}<p>点击<a href="{{linkappraisee}}">这里</a>可以访问已完成的评议。</p><p>亲切的问候,<br /> {{signofffirstname}} {{signofflastname}}</p><br /><hr><p>Further assistance can be found <a href="https://moodle.arup.com/appraisal/help">here</a> alternatively you can contact your local HR group or raise a Service Desk ticket.</p><p>This is an auto generated message sent to {{appraiseeemail}} from {{signoffemail}} by moodle.arup.com - Appraisal status: {{status}} - Email6Appraisee</p><p>Trouble viewing? To view your appraisal online please copy and paste this URL {{linkappraisee}} into your browser.</p>';

//$string['email:body:status:6_to_7:appraisee:groupleaderextra'] = '<p>你的评议已完成，等待领导的审阅和总结评语。你会收到相关通知。</p>';

//$string['email:subject:status:6_to_7:appraisee'] = 'Your Appraisal is Complete';

// ****** WORKFLOW Email6-APPRAISER ******
//$string['email:body:status:6_to_7:appraiser'] = '<p>尊敬的 {{appraiserfirstname}},</p><p>我现已签署{{appraiseefirstname}} {{appraiseelastname}}的评议。</p>{{groupleaderextra}}{{comment}}<p>点击<a href="{{linkappraiser}}">这里</a>可以访问已完成的评议。</p><p>亲切的问候,<br />{{signofffirstname}} {{signofflastname}}</p><br /><hr><p>Further assistance can be found <a href="https://moodle.arup.com/appraisal/help">here</a> alternatively you can contact your local HR group or raise a Service Desk ticket.</p><p>This is an auto generated message sent to {{appraiseremail}} from {{signoffemail}} by moodle.arup.com - Appraisal status: {{status}} - Email6Appraiser</p><p>Trouble viewing? To view your appraiser dashboard online please copy and paste this URL {{linkappraiserdashboard}} into your browser.</p>';

//$string['email:body:status:6_to_7:appraiser:groupleaderextra'] = '<p>你的评议已完成，等待领导的审阅和总结评语。你会收到相关通知。</p>';
//$string['email:subject:status:6_to_7:appraiser'] = 'Appraisal ({{appraiseefirstname}} {{appraiseelastname}}) is complete';

// ****** WORKFLOW Email7-GROUPLEADER ******
//$string['email:body:status:6_to_7:groupleader'] = '<p>尊敬的 {{groupleaderfirstname}},</p><p>{{appraiseefirstname}} {{appraiseelastname}} 的员工评议已完成，请审阅并写总结评语。</p>{{comment}}<p>点击<a href="{{linkgroupleader}}">这里</a>可以访问评议。.</p><p>亲切的问候，<br />{{signofffirstname}} {{signofflastname}}</p><br /><hr><p>Further assistance can be found <a href="https://moodle.arup.com/appraisal/help">here</a> alternatively you can contact your local HR group or raise a Service Desk ticket.</p><p>This is an auto generated message sent to {{groupleaderemail}} from {{signoffemail}} by moodle.arup.com - Appraisal status: {{status}} - Email7Leader</p><p>Trouble viewing? To view your leader dashboard online please copy and paste this URL {{linkgroupleaderdashboard}} into your browser.</p>';

//$string['email:subject:status:6_to_7:groupleader'] = '({{appraiseefirstname}} {{appraiseelastname}})的评议可以审阅了';

//$string['email:replacement:comment'] = '<p>我的评语：<br />{$a}</p>';