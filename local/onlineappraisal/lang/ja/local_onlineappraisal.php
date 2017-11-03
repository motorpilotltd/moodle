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

// START FORM

// General alerts.
$string['alert:language:notdefault'] = '<strong>注意</strong>:現在、閲覧しているアプレイザルの言語はデフォルトの言語ではありません。記入する際は、必ずアプレイザルの関係者に最も適切な言語を使ってください。';
$string['alert:language:notdefault:type'] = 'warning';

// Error alerts
$string['error:noaccess'] = 'このリソースを閲覧する権限がありません。';
$string['error:pagedoesnotexist'] = 'このページを閲覧する権限がありません。';

// Userinfo.
$string['form:userinfo:title'] = 'Appraisee Info';
$string['form:userinfo:intro'] = '以下の情報を記入してください。TAPSの記録から自動入力されている項目もありますが、それらのデータに誤りがある場合、HRに連絡してください。';
$string['form:userinfo:name'] = '被評価者の氏名';
$string['form:userinfo:staffid'] = 'スタッフID';
$string['form:userinfo:grade'] = 'Grade';
$string['form:userinfo:jobtitle'] = 'Job title';
$string['form:userinfo:operationaljobtitle'] = 'Operational job title';
$string['form:userinfo:facetoface'] = 'アプレイザルミーティングの実施予定日';
$string['form:userinfo:facetofaceheld'] = 'アプレイザルミーティングを終了しました。';
$string['form:userinfo:setf2f'] = 'Set your face to face meeting time and date';

// Introduction Page
$string['appraisee_heading'] = 'オンラインアプレイザルへようこそ';
$string['appraisee_welcome'] = 'アプレイザルはあなたと評価者があなたの業績と成績について価値ある議論を行うための機会です。<br /><br />
このオンラインツールはその議論を記録し、年間を通じて振り返るためのものです。<br /><br />
右のイメージをクリックし、Gregory Hodkinsonのイントロダクションメッセージをご覧ください。<br /><br />
アプレイザルに関するより詳細な説明は<a href="https://moodle.arup.com/appraisal/essentials" target="_blank"> ここから</a> 確認できます。';

$string['introduction:video'] = '<img src="https://moodle.arup.com/scorm/_assets/Gregory_Hodkinson.jpg" alt="Changes to Appraisal" onclick="window.open(\'https://moodle.arup.com/scorm/_assets/intro.pdf\', \'_blank\');"/>';

//Request Feedback.

$string['feedback_send_copy'] = 'Email me a copy';
$string['feedback_intro'] = '情報提供者を3人以上選んでください。社内外いずれから選んでも可能な場合がほとんどですが、詳しくは各リージョンの指示に従ってください。<br/><br/>  リージョン内の情報提供者については、「360度評価」という観点でフィードバックを得るようにしてください。すなわち、同期だけでなく自分より経験の長い同僚、短い同僚も情報提供者として選んでください。<br/><br/>
<div data-visible-regions="East Asia"><br />
EAリージョンにおいては、社内にのみフィードバックを依頼するようお願いします。外部のクライアントもしくはパートナーからのコメントは社内関係者を通じてフィードバックされることが期待されます。</div> <br /><br /> <div class="alert alert-danger"> 注意：あなたへのフィードバックは、情報提供者が不開示としないかぎり、アプレイザルミーティング後にこちらに開示されます。</div>';

// Last Year Review
$string['form:lastyear:title'] = 'Section 1: Review of last year';
$string['form:lastyear:nolastyear'] = 'Note: We notice that you don\'t have a previous appraisal in the system. Please upload your last appraisal as a pdf / word document below.';
$string['form:lastyear:intro'] = 'このセクションでは、この12ヶ月で何を達成し遂行したかを評価者・被評価者とで話し合います。

話し合いの意義については、<a href="https://moodle.arup.com/appraisal/guide" target="_blank">Guide To Appraisal </a>に詳述しています。';
// ERROR: please confirm positioning of Guide to Appraisal
$string['form:lastyear:upload'] = 'アプレイザルをアップロードします。';
$string['form:lastyear:appraiseereview'] = '1.1 被評価者による昨年度の業績の振り返り';
$string['form:lastyear:appraiseereviewhelp'] =
'<div class="well well-sm"> <em>プロジェクト・人・クライアントという観点から総合的に見て、昨年のアプレイザル以来どのように仕事を成し遂げましたか？</em>
    <ul class="m-b-0">
        <li><em>どのように周りと協力し専門知識を共有しましたか？その成果はどうでしたか？</em></li>
        <li><em>あなたの業績の中で、予想以下だったものはありますか？</em></li>
        <li><em>あなたが責任者の場合、彼らの業績や行動の良し悪しを適切に管理しましたか？</em></li>
        <li><em>効率を上げるため、どのようなテクノロジーを利用しましたか？</em></li>
    </ul>
</div>';
$string['form:lastyear:appraiserreview'] = '1.2 評価者による昨年度の業績の振り返り';
$string['form:lastyear:appraiserreviewhelp'] =
'<div class="well well-sm"><em>被評価者による振り返りに対し、コメントしてください。</em>
    <ul class="m-b-0">
        <li><em>被評価者はどんな進歩を遂げましたか？</em></li>
        <li><em>被評価者が情報提供者から得たフィードバックをまとめてください。</em></li>
    </ul>
<em>プロジェクト・チーム・クライアント・その他の人について、被評価者の業績が想定以下であった場合、その点について必ず話し合い、このセクションに記録してください。</em>
</div>';
$string['form:lastyear:appraiseedevelopment'] = '1.3 被評価者による昨年度の成長の振り返り';
$string['form:lastyear:appraiseedevelopmenthelp'] = '<div class="well well-sm">
    <em>昨年のアプレイザルからの、あなたの個人的な成長についてコメントしてください。</em>
    <ul class="m-b-0">
        <li><em>スキル、知識、行動についてどのように成長しましたか？</em></li>
        <li><em>昨年立てた成長目標で、引き続き目標とすべきものはありますか？</em></li>
    </ul>
</div>';
$string['form:lastyear:appraiseefeedback'] = '1.4 昨年度の振り返りの中で、あなたやチームの業績に影響を与えたり、業績を上げたりできる要素はあるでしょうか？';
$string['form:lastyear:appraiseefeedbackhelp'] = '<div class="well well-sm"><em>被評価者が入力してください。</em></div>';

// Career Direction
$string['form:careerdirection:title'] = 'Section 2: Career Direction';
$string['form:careerdirection:intro'] = 'このセクションは、被評価者がキャリアの方向性を考え、これらを現実的に話し合うことを目的とします。経験の短いスタッフは、今後1～3年を視野に入れてください。経験の長いスタッフは、今後3～5年を視野に入れてください。';
$string['form:careerdirection:progress'] = '2.1 どのようなキャリアを進みたいですか？';
$string['form:careerdirection:progresshelp'] =
'<div class="well well-sm"><em>以下について考えてください。</em>
    <ul class="m-b-0">
        <li><em>どのような種類の業務を、どのような責任をもって行いたいですか？</em></li>
        <li><em>今後数年、あなたにとって仕事で重要なことは何ですか（例えば業務の幅、深さ、専門性、普遍性、海外赴任、設計、人の管理等）？</em></li>
        <li><em>勤務地の希望はありますか？</em></li>
    </ul>
</div>';
$string['form:careerdirection:comments'] = '2.2 評価者からのコメント';
$string['form:careerdirection:commentshelp'] =
'<div class="well well-sm"><em>以下について考えてください。</em>
    <ul class="m-b-0">
        <li><em>被評価者の志望は現実的で、挑戦しがいがあり、野心的なものですか？</em></li>
        <li><em>どのような役割・プロジェクト・その他の業務機会によって、求められる経験・スキル・行動上の成長が得られるでしょうか？</em></li>
    </ul>
</div>';

// Agreed Impact Plan
$string['form:impactplan:title'] = 'Section 3: Agreed Impact Plan';
$string['form:impactplan:intro'] = 'このセクションでは、業務・および会社への全般的なインパクトという観点で、被評価者が今後一年間でどのような変化を遂げたいかを計画します。プランには、被評価者がどのように仕事・プロジェクト・チーム・オフィスを改善するかという内容を含める必要があります。具体的には、タイムライン、質、予算、設計や変革、そして人々・クライアント・仕事全般へのインパクトについての詳細を明記してください。<br /><br /> どのようにこれらの改善をすすめるかについては <a href="https://moodle.arup.com/appraisal/contribution" target="_blank">Contribution Guide</a> および <a href="https://moodle.arup.com/appraisal/guide" target="_blank">Guide To Appraisal</a> を参考にしてください。';

$string['form:impactplan:impact'] = '3.1 次年度、プロジェクト、クライアント、チームあるいは会社に対して与えたいインパクトについて記述してください。';
$string['form:impactplan:impacthelp'] =
'<div class="well well-sm"><em>以下について考えてください。</em>
    <ul class="m-b-0">
        <li><em>注力したい領域</em></li>
        <li><em>なぜそれらが重要なのか</em></li>
        <li><em>どのようにそれらを達成するのか</em></li>
        <li><em>誰と協力するのか</em></li>
        <li><em>おおよそのタイムライン：3ヶ月、6ヶ月、12ヶ月、18ヶ月あるいはそれ以上</em></li>
        <li><em>あなたのインパクトプランがあなたの目指すキャリアの向上にどのように当てはまり、助けとなるのか</em></li>
    </ul>
</div>';
$string['form:impactplan:support'] = '3.2 このインパクトプランを遂行するのに、アラップからどのようなサポートが必要ですか？';
$string['form:impactplan:supporthelp'] =
'<div class="well well-sm"><em>例：</em>
    <ul class="m-b-0">
        <li><em>他人からの手助け</em></li>
        <li><em>管理・監督</em></li>
        <li><em>リソース（時間、予算、道具）</em></li>
        <li><em>自己の成長</em></li>
        <li><em>ツール（ソフトウェア、ハードウェア）</em></li>
    </ul>
</div>';
$string['form:impactplan:comments'] = '3.3 評価者からのコメント';
$string['form:impactplan:commentshelp'] = '<div class="well well-sm"><em>評価者が入力してください。</em></div>';

// Development Plan
$string['form:development:title'] = 'Section 4: Development Plan';
$string['form:development:intro'] = 'このセクションでは、被評価者のキャリア向上やインパクトプランのためにどのようなスキル、知識、行動上の変化が必要かを計画します。<br /><br />
これらを達成するため、今後12～18ヶ月でどのような成長が必要ですか？どのようなサポートが必要ですか、またいつこれに着手しますか？<br /><br />
<div class="well well-sm">アラップは、「70:20:10」という原理で個人の成長を考えています。これは、多くの人にとって70%の成長は業務上の経験から、20%は他の人から、残りの10%は講義やEラーニング等の学習から得るべきもの、という考えです。それぞれのパーセンテージはあくまでも目安です。</div>';
$string['form:development:seventy'] = '業務を通じて得る70%の学びについて';
$string['form:development:seventyhelp'] =
'<div class="well well-sm"><em>例：</em>
    <ul class="m-b-0">
        <li><em>プロジェクトの業務</em></li>
        <li><em>チーム内の業務</em></li>
        <li><em>出張、赴任</em></li>
        <li><em>業務についての話し合いやフィードバック</em></li>
        <li><em>プロジェクトの振り返り、デザインシャレット</em></li>
        <li><em>関連書籍の読書</em></li>
        <li><em>研究</em></li>
    </ul>
</div>';
$string['form:development:twenty'] = '他の人を通じて得る20%の学びについて';
$string['form:development:twentyhelp'] =
'<div class="well well-sm"> <em>例：</em>
    <ul class="m-b-0">
        <li><em>チームメンバー</em></li>
        <li><em>専門家</em></li>
        <li><em>クライアント</em></li>
        <li><em>協業者</em></li>
        <li><em>カンファレンス</em></li>
        <li><em>コーチング</em></li>
        <li><em>メンターリング</em></li>
    </ul>
</div>';
$string['form:development:ten'] = '対面、あるいはオンライン等の講義・講習を通じて得る10%の学びについて';
$string['form:development:tenhelp'] =
'<div class="well well-sm"><em>例：</em>
    <ul class="m-b-0">
        <li><em>講義・講習</em></li>
        <li><em>Eラーニング</em></li>
        <li><em>バーチャルクラスルーム</em></li>
    </ul>
</div>';

// Summaries
$string['form:summaries:title'] = 'Section 5: Summaries';
$string['form:summaries:intro'] = 'このセクションでは、アプレイザルの内容のまとめを行い、給与、プロモーション、あるいは成長に関する決定を行う担当者が必要に応じて照会できるようにします。';
$string['form:summaries:appraiser'] = '5.1 評価者による、業績全体のまとめ';
$string['form:summaries:appraiserhelp'] = '<div class="well well-sm"><em>評価者は、明確で簡潔なまとめを行い、将来の給与・プロモーション・成長に関する決定を行う担当者にとっても理解しやすいようなものにしておくことが求められます。特に、実績が期待値まで到達したか、しなかったかは明記するようにしてください。</em>
</div>';
$string['form:summaries:recommendations'] = '5.2 合意されたアクションについて';
$string['form:summaries:recommendationshelp'] = '<div class="well well-sm">
    <em>評価者が入力してください。</em><br/>
    <em>被評価者は今、何をすべきか。例：</em>
    <ul>
        <li><em>成長</em></li>
        <li><em>出張</em></li>
        <li><em>赴任</em></li>
        <li><em>業績のサポート</em></li>
    </ul>
</div>';
$string['form:summaries:appraisee'] = '5.3 被評価者によるコメント';
$string['form:summaries:appraiseehelp'] = '<div class="well well-sm"><em>被評価者が入力してください。</em></div>';
$string['form:summaries:signoff'] = '5.4 承認者によるまとめ';
$string['form:summaries:signoffhelp'] = '<div class="well well-sm"><em>リーダー、もしくは任命された承認者が入力してください。</em></div>';
$string['form:summaries:groupleader'] = '5.5 リーダーによるまとめ';
$string['form:summaries:groupleaderhelp'] = '<div class="well well-sm"><em>グループリーダーが入力してください。</em></div>';
$string['form:summaries:grpleadercaption'] = '{$a->fullname}{$a->date}が完了しました。';

// Check-in
$string['checkins_intro'] = '年間を通じて、評価者と被評価者はインパクトプラン、ディベロプメントプラン、各自の行動や実績について話し合うことが期待されます。評価者・被評価者とも、この下の欄を利用して進捗を記録してください。話し合いの頻度は個々に任されていますが、少なくとも一年に一度は行ってください。';

// Feedback contribution
$string['feedback_header'] = '{$a->appraisee_fullname} さんへのフィードバックを入力してください。';
$string['feedback_addfeedback'] = '被評価者のこの12ヶ月の取り組みのうち、あなたの評価する3つの領域について記述してください。また、より効率よくできたであろう3つの領域についても記述してください。被評価者がより効果的に課題に取り組めるよう、率直に、ただし、建設的に論じるようにしてください。';
$string['confidential_label_text'] = 'あなたのコメントを開示したくない場合はこのチェックボックスにチェックを入れてください。チェックを入れなかった場合はあなたのコメントが被評価者に共有されます。';

$string['form:addfeedback:addfeedback'] = '被評価者のこの12ヶ月の取り組みのうち、あなたの評価する3つの領域について記述してください。また、より効率よくできたであろう3つの領域についても記述してください。被評価者がより効果的に課題に取り組めるよう、率直に、ただし、建設的に論じるようにしてください。';
$string['form:addfeedback:notfound'] = 'フィードバック依頼が見つかりません。';
$string['form:addfeedback:sendemailbtn'] = 'フィードバック依頼を送信します。';
$string['form:addfeedback:closed'] = 'フィードバック提出の期限が切れました。';
$string['form:addfeedback:submitted'] = 'フィードバックを提出しました。';
$string['form:feedback:email'] = 'メールアドレス';
$string['form:feedback:firstname'] = '名';
$string['form:feedback:lastname'] = '姓';
$string['form:feedback:language'] = 'フィードバックメールの言語を選択します。';
$string['form:feedback:sendemailbtn'] = 'Send email to Contributor';
$string['form:feedback:title'] = 'Feedback - Add a new Contributor';

// Page Content
$string['comment:status:7_to_9'] = ' {$a->relateduser}がコメントを追加しました。';

// Feedback request email - sent by APPRAISEE
$string['email:subject:appraiseefeedback'] = 'フィードバック依頼';
$string['email:body:appraiseefeedbackmsg'] = '<p><span class="placeholder bind_firstname">{{firstname}}</span> さん</p>
<p>私のアプレイザルが近づいています。この一年、一緒に働く機会が多かったため、私の仕事ぶりについてフィードバックをいただきたいと思っています。同意いただける場合は、以下のリンクをクリックしフィードバックを入力してください。</p>
<p>私のアプレイザルは<span class="placeholder">{{held_date}}</span>の予定です。この日より前にご提出ください。</p>
<p>入力いただいたフィードバックは、あなたが「開示しない」というチェックボックスにチェックを入れない限り、アプレイザルミーティング後に私に共有されます。</p>
<p>その他、<span class="placeholder">{{appraisee_fullname}}</span>からの補足コメントです：<br /> <span>{{emailtext}}</span></p>
<p>宜しくお願いいたします。<br />
<span class="placeholder">{{appraisee_fullname}}</span></p>';

// Feedback request email - sent by APPRAISER
$string['email:subject:appraiserfeedback'] = '{{appraisee_fullname}}さんへのフィードバック依頼';
$string['email:body:appraiserfeedbackmsg'] = '<p><span class="placeholder bind_firstname">{{firstname}}</span>さん</p>
<p><span class="placeholder">{{appraisee_fullname}}</span>さんのアプレイザルを行っています。あなたは最近<span class="placeholder">{{appraisee_fullname}}</span>さんと一緒に働く機会多かったため、彼らの仕事ぶりについてフィードバックをいただきたいと思っています。同意いただける場合は、以下のリンクをクリックしフィードバックを入力してください。</p>
<p>対象者のアプレイザルは<span class="placeholder">{{held_date}}</span>の予定です。この日より前にご提出ください。</p>
<p>入力いただいたフィードバックは、あなたが「開示しない」というチェックボックスにチェックを入れない限り、アプレイザルミーティング後に<span class="placeholder">{{appraisee_fullname}}</span> さんに共有されます。</p>
<p>その他、<span class="placeholder">{{appraisee_fullname}}</span>からの補足コメントです：<br /> <span>{{emailtext}}</span></p>
<p>宜しくお願いいたします。<br /> <span class="placeholder">{{appraiser_fullname}}</span></p>';

// PDF Strings
$string['pdf:form:summaries:appraisee'] = '被評価者によるコメント';
$string['pdf:form:summaries:appraiser'] = '評価者による、業績全体のまとめ';
$string['pdf:form:summaries:signoff'] = '承認';
$string['pdf:form:summaries:recommendations'] = '合意されたアクションについて';
$string['pdf:form:summaries:grpleader'] = 'リーダーによるまとめ';

// END FORM

// START OVERVIEW CONTENT

// APPRAISEE: Overview page Content
$string['overview:content:appraisee:1'] = ''; // Never seen...
$string['overview:content:appraisee:2'] = 'アプレイザルの入力を開始してください。<br /><br />
<strong>手順の流れ：</strong>
<ul class="m-b-20">
    <li>アプレイザルミーティングの実施予定日を入力</li>
    <li>フィードバックの依頼</li>
    <li>昨年度の業績と成長を振り返りコメントを入力</li>
    <li>アプレイザルミーティングで話し合うためにCareer Direction、Agreed Impact Plan及びDevelopment Planのページを入力</li>
    <li>ドラフトを{$a->styledappraisername}に提出</li>
</ul>
アプレイザルミーティング実施日の最低<strong><u>一週間</u></strong>前までに評価者にドラフトを提出してください。なお、提出した後でも編集可能です。<br /><br />
<div class="alert alert-danger" role="alert">ドラフトは提出するまで評価者に公開されません。
</div>';

$string['overview:content:appraisee:2:3'] = '評価者からドラフトの変更依頼がありました。<br /><br />
<strong>手順の流れ：</strong>
<ul class="m-b-20">
    <li>評価者の依頼に応じて変更する（詳しくは「アクティビティログ」で依頼項目をご覧ください。）</li>
    <li>ドラフトを{$a->styledappraisername}に提出</li>
</ul>';

$string['overview:content:appraisee:3'] = '{$a->styledappraisername}にアプレイザルのドラフトを提出しました。<br /><br />
<strong>手順の流れ：</strong>
<ul class="m-b-20">
    <li>アプレイザルミーティングを行います。事前に以下の資料をダウンロードすることをお勧めします。</li>
    <ul class="m-b-0">
        <li><a href="{$a->printappraisalurl}" target="_blank">アプレイザル</a></li>
        <li><a href="https://moodle.arup.com/appraisal/reference" target="_blank">Quick Reference Guide</a></li>
    </ul>
    <li>ミーティング後、評価者がアプレイザルをあなたに返却します。ミーティング中で合意した項目に応じて変更するか、最終コメントを入力するか、いずれかをお願いします。</li>
</ul>
<div class="alert alert-danger" role="alert"><strong>注意：</strong> 引き続きアプレイザルの変更は可能ですが、その際は変更した項目をアクティビティログに明記してください。</div>';

$string['overview:content:appraisee:3:4'] = '{$a->styledappraisername}にアプレイザルの編集を依頼しました。<br /><br /> アプレイザルが更新され、レビューできる状態になり次第、通知されます。<br /><br /> <div class="alert alert-danger" role="alert"><strong>注意：</strong> 引き続きアプレイザルの変更は可能ですが、その際は変更した項目をアクティビティログに明記してください。</div>';

$string['overview:content:appraisee:4'] = '{$a->styledappraisername}がコメントを入力しあなたにアプレイザルを返却しました。<br /><br />
<strong>手順の流れ：</strong>
<ul class="m-b-20">
    <li>評価者のコメントとまとめをレビューしてください。変更の必要がある場合、評価者にアプレイザルを返却してください。</li>
    <li>Summariesのページでコメントを入力します。</li>
    <li>評価者に提出し最終レビューを受けてください。その後、承認者が承認を行います。提出後のアプレイザルの変更は一切できません。</li>
</ul>
<div class="alert alert-danger" role="alert"><strong>注意：</strong>あなたのセクションへの変更は引き続き可能ですが、その際は変更した箇所をアクティビティログに明記してください。</div>';

$string['overview:content:appraisee:5'] = '{$a->styledappraisername}にアプレイザルを提出しました。この後評価者により最終レビューを行います。<br /><br />
<strong>手順の流れ：</strong>
    <ul class="m-b-20">
        <li>あなたのアプレイザルは評価者から{$a->styledsignoffname}に送信された後、承認されます。</li>
    </ul>
<div class="alert alert-danger" role="alert"><strong>注意：</strong> 評価者に変更依頼されて返却されない限り、アプレイザルの変更は一切できません。</div>';

$string['overview:content:appraisee:6'] = 'あなたのアプレイザルが{$a->styledsignoffname}に提出されました。<br /><br />
<div class="alert alert-danger" role="alert"><strong>注意：</strong>アプレイザルはロックされており、変更はできません。</div>';

$string['overview:content:appraisee:7'] = 'あなたのアプレイザルが完了しました。「アプレイザルをダウンロードする」ボタンをクリックすればいつでもPDFでダウンロードすることが可能です。';

$string['overview:content:appraisee:7:groupleadersummary'] = 'あなたのアプレイザルが完了し、リーダーがレビュー及びまとめを作成中です。完了次第、通知されます。';

$string['overview:content:appraisee:8'] = $string['overview:content:appraisee:7']; // For legacy where there was a six month status.
$string['overview:content:appraisee:9'] = $string['overview:content:appraisee:7']; // When Groupleader added summary.

// APPRAISER: Overview page Content
$string['overview:content:appraiser:1'] = ''; // Never seen...
$string['overview:content:appraiser:2'] = 'アプレイザルのドラフトは現在、{$a->styledappraiseename}が作成中です。完了次第、通知されます。<br /><br />
<div class="alert alert-danger" role="alert"><strong>注意：</strong>被評価者がアプレイザルの入力を完了するまで、閲覧できません。</div>';

$string['overview:content:appraiser:2:3'] = '{$a->styledappraiseename}にアプレイザルを返却しました。ドラフトが更新され次第、再レビューの通知がされます。<br /><br />
<div class="alert alert-danger" role="alert"><strong>注意：</strong>あなたが入力したセクションは引き続き変更可能です。</div>';

$string['overview:content:appraiser:3'] = '{$a->styledappraiseename}がアプレイザルミーティングに備えてアプレイザルのドラフトを提出しました。<br /><br />
<strong>手順の流れ：</strong>
<ul class="m-b-20">
    <li>アプレイザルミーティングまでにアプレイザルをレビューしてください。内容の追加が必要な場合、アプレイザルを被評価者に返却してください。</li>
    <li>事前に以下の資料をダウンロードすることをお勧めします。</li>
        <ul class="m-b-0">
            <li><a href="{$a->printappraisalurl}" target="_blank">アプレイザル</a></li>
            <li><a href="{$a->printfeedbackurl}" target="_blank">フィードバック</a></li>
            <li><a href="https://moodle.arup.com/appraisal/reference" target="_blank">Quick Reference Guide</a></li>
        </ul>
    <li>ミーティング後、下記の通り進めてください。</li>
        <ul class="m-b-0">
            <li>Appraisee Infoのページでアプレイザルミーティングの終了を入力</li>
            <li>自分のコメントを各セクションに入力</li>
            <li>「Summariesのセクションに自分のまとめと合意されたアクションを入力</li>
            （必要であれば、アプレイザルを被評価者に返却し、被評価者が変更した後にコメントを入力。）
        </ul>
    <li>被評価者に提出します。被評価者があなたのコメントをレビューし、フィードバックを読み、最終コメントを入力します。</li>
</ul>';

$string['overview:content:appraiser:3:4'] = '{$a->styledappraiseename}からアプレイザルの変更依頼がありました。<br /><br />
<strong>手順の流れ：</strong>
<ul class="m-b-20">
    <li>被評価者の依頼に応じて変更する（詳しくは「アクティビティログ」で依頼項目をご覧ください。）</li>
    <li>アプレイザルを{$a->styledappraiseename}と共有し、最終コメントの入力を依頼する</li>
</ul>';


$string['overview:content:appraiser:4'] = 'コメントとまとめを記入し、{$a->styledappraiseename}にアプレイザルを返却しました。最終レビューの準備ができ次第、通知されます。<br /><br />
<div class="alert alert-danger" role="alert"><strong>注意：</strong>あなたのセクションへの変更は引き続き可能ですが、その際は変更した箇所をアクティビティログに明記してください。</div>';

$string['overview:content:appraiser:5'] = '{$a->styledappraiseename}が最終コメントを入力しました。<br /><br />
<strong>手順の流れ：</strong>
<ul class="m-b-20">
    <li>アプレイザルをレビューしてください。</li>
    <li>{$a->styledsignoffname}に提出してください。承認者がレビューとまとめの入力を行います。</li>
    <li>アプレイザルが完了次第、あなたと被評価者に通知がされます。</li>
</ul>
<div class="alert alert-danger" role="alert"><strong>注意：</strong>被評価者にアプレイザルを返却しない限り、変更は一切できません。</div>';

$string['overview:content:appraiser:6'] = 'アプレイザルを{$a->styledsignoffname}に提出しました。<br /><br />
    <div class="alert alert-danger" role="alert"><strong>注意：</strong>アプレイザルは今ロックされており、変更はできません。</div>';

$string['overview:content:appraiser:7'] = 'アプレイザルが承認され終了しました。';

$string['overview:content:appraiser:7:groupleadersummary'] = 'アプレイザルが完了し、リーダーがレビュー及びまとめを作成中です。完了次第、通知されます。';

$string['overview:content:appraiser:8'] = $string['overview:content:appraiser:7']; // For legacy where there was a six month status.
$string['overview:content:appraiser:9'] = $string['overview:content:appraiser:7']; // When Groupleader added summary.

// Overview page GROUP LEADER Content.
$string['overview:content:groupleader:1'] = ''; // Never seen...
$string['overview:content:groupleader:2'] = 'アプレイザルは進行中です。';
$string['overview:content:groupleader:3'] = 'アプレイザルは進行中です。';
$string['overview:content:groupleader:4'] = 'アプレイザルは進行中です。';
$string['overview:content:groupleader:5'] = 'アプレイザルは進行中です。';
$string['overview:content:groupleader:6'] = 'アプレイザルは進行中です。';
$string['overview:content:groupleader:7'] = 'アプレイザルが承認され終了しました。';
$string['overview:content:groupleader:7:groupleadersummary'] = 'このアプレイザルは完了しています。レビュー及びまとめの入力をお願いします。<br /><br />
<strong>手順の流れ：</strong>
<ul class="m-b-20">
    <li>Summariesセクションにまとめを追記し、保存してください。</li>
    <li>あなたがコメントを追記次第、被評価者、評価者及び承認者に通知されます。</li>
</ul>';
$string['overview:content:groupleader:8'] = $string['overview:content:groupleader:7']; // For legacy where there was a six month status.
$string['overview:content:groupleader:9'] = $string['overview:content:groupleader:7'];

// Overview page SIGN OFF Content.
$string['overview:content:signoff:1'] = ''; // Never seen...
$string['overview:content:signoff:2'] = 'アプレイザルは進行中です。<br /><br /><div class="alert alert-danger" role="alert"><strong>注意：</strong>アプレイザルが完了し、レビュー及び承認できる状態になり次第、通知されます。</div>';
$string['overview:content:signoff:3'] = 'アプレイザルは進行中です。<br /><br /><div class="alert alert-danger" role="alert"><strong>注意：</strong>アプレイザルが完了し、レビュー及び承認できる状態になり次第、通知されます。</div>';
$string['overview:content:signoff:4'] = 'アプレイザルは進行中です。<br /><br /><div class="alert alert-danger" role="alert"><strong>注意：</strong>アプレイザルが完了し、レビュー及び承認できる状態になり次第、通知されます。</div>';
$string['overview:content:signoff:5'] = 'アプレイザルは進行中です。<br /><br /><div class="alert alert-danger" role="alert"><strong>注意：</strong>アプレイザルが完了し、レビュー及び承認できる状態になり次第、通知されます。</div>';
$string['overview:content:signoff:6'] = '{$a->styledappraiseename}のアプレイザルをあなたを提出しました。<br /><br />
<strong>手順の流れ：</strong>
<ul class="m-b-20">
    <li>アプレイザルをレビューしてください。</li>
    <li>Summariesセクションにまとめを入力します。</li>
    <li>アプレイザルを完了し、「承認する」ボタンをクリックします。</li>
</ul>';
$string['overview:content:signoff:7'] = 'アプレイザルが承認され終了しました。';

$string['overview:content:signoff:7:groupleadersummary'] = 'アプレイザルが完了し、リーダーがレビュー及びまとめを作成中です。完了次第、通知されます。';

$string['overview:content:signoff:8'] = $string['overview:content:signoff:7']; // For legacy where there was a six month status.
$string['overview:content:signoff:9'] = $string['overview:content:signoff:7']; // When groupleader added summary.

// Overview page buttons.
$string['overview:button:appraisee:2:extra'] = 'アプレイザルを開始する';
$string['overview:button:appraisee:2:submit'] = '{$a->plainappraisername}に提出する';

$string['overview:button:appraisee:4:return'] = '{$a->plainappraisername}に返却し、変更依頼をする';
$string['overview:button:appraisee:4:submit'] = '完成したアプレイザルを{$a->plainappraisername}に提出する';

$string['overview:button:appraiser:3:return'] = '{$a->plainappraiseename}に内容の変更を依頼する';
$string['overview:button:appraiser:3:submit'] = '{$a->plainappraiseename}に最終コメントを依頼する';

$string['overview:button:appraiser:5:return'] = '承認前に再変更する';
$string['overview:button:appraiser:5:submit'] = '{$a->plainsignoffname}に提出する';

$string['overview:button:signoff:6:submit'] = '承認する';

$string['overview:button:returnit'] = 'Return';
$string['overview:button:submitit'] = 'Send';

// END OVERVIEW CONTENT

// START JP string translations - spreadsheet
$string['startappraisal'] = 'オンラインアプレイザルを開始する';
$string['continueappraisal'] = 'オンラインアプレイザルを続ける';
$string['appraisee_feedback_edit_text'] = '編集する';
$string['appraisee_feedback_resend_text'] = '再送する';
$string['appraisee_feedback_view_text'] = '閲覧する';
$string['feedback_setface2face'] = 'フィードバックを依頼する前に、アプレイザルミーティングの日付を設定してください。日付の設定はAppraisee Infoページで行えます。';
$string['feedback_comments_none'] = '補足コメントはありません。';
$string['actionrequired'] = '入力してください。';
$string['actions'] = '操作';
$string['admin:bulkactions'] = '一括操作';
$string['admin:duedate'] = '期限';
$string['admin:email'] = '被評価者にEメールを送信する';
$string['admin:initialise'] = 'アプレイザルを作成する';
$string['admin:nousers'] = '一致するユーザが見つかりませんでした。';
$string['admin:toptext:archived'] = 'アーカイブされたアプレイザルは昨年度以前の履歴として記録されているものですので編集することはできません。';
$string['admin:toptext:complete'] = '承認者によって承認されたアプレイザルはここに表示されます。新しいアプレイザルを開始する前に、現在のアプレイザルをアーカイブしてください。アプレイザルがアーカイブされると、その時の状態でロックされそれ以上の編集ができなくなります。ユーザはダッシュボードの「アーカイブされたアプレイザル」からアプレイザルにアクセスすることができます。';
$string['admin:toptext:deleted'] = '削除されたアプレイザルは引き続き本システムには保存されています。';
$string['admin:toptext:initialise'] = 'ユーザのアプレイザルを設定するには、期限を設定し、ユーザの横にあるドロップダウンリストから評価者および承認者を選択し、「アプレイザルを作成する」をクリックしてください。アプレイザルフォームへのリンクが被評価者（Cc.評価者）に送信され、アプレイザルが開始します。';
$string['admin:toptext:inprogress'] = 'アプレイザルの進捗は以下のリストで確認することができます。承認されたアプレイザルは「完了」に移動します。「操作」からは、評価者や承認者を変更したり、アプレイザルを削除したりすることができます（一度削除したアプレイザルは復元できません）。ページ下部のドロップダウンボタンから、進捗を確認するためのメールをユーザに送ることができます。アーカイブは、年度末に新しいアプレイザルを開始するときに使います。';
$string['admin:usercount'] = '選択したコストセンターの全スタッフ数：{Number}';
$string['appraisals:archived'] = 'アーカイブされたアプレイザル';
$string['appraisals:current'] = '進行中のアプレイザル';
$string['appraisals:noarchived'] = 'アーカイブされたアプレイザルはありません。';
$string['appraisals:nocurrent'] = '進行中のアプレイザルはありません。';
$string['group'] = 'コストセンター';
$string['index:togglef2f:complete'] = 'アプレイザルミーティングを「終了」とする';
$string['index:togglef2f:notcomplete'] = 'アプレイザルミーティングの「終了」を解除する';
$string['index:notstarted'] = 'アプレイザルはまだ開始していない';
$string['index:notstarted:tooltip'] = '被評価者がアプレイザルを開始していません。開始し次第、アプレイザルにアクセスできるようになります。';
$string['index:printappraisal'] = 'アプレイザルをダウンロードする';
$string['index:printfeedback'] = 'フィードバックをダウンロードする';
$string['index:start'] = 'アプレイザルを開始する';
$string['index:toptext:appraisee'] = 'ここでは、進行中およびアーカイブされたアプレイザルを表示しています。進行中のアプレイザルの表示・ダウンロード、およびアーカイブされたアプレイザルのダウンロードは「操作」ボタンから行ってください。';
$string['index:toptext:appraiser'] = 'ここでは、進行中およびアーカイブされたアプレイザルのうち、あなたが評価者であるものを表示しています。進行中のアプレイザルの表示・ダウンロードするには、「操作」ボタンから行ってください。「フィードバックをダウンロードする」には、フィードバックが保存されています。フィードバックは、アプレイザルミーティングの後に被評価者に開示されます。不開示のフィードバックはいかなる場合も表示されません。アーカイブされたアプレイザルを表示するには、「操作」ボタンから行ってください。';
$string['index:toptext:groupleader'] = 'ここでは、進行中およびアーカイブされたアプレイザルのうち、あなたのコストセンター内のアプレイザルを表示しています。進行中のアプレイザルの表示・ダウンロード、およびアーカイブされたアプレイザルのダウンロードは「操作」ボタンから行ってください。';
$string['index:toptext:hrleader'] = 'ここでは、進行中およびアーカイブされたアプレイザルのうち、あなたのコストセンター内のアプレイザルを表示しています。進行中のアプレイザルの表示・ダウンロード、およびアーカイブされたアプレイザルのダウンロードは「操作」ボタンから行ってください。';
$string['index:toptext:signoff'] = 'ここでは、進行中およびアーカイブされたアプレイザルのうち、あなたが承認するアプレイザルを表示しています。進行中のアプレイザルの表示・ダウンロード、およびアーカイブされたアプレイザルのダウンロードは「操作」ボタンから行ってください。';
$string['index:view'] = 'アプレイザルを見る';
$string['success:appraisal:create'] = 'アプレイザルが作成されました。';
$string['success:appraisal:delete'] = 'アプレイザルが削除されました。';
$string['success:appraisal:update'] = 'アプレイザルが更新されました。';
$string['error:appraisal:create'] = '申し訳ありません。アプレイザルの作成中にエラーが発生しました。';
$string['error:appraisal:delete'] = '申し訳ありません。アプレイザルの削除中にエラーが発生しました。';
$string['error:appraisal:select'] = '1つ以上のアプレイザルを選択してください。';
$string['error:appraisal:update'] = '申し訳ありません。アプレイザルの更新中にエラーが発生しました。';
$string['error:appraisalexists'] = 'このユーザは既にアプレイザルを進行中です。';
$string['error:appraiseeassuperior'] = '被評価者は、同時に評価者や承認者になることはできません。';
$string['error:appraisernotvalid'] = '選択された評価者は、このグループでは無効です。';
$string['error:duedate'] = '期限を入力してください。';
$string['error:togglef2f:complete'] = 'プレイザルミーティングを「終了」とすることができませんでした。';
$string['error:togglef2f:notcomplete'] = 'プレイザルミーティングの「終了」を解除することができませんでした。';
$string['error:selectusers'] = '評価者と承認者を選択してください。';
$string['appraisee_feedback_email_success'] = 'Eメールの送信が完了しました。';
$string['appraisee_feedback_email_error'] = 'Eメールの送信に失敗しました。';
$string['appraisee_feedback_invalid_edit_error'] = '無効なEメールアドレスです。';
$string['appraisee_feedback_inuse_edit_error'] = 'このEメールアドレスは既に使用されています。';
$string['appraisee_feedback_inuse_email_error'] = 'このEメールアドレスは既に使用されています。';
$string['appraisee_feedback_resend_success'] = 'Eメールの再送信が完了しました。';
$string['appraisee_feedback_resend_error'] = 'Eメールの再送信中にエラーが発生しました。';
$string['form:choosedots'] = '選択してください';
$string['form:delete'] = '削除する';
$string['form:edit'] = '編集する';
$string['form:language'] = '言語';
$string['form:addfeedback:alert:cancelled'] = '送信がキャンセルされました。あなたのフィードバックはまだ送信されていません。';
$string['form:addfeedback:alert:error'] = '申し訳ありません。フィードバックの送信中にエラーが発生しました。';
$string['form:addfeedback:alert:saved'] = 'フィードバックの送信が完了しました。';
$string['form:feedback:alert:cancelled'] = 'フィードバック依頼はキャンセルされました。';
$string['form:feedback:alert:error'] = '申し訳ありません。フィードバック依頼の送信中にエラーが発生しました。';
$string['form:feedback:alert:saved'] = 'フィードバック依頼の送信が完了しました。';
$string['form:lastyear:nolastyear'] = 'あなたの前回のアプレイザルが登録されていません。前回のアプレイザルをPDFもしくはWordでアップロードしてください。';
$string['form:lastyear:file'] = '<strong>アプレイザルがアップロードされました：<a href="{$a->path}" target="_blank">{$a->filename}</a></strong>';
$string['form:lastyear:cardinfo:developmentlink'] = '昨年の成長';
$string['form:lastyear:cardinfo:performancelink'] = '昨年の実績';
$string['feedbackrequests:description'] = 'このダッシュボードでは、完了していないフィードバック依頼および過去に行ったフィードバックを表示しています。';
$string['feedbackrequests:outstanding'] = '未完了の依頼';
$string['feedbackrequests:norequests'] = '未完了の依頼フィードバック依頼はありません';
$string['feedbackrequests:completed'] = '完了した依頼';
$string['feedbackrequests:nocompleted'] = '完了したフィードバック依頼はありません';
$string['feedbackrequests:th:actions'] = '操作';
$string['feedbackrequests:emailcopy'] = 'コピーをEメールする';
$string['feedbackrequests:submitfeedback'] = 'フィードバックを提出する';
/*
$string['email:subject:myfeedback'] = '{{appraisee}}へのフィードバック';
$string['email:body:myfeedback'] = '{{recipient}}さん、あなたは以下のとおり{{appraisee}}に対しての{{confidential}}のフィードバックを提出しました： {{feedback}} {{feedback_2}}';
*/
$string['feedbackrequests:confidential'] = '開示しない';
$string['feedbackrequests:nonconfidential'] = '開示する';
$string['feedbackrequests:received:confidential'] = '受取りました（開示しない）';
$string['feedbackrequests:received:nonconfidential'] = '受取りました';
$string['feedbackrequests:paneltitle:confidential'] = 'フィードバック（開示しない）';
$string['feedbackrequests:paneltitle:nonconfidential'] = 'フィードバック';
$string['feedbackrequests:legend'] = '* 評価者が追加した情報提供者';
$string['success:checkin:add'] = 'チェックインが完了しました。';
$string['error:checkin:add'] = 'チェックインの入力に失敗しました。';
$string['error:checkin:validation'] = '入力してください。';
$string['checkin:deleted'] = 'チェックインを削除しました。';
$string['checkin:delete:failed'] = 'チェックインの削除に失敗しました。';
$string['checkin:update'] = '更新する';
$string['checkin:addnewdots'] = 'チェックインのコメントを入力してください。';

// END JP string translations - spreadsheet

// WORKFLOW EMAILS

// ****** WORKFLOW Email7-GROUPLEADER ******
//$string['email:body:status:6_to_7:groupleader'] = '<p>{{groupleaderfirstname}}さん</p><p>{{appraiseefirstname}} {{appraiseelastname}}}さんのアプレイザルが完了しました。レビュー及びまとめの記入をお願いします。</p>{{comment}}<p>アプレイザルは<a href="{{linkgroupleader}}">こちら</a>からアクセスしてください。.</p><p>宜しくお願いします。<br />{{signofffirstname}} {{signofflastname}}</p><br /><hr><p>Further assistance can be found <a href="https://moodle.arup.com/appraisal/help">here</a> alternatively you can contact your local HR group or raise a Service Desk ticket.</p><p>This is an auto generated message sent to {{groupleaderemail}} from {{signoffemail}} by moodle.arup.com - Appraisal status: {{status}} - Email7Leader</p><p>Trouble viewing? To view your leader dashboard online please copy and paste this URL {{linkgroupleaderdashboard}} into your browser.</p>';

//$string['email:subject:status:6_to_7:groupleader'] = 'アプレイザル({{appraiseefirstname}} {{appraiseelastname}})はレビューできる状態です。';

//$string['email:replacement:comment'] = '<p>私のコメント: <br />{$a}</p>';

// ****** WORKFLOW Email6-APPRAISER ******
//$string['email:body:status:6_to_7:appraiser'] = '<p>{{appraiserfirstname}}さん</p><p>{{appraiseefirstname}} {{appraiseelastname}}さんのアプレイザルを承認しました。</p>{{groupleaderextra}}{{comment}}<p>完了したアプレイザルは<a href="{{linkappraiser}}">こちら</a>で閲覧できます。</p><p>宜しくお願いします。<br />{{signofffirstname}} {{signofflastname}}</p><br /><hr><p>Further assistance can be found <a href="https://moodle.arup.com/appraisal/help">here</a> alternatively you can contact your local HR group or raise a Service Desk ticket.</p><p>This is an auto generated message sent to {{appraiseremail}} from {{signoffemail}} by moodle.arup.com - Appraisal status: {{status}} - Email6Appraiser</p><p>Trouble viewing? To view your appraiser dashboard online please copy and paste this URL {{linkappraiserdashboard}} into your browser.</p>';

//$string['email:body:status:6_to_7:appraiser:groupleaderextra'] = '<p>アプレイザルが完了し、リーダーがレビュー及びまとめを作成中です。完了次第、通知されます。</p>';
//$string['email:subject:status:6_to_7:appraiser'] = 'Appraisal ({{appraiseefirstname}} {{appraiseelastname}}) is complete';

// ****** WORKFLOW Email6-APPRAISEE ******
//$string['email:body:status:6_to_7:appraisee'] = '<p>{{appraiseefirstname}}さん</p><p>あなたのアプレイザルをレビューし、承認しました。</p>{{groupleaderextra}}{{comment}}<p>完了したアプレイザルは<a href="{{linkappraisee}}">こちら</a>で閲覧できます。</p><p>宜しくお願いします。<br />{{signofffirstname}} {{signofflastname}}</p><br /><hr><p>Further assistance can be found <a href="https://moodle.arup.com/appraisal/help">here</a> alternatively you can contact your local HR group or raise a Service Desk ticket.</p><p>This is an auto generated message sent to {{appraiseeemail}} from {{signoffemail}} by moodle.arup.com - Appraisal status: {{status}} - Email6Appraisee</p><p>Trouble viewing? To view your appraisal online please copy and paste this URL {{linkappraisee}} into your browser.</p>';

//$string['email:body:status:6_to_7:appraisee:groupleaderextra'] = '<p>あなたのアプレイザルが完了し、リーダーがレビュー及びまとめを作成中です。完了次第、通知されます。</p>';

//$string['email:subject:status:6_to_7:appraisee'] = 'Your Appraisal is Complete';

//APPRAISEE EMAIL
//$string['email:subject:summaries:groupleaderemail:appraisee'] = 'リーダーのコメントがアプレイザルに追加されました。';
//$string['email:body:summaries:groupleaderemail:appraisee'] = '<p> {{appraiseefirstname}}さん</p>あなたの完了したアプレイザルをレビューし、コメントを記入しました。<br><p>あなたの完了したアプレイザルは<a href="{{appraisalurl}}">こちら</a>で閲覧できます。.</p><p>宜しくお願いします。<br /><br>{{groupleadername}}';

//APPRAISER EMAIL
//$string['email:subject:summaries:groupleaderemail:appraiser'] = 'アプレイザル{{appraiseefirstname}} {{appraiseelastname}}が更新されました。';
//$string['email:body:summaries:groupleaderemail:appraiser'] = '<p>{{appraiserfirstname}}さん</p><p>{{appraiseefirstname}} {{appraiseelastname}}さんのアプレイザルにコメントを記入しました。</p><p>完了したアプレイザルは<a href="{{appraisalurl}}">こちら</a>で閲覧できます。</p>宜しくお願いします。<br><br>{{groupleadername}}';

//SIGN OFF EMAIL
//$string['email:subject:summaries:groupleaderemail:signoff'] = 'アプレイザル{{appraiseefirstname}} {{appraiseelastname}}が更新されました。';
//$string['email:body:summaries:groupleaderemail:signoff'] = '<p>{{signofffirstname}}さん</p><p>{{appraiseefirstname}} {{appraiseelastname}}さんのアプレイザルにコメントを記入しました。</p><p>完成したアプレイザルは<a href="{{appraisalurl}}">こちら</a>で閲覧できます。</p>宜しくお願いします。<br><br>{{groupleadername}}';

//$string['status:9'] = 'アプレイザルが完成しました。';