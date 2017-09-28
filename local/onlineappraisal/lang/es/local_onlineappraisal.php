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

//General alerts
$string['alert:language:notdefault'] ='Atención: No estás usando el idioma predeterminado para ver el appraisal. Por favor, asegúrate que rellenas este formulario en el idioma más apropiado para todas las partes implicadas. ';


//APPRAISEE

$string['overview:content:appraisee:2'] = 'Empieza a completar tu appraisal. <br/><br/>

<strong>Próximos pasos: </strong>
<ul class="m-b-20">
<li>Inserta la fecha de tu próxima reunión de appraisal</li>
<li>Contacta con tus colaboradores de feedback</li>
<li>Rellena el apartado Last Year\'s Performance and Development</li>
<li>Rellena el apartado Career Direction, Agreed Impact Plan y Development Plan para tratarlos con tu appraiser durante la reunión.</li>
<li>Notifica tus propuestas a {$a->styledappraisername}, tu appraiser</li>
</ul>
Por favor, notifica tus propuestas a tu appraiser al menos con <strong>una semana</strong> de antelación a la reunión del appraisal. En cualquier caso, podrás introducir cualquier cambio en tus propuestas aún después de haberlas notificado a tu appraiser. <br/><br/>

<div class="alert alert-danger" role="alert"><strong>Importante:</strong> Tu appraiser no podrá ver tus propuestas hasta que se las hayas notificado. </div>';

$string['overview:content:appraisee:2:3'] = '
Tu appraiser ha solicitado modificaciones en tu propuesta de appraisal.<br/><br/>
<strong>Próximos pasos:</strong>
<ul class="m-b-20">
<li>Realiza los cambios solicitados por tu appraiser (puedes consultar el registro de actividades para obtener información adicional sobre lo que tu appraiser ha solicitado)</li>
<li>Vuelve a notificar tu propuesta de appraisal a {$a->styledappraisername.</li>
</ul>';

$string['overview:content:appraisee:3'] = '
Acabas de enviar tu propuesta de appraisal a {$a->styledappraisername} para su revisión.<br /><br />
<strong>Próximos pasos:</strong>
<ul class="m-b-20">
    <li>Mantén tu entrevista de appraisal. Previamente deberás:</li>
    <ul class="m-b-0">
        <li><a href="{$a->printappraisalurl}">Descargar el formulario del Appraisal</a></li>
        <li><a href="https://moodle.arup.com/appraisal/reference" target="_blank">Descargar la Quick Reference Guide</a></li>
    </ul>
    <li>Tras la entrevista, tu appraiser te devolverá el appraisal con sus comentarios. Cuando lo vuelvas a recibir, podrás hacer las modificaciones pertinentes que hubiesen sido acordadas durante la reunión y escribir tus comentarios finales.</li>
</ul>
<div class="alert alert-danger" role="alert"><strong>Importante:</strong> Podrás seguir modificando tu appraisal aunque ya se lo hayas enviado a tu appraiser para que rellene las secciones de su competencia. En ese caso, te recomendamos que hagas uso del Activity Log, a través del cual resaltarás los cambios introducidos. </div>';

$string['overview:content:appraisee:3:4'] = '
Has reenviado tu appraisal a {$a->styledappraisername} para introducir modificaciones.<br /><br />
Se te notificará cuando los cambios hayan sido introducidos y el appraisal vuelva a estar listo para tu revisión.<br /><br />
<div class="alert alert-danger" role="alert"><strong>Importante:</strong> Podrás continuar modificando tu appraisal aun cuando tu appraiser esté rellenando las secciones de su competencia. En ese caso, te recomendamos que hagas uso del Activity Log, a través del cual resaltarás los cambios introducidos.</div>';

$string['overview:content:appraisee:4'] = '
{$a->styledappraisername} ha añadido sus comentarios. El appraisal te ha sido devuelto.<br /><br />
<strong>Próximos pasos:</strong>
<ul class="m-b-20">
    <li>Por favor, revisa los comentarios y resumen de tu appraiser. Si fuese necesario, reenvía el appraisal a tu appraiser para introducir cambios adicionales.</li>
    <li>Rellena la sección Summaries con tus comentarios</li>
    <li>Por último, reenvíaselo a tu appraiser para su última revisión antes de su confirmación. Una vez se haya enviado, el appraisal ya no se podrá volver a modificar.</li>
</ul>
<div class="alert alert-danger" role="alert"><strong>Importante:</strong> Todavía puedes introducir cambios en las secciones a rellenar por ti del appraisal. En ese caso, te recomendamos que hagas uso del Activity Log, a través del cual resaltarás los cambios introducidos.</div>';

$string['overview:content:appraisee:5'] = '
Ya has enviado tu appraisal completo a {$a->styledappraisername} para su última revisión.<br /><br />
<strong>Próximos pasos:</strong>
<ul class="m-b-20">
    <li>Tu appraiser enviará ahora tu appraisal a {$a->styledsignoffname} para su confirmación.</li>
</ul>
<div class="alert alert-danger" role="alert"><strong>Importante:</strong> A partir de ahora ya no podrás introducir ningún cambio en tu appraisal, a excepción de si tu appraiser específicamente te lo reenviase para su modificación.</div>';

$string['overview:content:appraisee:6'] = '
Tu appraisal ha sido enviado a {$a->styledsignoffname} para que incluya su resumen y última revisión.<br /><br />
<div class="alert alert-danger" role="alert"><strong>Importante:</strong> El appraisal ha sido desactivado. No se pueden introducir cambios.</div>';

$string['overview:content:appraisee:7'] = '
Tu appraisal ha sido completado. Puedes descargar una copia en PDF del mismo en cualquier momento marcando la casilla "Download appraisal".';

//APPRAISER

$string['overview:content:appraiser:2'] = '
El appraisal está siendo redactado por {$a->styledappraiseename}. Se te avisará cuando las propuestas estén listas para tu revisión.<br /><br />
<div class="alert alert-danger" role="alert"><strong>Importante:</strong> No podrás ver el appraisal hasta que no se te haya notificado.</div>';

$string['overview:content:appraiser:2:3'] ='Has solicitado la modificación de la propuesta de appraisal a {$a->styledappraiseename} para introducir cambios. Se te notificará cuando el appraisal esté listo para que lo revises de nuevo.
<div class="alert alert-danger" role="alert"><strong>Importante:</strong> Todavía puedes introducir cambios en las secciones a rellenar por ti del appraisal.';

$string['overview:content:appraiser:3'] = '
{$a->styledappraiseename} te ha enviado su propuesta de appraisal para vuestra próxima reunión.<br /><br />
<strong>Próximos pasos:</strong>
<ul class="m-b-20">
    <li>Por favor, revisa esta propuesta antes de vuestra reunión. Si fuese necesario hacer algún cambio/introducir información adicional, la propuesta se le enviaría de nuevo al appraisee para su modificación.</li>
    <li>Previamente a la reunión deberás:</li>
    <ul class="m-b-0">
        <li><a href="{$a->printappraisalurl}">Descargar el formulario de appraisal</a></li>
        <li><a href="{$a->printfeedbackurl}">Descargar el feedback recibido por parte de los colaboradores a los que se les hubiese solicitado</a></li>
        <li>También te podría ser útil descargar la <a href="https://moodle.arup.com/appraisal/reference" target="_blank">quick reference guide</a></li>
    </ul>
    <li>Tras mantener la reunión deberás:</li>
    <ul class="m-b-0">
        <li>Marcar la casilla que indica que la reunión ya ha sido mantenida y que encontrarás en la sección Apraisee Info</li>
        <li>Añade los comentarios pertinentes en las secciones de tu competencia </li>
        <li>Añade tu resumen y Agreed Actions en la sección de Summaries</li>
        (Si fuese necesario, puedes reenviar el appraisal a tu appraisee para su modificación antes de añadir tus comentarios, resumen y Agreed Actions)
    </ul>
    <li>Reenvía toda la información añadida a tu appraisee para que revise tus comentarios, el feedback recibido durante la reunión y para que pueda introducir sus comentarios finales.</li>
</ul>';

$string['overview:content:appraiser:3:4'] = '
{$a->styledappraiseename} ha solicitado modificaciones en su appraisal.<br /><br />
<strong>Próximos pasos:</strong>
<ul class="m-b-20">
    <li>Realiza los cambios solicitados por el appraisee (puedes consultar el registro de actividades para obtener información adicional sobre lo que tu appraisee ha solicitado).</li>
    <li>Una vez realizados estos cambios, notifícaselo a {$a->styledappraiseename} para que introduzca sus comentarios finales. </li>
</ul>';

$string['overview:content:appraiser:4'] = '
Has añadido tus comentarios y resumen, por lo que el appraisal ya ha sido reenviado a {$a->styledappraiseename} para que incluya sus comentarios finales. Se te notificará cuando vuelva a estar listo para tu última revisión.<br /><br />
<div class="alert alert-danger" role="alert"><strong>Importante:</strong> Todavía puedes introducir cambios en las secciones a rellenar por ti del appraisal. En ese caso, te recomendamos que hagas uso del Activity Log, a través del cual resaltarás los cambios introducidos.</div>';

$string['overview:content:appraiser:5'] = '
{$a->styledappraiseename} ha añadido sus comentarios finales. <br /><br />
<strong>Próximos pasos:</strong>
<ul class="m-b-20">
    <li>Revisa que el appraisal esté completo y listo para su confirmación.</li>
    <li>Envíaselo a {$a->styledsignoffname} para su revisión y resumen del contenido.</li>
</ul>
<div class="alert alert-danger" role="alert"><strong>Importante:</strong> A partir de ahora ya no podrás introducir ningún cambio en el appraisal, a excepción de si se lo volvieses a reenviar al appraisee para que hiciese alguna modificación.</div>';

$string['overview:content:appraiser:6'] = '
Acabas de enviar el appraisal a {$a->styledsignoffname} para que lo complete de manera definitiva.<br /><br />
<div class="alert alert-danger" role="alert"><strong>Importante:</strong> El appraisal ha sido desactivado. No se pueden introducir cambios.</div>';

$string['overview:content:appraiser:7'] = 'El appraisal ha sido completado y confirmado.';

//SIGN OFF

$string['overview:content:signoff:2'] = 'El appraisal está en proceso.<br /><br /><div class="alert alert-danger" role="alert"><strong>Importante:</strong> Se te notificará cuando el appraisal esté listo para tu revisión y posterior confirmación.</div>';

$string['overview:content:signoff:3'] = 'El appraisal está en proceso.<br /><br /><div class="alert alert-danger" role="alert"><strong>Importante:</strong> Se te notificará cuando el appraisal esté listo para tu revisión y posterior confirmación.</div>';

$string['overview:content:signoff:4'] = 'El appraisal está en proceso.<br /><br /><div class="alert alert-danger" role="alert"><strong>Importante:</strong> Se te notificará cuando el appraisal esté listo para tu revisión y posterior confirmación.</div>';

$string['overview:content:signoff:5'] = 'El appraisal está en proceso.<br /><br /><div class="alert alert-danger" role="alert"><strong>Importante:</strong> Se te notificará cuando el appraisal esté listo para tu revisión y posterior confirmación.</div>';

$string['overview:content:signoff:6'] = 'Se te ha enviado el appraisal de {$a->styledappraiseename} para su última revisión.<br /><br />
<strong>Próximos pasos:</strong>
<ul class="m-b-20">
    <li>Revisa el appraisal</li>
    <li>Escribe tu resumen en la sección Summaries</li>
    <li>Marca la casilla Sign Off para completar de manera definitiva este appraisal</li>
</ul>';

$string['overview:content:signoff:7'] = 'El appraisal ha sido completado y confirmado.';

//GROUP LEADER

$string['overview:content:groupleader:2'] = 'El appraisal está en proceso.';
$string['overview:content:groupleader:3'] = 'El appraisal está en proceso.';
$string['overview:content:groupleader:4'] = 'El appraisal está en proceso.';
$string['overview:content:groupleader:5'] = 'El appraisal está en proceso.';
$string['overview:content:groupleader:6'] = 'El appraisal está en proceso.';
$string['overview:content:groupleader:7'] = 'El appraisal ha sido completado y confirmado.';

//OVERVIEW BUTTONS

$string['overview:button:appraisee:2:extra'] = 'Comienza tu appraisal';
$string['overview:button:appraisee:2:submit'] = 'Notificar a {$a->plainappraisername}';

$string['overview:button:appraisee:4:return'] = 'Reenviar a {$a->plainappraisername} para que introduzca modificaciones';
$string['overview:button:appraisee:4:submit'] = 'Enviar appraisal completo a {$a->plainappraisername}';

$string['overview:button:appraiser:3:return'] = 'Solicita más información a {$a->plainappraiseename}';
$string['overview:button:appraiser:3:submit'] = 'Enviar a {$a->plainappraiseename} para que incluya sus comentarios finales';

$string['overview:button:appraiser:5:return'] = 'Reenviar al appraisee para introducir modificaciones adicionales';
$string['overview:button:appraiser:5:submit'] = 'Enviar appraisal a {$a->plainsignoffname} para su firma';
$string['overview:button:signoff:6:submit'] = 'Firma';
//OVERVIEW CONTENT END

//START FORM
// Introduction Page
$string['appraisee_heading'] = 'Bienvenido a tu Appraisal';
$string['appraisee_welcome'] = 'El appraisal es una oportunidad única para ti y tu appraiser para mantener una conversación útil y en profundidad acerca de tu desempeño y desarrollo.<br /><br />
El propósito de esta nueva herramienta es únicamente el de ayudar en esta conversación, orientando a ambas partes en las reflexiones y acciones a llevar cabo. Podrás acceder a esta plataforma en cualquier momento, lo que te permitirá la posibilidad de consultar lo acordado con tu appraiser en cualquier momento del año. <br /><br />
Haz click en la imagen de tu derecha para ver el mensaje introductorio de Gregory Hodkinson.<br /><br />
Puedes encontrar información adicional acerca del nuevo appraisal  <a href="https://moodle.arup.com/appraisal/essentials" target="_blank">aquí</a>';

// Userinfo.
$string['form:userinfo:intro'] = 'Por favor, rellena los siguientes campos. Algunos apartados vienen completados por defecto a partir de tus datos registrados en TAPS. Si alguno de estos datos predeterminados fuera incorrecto, por favor contacta con el Departamento de Recursos Humanos.';
$string['form:userinfo:name'] = 'Nombre del Appraisee';
$string['form:userinfo:staffid'] = 'Staff ID';
$string['form:userinfo:grade'] = 'Grado';
$string['form:userinfo:jobtitle'] = 'Job title';
$string['form:userinfo:operationaljobtitle'] = 'Operational job title';
$string['form:userinfo:facetoface'] = 'Fecha de reunión de appraisal propuesta';
$string['form:userinfo:facetofaceheld'] = 'Ya se ha mantenido la reunión de appraisal';

// Feedback
$string['form:feedback:email']='Dirección de correo ';
$string['form:feedback:firstname']='Nombre';
$string['form:feedback:lastname']='Apellidos';
$string['form:feedback:language'] = 'Selecciona el idioma para el email de petición de feedback';
$string['feedbackrequests:legend']='*indica colaborador de feedback añadido por el appraiser ';

//Last Year
$string['form:lastyear:nolastyear'] = 'Importante: No tienes appraisals anteriores registrados en el sistema. Por favor, sube tu último appraisal como documento en pdf/word.';
$string['form:lastyear:intro'] = 'En esta sección se hace referencia a lo que se ha logrado durante los últimos 12 meses (proyectos, objetivos cumplidos; en general, cualquier contribución de carácter tangible) y a la manera en la que se ha logrado. En <a href="https://moodle.arup.com/appraisal/guide" target="_blank">la Guía del Appraisal</a> puedes encontrar más información acerca de lo que se debería tratar en esta sección.';
$string['form:lastyear:upload'] = 'Subir appraisal';
$string['form:lastyear:appraiseereview'] = '1.1 Síntesis del appraisee';
$string['form:lastyear:appraiseereviewhelp'] = '<div class="well well-sm"> <em>En general, ¿cómo defines tu rendimiento en términos de proyectos, relaciones internas y externas, desde tu último appraisal? Puedes incluir:</em>
    <ul class="m-b-0">
        <li><em>¿Qué has logrado?</em></li>
        <li><em>¿De qué manera lo has logrado?</em></li>
        <li><em>¿Con quién has colaborado estos últimos 12 meses? ¿Cuál ha sido el resultado de este trabajo conjunto?</em></li>
        <li><em>¿Ha habido algún área en la que los resultados han estado por debajo de lo esperado?</em></li>
        <li><em>Si durante este último año has tenido bajo tu responsabilidad a algún compañero, valora tu propia gestión sobre su actividad ¿ha sido lo suficientemente efectiva?</em></li>
        <li><em>¿En qué medida has utilizado las herramientas electrónicas de las que dispones en tu beneficio? En general, ¿te han ayudado a ser más efectivo?</em></li>
    </ul>
</div>';
$string['form:lastyear:appraiserreview'] = '1.2. Síntesis del appraiser';
$string['form:lastyear:appraiserreviewhelp'] = '<div class="well well-sm">
    <em>En general, ¿cómo describirías la actividad desempeñada por el appraisee desde su último appraisal? Puedes incluir:</em>
    <ul class="m-b-0">
        <li><em>¿Cuál ha sido su progreso en este último año? Si existiese algún área de la actividad desempeñada por el appraisee que no cubriese las expectativas marcadas (proyectos, equipo, clientes, entorno) debería aparecer en esta sección para ser discutido entre ambas partes</em></li>
        <li><em>Resumen del feedback obtenido del appraisee por los colaboradores seleccionados. </em></li>
    </ul>
</div>';
$string['form:lastyear:appraiseedevelopment'] = '1.3. Síntesis del appraisee de su desarrollo';
$string['form:lastyear:appraiseedevelopmenthelp'] = '<div class="well well-sm">
    <em>Describe tu propio desarrollo desde el último appraisal. Puedes incluir:</em>
    <ul class="m-b-0">
        <li><em>¿Cómo han evolucionado tus habilidades y conocimientos marcados a desarrollar desde la última evaluación?</em></li>
        <li><em>¿Hay alguna habilidad y/o conocimiento que consideras aún pendiente por desarrollar?</em></li>
    </ul>
</div>';
$string['form:lastyear:appraiseefeedback'] = '1.4. ¿Existe algún elemento que dificulte o que pueda mejorar tu propio rendimiento o el de tu equipo?';
$string['form:lastyear:appraiseefeedbackhelp'] = '<div class="well well-sm"><em>A rellenar por el appraisee</em></div>';

//Career Direction
$string['form:careerdirection:intro'] = 'El objetivo de esta sección es dar la oportunidad al appraisee de considerar sus aspiraciones laborales a largo plazo y de forma práctica. Para G2-5, se propone un plazo de 1 a 3 años. Para G6-9, la idea es de un plazo de 3 a 5 años.';
$string['form:careerdirection:progress'] = '2.1. Pensando a largo plazo, ¿en qué dirección quieres encaminar tu carrera?';
$string['form:careerdirection:progresshelp'] =
'<div class="well well-sm"> <em>Puedes incluir:</em>
    <ul class="m-b-0">
        <li><em>¿Qué tipo de trabajo te ves desarrollando en el plazo temporal propuesto? ¿A qué nivel de responsabilidad te gustaría desarrollar estas tareas?</em></li>
        <li><em>En general, ¿cuál es el aspecto de tu trabajo qué más te gustaría potenciar?(ej. preferencia por tareas más generalistas/específicas, técnicas/de gestión) ¿A qué querrías dar prioridad en un futuro? (ej. movilidad internacional, mayor responsabilidad, liderazgo de equipos)</em></li>
        <li><em>¿Dónde te gustaría estar desarrollándote profesionalmente? (ej. assignments)</em></li>
    </ul>
</div>';
$string['form:careerdirection:comments'] = '2.2. Síntesis del appraiser';
$string['form:careerdirection:commentshelp'] =
'<div class="well well-sm"> <em>Puedes incluir:</em>
    <ul class="m-b-0">
        <li><em>Analizar las posibilidades reales del cumplimiento de las aspiraciones del appraisee. Se trata de mantener una conversación distendida sobre las verdaderas oportunidades que tendría el appraisee de conseguir sus metas profesionales y las dificultades que se le pondrían plantear</em></li>
        <li><em>¿Qué proyectos, roles u oportunidades profesionales le reportarían la experiencia/conocimientos/habilidades necesarios para ello?</em></li>
    </ul>
</div>';

//Impact Plan
$string['form:impactplan:intro'] = 'En el Agreed Impact Plan, se pretende que el appraisee describa de una forma más personal, amplia y flexible lo que quiere hacer en el próximo año. El appraisee detallará la actividad que quiere desarrollar a nivel individual, pero considerando el impacto que tendrá sobre el equipo y sobre la organización en general. <br /><br /> El Impact Plan debería incluir la forma en la que el appraisee quiere mejorar su trabajo, pero también el de su equipo/departamento/organización/proyectos en los que participe. Se deberían incluir plazos específicos a cumplir, presupuestos o calidad del trabajo a presentar, entre otros. En la <a href="https://moodle.arup.com/appraisal/contribution" target="_blank">Contribution Guide</a> y <a href="https://moodle.arup.com/appraisal/guide" target="_blank">la Guía del Appraisal 2016</a> puedes encontrar sugerencias y ayuda para rellenar este apartado. ';
$string['form:impactplan:impact'] = '3.1. Describe la contribución que quieres aportar sobre los proyectos que lleves a cabo, tus clientes, tu equipo y la organización en su conjunto, para el próximo año:';
$string['form:impactplan:impacthelp'] = '<div class="well well-sm">
    <em>Puedes incluir:</em>
    <ul class="m-b-0">
        <li><em>Las áreas en la que te gustaría enfocarte</em></li>
        <li><em>Para qué quieres enfocarte en dichas áreas</em></li>
        <li><em>Con quién colaborarás durante su desempeño</em></li>
        <li><em>Plazos específicos a cumplir: 3/6/18 meses o más</em></li>
        <li><em>Tu Agreed Impact Plan debería ir en la misma dirección que tu Career Direction, para que te ayude de forma progresiva a alcanzar tus metas profesionales a largo plazo</em></li>
    </ul>
</div>';
$string['form:impactplan:support'] = '3.2. ¿Qué tipo de ayuda necesitarías por parte de Arup para alcanzar lo propuesto en tu Impact Plan?';
$string['form:impactplan:supporthelp'] =
'<div class="well well-sm">
    <em>Puedes incluir:</em>
    <ul class="m-b-0">
        <li><em>La colaboración que necesitarías de otros compañeros</em></li>
        <li><em>Los recursos necesarios (equipamiento, presupuesto, franja temporal)</em></li>
        <li><em>Herramientas (software, hardware)</em></li>
    </ul>
</div>';
$string['form:impactplan:comments'] = '3.3. Síntesis del appraiser';
$string['form:impactplan:commentshelp'] = '<div class="well well-sm"><em>A completar por el appraiser</em></div>';

//Development
$string['form:development:intro'] = 'En el Development Plan, se pretende que el appraisee describa las habilidades, conocimientos o comportamientos que necesita para ayudarle tanto en su Career Direction como en su Agreed Impact Plan. <br /><br />
El appraisee debería plantearse: ¿cómo debo desarrollarme?, ¿qué necesito para desarrollarme? y marcarse un límite temporal.<br /><br />
<div class="well well-sm">En el ámbito del desarrollo personal, en Arup seguimos el principio del “70-20-10”. Esto significa el 70% del desarrollo debería realizarse “on the job” a través de la rutina diaria y la experiencia. El 20% debería realizarse a través del aprendizaje de terceros (ej. mentoring o coaching) y el 10% restante debería adquirirse a través de métodos formales de aprendizaje (ej. cursos presenciales y online). Señalar que estos porcentajes son meramente orientativos
</div>';
$string['form:development:seventy'] = 'Aprendizaje en tu día a día (sobre el 70%)';
$string['form:development:seventyhelp'] =
'<div class="well well-sm">
    <em>Como ejemplos:</em>
    <ul class="m-b-0">
        <li><em>Proyectos asignados </em></li>
        <li><em>Trabajos en equipo</em></li>
        <li><em>Movilidad internacional</em></li>
        <li><em>Análisis de proyectos y feedback de los mismos</em></li>
        <li><em>Project reviews, design charrettes</em></li>
        <li><em>Investigación</em></li>
    </ul>
</div>';
$string['form:development:twenty'] = 'Aprendizaje de terceros (sobre el 20%)';
$string['form:development:twentyhelp'] =
'<div class="well well-sm">
    <em>Como ejemplos:</em>
    <ul class="m-b-0">
        <li><em>Miembros de tu equipo/Departamento</em></li>
        <li><em>Expertos de la materia en cuestión</em></li>
        <li><em>Clientes</em></li>
        <li><em>Conferencias</em></li>
        <li><em>Coaching</em></li>
        <li><em>Mentoring</em></li>
        <li><em>Colaboradores</em></li>
    </ul>
</div>';
$string['form:development:ten'] = 'Aprendizaje a través de métodos formales, presenciales y online (sobre 10%)';
$string['form:development:tenhelp'] =
'<div class="well well-sm">
    <em>Como ejemplos:</em>
    <ul class="m-b-0">
        <li><em>Cursos presenciales/semipresenciales</em></li>
        <li><em>Cursos a distancia</em></li>
        <li><em>Clases virtuales</em></li>
        <li><em>Moodle</em></li>
    </ul>
</div>';

//Summaries
$string['form:summaries:intro'] = 'La finalidad de este apartado es sintetizar el contenido del presente formulario para que sirva de referencia  en la toma de futuras decisiones sobre el appraisee (ej. decisiones salariales, promocionales, de desarrollo)';
$string['form:summaries:appraiser'] = '5.1. Síntesis general del appraiser';
$string['form:summaries:appraiserhelp'] = '<div class="well well-sm">
    <em>El appraiser deberá resumir de forma clara y concisa todo lo tratado durante la realización de este appraisal (tanto la revisión del rendimiento del año pasado, como las secciones de Career Direction y el Agreed Impact Plan).  Ésta síntesis deberá redactarse de forma sencilla, con el objetivo de agilizar la toma de decisiones salariales, de promoción y de desarrollo que se tomen sobre el appraisee.</em>
</div>';
$string['form:summaries:recommendations'] = '5.2. Actuaciones acordadas';
$string['form:summaries:recommendationshelp'] = '<div class="well well-sm">
    <em>A completar por el appraiser</em><br/>
    <em>¿Cuáles son las siguientes actuaciones a llevar a cabo? Como ejemplos:</em>
    <ul>
        <li><em>Acciones de desarrollo profesional</em></li>
        <li><em>Assignments</em></li>
        <li><em>Herramientas de apoyo para el rendimiento del appraisee</em></li>
    </ul>
</div>';
$string['form:summaries:appraisee'] = '5.3. Comentarios adicionales del appraisee';
$string['form:summaries:appraiseehelp'] = '<div class="well well-sm"><em>A completar por el appraisee</em></div>';
$string['form:summaries:signoff'] = '5.4. Síntesis final';
$string['form:summaries:signoffhelp'] = '<div class="well well-sm"><em>A completar por el Group Leader o persona designada para ello.</em></div>';

// Checkins
$string['appraisee_checkin_title'] = 'Section 6. Check-in';
$string['checkins_intro'] = 'A lo largo del año, se espera que el appraiser y el appraisee revisen lo acordado en este formulario. La finalidad de esta sección es que todas las revisiones que se hagan del presente appraisal queden aquí reflejadas. Como en años anteriores, la revisión del appraisal deberá hacerse, como mínimo, una vez al año.';

// Give Feedback.
$string['feedback_header'] = 'Se aporta feedback de {$a->appraisee_fullname}';
$string['confidential_label'] = 'Confidential';
$string['confidential_label_text'] = 'Marca la casilla para mantener tus comentarios ocultos. Si no marcas la casilla, tus comentarios serán notificados al appraisee.';

$string['feedback_send_copy'] = 'Email me a copy';
$string['feedback_intro'] = 'Conforme a lo previamente acordado entre tu appraiser y tú, notifica la petición de feedback a tus colaboradores.<br/><br/>  Estos colaboradores pueden ser tanto internos como externos. Para colaboradores internos (ej. supervisores, colegas de proyecto junior o senior) y externos (ej. clientes) selecciona personas con la que hayas trabajado activamente durante este año y puedan aportar una visión realista y constructiva de tu desempeño.<br/><br/>Lo indicado sería una perspectiva lo más amplia y heterogénea posible de tu rendimiento (lo que llamamos feedback “360 grados”). Para cualquier consulta, no dudes en acudir al Departamento de RRHH. <br /><br /><div class="alert alert-danger"> Observaciones: los comentarios de tus colaboradores de feedback se publicarán aquí tras tu reunión de appraisal, a no ser que decidan conservárselo.</div>';

// FEEDBACK EMAIL - SENT BY APPRAISEE

$string['email:subject:appraiseefeedback'] = 'Solicitar feedback para mi appraisal';
$string['email:body:appraiseefeedbackmsg'] =
'<p>Estimado/a <span class="placeholder bind_firstname">{{firstname}}</span>,</p>
<p>Mi appraisal será proximamente. Por ello y ya que hemos tenido la oportunidad de trabajar de forma conjunta durante este último año, apreciaría enormemente feedback por tu parte tanto en las áreas en las que hayas podido comprobar mi valía como en aquellas otras en las que consideres hubiera podido ser más eficiente. Si estás de acuerdo, por favor haz click en el link que se adjunta para ser parte de mis colaboradores de feedback.</p>
<p>Mi appraisal se llevará a cabo el <span class="placeholder">{{held_date}}</span>], por ello necesitaría que registrases mi feedback antes de esa fecha.</p>
<p>Además, tu feedback se me hará saber tras la reunión del appraisal, a no ser que marques la casilla de confidencialidad antes de registrar el feedback.</p>
<p>A continuación puedes encontrar comentarios adicionales de <span class="placeholder">{{appraisee_fullname}}</span>:<br /> <span>{{emailtext}}</span></p>
<p>Muchas gracias,<br />
<span class="placeholder">{{appraisee_fullname}}</span></p>';


//FEEDBACK EMAIL - SENT BY APPRAISER
$string['email:subject:appraiserfeedback'] = 'Solicitar feedback para el appraisal de {{appraisee_fullname}}';
$string['email:body:appraiserfeedbackmsg'] = '<p>Estimado/a <span class="placeholder bind_firstname">{{firstname}}</span>,</p>
<p>Actualmente me encuentro realizando el appraisal de <span class="placeholder">{{appraisee_fullname}}</span>. Ya que has tenido la oportunidad de trabajar de forma conjunta con esta persona, apreciaría enormemente feedback por tu parte tanto en las áreas en las que hayas podido comprobar su valía como en las que consideres su rendimiento podría haber sido mayor. Si estás de acuerdo, por favor haz click en el link que se adjunta para aportar tu feedback. </p>
<p>Este appraisal se llevará a cabo el <span class="placeholder">{{held_date}}</span>, por ello necesitaría tu respuesta antes de esta fecha.</p>
<p>Tu feedback se compartirá con <span class="placeholder">{{appraisee_fullname}}</span> tras la reunión del appraisal, a no ser que marques la casilla de confidencialidad antes de registrar el feedback.</p>
<p>A continuación puedes encontrar comentarios adicionales de <span class="placeholder">{{appraiser_fullname}}</span>:<br /> <span>{{emailtext}}</span></p>
<p>Muchas gracias,<br />
<span class="placeholder">{{appraiser_fullname}}</span></p>';

// PDF strings
$string['pdf:form:summaries:appraisee'] = 'Comentarios adicionales del appraisee';
$string['pdf:form:summaries:appraiser'] = 'Síntesis general del appraiser';
$string['pdf:form:summaries:signoff'] = 'Síntesis final';
$string['pdf:form:summaries:recommendations'] = 'Actuaciones acordadas';

//END FORM

//START SPREADSHEET STRINGS

$string['startappraisal'] = 'Empezar Appraisal';
$string['continueappraisal'] = 'Continuar Appraisal';
$string['appraisee_feedback_edit_text'] = 'Editar';
$string['appraisee_feedback_resend_text'] = 'Reenviar';
$string['appraisee_feedback_view_text'] = 'Ver';
$string['feedback_setface2face'] = 'Tienes que fijar una fecha para la reunión de appraisal antes de poder añadir a tus colaboradores de feedback. Puedes encontrar información adicional en la sección Appraisee Info';
$string['feedback_comments_none'] = '<em>Sin comentarios adicionales</em>';
$string['actionrequired'] = 'Acciones requeridas';
$string['actions'] = 'Acciones ';
$string['appraisals:archived'] = 'Appraisals archivados';
$string['appraisals:current'] = 'Appraisal actual';
$string['appraisals:noarchived'] = 'No tienes appraisals archivados';
$string['appraisals:nocurrent'] = 'No tienes appraisals actuales';
$string['comment:adddots'] = 'Añadir comentarios…';
$string['comment:addingdots'] = 'Añadiendo…';
$string['comment:addnewdots'] = 'Añadir nuevo comentario…';
$string['comment:showmore'] = '<i class="fa fa-plus-circle"></i> Mostrar más';

$string['comment:status:0_to_1'] = '{$a->status} - El appraisal ha sido creado pero no se ha empezado';
$string['comment:status:1_to_2'] = '{$a->status} - El appraisal ha sido empezado por el appraisee';
$string['comment:status:2_to_3'] = '{$a->status} - El appraisal ha sido enviado para su revisión por el appraiser';
$string['comment:status:3_to_2'] = '{$a->status} - El appraisal ha sido enviado al appraisee';
$string['comment:status:3_to_4'] = '{$a->status} - Appraisal a la espera de los comentarios del appraisee';
$string['comment:status:4_to_3'] = '{$a->status} - El appraisal ha sido enviado al appraiser';
$string['comment:status:4_to_5'] = '{$a->status} - A la espera de que el appraiser envíe el appraisal para su confirmación';
$string['comment:status:5_to_4'] = '{$a->status} - El appraisal ha sido enviado al appraisee';
$string['comment:status:5_to_6'] = '{$a->status} - Appraisal enviado  para su confirmación';
$string['comment:status:6_to_7'] = '{$a->status} - El appraisal ha sido completado';

$string['comment:updated:appraiser'] = '{$a->ba} cambió el appraiser de {$a->oldappraiser} a {$a->newappraiser}.';
$string['comment:updated:signoff'] = '{$a->ba} cambió la confirmación del appraisal de {$a->oldsignoff} a {$a->newsignoff}.';
$string['index:togglef2f:complete'] = 'Marca F2F para Omitir';
$string['index:togglef2f:notcomplete'] = 'Marca F2F para No Omitir';
$string['index:notstarted'] = 'Sin empezar';
$string['index:notstarted:tooltip'] = 'El appraisee aun no ha comenzado su appraisal; podrás acceder al mismo una vez empezado ';
$string['index:printappraisal'] = 'Descargar Appraisal';
$string['index:printfeedback'] = 'Descargar Feedback';
$string['index:start'] = 'Empezar Appraisal';
$string['index:toptext:appraisee'] = 'El siguiente interfaz te muestra tu appraisal actual y todos los archivados. Puedes acceder a tu appraisal actual a través del enlace situado bajo el menú desplegable de Acciones. Puedes descargar los appraisals archivados pinchando en el botón Descargar Appraisal. ';
$string['index:toptext:appraiser'] = 'El siguiente interfaz te muestra cualquier appraisal actual o archivado en los cuales has sido appraiser. Puedes acceder a cualquiera de tus appraisals actuales a través del enlace situado bajo el menú desplegable de Acciones. Puedes descargar también la información sobre feedback, que no estará disponible para el appraisee hasta que se haya mantenido la reunión del appraisal. Cualquier feedback que haya sido clasificado como confidencial permanecerá oculto a lo largo del todo el proceso del appraisal. Puedes descargar los appraisals archivados pinchando en el botón Descargar Appraisal.';
$string['index:toptext:groupleader'] = 'El siguiente interfaz muestra todos los appraisals actuales y archivados de tu cost centre. Puedes consultar/descargar cualquiera de los appraisals actuales a través del enlace situado bajo el menú desplegable de Acciones. Puedes descargar cualquier appraisal archivado pinchando en el botón Descargar Appraisal';
$string['index:toptext:signoff'] = 'El siguiente interfaz muestra todos los appraisals actuales y archivados de los cuáles eres el encargado de confirmación. Puedes acceder a cualquiera de tus appraisals actuales a través del enlace situado bajo el menú desplegable de Acciones. Puedes descargar cualquiera a de los appraisals archivados pinchando en el botón Descargar Appraisal.';
$string['index:view'] = 'Ver Appraisal';

// Time Variables

$string['timediff:now'] = 'Ahora';
$string['timediff:second'] = '{$a} segundo';
$string['timediff:seconds'] = '{$a} segundos';
$string['timediff:minute'] = '{$a} minuto';
$string['timediff:minutes'] = '{$a} minutos';
$string['timediff:hour'] = '{$a} hora';
$string['timediff:hours'] = '{$a} horas';
$string['timediff:day'] = '{$a} día';
$string['timediff:days'] = '{$a} días';
$string['timediff:month'] = '{$a} mes';
$string['timediff:months'] = '{$a} meses';
$string['timediff:year'] = '{$a} año';
$string['timediff:years'] = '{$a} años';


$string['error:togglef2f:complete'] = 'No se puede marcar F2 para Omitir';
$string['error:togglef2f:notcomplete'] = 'No se puede marcar F2 para No Omitir';
$string['appraisee_feedback_email_success'] = 'Tu email ha sido enviado';
$string['appraisee_feedback_email_error'] = 'Fallo en el envío del email';
$string['appraisee_feedback_invalid_edit_error'] = 'Cuenta de correo no válida';
$string['appraisee_feedback_inuse_edit_error'] = 'Cuenta de correo ya en uso ';
$string['appraisee_feedback_inuse_email_error'] = 'Cuenta de correo ya en uso ';
$string['appraisee_feedback_resend_success'] = 'Email reenviado con éxito';
$string['appraisee_feedback_resend_error'] = 'Fallo en el reenvío del email';
$string['form:add'] = 'Añadir';
$string['form:language'] = 'Idioma';

// ADD FEEDBACK
$string['form:addfeedback:notfound']='No se ha encontrado ninguna solicitud de feedback ';
$string['form:addfeedback:sendemailbtn']='Enviar feedback';
$string['form:addfeedback:closed']='La pestaña para enviar el feedback ya está cerrada';
$string['form:addfeedback:submitted']='Feedback enviado';
$string['form:addfeedback:addfeedback']='Por favor, describe al menos tres áreas en las cuáles hayas podido valorar el rendimiento del appraisee en los últimos 12 meses. Por favor, aporta ejemplos de áreas en las que su rendimiento debería haber sido más efectivo. Se espera una crítica constructiva que pueda ayudar a appraiser y appraisee en su reunión. ';

//Feedback ALERT MESSAGES
$string['form:addfeedback:alert:cancelled'] = 'Envío cancelado, el feedback del appraisal no ha sido enviado';
$string['form:addfeedback:alert:error'] = 'Lo siento, ha habido un error durante el envío del feedback del appraisal';
$string['form:addfeedback:alert:saved'] = 'Gracias, el feedback del appraisal ha sido enviado con éxito';

$string['form:feedback:alert:cancelled'] = 'Fallo en el envío, tu feedback no ha podido ser enviado';
$string['form:feedback:alert:error'] = 'Lo siento, ha habido un error en el envío de tu petición de feedback';
$string['form:feedback:alert:saved'] = 'Tu petición de feedback ha sido enviada con éxito';


$string['form:lastyear:nolastyear'] = 'Importante: No tienes ningún appraisal registrado en la plataforma. Por favor, sube tu último appraisal como documento en word/pdf';
$string['form:lastyear:file'] = '<strong>El appraisee ha subido un archivo para su revisión <a href="{$a->path}" target="_blank">{$a->filename}</a></strong>';
$string['form:lastyear:cardinfo:developmentlink'] = 'Desarrollo del año anterior';
$string['form:lastyear:cardinfo:performancelink'] = 'Desempeño del año anterior';

//Feedback requests
$string['feedbackrequests:description'] = 'El siguiente interfaz muestra todas las peticiones de feedback pendientes que tienes y te permite acceder a cualquiera de los feedbacks que ya hubieses registrado en el pasado';
$string['feedbackrequests:outstanding'] = 'Peticiones pendientes';
$string['feedbackrequests:norequests'] = 'No hay peticiones pendientes';
$string['feedbackrequests:completed'] = 'Peticiones completadas';
$string['feedbackrequests:nocompleted'] = 'Peticiones no completadas';
$string['feedbackrequests:th:actions'] = 'Acciones';
$string['feedbackrequests:emailcopy'] = 'Mandar una copia por correo electrónico';
$string['feedbackrequests:submitfeedback'] = 'Enviar feedback';
$string['feedbackrequests:confidential'] = 'confidencial';
$string['feedbackrequests:nonconfidential'] = 'no confidencial';
$string['feedbackrequests:received:confidential'] ='Recibido (oculto)';
$string['feedbackrequests:received:nonconfidential']='Recibido';
$string['feedbackrequests:paneltitle:confidential']	='Feedback (oculto)';
$string['feedbackrequests:paneltitle:nonconfidential']='Feedback';


$string['email:subject:myfeedback'] = 'Tu feedback para {{appraisee}}';
$string['email:body:myfeedback'] = '<p>Estimado/a {{recipient}},</p>
<p>Has enviado el sguiente {{confidential}}  feedback de {{appraisee}}:</p> <div>{{feedback}}</div>';

$string['success:checkin:add'] = 'Añadido con éxito al registro';
$string['error:checkin:add'] = 'Fallo al añadirlo al registro';
$string['error:checkin:validation'] = 'Texto requerido';
$string['checkin:deleted'] = 'Eliminar registro';
$string['checkin:delete:failed'] = 'Fallo al eliminar registro';
$string['checkin:update'] = 'Actualizar';
$string['checkin:addnewdots'] = 'Registrar…';

// Checkins
$string['checkins_intro'] = 'A lo largo del año, se espera que el appraiser y el appraisee revisen lo acordado en este formulario. La finalidad de esta sección es que todas las revisiones que se hagan del presente appraisal queden aquí reflejadas. Como en años anteriores, la revisión del appraisal deberá hacerse, como mínimo, una vez al año. ';
