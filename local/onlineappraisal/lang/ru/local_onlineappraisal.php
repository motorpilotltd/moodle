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

$string['overview:content:appraisee:2'] = 'Начните заполнять форму для аттестации.<br /><br />
<strong>Следующие действия:</strong>
<ul class="m-b-20">
    <li>Укажите дату намеченной личной встречи</li>
    <li>Запросите отзыв</li>
    <li>Изложите прошлогодние показатели деятельности и развития и прокомментируйте их</li>
    <li>Заполните разделы «Направление развития карьеры», «План влияния» и «План развития» для того, чтобы обсудить их во время Вашей личной встречи</li>
    <li>Поделитесь Вашим проектом для аттестации с {$a->styledappraisername} - Вашим аттестующим.</li>
</ul>
Поделитесь Вашим проектом для аттестации с Вашим аттестующим не менее чем за <strong><u>неделю</u></strong> до личной встречи. Вы сможете вносить изменения в проект после того, как поделитесь им.<br /><br />
<div class="alert alert-danger" role="alert"><strong>Примечание:</strong> Ваш аттестующий не сможет увидеть Ваш проект аттестации, пока Вы не поделитесь с ним.</div>';
$string['overview:button:appraisee:2:extra'] = 'Начать заполнение формы для аттестации';
$string['overview:button:appraisee:2:submit'] = 'Поделиться с {$a->plainappraisername}';
$string['overview:content:appraisee:2:3'] = 'Ваш аттестующий потребовал изменить проект формы для аттестации.<br /><br />
<strong>Следующие действия:</strong>
<ul class="m-b-20">
    <li>Внесите изменения, которые требует Ваш аттестующий (изучите журнал операций для получения дальнейшей информации о требованиях).</li>
    <li>Поделитесь Вашим проектом для аттестации с {$a->styledappraisername} - Вашим аттестующим.</li>
</ul>';
$string['overview:content:appraisee:3:4'] = 'Вы вернули Вашу форму для аттестации {$a->styledappraisername} для внесения изменений.<br /><br /> Вы получите уведомление, когда пользователь внесет поправки, и форма снова будет готова для просмотра.<br /><br /> <div class="alert alert-danger" role="alert"><strong>Примечание:</strong> Вы можете продолжать редактировать форму для аттестации, если изменения предложены Вашим аттестующим, но Вам рекомендуется использовать журнал операций для того, чтобы отмечать любые внесенные Вами изменения. </div>';
$string['overview:content:appraisee:4'] = 'Форма для аттестации возвращена Вам с комментариями, добавленными {$a->styledappraisername}.<br /><br />
<strong>Следующие действия:</strong>
<ul class="m-b-20">
    <li>Изучите комментарии и краткий отчет аттестующего. При необходимости, если Вам необходимо внести изменения, верните форму для аттестации аттестующему.</li>
    <li>Оставьте свои комментарии в разделе «Краткие отчеты»</li> <li>Отправьте форму Вашему аттестующему для окончательного просмотра перед утверждением.  Отправленная форма редактированию не подлежит.</li>
</ul>
<div class="alert alert-danger" role="alert"><strong>Примечание:</strong> Вы можете продолжать редактировать Ваши разделы формы для аттестации, но Вам рекомендуется использовать журнал операций для того, чтобы отмечать любые внесенные Вами изменения.</div>';
$string['overview:button:appraisee:4:return'] = 'Вернуть {$a->plainappraisername} для внесения изменений';
$string['overview:button:appraisee:4:submit'] = 'Отправить окончательный вариант формы {$a->plainappraisername}';
$string['overview:content:appraisee:5'] = 'Окончательный вариант формы для аттестации отправлен Вами для окончательного просмотра {$a->styledappraisername}.<br /><br /> <strong>Следующие действия:</strong> <ul class="m-b-20"> <li>Ваш аттестующий отправит форму для аттестации  {$a->styledsignoffname} для утверждения.</li> </ul> <div class="alert alert-danger" role="alert"><strong>Примечание:</strong> Вы больше не можете вносить изменения в форму для аттестации, если только она не будет возвращена Вам аттестующим для редактирования.</div>';
$string['overview:content:appraisee:6'] = 'Ваша форма для аттестации отправлена {$a->styledsignoffname} для просмотра и внесения краткого отчета.<br /><br />
<div class="alert alert-danger" role="alert"><strong>Примечание:</strong> Форма для аттестации заблокирована и недоступна для внесения изменений</div>';

$string['overview:content:appraisee:7'] = 'Ваша форма для аттестации готова. Вы можете скачать ее в pdf-формате в любое время, нажав «Скачать форму для аттестации».';
$string['overview:content:appraiser:2'] = 'Форма для аттестации в настоящий момент редактируется {$a->styledappraiseename}. Вы будете уведомлены, когда форма будет готова для просмотра.<br /><br />
<div class="alert alert-danger" role="alert"><strong>Примечание:</strong> Вы получите уведомление, когда форма для аттестации будет готова для просмотра и утверждения.</div>';
$string['overview:content:appraiser:2:3'] = 'Вы вернули форму для аттестации {$a->styledappraiseename} для внесения изменений. Вы получите уведомление, когда пользователь внесет поправки и проект снова будет готов для просмотра.<br /><br />
<div class="alert alert-danger" role="alert"><strong>Примечание:</strong> Вы по-прежнему можете вносить изменения в Ваши разделы.</div>';
$string['overview:button:appraiser:3:return'] = 'Запросить дальнейшую информацию у {$a->plainappraiseename}';
$string['overview:button:appraiser:3:submit'] = 'Отправить {$a->plainappraiseename} для заключительных комментариев';
$string['overview:content:appraiser:3:4'] = '{$a->styledappraiseename} запросил о внесении изменений в форму для аттестации.<br /><br />
<strong>Следующие действия:</strong>
<ul class="m-b-20">
    <li>Внесите изменения, требуемые аттестуемым (изучите журнал операций для получения дальнейшей информации о требованиях)</li>
    <li>Поделитесь формой для аттестации с {$a->styledappraiseename} для получения заключительных комментариев</li>
</ul>';
$string['overview:content:appraiser:4'] = 'Форма для аттестации возвращена {$a->styledappraiseename} с добавленными Вами комментариями и кратким отчетом для внесения им своих заключительных комментариев. Вы получите уведомление, когда форма для аттестации будет готова для окончательного просмотра.<br /><br />
<div class="alert alert-danger" role="alert"><strong>Примечание:</strong> Вы можете продолжать редактировать Ваши разделы формы для аттестации, но Вам рекомендуется использовать журнал операций для того, чтобы отмечать любые внесенные Вами изменения.</div>';

$string['overview:content:appraiser:5'] = '{$a->styledappraiseename} добавил свои заключительные комментарии<br /><br />
<strong>Следующие действия:</strong>
<ul class="m-b-20">
    <li>Просмотрите окончательный вариант формы для аттестации, готовой для утверждения.</li>
    <li>Отправьте форму [имя утверждающего] для просмотра и добавления им краткого отчета.</li>
    <li>Вы и аттестуемый получите уведомление, когда форма для аттестации будет готова.</li>
</ul>
<div class="alert alert-danger" role="alert"><strong>Примечание:</strong> Вы больше не можете вносить изменения в форму для аттестации, если только Вы не возвращаете ее аттестуемому.</div>';
$string['overview:button:appraiser:5:return'] = 'Перед утверждением требуется дальнейшее редактирование';
$string['overview:button:appraiser:5:submit'] = 'Отправить {$a->plainsignoffname} для утверждения';
$string['overview:content:appraiser:6'] = 'Форма для аттестации отправлена Вами {$a->styledsignoffname} for completion.<br /><br />
<div class="alert alert-danger" role="alert"><strong>Примечание:</strong> Форма для аттестации заблокирована и недоступна для внесения изменений</div>';
$string['overview:content:appraiser:7'] = 'Форма для аттестации готова и утверждена.';
$string['overview:content:signoff:2'] = 'Форма для аттестации находится в стадии разработки.<br /><br /><div class="alert alert-danger" role="alert"><strong>Примечание:</strong> Вы получите уведомление, когда форма для аттестации будет готова для просмотра и утверждения.</div>';
$string['overview:content:signoff:3'] = 'Форма для аттестации находится в стадии разработки.<br /><br /><div class="alert alert-danger" role="alert"><strong>Примечание:</strong> Вы получите уведомление, когда форма для аттестации будет готова для просмотра и утверждения.</div>';
$string['overview:content:signoff:4'] = 'Форма для аттестации находится в стадии разработки.<br /><br /><div class="alert alert-danger" role="alert"><strong>Примечание:</strong> Вы получите уведомление, когда форма для аттестации будет готова для просмотра и утверждения.</div>';
$string['overview:content:signoff:5'] = 'Форма для аттестации находится в стадии разработки.<br /><br /><div class="alert alert-danger" role="alert"><strong>Примечание:</strong> Вы получите уведомление, когда форма для аттестации будет готова для просмотра и утверждения.</div>';
$string['overview:content:signoff:6'] = 'Вам отправлена форма для аттестации {$a->styledappraiseename} для просмотра.<br /><br />
<strong>Следующие действия:</strong>
<ul class="m-b-20">
    <li>Просмотрите форму для аттестации</li>
    <li>Напишите краткий отчет в разделе «Краткие отчеты»</li>
    <li>Нажмите кнопку «Утвердить» для того, чтобы завершить работу над формой для аттестации</li>
</ul>';
$string['overview:button:signoff:6:submit'] = 'Утвердить';
$string['overview:content:signoff:7'] = 'Данная форма для аттестации готова и утверждена.';
$string['overview:content:groupleader:2'] = 'Форма для аттестации находится в стадии разработки.';
$string['overview:content:groupleader:3'] = 'Форма для аттестации находится в стадии разработки.';
$string['overview:content:groupleader:4'] = 'Форма для аттестации находится в стадии разработки.';
$string['overview:content:groupleader:5'] = 'Форма для аттестации находится в стадии разработки.';
$string['overview:content:groupleader:6'] = 'Форма для аттестации находится в стадии разработки.';
$string['overview:content:groupleader:7'] = 'Данная форма для аттестации готова и утверждена.';

// Excel Section
// Pages
$string['startappraisal'] = 'Начать онлайн аттестацию';
$string['continueappraisal'] = 'Продолжить онлайн аттестацию';

// Request Feedback.
$string['appraisee_feedback_view_text'] = 'Просмотр';
$string['feedback_setface2face'] = 'Вам необходимо назначить дату аттестации перед тем, как добавлять запрос на отзывы. Это находится на странице информации Аттестуемого.';
$string['feedback_comments_none'] = '<em>Дополнительных комментариев не предоставлено.</em>';

// Page content.
$string['actionrequired'] = 'Требуется принятие мер';
$string['actions'] = 'Действия';

$string['appraisals:archived'] = 'Аттестации в архиве';
$string['appraisals:current'] = 'Текущие Аттестации';
$string['appraisals:noarchived'] = 'У вас нет аттестаций в архиве.';
$string['appraisals:nocurrent'] = 'У вас нет текущих аттестаций.';

$string['comment:adddots'] = 'Добавьте комментарий…';
$string['comment:addingdots'] = 'Добавляется…';
$string['comment:addnewdots'] = 'Добавьте новый комментарий…';
$string['comment:showmore'] = '<i class="fa fa-plus-circle"></i> Показать больше';

$string['comment:status:0_to_1'] = '{$a->status} - Аттестация создана, но еще не начата.';
$string['comment:status:1_to_2'] = '{$a->status} - Аттестация начата Аттестуемым.';
$string['comment:status:2_to_3'] = '{$a->status} - Аттестация отправлена менеджеру, проводящему Аттестацию.';
$string['comment:status:3_to_2'] = '{$a->status} - Аттестация возвращена Аттестуемому.';
$string['comment:status:3_to_4'] = '{$a->status} - Аттестация ожидает комментариев Аттестуемого.';
$string['comment:status:4_to_3'] = '{$a->status} - Аттестация возвращена менеджеру, проводящему Аттестацию.';
$string['comment:status:4_to_5'] = '{$a->status} - Ожидание отправки на подпись руководителю.';
$string['comment:status:5_to_4'] = '{$a->status} - Аттестация возвращена Аттестуемому.';
$string['comment:status:5_to_6'] = '{$a->status} - Отправлено на подпись руководителю.';
$string['comment:status:6_to_7'] = '{$a->status} - Аттестация завершена.';

$string['comment:updated:appraiser'] = '{$a->ba} менеджер, проводящий Аттестацию изменен с {$a->oldappraiser} на {$a->newappraiser}.';
$string['comment:updated:signoff'] = '{$a->ba} руководитель изменен с {$a->oldsignoff} на {$a->newsignoff}.';

// Dashboards
$string['index:togglef2f:complete'] = 'Отметить встречу лицом к лицу как состоявшуюся';
$string['index:togglef2f:notcomplete'] = 'Отметить встречу лицом к лицу как несостоявшуюся';
$string['index:notstarted'] = 'Еще не начинали';
$string['index:notstarted:tooltip'] = 'Аттестуемые еще не начали свою Аттестацию, как только они начнут, у вас появится доступ.';
$string['index:printappraisal'] = 'Загрузить Аттестацию';
$string['index:printfeedback'] = 'Загрузить отзывы';
$string['index:start'] = 'Начать Аттестацию';

// Dashboard Descriptions
$string['index:toptext:appraisee'] = 'Эта информационная панель показывает ваши текущие Аттестации, а также находящиеся в архиве.Ваша текущая Аттестация доступна по ссылке под Действиями. Аттестации из архива могут быть загружены с помощью кнопки Загрузить Аттестацию ниже.';
$string['index:toptext:appraiser'] = 'Эта информационная панель показывает текущие Аттестации, а также находящиеся в архиве, по которым вы являетесь менеджером, проводящим Аттестацию. Любые текущие Аттестации доступны по ссылке под Действиями. Загрузка отзывов содержит отзывы, недоступные Аттестуемому до момента после вашей встречи. Любой конфиденциальный отзыв будет скрыт на всех этапах Аттестации. Аттестации из архива могут быть загружены с помощью кнопки Загрузить Аттестацию ниже.';
$string['index:toptext:groupleader'] = 'Эта информационная панель показывает текущие Аттестации, а также находящиеся в архиве, которые относятся к вашему кост центру. Любые текущие Аттестации доступны по ссылке под Действиями. Аттестации из архива могут быть загружены с помощью кнопки Загрузить Аттестацию ниже.';
$string['index:toptext:signoff'] = 'Эта информационная панель показывает текущие Аттестации, а также находящиеся в архиве, по которым вы являетесь руководителем. Любые текущие Аттестации доступны по ссылке под Действиями. Аттестации из архива могут быть загружены с помощью кнопки Загрузить Аттестацию ниже.';

// Dashboards
$string['index:view'] = 'Просмотр Аттестации';

// Time Strings
$string['timediff:now'] = 'Сейчас';
$string['timediff:second'] = '{$a} сек';
$string['timediff:seconds'] = '{$a} сек';
$string['timediff:minute'] = '{$a} мин';
$string['timediff:minutes'] = '{$a} мин';
$string['timediff:hour'] = '{$a} час';
$string['timediff:hours'] = '{$a} час';
$string['timediff:day'] = '{$a} день';
$string['timediff:days'] = '{$a} дней';
$string['timediff:month'] = '{$a} месяц';
$string['timediff:months'] = '{$a} месяцев';
$string['timediff:year'] = '{$a} год';
$string['timediff:years'] = '{$a} лет';

//// ALERT MESSAGES

// General alerts.
$string['alert:language:notdefault'] = '<strong>Внимание:</strong> Вы используете неосновной язык для просмотра данной аттестации. Пожалуйста, убедитесь, что вы отвечаете на вопросы на языке, используемом всеми участниками Вашей аттестации.';

// Error Strings
$string['error:togglef2f:complete'] = 'Невозможно отметить встречу лицом к лицу как состоявшуюся.';
$string['error:togglef2f:notcomplete'] = 'Невозможно отметить встречу лицом к лицу как несостоявшуюся.';

// Feedback Requests Alert Messages
$string['appraisee_feedback_email_success'] = 'Отправка e-mail прошла успешно';
$string['appraisee_feedback_email_error'] = 'Ошибка при попытке отправить e-mail';
$string['appraisee_feedback_invalid_edit_error'] = 'Этот e-mail адрес недействителен';
$string['appraisee_feedback_inuse_edit_error'] = 'Этот e-mail адрес уже используется';
$string['appraisee_feedback_inuse_email_error'] = 'Этот e-mail адрес уже используется';
$string['appraisee_feedback_resend_success'] = 'Повторная отправка e-mail прошла успешно';
$string['appraisee_feedback_resend_error'] = 'Ошибка при попытке повторно отправить e-mail';

// General.
$string['form:add'] = 'Добавьте';
$string['form:language'] = 'Выбор языка';

//Feedback ALERT MESSAGES
$string['form:addfeedback:alert:cancelled'] = 'Отправка отменена, ваш отзыв не был отправлен.';
$string['form:addfeedback:alert:error'] = 'Извините, при отправке вашего отзыва произошла ошибка.';
$string['form:addfeedback:alert:saved'] = 'Спасибо, ваш отзыв был успешно отправлен.';

$string['form:feedback:alert:cancelled'] = 'Отправка отменена, ваш запрос на отзыв не был отправлен.';
$string['form:feedback:alert:error'] = 'Извините, при отправке вашего запроса на отзыв произошла ошибка.';
$string['form:feedback:alert:saved'] = 'Спасибо, ваш запрос на отзыв был успешно отправлен.';

// Last Year Review
$string['form:lastyear:nolastyear'] = 'Прим.: Мы заметили, что у вас нет записи о предыдущей аттестации в системе. Пожалуйста, загрузите свою предыдущую аттестацию в формате pdf или Word.';
$string['form:lastyear:file'] = '<strong>Файл с аттестацией был загружен Аттестуемым: <a href="{$a->path}"  target="_blank">{$a->filename}</a></strong>';
$string['form:lastyear:cardinfo:developmentlink'] = 'Прошлый год : развитие';

// Feedback requests page.
$string['feedbackrequests:description'] = 'Информационная панель показывает любые необработанные запросы на ваши отзывы и дает возможность доступа к любым отзывам, которые вы отправили ранее.';
$string['feedbackrequests:outstanding'] = 'Необработанные запросы';
$string['feedbackrequests:norequests'] = 'Нет необработанных запросов на отзывы';
$string['feedbackrequests:completed'] = 'Отвеченные запросы';
$string['feedbackrequests:nocompleted'] = 'Нет отвеченных запросов на отзывы';
$string['feedbackrequests:th:confidential'] = 'конфиденц';
$string['feedbackrequests:th:actions'] = 'Действия';
$string['feedbackrequests:emailcopy'] = 'Отправить мне копию по электронной почте';
$string['feedbackrequests:submitfeedback'] = 'Отправить отзыв';
/*
$string['email:subject:myfeedback'] = 'Ваш отзыв на аттестацию для {{appraisee}}';
$string['email:body:myfeedback'] = '<p>Уважаемый {{recipient}},</p>
<p>Вы отправили следующий {{confidential}} отзыв для {{appraisee}}:</p> <div>{{feedback}}</div> <div>{{feedback_2}}</div>';
*/
$string['feedbackrequests:confidential'] = 'конфиденциальный';
$string['feedbackrequests:nonconfidential'] = 'не конфиденциальный';

$string['feedbackrequests:received:confidential'] ='Получено (конфиденц.)';
$string['feedbackrequests:received:nonconfidential']='Получено';
$string['feedbackrequests:paneltitle:confidential']	='Отзыв (конфиденц.)';
$string['feedbackrequests:paneltitle:nonconfidential']='Отзыв';

//CHECK IN
$string['success:checkin:add'] = 'Успешно добавлена регистрация';
$string['error:checkin:add'] = 'Не получилось добавить регистрацию';
$string['error:checkin:validation'] = 'Пожалуйста, заполните это поле.';
$string['checkin:addnewdots'] = 'Регистрация…';
$string['checkin:deleted'] = 'Регистрация удалена';
$string['checkin:delete:failed'] = 'Не получилось удалить регистрацию';
$string['checkin:update'] = 'Обновление';


////APPRAISAL QUESTIONS
// Introduction Page
$string['appraisee_heading'] = 'Добро пожаловать в онлайн-сервис аттестации';

// Feedback Contributor Page
$string['form:feedback:email']='адрес';
$string['form:feedback:firstname']='Имя';
$string['form:feedback:lastname']='Фамилия';
$string['form:feedback:language'] = 'Выберите язык для запроса отзыва';
$string['feedbackrequests:legend']='* отмечает сотрудника, готовящего отзыв по запросу аттестующего';
$string['form:addfeedback:notfound']='Запросов на отзыв не найдено';
$string['form:addfeedback:sendemailbtn']='Отправить отзыв для аттестации';
$string['form:addfeedback:closed']='Срок отправки отзыва прошел';
$string['form:addfeedback:submitted']='Отзыв отправлен';

// Userinfo.
$string['form:userinfo:intro'] = 'Пожалуйста, заполните форму ниже. Некоторые поля заполнены автоматически информацией из вашего профиля TAPS. Если какое-либо из полей заполнено некорректно, обратитесь, пожалуйста, к Вашему HR-менеджеру.';
$string['form:userinfo:name'] = 'Имя аттестуемого';
$string['form:userinfo:staffid'] = 'Табельный номер';
$string['form:userinfo:grade'] = 'Грейд';
$string['form:userinfo:jobtitle'] = 'Позиция';
$string['form:userinfo:operationaljobtitle'] = 'Должность';
$string['form:userinfo:facetoface'] = 'Предлагаемая дата личной встречи';
$string['form:userinfo:facetofaceheld'] = 'Личная встреча состоялась';

// Last Year Review
$string['form:lastyear:nolastyear'] = 'Прим.: Ваша предыдущая аттестационная форма отстуствует в системе. Пожалуйста, загрузите ее в формате pdf или Word.';
$string['form:lastyear:intro'] = 'В этом разделе аттестуемый и аттестующий обсуждают результаты, которые были получены за последние двенадцать месяцев, и то, каким образом они были получены.  Кликните <a href="https://moodle.arup.com/appraisal/guide" target="_blank">Guide to Appraisal</a> для получения дополнительной информации об этом обсуждении, изложенной в Руководстве по аттестации.';

$string['form:lastyear:upload'] = 'Загрузить аттестацию';
$string['form:lastyear:appraiseereview'] = '1.1 Рассмотрение деятельности за прошлый год аттестуемым';
$string['form:lastyear:appraiseereviewhelp'] = '<div class="well well-sm">
	<em>В целом, насколько хорошо Вы выполняете свои обязанности в контексте реализации проектов, работы с коллегами и заказчиками с момента Вашей последней аттестации?</em>
	<ul class="m-b-0">
		<li><em>Каким образом Вы сотрудничали и делились информацией и опытом? Каких результатов Вы достигли?</em></li>
		<li><em>Получали ли Вы результаты, которые были ниже ожидаемых?</em></li>
		<li><em>Если Вы несете ответственность за работу других сотрудников, должным ли образом Вы влияли на их деятельность и поведение, как положительное, так и отрицательное?</em></li>
		<li><em>Каким образом Вы применяете технологии для повышения эффективности своей деятельности?</em></li>
	</ul>
</div>';
$string['form:lastyear:appraiserreview'] = '1.2 Рассмотрение деятельности за прошлый год аттестующим';
$string['form:lastyear:appraiserreviewhelp'] = '<div class="well well-sm">
    <em>Оставьте комментарий о том, как аттестуемый рассматривает результаты своей деятельности за прошлый год.</em>
    <ul class="m-b-0">
        <li><em>Какого ему удалось добиться прогресса?</em></li>
        <li><em>Подведите итоги на основании отзывов, которые предоставили аттестуемому выбранные коллеги.</em></li>
    </ul>
    <em>Если результаты какой-либо деятельности или поведения оцениваются как ниже ожидаемых, следует обсудить и зафиксировать этот аспект в настоящем разделе. Это может касаться его проектов, команды, клиентов или других людей в целом.</em>
</div>';
$string['form:lastyear:appraiseedevelopment'] = '1.3 Рассмотрение развития за прошлый год аттестуемым';
$string['form:lastyear:appraiseedevelopmenthelp'] = '<div class="well well-sm">
    <em>Прокомментируйте Ваше личностное развитие с момента последней аттестации:</em>
    <ul class="m-b-0">
        <li><em>Каким образом Вы развивали Ваши способности, знания или модель поведения?</em></li>
        <li><em>Что из запланированного на прошлый год осталось нереализованным?</em></li>
    </ul>
</div>';
$string['form:lastyear:appraiseefeedback'] = '1.4 Есть ли что-либо, что может повлиять на Вашу деятельность или деятельность команды, либо повысить эффективность указанной деятельности?';
$string['form:lastyear:appraiseefeedbackhelp'] = '<div class="well well-sm"><em>Заполняется аттестуемым</em></div>';

// Career Direction
$string['form:careerdirection:intro'] = 'Цель настоящего раздела состоит в том, чтобы аттестуемый смог рассмотреть свои карьерные устремления и практически обсудить их со своим аттестующим.  Сотрудники, занимающие нижестоящие должности, вероятно, ориентируются на перспективу в 1-3 года, а сотрудники, занимающие вышестоящие должности, как ожидается, определяют период от 3 до 5 лет.';
$string['form:careerdirection:progress'] = '2.1 Каким образом Вы хотите, чтобы развивалась Ваша карьера?';
$string['form:careerdirection:progresshelp'] = '<div class="well well-sm">
	<em>Рассмотрите следующие вопросы:</em>
	<ul class="m-b-0">
		<li><em>Какой вид работы Вы хотите выполнять и с каким уровнем ответственности?</em></li>
		<li><em>Какие аспекты Вашей работы являются важными для Вас в течение ближайших нескольких лет, например, широта, глубина, специализация, обобщение, мобильность, планирование, ответственность за работу других сотрудников и проч.?</em></li>
		<li><em>Определите Ваше желаемое местоположение?</em></li>
	</ul>
</div>';
$string['form:careerdirection:comments'] = '2.2 Комментарии аттестующего';
$string['form:careerdirection:commentshelp'] = '<div class="well well-sm">
    <em>Рассмотрите следующие вопросы:</em>
    <ul class="m-b-0">
        <li><em>Насколько реалистичны, трудны, но интересны, а также амбициозны устремления аттестуемого?</em></li>
        <li><em>Выполнение каких ролей, проектов и других видов деятельности будет способствовать развитию требуемого опыта, навыков и моделей поведения?</em></li>
    </ul>
</div>';

// Agreed Impact Plan
$string['form:impactplan:intro'] = 'Согласованный план влияния содержит информацию о том, как аттестуемый хочет изменить ситуацию в течение следующего года в контексте выполняемой им работы и влияния на работу компании в целом. План должен отображать меры, выполняя которые аттестуемый улучшит исполнение своих обязанностей в отношении проектов/работы в команде/компании/группе. На практике это означает, что должна быть предоставлена конкретная информация о сроках выполнения работы, качестве, бюджете, планировании/инновациях и влиянии на сотрудников, клиентов, либо на работу в целом.<br /><br />
<a href="https://moodle.arup.com/appraisal/contribution" target="_blank">Contribution Guide </a> и <a href="https://moodle.arup.com/appraisal/guide" target="_blank">Guide to Appraisal</a> содержат рекомендации по внесению этих улучшений';
$string['form:impactplan:impact'] = '3.1 Опишите влияние, которое Вы хотите оказать на Ваши проекты, клиентов, команду или компанию в следующем году:';
$string['form:impactplan:impacthelp'] = '<div class="well well-sm">
    <em>Ваш ответ может содержать следующие аспекты:</em>
    <ul class="m-b-0">
        <li><em>Ваши направления деятельности</em></li>
        <li><em>Важность этих направлений</em></li>
        <li><em>Способы достижения поставленных целей</em></li>
        <li><em>С кем Вы будете сотрудничать</em></li>
        <li><em>Приблизительные сроки: 3/6/12/18 месяцев или более</em></li>
        <li><em>Каким образом Ваш Согласованный план влияния соответствует и обеспечивает реализацию Вашего карьерного роста</em></li>
    </ul>
</div>';
$string['form:impactplan:support'] = '3.2 В какой поддержке со стороны «Аруп» Вы нуждаетесь для достижения этого?';
$string['form:impactplan:supporthelp'] = '<div class="well well-sm">
    <em>Вы можете рассмотреть следующие аспекты:</em>
    <ul class="m-b-0">
        <li><em>Помощь от других людей</em></li>
        <li><em>Курирование</em></li>
        <li><em>Ресурсы (время, бюджет, оборудование)</em></li>
        <li><em>Личностное развитие</em></li>
        <li><em>Инструменты (программное обеспечение, аппаратное оборудование)</em></li>
    </ul>
</div>';
$string['form:impactplan:comments'] = '3.3 Комментарии аттестующего';
$string['form:impactplan:commentshelp'] = '<div class="well well-sm"><em>Заполняется аттестующим</em></div>';

// Development Plan
$string['form:development:intro'] = 'План развития содержит информацию о том, какие личные навыки, знания или модели поведения необходимы для реализации карьерного роста аттестуемого и Согласованного плана влияния.<br /><br />
В каких направлениях Вам нужно развиваться в следующие 12-18 месяцев, чтобы достичь данных целей?
 Какая поддержка Вам необходима и когда Вы планируете осуществлять указанное развитие?<br /><br />
<div class="well well-sm">Компания «Аруп» придерживается следующего принципа личностного развития: «70-20-10» . Это означает, что для большинства сотрудников 70 % их развития должны осуществляться «на работе» и должны быть получены из опыта. 20 % должны быть реализованы через обучение у других людей, возможно при помощи тренингов или наставничества. Последние 10 % приходятся на формальные методы обучения, такие как очные занятия и интернет-обучение. Указанные проценты, конечно же, являются ориентировочными.</div>';
$string['form:development:seventy'] = 'Обучение, которое осуществляется в процессе выполнения Вашей работы - приблизительно 70 %';
$string['form:development:seventyhelp'] = '<div class="well well-sm">
	<em>Например:</em>
	<ul class="m-b-0">
		<li><em>Проектные задания</em></li>
		<li><em>Командные задания</em></li>
		<li><em>Мобильность</em></li>
		<li><em>Обсуждение работы и отзывы</em></li>
		<li><em>Обзоры проектов, период интенсивной работы над проектом</em></li>
		<li><em>Чтение</em></li>
		<li><em>Исследование</em></li>
	</ul>
</div>';
$string['form:development:twenty'] = 'Обучение у других людей – приблизительно 20 %';
$string['form:development:twentyhelp'] = '<div class="well well-sm">
	<em>Например:</em>
	<ul class="m-b-0">
		<li><em>Члены команды</em></li>
		<li><em>Эксперты</em></li>
		<li><em>Клиенты</em></li>
		<li><em>Коллеги</em></li>
		<li><em>Участники совещаний</em></li>
		<li><em>Тренинг</em></li>
		<li><em>Наставничество</em></li>
	</ul>
</div>';
$string['form:development:ten'] = 'Формальное обучение – очное или дистанционное при помощи Интернета – приблизительно 10 %';
$string['form:development:tenhelp'] = '<div class="well well-sm">
    <em>Например:</em>
    <ul class="m-b-0">
        <li><em>Аудиторные занятия</em></li>
        <li><em>Формальное интернет-обучение</em></li>
        <li><em>Обучение в виртуальной аудитории</em></li>
    </ul>
</div>';

// Summaries
$string['form:summaries:intro'] = 'Целью настоящего раздела является подведение кратких итогов относительно содержания аттестации, которое в дальнейшем будет доступно для лиц, ответственных за вопросы оплаты, продвижения или развития.';
$string['form:summaries:appraiser'] = '5.1 Краткий отчет аттестующего об общей деятельности';
$string['form:summaries:appraiserhelp'] = '<div class="well well-sm">
    <em>Аттестующий должен предоставить четкий и краткий отчет о деятельности аттестуемого, который в дальнейшем может быть без труда использован лицами, ответственными за вопросы оплаты, продвижения или развития. В частности, аттестующий должен отчетливо указать, в каких моментах деятельность сотрудника не оправдала, либо превысила ожидаемые результаты.</em>
</div>';
$string['form:summaries:recommendations'] = '5.2 Согласованные действия';
$string['form:summaries:recommendationshelp'] = '<div class="well well-sm">
    <em>Заполняется аттестующим</em><br />
    <em>Какие действия следует предпринять? Например:</em>
    <ul>
        <li><em>Развитие</em></li>
        <li><em>Мобильность</em></li>
        <li><em>Перевод в другой офис</em></li>
        <li><em>Предоставление поддержки в ходе деятельности</em></li>
    </ul>
</div>';
$string['form:summaries:appraisee'] = '5.3 Комментарии аттестуемого';
$string['form:summaries:appraiseehelp'] = '<div class="well well-sm"><em>Заполняется аттестуемым</em></div>';
$string['form:summaries:signoff'] = '5.4 Краткое резюме';
$string['form:summaries:signoffhelp'] = '<div class="well well-sm"><em>Осуществляется руководителем/уполномоченным лицом</em></div>';

// Give Feedback.
$string['confidential_label_text'] = 'Отметьте эту ячейку, если Вы хотите, чтобы Ваши комментарии не подлежали разглашению. Если ячейка не будет отмечена Вами, аттестуемому будут доступны Ваши комментарии.';

// FFF Email templates Feedback FFF.
$string['email:subject:appraiseefeedback'] = 'Запрос на отзыв для моей аттестации';
$string['email:body:appraiseefeedback_link_here'] = 'здесь';

$string['email:subject:appraiserfeedback'] = 'Запрос на отзыв для аттестации {{appraisee_fullname}}';

// PDF Strings
$string['pdf:form:summaries:appraisee'] = 'Комментарии аттестуемого';
$string['pdf:form:summaries:appraiser'] = 'Краткий отчет аттестующего об общей деятельности';
$string['pdf:form:summaries:signoff'] = 'Краткое резюме';
$string['pdf:form:summaries:recommendations'] = 'Согласованные действия';

//CHECK IN Introduction
$string['checkins_intro']='В течение года ожидается, что атестуемый и аттестующий будут обсуждать прогресс в соответствии с Согласованным Планом Влияния, Планом Развития, предпринятыми действиями и производительностью. Аттестуемый и/или аттестующий может использовать раздел ниже, чтобы фиксировать прогресс. Частота этих бесед зависит от Вас, но рекомендуется проводить их, как минимум, раз в год.';

// 2017 : Updates and additions.
$string['addreceivedfeedback'] = 'Добавить полученный отзыв';
$string['appraisee_feedback_savedraft_error'] = 'При попытке сохранить черновик произошла ошибка';
$string['appraisee_feedback_savedraft_success'] = 'Черновик отзыва сохранен';
$string['appraisee_feedback_viewrequest_text'] = 'Посмотреть письмо-запрос';
$string['appraisee_welcome'] = 'Ваша аттестация - это возможность для Вас и Вашего аттестующего в процессе содержательной беседы оценить показатели Вашей деятельности, равзития и дальнейший вклад в бизнес. Мы хотим, чтобы Вы провели конструктивную беседу, которая несет индивидуальный характер и принесет всем пользу. <br /><br />

Цель настоящей программы заключается в том, чтобы помочь Вам записать беседу и обращаться к ее материалам в течение года. <br /><br />Дальнейшая информация о процессе аттестации доступна <a href="https://moodle.arup.com/appraisal/essentials" target="_blank">здесь</a>';
$string['appraisee_welcome_info'] = 'Конечный срок Вашей аттестациив этом году {$a}.';
$string['email:body:appraiseefeedback'] = '{{emailmsg}}
<br>
<hr>
<p>Пожалуйста, пройдите по {{link}} ссылке ниже, чтобы оставить отзыв. </p><p>Аттестация  {{appraisee_fullname}}<br> Аттестация назначена на <span class="placeholder">{{held_date}}</span></p>
<p> Это автоматическое письмо отправлено {{appraisee_fullname}} для {{firstname}} {{lastname}}.</p> <p> Если ссылка не работает, пожалуйста, скопируйте ее в браузер, чтобы посмотреть аттестацию:<br />{{linkurl}}</p>';
$string['email:body:appraiseefeedbackmsg'] = '<p>Уважаемый <span class="placeholder bind_firstname">{{firstname}}</span>,
</p> <p>Моя аттестация назначена на<span class="placeholder">{{held_date}}</span>. Аттестацию проводит <span class="placeholder">{{appraiser_fullname}}</span>. В прошлом году мы много работали вместе, я буду признателен получить Ваш отзыв по тем направлениям, где Вы особенно оценили мой вклад, и где Вы считаете, я мог бы быть продуктивнее. </p> <p>Если Вы желаете оставить отзыв, то пройдите по ссылке ниже.  Я буду очень благодарен, если Вы ответите до моей встречи с аттестующим.</p>
<p class="ignoreoncopy">Дополнительные комментарии ниже <span class="placeholder">{{appraisee_fullname}}</span>:<br /> <span>{{emailtext}}</span></p>
<p> С уважением, <br />
<span class="placeholder">{{appraisee_fullname}}</span></p>';
$string['email:body:appraiserfeedback'] = '{{emailmsg}} <br> <hr> <p>Пожалуйста, пройдите по {{link}} ссылке ниже, чтобы оставить отзыв. </p> <p> Аттестация {{appraisee_fullname}}<br> Аттестация назначена на (дата) Это автоматическое письмо отправлено <span class="placeholder">{{held_date}}</span></p> <p> для {{firstname}} {{lastname}}.</p> <p>Если ссылка не работает, пожалуйста, скопируйте ее в браузер, чтобы посмотреть аттестацию. <br />{{linkurl}}</p>';
$string['email:body:appraiserfeedbackmsg'] = '<p> Уважаемый <span class="placeholder bind_firstname">{{firstname}}</span>,</p>
<p>Моя аттестация назначена на <span class="placeholder">{{appraisee_fullname}}</span> Аттестацию проводит  <span class="placeholder">{{held_date}}</span>. В прошлом году мы много работали вместе, я буду признателен получить Ваш отзыв по тем направлениям, где Вы особенно оценили мой вклад, и где Вы считаете, я мог бы быть продуктивнее. Если Вы желаете оставить отзыв, то пройдите по ссылке ниже.  Я буду очень благодарен, если Вы ответите до моей встречи с аттестующим. </p> <p>Дополнительные комментарии ниже <span class="placeholder">{{appraiser_fullname}}</span>:<br /> <span>{{emailtext}}</span></p>
<p> С уважением, <br /> <span class="placeholder">{{appraiser_fullname}}</span></p>';
$string['email:body:myfeedback'] = '<p>Уважаемый {{recipient}},</p> <p>Вы отправили следующий {{confidential}} отзыв для {{appraisee}}:</p> <div>{{feedback}}</div> <div>{{feedback_2}}</div>';
$string['email:subject:myfeedback'] = 'Ваш отзыв для {{appraisee}}';
$string['error:noappraisal'] = 'Ошибка - аттестации нет в системе. Пожалуйста, обратитесь за помощью к администратору по аттестации ниже, если требуется заполнить аттестацию: {$}';
$string['feedback_header'] = 'Дать отзыв на {$a->appraisee_fullname} (Аттестующий {$a->appraiser_fullname} - Дата аттестации {$a->facetofacedate})';
$string['feedback_intro'] = 'Пожалуйста, выберете трех или более коллег для того, чтобы они оставили отзывы в форме Вашей аттестации. В большинстве регионов отзывы могут быть внутренними или внешними. Следуйте указаниям, определенным для Вашего региона.<br/><br/> Для составления внутренних отзывов ориентируйтесь на перспективу «360 градусов», т.е. отзывы должны подготовить Ваши коллеги, которые занимают как выше-, так и нижестоящие должности. Вы должны выбрать разных людей. <br/><br/><div data-visible-regions="UKIMEA, EUROPE, AUSTRALASIA">В качестве составителя отзыва могут выступать либо сторонний заказчик, либо сотрудник Аруп, знающий Вас достаточно хорошо.</div> <div data-visible-regions="East Asia"><br /><div class="alert alert-warning">For East Asia region, we expect feedback to be from internal source only. Comments from external client or collaborator should be understood and fed back through internal people.</div></div> <div data-visible-regions="Americas"><br /><div class="alert alert-warning">For the Americas Region, comments from external clients or collaborators should be fed back through conversations gathered outside of this feedback tool.</div> </div> <br /><div class="alert alert-danger"> Примечание: Отзывы будут опубликованы, как только будут получены, если только отзыв не был запрошен Вашим аттестующим. В этом случае аттестующий должен отправить Вам аттестацию для конечных комментариев (пункт 3), чтобы отзыв появился. </div>';
$string['feedbackrequests:paneltitle:requestmail'] = 'Письмо-запрос отзыва';
$string['form:addfeedback:addfeedback'] = 'Пожалуйста, опишите до трех направлений, в которых вы оценили вклад аттестуемого за последние 12 месяцев.';
$string['form:addfeedback:addfeedback_2'] = 'Пожалуйста, сообщите подробности до трех направлений, где по Вашему мнению аттестуемый мог бы быть более продуктивным. Будьте честными, но предоставьте конструктивную критику , т.к. Ваш отзыв поможет коллеге решать вопросы продуктивнее.';
$string['form:addfeedback:addfeedback_2help'] = '<div class="well well-sm">Для всех участников важно получить ценный и сбалансированный отзыв, включающий в себя положительный и отрицательный вклад. Для дальнейшей инструкции, пожалуйста, пройдите по <a href="https://moodle.arup.com/scorm/_assets/ArupAppraisalGuidanceFeedback.pdf"
target="_blank">ссылке</a></div>';
$string['form:addfeedback:addfeedback_help'] = 'Пожалуйста, просто скопируйте и вставьте полученный отзыв в "ценный вклад", если Вы не можете выбрать между "ценный" и "наиболее продуктивный".';
$string['form:addfeedback:addfeedbackhelp'] = '<div class="well well-sm">Для всех участников важно получить ценный и сбалансированный отзыв, включающий в себя положительный и отрицательный вклад. Для дальнейшей инструкции, пожалуйста, пройдите по <a href="https://moodle.arup.com/scorm/_assets/ArupAppraisalGuidanceFeedback.pdf"
target="_blank">ссылке</a></div>';
$string['form:addfeedback:firstname'] = 'Имя составителя отзыва';
$string['form:addfeedback:lastname'] = 'Фамилия составителя отзыва';
$string['form:addfeedback:saveddraft'] = 'Вы сохранили черновик Вашего отзыва. Ваш отзыв не будет виден аттестуемому или аттестующему, пока Вы его не отправите.';
$string['form:addfeedback:savedraftbtn'] = 'Сохранить как черновик';
$string['form:addfeedback:savedraftbtntooltip'] = 'Сохраните черновик, чтобы завершить его позже. Аттестуемый/аттестующий не увидит его.';
$string['form:addfeedback:savefeedback'] = 'Сохранить отзыв';
$string['form:development:comments'] = 'Комментарии аттестующего';
$string['form:development:commentshelp'] = '<div class="well well-sm"><em>Заполняется аттестующим</em></div>';
$string['form:feedback:editemail'] = 'Редактировать';
$string['form:feedback:providefirstnamelastname'] = 'Пожалуйста, введите имя и фамилию получателя прежде, чем выбирать "редактировать".';
$string['form:lastyear:cardinfo:performancelink'] = 'Прошлогодний план влияния';
$string['form:lastyear:printappraisal'] = '<a href="{$a}" target="_blank">Вы можете осмотреть прошлогоднюю аттестацию</a> (PDF - открывается в новой вкладке).';
$string['form:summaries:grpleaderhelp'] = '<div class="well well-sm"><em>Заполняется руководителем в качестве заключения. </em></div>';
$string['leadersignoff'] = 'Заключение руководителя';
$string['modal:printconfirm:cancel'] = 'Это правильно';
$string['modal:printconfirm:content'] = 'Вам действительно нужно распечатать этот документ?';
$string['modal:printconfirm:continue'] = 'Да, продолжайте.';
$string['modal:printconfirm:title'] = 'Подумайте, прежде чем распечатать.';
$string['overview:content:appraisee:3'] = 'Вы отправили черновик аттестации {$a->styledappraisername} на рассмотрение. <br /><br />
<strong> Следующие шаги: </strong>
<ul class="m-b-20"> <li> провести личную встречу - перед встречей Вы можете: </li> <ul class="m-b-0"> <li><a class="oa-print-confirm" href="{$a->printappraisalurl}"> скачать аттестацию </a></li> <li><a href="https://moodle.arup.com/appraisal/reference" target="_blank"> скачать краткое руководство </a></li> </ul> <li>После встречи аттестующий попросит Вас внести изменения, которые Вы согласуете при личной встрече или написать заключение</li> </ul> <div class="alert alert-danger" role="alert"><strong> Примечание: </strong> Вы можете продолжать редактировать аттестацию после того, как Вы отправили ее аттестующему, но советуем  использовать историю операций, чтобы отслеживать все изменения. </div>';
$string['overview:content:appraiser:3'] = '{$a->styledappraiseename} отправил черновик при подготовке к личной встречи. <br /><br /> <strong> Следующие шаги: </strong> <ul class="m-b-20"> <li> Пожалуйста, посмотрите аттестацию при подготовке к встрече. При необходимости попросите аттестуемого дополнить форму. </li>
 <li> Перед встречей Вы должны: </li> <ul class="m-b-0"> <li><a class="oa-print-confirm" href="{$a->printappraisalurl}"> скачать аттестацию
-скачать полученные отзывы </a></li> <li><a class="oa-print-confirm" href="{$a->printfeedbackurl}">Вы также можете скачать краткое руководство </a></li> <li> После личной встречи, <a href="https://moodle.arup.com/appraisal/reference" target="_blank">пожалуйста </a></li> </ul> <li> Отметьте в информации об аттестуемом, что личная встреча состоялась </li> <ul class="m-b-0"> <li>Добавьте Ваш комментарий в каждый раздел </li> <li> Напишите Ваше заключение и согласованные действия в раздел Заключение
(При необходимости Вы можете попросить аттестуемого внести изменения прежде, чем добавлять комментарии) </ul> <li> Отправьте аттестуемому, чтобы посмотреть комментарии, отзывы и добавить последние комментарии</li> </ul>';
$string['overview:content:special:archived'] = '<div class="alert alert-danger" role="alert">Ваша аттестация была перенесена в архив. <br /> Теперь ее можно <a class="oa-print-confirm" href="{$a->printappraisalurl}"> только скачать</a>.</div>';
$string['overview:content:special:archived:appraisee'] = '<div class="alert alert-danger" role="alert">Ваша аттестация была перенесена в архив. <br />Теперь ее можно <a class="oa-print-confirm" href="{$a->printappraisalurl}">только скачать. </a>.</div>';
$string['overview:lastsaved'] = 'Последнее сохранение: {$a}';
$string['overview:lastsaved:never'] = 'Никогда';
$string['pdf:feedback:confidentialhelp:appraisee'] = 'Показывает конфидициальный отзыв, который Вы не можете видеть.';
$string['pdf:feedback:notyetavailable'] = 'Пока не видно.';
$string['pdf:feedback:requestedfrom'] = 'Рецензент{$a->firstname} {$a->lastname}{$a->appraiserflag}{$a->confidentialflag}:';
$string['pdf:feedback:requestedhelp'] = 'Показывает конфидициальный отзыв, запрошенный Вашим аттестующим, который Вы не можете видеть.';
$string['pdf:header:warning'] = 'Скачено:{$a->who} on {$a->when}<br> Пожалуйста, не сохраняйте и не оставляйте его в ненадежном месте.';
$string['status:7:leadersignoff'] = 'Заключение руководителя';
