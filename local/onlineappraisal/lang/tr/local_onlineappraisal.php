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

//NOTE: ERROR messages underneath relevant string

// START FORM

// INTRODUCTION PAGE
$string['appraisee_heading'] = 'Çevrimiçi Değerlendirmeye Hoş Geldiniz';

// ERROR: remove east asia specfic content?

// LAST YEAR REVIEW
$string['form:lastyear:title'] = 'Section 1: Review of last year';
$string['form:lastyear:nolastyear'] = 'Note: We notice that you don\'t have a previous appraisal in the system. Please upload your last appraisal as a pdf / word document below.';

//ERROR: translations provided from this point onwards

$string['form:lastyear:intro'] = 'Bu bölümde hem değerlendirilen kişi, hem de değerlendirici geride kalan on iki aylık süreçte gösterilen performansı ve bu performansın nasıl gerçekleştirildiğini ele alır. <a href="https://moodle.arup.com/appraisal/guide" target="_blank">Değerlendirme klavuzu</a>, müzakerenin niteliğine ilişkin daha detaylı bilgi verir.';
$string['form:lastyear:upload'] = 'Değerlendirme formunuzu yükleyin';
$string['form:lastyear:appraiseereview'] = '1.1 Geçen yıla ait performansın değerlendirilen kişi tarafından gözden geçirilmesi';
$string['form:lastyear:appraiseereviewhelp'] =
'<div class="well well-sm"><em>Genel hatlarıyla ele alındığında; projeler, kişiler ve müşteriler bağlamında, son değerlendirmenizden bu yana gösterdiğiniz performans nasıldır? </em>
    <ul class="m-b-0">
        <li><em>Bilgi ve uzmanlık hususunda nasıl işbirliği yaptınız ve bunları nasıl paylaştınız?  Bunun sonuçları nelerdir?</em></li>
        <li><em>Performansınızın herhangi bir kısmında beklentilerin altında kaldınız mı?</em></li>
        <li><em>Başka kişilerden sorumlu iseniz, o kişilerin iyi veya kötü yönlü performansını ve davranışlarını gerektiği şekilde yönettiniz mi?</em></li>
        <li><em>Daha etkin bir şekilde çalışabilmek için teknolojiyi nasıl kullandınız? </em></li>
    </ul>
</div>';
$string['form:lastyear:appraiserreview'] = '1.2 Geçen yıla ait performansın değerlendirici tarafından gözden geçirilmesi';
$string['form:lastyear:appraiserreviewhelp'] =
'<div class="well well-sm"><em>Son değerlendirmeden bu yana, değerlendirilen kişinin kendi performansı ile ilgili gözden geçirmesi hakkında yorumlarınızı belirtin.</em>
    <ul class="m-b-0">
        <li><em>Gösterilen ilerlemeler nelerdir?</em></li>
        <li><em>Değerlendirilen kişinin önceden kararlaştırılmış ve kişi hakkında geri bildirim verecek kişilerden aldığı geri bildirimleri özetleyin.</em></li>
    </ul>
<em>Performans veya davranışlarının herhangi bir kısmı beklentilerin altında kalmış ise, bu hususun bu bölümde ele alınması ve kaydedilmesi <strong>şarttır</strong>. Bunlar; onların projeleri, ekipleri, müşterileri veya genel olarak diğer kişilerle ilgili olabilir.</em>
</div>';
$string['form:lastyear:appraiseedevelopment'] = '1.3 Geçen yıla ait gelişimin değerlendirilen kişi tarafından gözden geçirilmesi';
$string['form:lastyear:appraiseedevelopmenthelp'] =
'<div class="well well-sm"><em>Son değerlendirmenizden bu yana, kişisel gelişiminizle ilgili yorumlarınızı belirtin:</em>
    <ul class="m-b-0">
        <li><em>Beceri, bilgi ve davranışlarınızı nasıl geliştirdiniz?</em></li>
        <li><em>Geçen yıl için planladığınız, fakat henüz gerçekleştirmediğiniz gelişmeler nelerdir?</em></li>
    </ul>
</div>';
$string['form:lastyear:appraiseefeedback'] = '1.4 Performansınızı veya ekibinizin performansını etkileyen ya da iyileştiren herhangi bir faktör var mı?';
$string['form:lastyear:appraiseefeedbackhelp'] = '<div class="well well-sm"><em>Değerlendirilen kişi tarafından doldurulacaktır</em></div>';

// CAREER DIRECTION
$string['form:careerdirection:title'] = 'Section 2: Career Direction';
$string['form:careerdirection:intro'] = 'Bu bölümün amacı, değerlendirilen kişinin kendi kariyer hedefleri üzerinde düşünmesine ve pratik bir yol izleyerek değerlendiricisiyle bu konular hakkında fikir alışverişinde bulunmasına olanak sağlamaktır. Kıdemi düşük personel için bu bağlamda yaklaşık 1-3 yıl içindeki kariyer gelişimi planlanabilir. Daha kıdemli personelin ise bu sürenin 3-5 yıl olması beklenir. ';
$string['form:careerdirection:progress'] = '2.1 Kariyerinizin nasıl ilerlemesini istersiniz?';
$string['form:careerdirection:progresshelp'] =
'<div class="well well-sm"> <em>Dikkate almanız gerekenler:</em>
    <ul class="m-b-0">
        <li><em>Ne tür bir iş yapmak ve hangi seviyede sorumluluk almak istersiniz?</em></li>
        <li><em>Önümüzdeki birkaç yıllık süreçte çalışma hayatınızla ilgili önem verdiğiniz hususlar nelerdir (örneğin; düşünce özgürlüğü, rahatlık, uzmanlaşma, genelleşme, mobilite, tasarım, başkalarının sorumluluğunu alma, vb.)?</em></li>
        <li><em>Coğrafi konum olarak nerede bulunmak istersiniz?</em></li>
    </ul>
</div>';
$string['form:careerdirection:comments'] = '2.2 Değerlendiricinin yorumları';
$string['form:careerdirection:commentshelp'] =
'<div class="well well-sm"><em>Dikkate almanız gerekenler:</em>
    <ul class="m-b-0">
        <li><em>Değerlendirilen kişinin hedefleri ne derece gerçekçi, zorlu ve iddialı?</em></li>
        <li><em>Gereken deneyim, beceri ve davranışsal gelişimi sağlayacak görevler, projeler ve diğer iş fırsatları nelerdir?</em></li>
    </ul>
</div>';

// AGREED IMPACT PLAN
$string['form:impactplan:title'] = 'Section 3: Agreed Impact Plan';
$string['form:impactplan:intro'] = 'Kararlaştırılan Etki Planı, sorumlu olduğu alanla ilgili olarak değerlendirilen kişinin önümüzdeki yıl nasıl bir farklılık yaratacağını ve bu farklılığın genel itibariyle şirkete neler getireceğini ortaya koyar. Plan aynı zamanda, değerlendirilen kişinin kendi sorumluluk alanındaki işleri ya da kendi projesini / ekibini / ofisini / grubunu nasıl geliştireceğini de kapsamalıdır. Uygulama açısından düşünüldüğünde, zaman çizelgesi, kalite, bütçe, tasarım/yenilik ve kişiler, müşteriler ya da genel olarak işler üzerindeki etkiler hakkında spesifik bilgilerin verilmesi sürecidir.<br /><br />
<a href="https://moodle.arup.com/appraisal/contribution" target="_blank">Katkı Kılavuzu</a> ve <a href="https://moodle.arup.com/appraisal/guide" target="_blank">Değerlendirme Kılavuzu</a>, bu iyileştirmelerin nasıl yapılabileceğine ilişkin önerileri içerecektir.';

$string['form:impactplan:impact'] = '3.1 Projeleriniz, müşterileriniz, ekibiniz veya firmanızda önümüzdeki yıl yapmak istediğiniz etkiyi açıklayınız:';
$string['form:impactplan:impacthelp'] =
'<div class="well well-sm"><em>Açıklamalarınız aşağıdakileri içerebilir:</em>
    <ul class="m-b-0">
        <li><em>Faaliyet alanlarınız</em></li>
        <li><em>Neden önemli oldukları</em></li>
        <li><em>Bunları nasıl gerçekleştireceğiniz</em></li>
        <li><em>Kiminle işbirliği yapacağınız</em></li>
        <li><em>Yaklaşık zaman dilimi: 3/6/12/18 ay veya daha uzun</em></li>
        <li><em>Kararlaştırılan Etki Planınızın arzu ettiğiniz kariyer ilerlemesine uygunluğu ve kariyer planlamanızı ne derece desteklediği</em></li>
    </ul>
</div>';
$string['form:impactplan:support'] = '3.2 Bu amaca ulaşma hususunda Arup’tan beklediğiniz destek nedir? ';
$string['form:impactplan:supporthelp'] =
'<div class="well well-sm"><em>Aşağıdakileri dikkate alabilirsiniz:</em>
    <ul class="m-b-0">
        <li><em>Diğerlerinin yardımı</em></li>
        <li><em>Denetim</em></li>
        <li><em>Kaynaklar (zaman, bütçe, ekipman)</em></li>
        <li><em>Kişisel gelişim</em></li>
        <li><em>Araçlar (yazılım, donanım)</em></li>
    </ul>
</div>';
$string['form:impactplan:comments'] = '3.3 Değerlendiricinin yorumları';
$string['form:impactplan:commentshelp'] = '<div class="well well-sm"><em>Değerlendirici tarafından doldurulacaktır</em></div>';

// DEVELOPMENT PLAN
$string['form:development:title'] = 'Section 4: Development Plan';
$string['form:development:intro'] = 'Gelişim Planı, değerlendirilen kişinin kariyer ilerlemesi ve Kararlaştırılan Etki Planını desteklemek için ihtiyaç duyulan kişisel beceriler, bilgiler veya davranışsal değişikliklerin neler olduğunu ortaya koyar. <br /><br />
Bu amaca ulaşmak için önümüzdeki 12-18 aylık dönemde nasıl gelişim göstermeniz gerekiyor?  İhtiyaç duyacağınız destek nedir ve bu gelişimi ne zaman gerçekleştirmeyi planlıyorsunuz?<br /><br />
<div class="well well-sm">Kişisel gelişim açısından Arup’ta “70-20-10” ilkesini kullanıyoruz.  Bu ilke; çoğu kişi için, gelişimin %70’inin “iş başındayken” olması ve deneyim yoluyla kazanılması gerektiğini gösterir. %20’si, koçluk veya akıl hocalığı yoluyla diğer kişiler aracılığıyla olmalıdır.  Geri kalan %10 ise, sınıf kursları veya resmi e-öğrenme gibi resmi öğrenme yöntemleriyle kazanılmalıdır. Yüzdelik oranlar tabiki bir yönerge olarak belirtilmiştir.</div>';
$string['form:development:seventy'] = 'İş başındayken gerçekleşen öğrenme – yaklaşık %70';
$string['form:development:seventyhelp'] =
'<div class="well well-sm"><em>Örneğin:</em>
    <ul class="m-b-0">
        <li><em>Proje atamaları</em></li>
        <li><em>Ekip atamaları</em></li>
        <li><em>Mobilite</em></li>
        <li><em>İş ve geri bildirimle ilgili görüşmeler</em></li>
        <li><em>Projelerle ilgili gözden geçirmeler, tasarım yetiştirme çalışmaları</em></li>
        <li><em>Okuma</em></li>
        <li><em>Araştırma</em></li>
    </ul>
</div>';
$string['form:development:twenty'] = 'Diğer kişilerden öğrenme – yaklaşık %20';
$string['form:development:twentyhelp'] =
'<div class="well well-sm"><em>Örneğin:</em>
    <ul class="m-b-0">
        <li><em>Ekip üyeleri</em></li>
        <li><em>Uzmanlar</em></li>
        <li><em>Müşteriler</em></li>
        <li><em>İşbirlikçiler</em></li>
        <li><em>Konferanslar</em></li>
        <li><em>Koçluk</em></li>
        <li><em>Akıl Hocalığı</em></li>
    </ul>
</div>';
$string['form:development:ten'] = 'Resmi kurslardan öğrenme - yüz yüze veya çevrimiçi – yaklaşık %10';
$string['form:development:tenhelp'] =
'<div class="well well-sm"><em>Örneğin:</em>
    <ul class="m-b-0">
        <li><em>Sınıf kursları</em></li>
        <li><em>Resmi e-öğrenme</em></li>
        <li><em>Sanal sınıf öğrenimi</em></li>
    </ul>
</div>';

// SUMMARIES
$string['form:summaries:title'] = 'Section 5: Summaries';
$string['form:summaries:intro'] = 'Bu bölümün amacı; ücretlendirme, terfi ve gelişim kararlarına iştirak edenlerin sonradan başvurabileceği, değerlendirme formuyla ilgili içeriğin özetlenmesidir.';
$string['form:summaries:appraiser'] = '5.1 Değerlendiricinin genel performans özeti';
$string['form:summaries:appraiserhelp'] = '<div class="well well-sm"><em>Değerlendirici, gelecekteki maaş/terfi/gelişim kararlarıyla bağlantılı kişilerin kolaylıkla anlayabileceği şekilde, performansa ilişkin kısa ve öz bir özet sunmalıdır.  Bilhassa, genel performansın beklentilerin altında kaldığı, ya da beklentileri aştığı değerlendirici tarafından net bir şekilde belirtilmelidir. </em>
</div>';
$string['form:summaries:recommendations'] = '5.2 Kararlaştırılan eylemler';
$string['form:summaries:recommendationshelp'] =
'<div class="well well-sm"><em>Değerlendirici tarafından doldurulacaktır</em><br/>
<em>Şimdi ne olması gerekiyor? Örneğin:</em>
    <ul>
        <li><em>Gelişme</em></li>
        <li><em>Mobilite</em></li>
        <li><em>Atamalar</em></li>
        <li><em>Performans desteği</em></li>
    </ul>
</div>';
$string['form:summaries:appraisee'] = '5.3 Değerlendirilen kişinin yorumları';
$string['form:summaries:appraiseehelp'] = '<div class="well well-sm"><em>Değerlendirilen kişi tarafından doldurulacaktır</em></div>';
$string['form:summaries:signoff'] = '5.4 Onaylama özeti';
$string['form:summaries:signoffhelp'] = '<div class="well well-sm"><em>Lider/belirlenmiş onaylayan tarafından doldurulacaktır.</em></div>';
$string['form:summaries:groupleader'] = '5.5 Groupleader summary';
$string['form:summaries:groupleaderhelp'] = '<div class="well well-sm"><em>To be completed by group leader.</em></div>';
// ERROR: missing translation

// CHECK-IN
$string['appraisee_checkin_title'] = 'Section 6. Check-in';
$string['checkins_intro'] = 'Yıl boyunca, değerlendirilen kişi ve değerlendiricinin Kararlaştırılan Etki Planı, Gelişim Planı, eylemler ve performansa göre ilerleme durumunu müzakere etmeyi arzu etmesi beklenir. Değerlendirilen kişi ve/veya değerlendirici, ilerleme durumunu kayıt altına almak için aşağıdaki bölümü kullanabilir. Bu görüşmelerin sıklığı size bağlı olup, en az yılda bir kez yapılması tavsiye edilir.';

// FEEDBACK CONTRIBUTION
$string['feedback_header'] = '{$a->appraisee_fullname} ile ilgili geri bildirimde bulunma';
$string['feedback_addfeedback'] = 'Değerlendirilen kişinin son 12 aylık dönemdeki katkısını takdir ettiğiniz üç alanı belirtin. Daha fazla verimli olabileceğini hissettiğiniz en fazla üç alanla ilgili bilgi verin. Bu geri bildirim iş arkadaşlarınızın sorunları daha etkin bir şekilde çözmesine yardımcı olacağından, eleştiri yaparken dürüst fakat <i>yapıcı</i> davranın.';
$string['confidential_label_text'] = 'Yorumlarınızın gizli kalması için bu kutuyu işaretleyin. Bu kutu işaretlenmemiş olursa yorumlarınız değerlendirilen kişiyle paylaşılacaktır.';

// FEEDBACK EMAILS - sent by appraisee
$string['email:subject:appraiseefeedback'] = 'Değerlendirmem için geri bildirim talep et';

// FEEDBACK EMAIL - sent by appraiser
$string['email:subject:appraiserfeedback'] = '{{appraisee_fullname}}in değerlendirmesi için geri bildirim talep et';

// END FORM

// START OVERVIEW CONTENT

// APPRAISEE: Overview page content
$string['overview:content:appraisee:1'] = ''; // Never seen...
$string['overview:content:appraisee:2'] = 'Lütfen değerlendirme formunuzu doldurmaya başlayın.<br /><br />
<strong>Sonraki adımlar: </strong>
<ul class="m-b-20">
    <li>Planlanan yüz yüze görüşme tarihini belirtin.</li>
    <li>Geri bildirim talep edin.</li>
    <li>Geçen Yıla Ait Performans ve Gelişim bilgilerini verin ve buna ilişkin yorumlar yapın.</li>
    <li>Yüz yüze görüşmede ele alınmak üzere Kariyer Yönetimi, Etki ve Gelişim Planı bölümlerini doldurun.</li>
    <li>Taslak formunuzu değerlendiriciniz {$a->styledappraisername}, ile paylaşın.</li>
</ul>
Taslak formunuzu yüz yüze görüşmeden en az <strong><u>bir hafta</u></strong> önce değerlendiricinizle paylaşın. Paylaştıktan sonra da düzenlemeye devam edebileceksiniz.<br /><br />
<div class="alert alert-danger" role="alert"><strong>Not:</strong> Taslak formunuzu paylaşana kadar değerlendiriciniz taslak formunuzu göremeyecektir.</div>';

$string['overview:content:appraisee:2:3'] = 'Değerlendiriciniz değerlendirme taslağınızda değişiklikler yapılmasını talep etti.<br /><br />
<strong>Sonraki adımlar:</strong>
<ul class="m-b-20">
    <li>Değerlendiriciniz tarafından talep edilen değişiklikleri yapın (talep edilenler hakkında daha fazla bilgi için faaliyet günlüğüne bakın).</li>
    <li>Taslak formunuzu {$a->styledappraisername} ile paylaşın.</li>
</ul>';

$string['overview:content:appraisee:3:4'] = 'Değerlendirme formunuzu, üzerinde değişiklikler yapılmak üzere şu kişiye geri gönderdiniz: {$a->styledappraisername} <br /><br /> Değerlendirme formu güncellenip, tekrar gözden geçirmeniz için hazır olduğunda size bildirim gönderilecektir.<br /><br /> <div class="alert alert-danger" role="alert"><strong>Not:</strong> Değerlendirme formunuz değerlendiricideyken form üzerinde düzenleme yapmaya devam edebilirsiniz. Ancak yaptığınız değişiklikleri vurgulamak için faaliyet günlüğünü kullanmanızı öneririz.</div>';

$string['overview:content:appraisee:4'] = '{$a->styledappraisername} yorumlarını ekledi ve değerlendirme formu size geri gönderildi.<br /><br />
<strong>Sonraki adımlar: </strong>
<ul class="m-b-20">
    <li>Değerlendiricinizin yorumlarını ve özeti gözden geçirin. Değişiklik yapılmasına ihtiyaç duyarsanız gerekirse değerlendirme formunu değerlendiricinize geri gönderin.</li>
    <li>Yorumlarınızı Özetler bölümünde belirtin.</li>
    <li>Onaylayıp çıkmadan önce nihai gözden geçirme amacıyla değerlendiricinize gönderin. Değerlendirme formunu gönderdikten sonra üzerinde düzenleme yapamazsınız.</li>
</ul>
<div class="alert alert-danger" role="alert"><strong>Not:</strong> Değerlendirme formunuzdaki bölümler üzerinde düzenleme yapmaya devam edebilirsiniz. Ancak değişiklikleri değerlendiricinizin dikkatini çekecek şekilde vurgulamak için faaliyet günlüğünü kullanmanızı öneririz</div>';

$string['overview:content:appraisee:5'] = 'Doldurulmuş değerlendirme formunuzu son kez gözden geçirilmek üzere şu kişiye gönderdiniz: {$a->styledappraisername}<br /><br />
<strong>Sonraki adımlar:</strong>
    <ul class="m-b-20">
        <li>Değerlendiriciniz değerlendirme formunu şu kişinin onayına gönderecektir: {$a->styledsignoffname}</li>
    </ul>
<div class="alert alert-danger" role="alert"><strong>Not:</strong> Değerlendirici başka düzenlemeler yapmak üzere size geri göndermediği sürece değerlendirme formunda artık değişiklik yapamazsınız.</div>';

$string['overview:content:appraisee:6'] = 'Değerlendirme formunuz gözden geçirilmek ve özet yazılmak üzere şu kişiye gönderildi: {$a->styledsignoffname}<br /><br />
<div class="alert alert-danger" role="alert"><strong>Not:</strong> Değerlendirme formu şu anda kilitlidir, üzerinde düzenleme yapılamaz.</div>';

$string['overview:content:appraisee:7'] = 'Değerlendirmeniz artık tamamlanmıştır. “Değerlendirmeyi indir” düğmesine tıklayarak dilediğiniz zaman bir PDF kopyasını indirebilirsiniz.';

// Overview page APPRAISER Content.
$string['overview:content:appraiser:1'] = ''; // Never seen...
$string['overview:content:appraiser:2'] = 'Değerlendirme formu şu anda {$a->styledappraiseename}. tarafından hazırlanıyor. Gözden geçirilmeye hazır olduğunda size bilgi verilecektir.<br /><br />
<div class="alert alert-danger" role="alert"><strong>Not:</strong> Sizinle paylaşılana kadar değerlendirme formunu göremeyeceksiniz.</div>';

$string['overview:content:appraiser:2:3'] = 'Değerlendirme formunu, üzerinde değişiklikler yapılmak üzere şu kişiye geri gönderdiniz: {$a->styledappraiseename}. Değerlendirme taslak formu güncellenip, tekrar gözden geçirmeniz için hazır olduğunda size bildirim gönderilecektir.<br /><br />
<div class="alert alert-danger" role="alert"><strong>Not:</strong> Kendi bölümlerinizde değişiklik yapmaya devam edebilirsiniz.</div>';

$string['overview:content:appraiser:3:4'] = '{$a->styledappraiseename} değerlendirme formunda değişiklikler yapılmasını talep etti.<br /><br />
<strong>Sonraki adımlar:</strong>
<ul class="m-b-20">
    <li>Değerlendirilen kişi tarafından talep edilen değişiklikleri yapın (talep edilenler hakkında daha fazla bilgi için faaliyet günlüğüne bakınız).</li>
    <li>Nihai yorumlar için değerlendirme formunu {$a->styledappraiseename} ile paylaşın</li>
</ul>';

$string['overview:content:appraiser:4'] = 'Yorumlarınızı ve özeti eklediniz; nihai yorumların belirtilmesi amacıyla değerlendirme formunu şu kişiye geri gönderdiniz: {$a->styledappraiseename}. Son gözden geçirmeye hazır olduğunda size bilgi verilecektir.<br /><br />
<div class="alert alert-danger" role="alert"><strong>Not:</strong> Değerlendirme formundaki bölümleriniz üzerinde düzenleme yapmaya devam edebilirsiniz. Ancak değişiklikleri değerlendirilen kişinin dikkatini çekecek şekilde vurgulamak için faaliyet günlüğünü kullanmanızı öneririz.</div>';

$string['overview:content:appraiser:5'] = '{$a->styledappraiseename} nihai yorumlarını ekledi.<br /><br />
<strong>Sonraki adımlar:</strong>
<ul class="m-b-20">
    <li>Onaylanmaya hazır olan doldurulmuş değerlendirme formunu gözden geçirin.</li>
    <li>Gözden geçirmek ve özet eklemek üzere şu kişiye gönderin: {$a->styledsignoffname}.</li>
    <li>Değerlendirme formu tamamlandığında siz ve değerlendiricinize bilgi verilecektir.</li>
</ul>
<div class="alert alert-danger" role="alert"><strong>Not:</strong> Değerlendirilen kişiye geri göndermediğiniz sürece değerlendirme formunda artık değişiklik yapamazsınız.</div>';

$string['overview:content:appraiser:6'] = 'Bu değerlendirme formunu tamamlanmak üzere şu kişiye gönderdiniz: {$a->styledsignoffname}.<br /><br />
    <div class="alert alert-danger" role="alert"><strong>Not:</strong> Değerlendirme formu şu anda kilitlidir, üzerinde düzenleme yapılamaz</div>';

$string['overview:content:appraiser:7'] = 'Bu değerlendirme tamamlanmış ve onaylanmıştır.';

// Overview page GROUP LEADER Content.
$string['overview:content:groupleader:1'] = ''; // Never seen...
$string['overview:content:groupleader:2'] = 'Değerlendirme devam ediyor.';
$string['overview:content:groupleader:3'] = 'Değerlendirme devam ediyor.';
$string['overview:content:groupleader:4'] = 'Değerlendirme devam ediyor.';
$string['overview:content:groupleader:5'] = 'Değerlendirme devam ediyor.';
$string['overview:content:groupleader:6'] = 'Değerlendirme devam ediyor.';
$string['overview:content:groupleader:7'] = 'Bu değerlendirme tamamlanmış ve onaylanmıştır.';
$string['overview:content:groupleader:7:groupleadersummary'] = 'This appraisal is complete but the Groupleader can still add a Groupleader Summary on the Summaries page';
// ERROR: missing translation

// Overview page SIGN OFF Content.
$string['overview:content:signoff:1'] = ''; // Never seen...
$string['overview:content:signoff:2'] = 'Değerlendirme devam ediyor.<br /><br /><div class="alert alert-danger" role="alert"><strong>Not:</strong> Değerlendirme gözden geçirilmeye ve onaylanmaya hazır olduğunda size bilgi verilecektir.</div>';
$string['overview:content:signoff:3'] = 'Değerlendirme devam ediyor.<br /><br /><div class="alert alert-danger" role="alert"><strong>Not:</strong> Form gözden geçirilmeye ve onaylanmaya hazır olduğunda size bilgi verilecektir.</div>';
$string['overview:content:signoff:4'] = 'Değerlendirme devam ediyor.<br /><br /><div class="alert alert-danger" role="alert"><strong>Not:</strong> Form gözden geçirilmeye ve onaylanmaya hazır olduğunda size bilgi verilecektir.</div>';
$string['overview:content:signoff:5'] = 'Değerlendirme devam ediyor.<br /><br /><div class="alert alert-danger" role="alert"><strong>Not:</strong> Form gözden geçirilmeye ve onaylanmaya hazır olduğunda size bilgi verilecektir.</div>';
$string['overview:content:signoff:6'] = 'Şu kişiye ait değerlendirme, gözden geçirilmek üzere size gönderilmiştir: {$a->styledappraiseename}.<br /><br />
<strong>Sonraki adımlar:</strong>
<ul class="m-b-20">
    <li>Değerlendirme formunu gözden geçirin</li>
    <li>Özetinizi Özetler bölümünde belirtin</li>
    <li>Değerlendirmeyi tamamlamak için Onayla düğmesine tıklayın</li>
</ul>';

$string['overview:content:signoff:7'] = 'Bu değerlendirme tamamlanmış ve onaylanmıştır. ';

// Overview page buttons.
$string['overview:button:appraisee:2:extra'] = 'Değerlendirme formunuzu doldurmaya başlayın';
$string['overview:button:appraisee:2:submit'] = '{$a->plainappraisername} ile paylaşın';

$string['overview:button:appraisee:4:return'] = 'Değişiklik yapabilmesı için {$a->plainappraisername} a geri gönderin';
$string['overview:button:appraisee:4:submit'] = 'Tamamlanmış değerlendirmenizi {$a->plainappraisername} a gönderin';

$string['overview:button:appraiser:3:return'] = 'Şu kişiden ek bilgi isteyin: {$a->plainappraiseename}';
$string['overview:button:appraiser:3:submit'] = 'Nihai yorumlar için şu kişiye gönderin: {$a->plainappraiseename}';

$string['overview:button:appraiser:5:return'] = 'Onaylanmadan önce başka düzeltme talep edildi';
$string['overview:button:appraiser:5:submit'] = 'Onay için {$a->plainsignoffname} a gönderildi';

$string['overview:button:signoff:6:submit'] = 'Onaylayarak Çıkış';

$string['overview:button:returnit'] = 'Geri dön';
$string['overview:button:submitit'] = 'Gönder';

// END OVERVIEW CONTENT

// START TR STRING TRANSLATIONS - SPREADSHEET

$string['startappraisal'] = 'Çevrimiçi Değerlendirmeyi Başlat';
$string['continueappraisal'] = 'Çevrimiçi Değerlendirmeye Devam Et';
$string['appraisee_feedback_edit_text'] = 'Düzenle';
$string['appraisee_feedback_resend_text'] = 'Tekrar Gönder';
$string['appraisee_feedback_view_text'] = 'Görüntüle';
$string['feedback_setface2face'] = 'Geri bildirim taleplerini eklemeden önce yüz yüze değerlendirme görüşmesi için bir tarih belirlemeniz şarttır. Bunu Değerlendirilen Kişi Bilgileri sayfasında bulabilirsiniz.';
$string['feedback_comments_none'] = 'İlave yorum yapılmamıştır.';
$string['actionrequired'] = 'Yapılması gereken eylem';
$string['actions'] = 'Eylemler';
$string['appraisals:archived'] = 'Arşivlenmiş Değerlendirmeler';
$string['appraisals:current'] = 'Güncel Değerlendirmeler';
$string['appraisals:noarchived'] = 'Arşivlenmiş değerlendirmeniz yok.';
$string['appraisals:nocurrent'] = 'Güncel değerlendirmeniz yok.';
$string['comment:adddots'] = 'Bir yorum ekle...';
$string['comment:addingdots'] = 'Ekleniyor...';
$string['comment:addnewdots'] = 'Yeni bir yorum ekle...';
$string['comment:showmore'] = '<i class="fa fa-plus-circle"></i>  Daha fazla göster';
$string['comment:status:0_to_1'] = '{$a->status} - Değerlendirme oluşturuldu, fakat henüz başlatılmadı.';
$string['comment:status:1_to_2'] = '{$a->status} - Değerlendirilen kişi değerlendirmeyi başlattı.';
$string['comment:status:2_to_3'] = '{$a->status} - Değerlendirme değerlendiricinin incelemesine sunuldu.';
$string['comment:status:3_to_2'] = '{$a->status} - Değerlendirme değerlendirilen kişiye geri gönderildi.';
$string['comment:status:3_to_4'] = '{$a->status} - Değerlendirme değerlendirilen kişinin yorumlarını bekliyor.';
$string['comment:status:4_to_3'] = '{$a->status} - Değerlendirme değerlendiriciye geri gönderildi.';
$string['comment:status:4_to_5'] = '{$a->status} - Değerlendirici tarafından onaylayan kişinin onayına gönderilmeyi bekliyor.';
$string['comment:status:5_to_4'] = '{$a->status} - Değerlendirme değerlendirilen kişiye geri gönderildi.';
$string['comment:status:5_to_6'] = '{$a->status} - Onaylayan kişinin nihai onayına gönderildi.';
$string['comment:status:6_to_7'] = '{$a->status} - Değerlendirme tamamlandı.';
$string['comment:updated:appraiser'] = '{$a->ba} değerlendirici {$a->oldappraiser} durumunu {$a->newappraiser} olarak değiştirdi.';
$string['comment:updated:signoff'] = 'Onaylayan kişi {$a->oldsignoff} {$a->ba} tarafından {$a->newsignoff} olarak değiştirildi.';
$string['index:togglef2f:complete'] = 'Yüz yüze görüşme düzenlendi olarak işaretle';
$string['index:togglef2f:notcomplete'] = 'Yüz yüze görüşme düzenlenmedi olarak işaretle';
$string['index:notstarted'] = 'Başlatılmadı';
$string['index:notstarted:tooltip'] = 'Değerlendirilen kişi kendi değerlendirmesini henüz başlatmadı. Başlattığında giriş yapabileceksiniz.';
$string['index:printappraisal'] = 'Değerlendirmeyi İndir';
$string['index:printfeedback'] = 'Geri Bildirimi İndir';
$string['index:start'] = 'Değerlendirmeyi Başlat';
$string['index:toptext:appraisee'] = 'Bu pano güncel ve arşivlenmiş değerlendirmelerinizi gösterir. Eylemler menüsünün altındaki bağlantıyı kullanarak güncel değerlendirmenize erişebilirsiniz. Alttaki Değerlendirmeyi İndir butonunu kullanarak arşivlenmiş değerlendirmeleri indirebilirsiniz.';
$string['index:toptext:appraiser'] = 'Bu pano, değerlendiricisi olduğunuz güncel veya arşivlenmiş değerlendirmeleri gösterir. Eylemler menüsünün altındaki bağlantıyı kullanarak güncel değerlendirmelere erişebilirsiniz. Geri bildirim indirmeleri, yüz yüze görüşme sonrasına kadar değerlendirilen kişiye bildirilmeyecek olan geri bildirimleri içerir. Gizli geri bildirimler her aşamada gizli tutulacaktır. Alttaki Değerlendirmeyi İndir butonunu kullanarak arşivlenmiş değerlendirmeleri indirebilirsiniz.';
$string['index:toptext:groupleader'] = 'Bu pano, maliyet merkezlerinizdeki güncel ve arşivlenmiş değerlendirmeleri gösterir. Eylemler menüsünün altındaki bağlantıları kullanarak güncel değerlendirmelere erişebilirsiniz. Alttaki Değerlendirmeyi İndir butonunu kullanarak arşivlenmiş değerlendirmeleri indirebilirsiniz.';
$string['index:toptext:signoff'] = 'Bu pano, onaylayıcısı olduğunuz güncel ve arşivlenmiş değerlendirmeleri gösterir. Eylemler menüsünün altındaki bağlantıyı kullanarak güncel değerlendirmelere erişebilirsiniz. Alttaki Değerlendirmeyi İndir butonunu kullanarak arşivlenmiş değerlendirmeleri indirebilirsiniz.';
$string['index:view'] = 'Değerlendirmeyi Görüntüle';
$string['timediff:now'] = 'Şimdi';
$string['timediff:second'] = '{$a} saniye';
$string['timediff:seconds'] = '{$a} saniyeler';
$string['timediff:minute'] = '{$a} dakika';
$string['timediff:minutes'] = '{$a} dakikalar';
$string['timediff:hour'] = '{$a} saat';
$string['timediff:hours'] = '{$a} saatler';
$string['timediff:day'] = '{$a} gün';
$string['timediff:days'] = '{$a} günler';
$string['timediff:month'] = '{$a} ay';
$string['timediff:months'] = '{$a} aylar';
$string['timediff:year'] = '{$a} yıl';
$string['timediff:years'] = '{$a} yıllar';
$string['error:togglef2f:complete'] = 'Yüz yüze görüşme düzenlendi olarak işaretlenemiyor';
$string['error:togglef2f:notcomplete'] = 'Yüz yüze görüşme düzenlenmedi olarak işaretlenemiyor';
$string['appraisee_feedback_email_success'] = 'E-posta başarıyla gönderildi';
$string['appraisee_feedback_email_error'] = 'E-posta gönderilemedi';
$string['appraisee_feedback_invalid_edit_error'] = 'Verilen e-posta adresi geçersiz';
$string['appraisee_feedback_inuse_edit_error'] = 'E-posta adresi zaten kullanılıyor';
$string['appraisee_feedback_inuse_email_error'] = 'E-posta adresi zaten kullanılıyor';
$string['appraisee_feedback_resend_success'] = 'E-postanın tekrar gönderimi başarılı';
$string['appraisee_feedback_resend_error'] = 'E-posta tekrar gönderilirken hata oluştu';
$string['form:add'] = 'Ekle';
$string['form:language'] = 'Dil';
$string['form:addfeedback:alert:cancelled'] = 'Gönderim iptal edildi, değerlendirme geri bildiriminiz gönderilmedi.';
$string['form:addfeedback:alert:error'] = 'Üzgünüz, değerlendirme geri bildiriminizin gönderiminde hata oluştu.';
$string['form:addfeedback:alert:saved'] = 'Teşekkür ederiz, değerlendirme geri bildiriminiz başarıyla gönderildi.';
$string['form:feedback:alert:cancelled'] = 'Gönderim iptal edildi, değerlendirme geri bildirim talebiniz gönderilmedi.';
$string['form:feedback:alert:error'] = 'Üzgünüz, değerlendirme geri bildirim talebinizin gönderiminde hata oluştu.';
$string['form:feedback:alert:saved'] = 'Değerlendirme geri bildirim talebiniz başarıyla gönderildi.';
$string['form:lastyear:nolastyear'] = 'Not: Önceki değerlendirmenizin sistemde kayıtlı olmadığını farkettik. Lütfen son yapılmış değerlendirmenizi pdf ya da word formatında yükleyin.';
$string['form:lastyear:file'] = '<strong>Değerlendirilen kişi tarafından bir gözden geçirme dosyası yüklendi: <a href="{$a->path}" target="blank">{$a->filename}</a></strong>';
$string['form:lastyear:cardinfo:developmentlink'] = 'Geçen Yıla Ait Gelişme';
$string['feedbackrequests:description'] = 'Bu pano, yapmış olduğunuz açık geri bildirim taleplerini gösterir ve geçmişte yaptığınız herhangi bir geri bildirime erişmenize olanak sağlar.';
$string['feedbackrequests:outstanding'] = 'Açık Talepler';
$string['feedbackrequests:norequests'] = 'Açık geri bildirim talepleri yok';
$string['feedbackrequests:completed'] = 'Kapanmış Talepler';
$string['feedbackrequests:nocompleted'] = 'Kapanmış geri bildirim talepleri yok';
$string['feedbackrequests:th:actions'] = 'Eylemler';
$string['feedbackrequests:emailcopy'] = 'Bir kopyasını bana gönder';
$string['feedbackrequests:submitfeedback'] = 'Geri bildirimi gönder';
/*
$string['email:subject:myfeedback'] = '{{appraisee}} ile ilgili değerlendirme geri bildiriminiz';
$string['email:body:myfeedback'] = 'Sayın {{recipient}},
{{appraisee}} ile ilgili göndermiş olduğunuz {{confidential}} geri bildirim şöyledir: {{feedback}} {{feedback_2}}';
*/
$string['feedbackrequests:confidential'] = 'gizli';
$string['feedbackrequests:nonconfidential'] = 'gizli değil';
$string['success:checkin:add'] = 'Giriş kaydı başarıyla eklendi';
$string['error:checkin:add'] = 'Giriş kaydı eklemesi başarısız';
$string['error:checkin:validation'] = 'Bir şeyler yazın.';
$string['checkin:deleted'] = 'Giriş kaydı silindi';
$string['checkin:delete:failed'] = 'Giriş kaydı silinmesi başarısız';
$string['checkin:update'] = 'Güncelle';
$string['checkin:addnewdots'] = 'Giriş kaydı...';

//General alerts
$string['alert:language:notdefault'] = '<strong>Uyarı</strong>: Değerlendirmeniz için öntanımlanmış dil seçeneğini kullanmıyorsunuz. Lütfen soruları size en uygun dilde yanıtladığınızdan emin olun.';

// Userinfo.
$string['form:userinfo:intro'] = 'Lütfen aşağıdaki bilgileri tamamlayın. Bazı alanlar Taps bilgileriniz kullanılarak önceden doldurulmuştur. Eğer önceden doldurulmuş alanlar hatalıysa lüften İK sorumlunuzla görüşün.';
$string['form:userinfo:name'] = 'Değerlendirilen kişinin adı';
$string['form:userinfo:staffid'] = 'Staff ID / Sicil No';
$string['form:userinfo:grade'] = 'Grade';
$string['form:userinfo:jobtitle'] = 'İş Ünvanı';
$string['form:userinfo:operationaljobtitle'] = 'Operasyonel Unvan';
$string['form:userinfo:facetoface'] = 'Önerilmiş yüz yüze değerlendirme görüşmesi tarihi';
$string['form:userinfo:facetofaceheld'] = 'Yüz yüze görüşme düzenlendi';

//Feedback
$string['feedbackrequests:received:confidential'] = 'Alındı (gizli)';
$string['feedbackrequests:received:nonconfidential'] = 'Alındı';
$string['feedbackrequests:paneltitle:confidential'] = 'Geri bildirim (gizli)';
$string['feedbackrequests:paneltitle:nonconfidential'] = 'Geri bildirim';
$string['feedbackrequests:legend'] = '* Değerlendirici tarafından eklenen geri bildirim sağlayacak kişiyi gösterir';
$string['form:feedback:email'] = 'E-posta adresi';
$string['form:feedback:firstname'] = 'Ad';
$string['form:feedback:lastname'] = 'Soyad';
$string['form:feedback:language'] = 'Geri bildirim e-postasının dilini seçin';
$string['form:addfeedback:sendemailbtn'] = 'Değerlendirme geri bildirimini gönder';
$string['form:addfeedback:closed'] = 'Geri bildirim göndereceğiniz pencere şu an kapalı';
$string['form:addfeedback:submitted'] = 'Geri bildirim gönderildi';
$string['form:feedback:alert:cancelled'] = 'Gönderim iptal edildi, değerlendirme geri bildirim talebiniz henüz gönderilmedi.';
$string['form:feedback:alert:error'] = 'Üzgünüz, değerlendirme geri bildirim talebiniz gönderilirken bir hata oluştu.';
$string['form:feedback:alert:saved'] = 'Değerlendirme geri bildirim talebiniz başarıyla gönderildi.';

//pdf
$string['pdf:form:summaries:appraisee'] = 'Değerlendirilen kişinin yorumları';
$string['pdf:form:summaries:appraiser'] = 'Değerlendiricinin genel performans özeti';
$string['pdf:form:summaries:signoff'] = 'Onaylama özeti';
$string['pdf:form:summaries:recommendations'] = 'Kararlaştırılan eylemler';

// END TR STRING TRANSLATIONS - SPREADSHEET

// 2017 : Updates and additions.
$string['addreceivedfeedback'] = 'Add Received Feedback';
$string['appraisee_feedback_savedraft_error'] = 'Taslak Kaydedilirken Hata Oluştu';
$string['appraisee_feedback_savedraft_success'] = 'Geri Bildirim Taslağı Kaydedildi';
$string['appraisee_feedback_viewrequest_text'] = 'Talep e-postasını görüntüle';
$string['appraisee_welcome'] = 'Performans Değerlendirmesi; Değerlendiricinizle birlikte, performansınız, kariyer gelişminiz ve işinize gelecekte yapabileceğiniz katkınıza dair konuşabileceğiniz değerli bir görüşme fırsatıdır. Bu görüşmenin, kişisel ve her iki taraf için de yararlı, yapıcı bir diyalog şeklinde olması dileğimizdir.<br /><br />
Bu çevrimiçi araç, yaptığınız görüşmeleri kaydetmenizi ve yıl boyu formunuza erişmenizi sağlar. <br /><br />Değerlendirme süreciyle ilgili daha detaylı bilgi <a href="https://moodle.arup.com/appraisal/essentials" target="_blank">burada bulunabilir.</a>';
$string['appraisee_welcome_info'] = 'Bu yılki değerlendirmenizi tamamlamanız için en son tarih {$a}.';
$string['email:body:appraiseefeedback'] = '{{emailmsg}}
<br>
<hr>
<p>Kliknij {{link}} aby dodać opinię.</p>
<p>Appraisal Name {{appraisee_fullname}}<br>My appraisal is on <span class="placeholder">{{held_date}}</span></p>
<p>This is an auto generated email sent by {{appraisee_fullname}} to {{firstname}} {{lastname}}.</p>
<p>If the link above does not work, please copy the following link into your browser to access the appraisal:<br />{{linkurl}}</p>';
$string['email:body:appraiseefeedbackmsg'] = 'Değerli <span class="placeholder bind_firstname">{{firstname}}</span>,</p>
<p> Bu yılki performans değerlendirmesi görüşmem <span class="placeholder">{{held_date}}</span>. Değerlendirme görüşmemi<span class="placeholder">{{appraiser_fullname}} ile yapacağım</span>. Geçtiğimiz yıl boyunca sizinle birlikte çalıştığımız için, sizden bu yıl değer yaratıp, katkı sağladığım alanlar ve daha etkin olduğum ve olabileceğim, gelişime açık alanlar hakkındaki geri bildirimlerinizi rica ediyorum. Eğer geri bildirim vererek kişisel ve profesyonel gelişimime katkı sağlamak istiyorsanız aşağıdaki linke tıklamanızı rica ederim.</p> <p>
Yüzyüze görüşmem öncesinde geri bildiriminizi gönderebilirseniz çok memnun olurum.</p>
<p class="ignoreoncopy">Aşağıda \'a ait ek yorumları bulabilirsiniz. <span class="placeholder">{{appraisee_fullname}}</span>:<br /> <span>{{emailtext}}</span></p>
<p>Saygılarımla,<br />
<span class="placeholder">{{appraisee_fullname}}</span></p>';
$string['email:body:appraiserfeedback'] = '{{emailmsg}}
<br>
<hr>
<p>Lütfen tıklayıp {{link}} geri bildiriminizi veriniz.</p>
<p>Değerlendirilenin Adı Soyadı {{appraisee_fullname}}<br>
Değerlendirme tarihi <span class="placeholder">{{held_date}}</span></p>
<p>Bu e-mail {{appraiseer_fullname}} tarafından otomatik olarak {{firstname}} {{lastname}}\'a gönderilmiştir.</p>
<p>Eğer yukarıdaki link çalışmıyorsa lütfen aşağıdaki linki tarayıcınızın adres çubuğuna kopyalayıp deneyiniz.<br />{{linkurl}}</p>';
$string['email:body:appraiserfeedbackmsg'] = '<p>Değerli <span class="placeholder bind_firstname">{{firstname}}</span>,</p>
<p>için performans değerlendirme görüşmemiz <span class="placeholder">{{appraisee_fullname}}</span> tarihinde gerçekleşecektir <span class="placeholder">{{held_date}}</span>.  Kendisi geçtiğimiz yıl boyunca sizinle birlikte çalıştığı için, sizden bu yıl değer yaratıp, katkı sağladığı alanlar ve daha etkin olmuş olabileceği, gelişime açık alanları hakkındaki geri bildirimlerinizi rica ediyorum. Eğer geri bildirim vermek istiyorsanız aşağıdaki linke tıklamanızı rica ederim.</p> <p>Yüzyüze görüşme öncesinde geri bildiriminizi gönderebilirseniz çok memnun olurum.</p>
<p class="ignoreoncopy">Aşağıda\'a ait ek yorumları bulabilirsiniz <span class="placeholder">{{appraiser_fullname}}</span>:<br /> <span>{{emailtext}}</span></p>
<p>Saygılarımla,<br /> <span class="placeholder">{{appraiser_fullname}}</span></p>';
$string['email:body:myfeedback'] = '<p>Dear {{recipient}},<p><p>{{appraisee}}\'nin değerlendirmesi için {{confidential}} geribildiriminizi ilettiniz.</p> <div>{{feedback}}</div> <div>{{feedback_2}}</div>';
$string['email:subject:myfeedback'] = '{{appraisee}} \'nin değerlendirmesi için geri bildiriminiz';
$string['error:noappraisal'] = 'Hata - Sistemde bir değerlendirmeniz bulunmamaktadır. Lütfen aşağıda isimleri bulunan Performans Değerlendirme süreci yöneticilerinin birinden yardım talep ediniz:
{$a}';
$string['feedback_intro'] = 'Değerlendirme sürecinizde size geri bildirim vermesi için lütfen 3 yada daha fazla çalışma arkadaşı seçiniz. Geri bildirimler şirket içinde ya da dışındaki kişilerden alınabilir. Spesifik geribildirim alabilmek için ofisinizin hangi bölgeye bağlı olduğu bilgisini veriniz.<br/><br/> Şirket içindeki kişilerden geribildirim alırken, "360 derece" perspektifini dikkate almalısınız;  yani çalışma arkadaşlarınız, sizden deneyimli ve sizden daha az deneyimli kişiler olabilir. Bu kriterlere uyan birkaç kişi seçmelisiniz.<br/><br/><div data-visible-regions="UKMEA, EUROPE, AUSTRALASIA"> Geri bildirim alacağınız kişilerden birisi de sizi çok iyi tanıyan şirket dışından bir iş ortağınız ya da işvereniniz olabilir.</div><div data-visible-regions="East Asia"><br /><div class="alert alert-warning">For East Asia region, we expect feedback to be from internal source only. Comments from external client or collaborator should be understood and fed back through internal people.</div></div> <div data-visible-regions="Americas"><br /><div class="alert alert-warning">For the Americas Region, comments from external clients or collaborators should be fed back through conversations gathered outside of this feedback tool.</div></div>
<br /><div class="alert alert-danger"> Not: Seçtiğiniz kişilerin geri bildirimleri ulaştığında buradan görebileceksiniz fakat Değerlendirici\'nin talep ettiği geri bildirimleri göremeyeceksiniz. Bu durumda geri bildirimi ancak Değerlendirici size değerlendirmenizi son yorumlarınız için gönderdiği zaman görebileceksiniz (3ncü Aşama). </div>';
$string['feedbackrequests:paneltitle:requestmail'] = 'Geri bildirim talep e-postası';
$string['form:addfeedback:addfeedback'] = 'Lütfen son 12 ay içerisinde Değerlendirilen\'e katkı sağladığınız 3 alanı özetleyin.';
$string['form:addfeedback:addfeedback_2'] = 'Lütfen Değerlendirilen\'in daha da etkin olabileceği 3 alanı paylaşın. Dürüst olun, yapıcı eleştiriler yapın çünkü bu geri bildirimler çalışma arkadaşınızın gelişimi için önemli olacaktır.';
$string['form:addfeedback:addfeedback_2help'] = '<div class="well well-sm">Tüm çalışanlar için değerli, dengeli, pozitif ve kritik tavsiyeler içeren geri bildirim almak önemlidir. <br>Daha detaylı yardım için lütfen tıklayın <a href="https://moodle.arup.com/scorm/_assets/ArupAppraisalGuidanceFeedback.pdf" target="_blank">buraya</a></div>';
$string['form:addfeedback:addfeedback_help'] = 'Eğer aldığın geri bildirimi "değerli" ve "etkili" olarak değerlendirmiyorsan, geri bildirimi "değerli katkı" kutusuna kopyala yapıştır yapman yeterli olacaktır.';
$string['form:addfeedback:addfeedbackhelp'] = '<div class="well well-sm">Tüm çalışanlar için değerli, dengeli, pozitif ve kritik tavsiyeler içeren geri bildirim almak önemlidir. <br>Daha detaylı yardım için lütfen tıklayın <a href="https://moodle.arup.com/scorm/_assets/ArupAppraisalGuidanceFeedback.pdf" target="_blank">buraya</a></div>';
$string['form:addfeedback:firstname'] = 'Geri Bildirim veren kişinin Adı';
$string['form:addfeedback:lastname'] = 'Geri Bildirim Veren Kişinin Soyadı';
$string['form:addfeedback:saveddraft'] = 'Geri Bildiriminin taslak halini kaydettin. Göndermediğin sürece, geri bildirimin değerlendirici yada değerlendirilen tarafından görülemeyecektir.';
$string['form:addfeedback:savedraftbtn'] = 'Taslak Olarak Kaydet';
$string['form:addfeedback:savedraftbtntooltip'] = 'Taslağı daha sonra tamamlamak üzere kaydet. Bu aksiyon sonucu geri bildirimin değerlendirici yada değerlendirilen ile paylaşılmaz.';
$string['form:addfeedback:savefeedback'] = 'Geri Bildirimi Kaydet';
$string['form:development:comments'] = 'Değerlendirici Yorumları';
$string['form:development:commentshelp'] = '<div class="well well-sm"><em>Değerlendirici tarafından doldurulmalı</em></div>';
$string['form:feedback:editemail'] = 'Düzenle';
$string['form:feedback:providefirstnamelastname'] = 'Lütfen düzenle tuşuna basmadan önce alıcının adını ve soyadını girin.';
$string['form:lastyear:cardinfo:performancelink'] = 'Geçtiğimiz yılın katkı planı';
$string['form:lastyear:printappraisal'] = '<a href="{$a}" target="_blank">Geçtiğimiz yılın değerlendirmesi</a> görüntülenebilir
(PDF - opens in new window)';
$string['form:summaries:grpleaderhelp'] = '<div class="well well-sm"><em>Son Yönetici tarafından onaylanarak tamamlanacaktır.</em></div>';
$string['leadersignoff'] = 'Son Yönetici Onayı';
$string['modal:printconfirm:cancel'] = 'Hayır';
$string['modal:printconfirm:content'] = 'Bu Belgeyi Yazdırmaya Gerçekten İhtiyacın Var mı?';
$string['modal:printconfirm:continue'] = 'Evet, Devam Et';
$string['modal:printconfirm:title'] = 'Yazdırmadan Önce Bir Daha Düşün';
$string['overview:content:appraisee:3'] = 'Değerlendirme taslağınızı gözden geçirebilmesi için {appraiser name}\'a ilettiniz.<br /><br />
<strong>Bir sonraki adım:</strong> <ul class="m-b-20"> <li>Yüzyüze görüşmenizi gerçekleştirin - yüzyüze görüşme öncesi aşağıdakilere göz atmanız faydalı olacaktır:</li> <ul class="m-b-0"> <li><a class="oa-print-confirm" href="{$a->printappraisalurl}">Değerlendirmeyi İndir</a></li> <li><a href="https://moodle.arup.com/appraisal/reference" target="_blank">Hızlı Referans Kılavuzunu İndir</a></li> </ul> <li> Görüşme sonrasında, Değerlendirici değerlendirmenizi size geri gönderecektir. Bu aşamada, yüz yüze görüşmenizde uzlaştığınız değişiklikleri yapabilir yada son yorumlarınızı yazabilirsiniz.</li> </ul> <div class="alert alert-danger" role="alert"><strong>Not:</strong>  Değerlendirmenizi Değerlendirici\'ye gönderdikten sonra da üzerinde ekleme ya da düzeltme yapabilirsiniz. Bu durumda yaptığınız değişiklikleri faaliyet kaydı / activiti log bölümünde vurgulamanı öneririz.</div>';
$string['overview:content:appraiser:3'] = '{$a->styledappraiseename} değerlendirme taslağını yüz yüze görüşmenize hazırlık olması için size iletti.<br /><br /> <strong>Bir sonraki adım:</strong> <ul class="m-b-20"> <li>Yüz yüze görüşmenize hazırlık olması için değerlendirme taslağını inceleyin. Eğer detaylı bilgiye ihtiyacınız varsa Değerlendirilen\'e geri gönderebilirsiniz.</li> <li>Görüşme öncesi yapabilecekleriniz:</li><ul class="m-b-0"> <li><a class="oa-print-confirm" href="{$a->printappraisalurl}"> Değerlendirmeyi İndir</a></li> <li><a class="oa-print-confirm" href="{$a->printfeedbackurl}">Gelen Geri Bildirimleri İndir</a></li> <li><a href="https://moodle.arup.com/appraisal/reference" target="_blank"> Hızlı Referans Kılavuzunu İndir</a></li> </ul> <li>Görüşme sonrasında lütfen</li> <ul class="m-b-0"> <li>Değerlendirilen Bilgisi kısmından yüz yüze görüşmenin gerçekleştiğini işaretleyin</li> <li>Her bölüme yorumlarınızı yazın</li> <li>Özet kısmına, uzlaşılan aksiyonları ve özetinizi yazın</li> (Eğer gerekirse, yorumlarınızı eklemeden önce değerlendirmeyi değerlendirilen\'e gönderip düzenlemesini sağlayabilirsiniz.) </ul> <li>Yorumlarınızı gözden geçirmesi için değerlendirmeyi değerlendirilen\'e gönderin, geri bildirimleri görüntüleyin ve değerlendirilenin son yorumlarını eklemesini sağlayın</li> </ul>';
$string['overview:content:special:archived'] = '<div class="alert alert-danger" role="alert">Bu değerlendirme arşivlenmiştir. <br /> <a class="oa-print-confirm" href="{$a->printappraisalurl}"> Değerlendirmenizi sadece indirebilirsiniz </a>.</div>';
$string['overview:content:special:archived:appraisee'] = '<div class="alert alert-danger" role="alert">Bu değerlendirme arşivlenmiştir. <br /> <a class="oa-print-confirm" href="{$a->printappraisalurl}"> Değerlendirmenizi sadece indirebilirsiniz </a>.</div>';
$string['overview:lastsaved'] = 'En son zaman kaydedildi: {$a}';
$string['overview:lastsaved:never'] = 'Asla';
$string['pdf:feedback:confidentialhelp:appraisee'] = '# Senin görün, gizli verilen geri bildirimleri gösterir.';
$string['pdf:feedback:notyetavailable'] = 'Henüz görüntülenemez.';
$string['pdf:feedback:requestedfrom'] = 'Gözden Geçiren {$a->firstname} {$a->lastname}{$a->appraiserflag}{$a->confidentialflag}:';
$string['pdf:feedback:requestedhelp'] = '* Değerlendirici tarafından talep edilen, henüz görüntüleyemeyeceğin geribildirimleri gösterir.';
$string['pdf:header:warning'] = 'Tarafından İndirildi: {$a->who} on {$a->when}<br>
Lütfen dosyayı bilgisayarınıza kaydetmeyin veya çıktısını güvenli olmayan bir yerde bırakmayın.';
$string['status:7:leadersignoff'] = 'Son Yönetici Onayı';
