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
$string['alert:language:notdefault'] ='Waarschuwing: je gebruikt niet de standaard taal voor dit appraisal. Zorg er alsjeblieft voor dat je de meest geschikte taal kiest voor de betrokkenen bij dit appraisal.';


// START FORM
// Introduction Page
$string['appraisee_heading'] = 'Welkom bij het online beoordeling systeem';

// Userinfo.
$string['form:userinfo:intro'] = 'Vul alsjeblieft de gegevens hieronder in. Sommige velden zijn al ingevuld door middel van TAPS data. Als de informatie in deze velden niet correct is, neem dan contact op met de HR afdeling. ';
$string['form:userinfo:name'] = 'Appraisee naam';
$string['form:userinfo:staffid'] = 'Personeelsnummer';
$string['form:userinfo:grade'] = 'Grade';
$string['form:userinfo:jobtitle'] = 'Functietitel';
$string['form:userinfo:operationaljobtitle'] = 'Operationele functietitel
';
$string['form:userinfo:facetoface'] = 'Voorgestelde face to face datum';
$string['form:userinfo:facetofaceheld'] = 'Face to face bijeenkomst datum';

// Feedback
$string['form:feedback:email']='Email adres';
$string['form:feedback:firstname']='Voornaam';
$string['form:feedback:lastname']='Achternaam';
$string['form:feedback:language'] = 'Selecteer een taal voor je feedback email';
$string['feedbackrequests:legend']='*notities gemaakt door appraiser ';

// Last Year Review
$string['form:lastyear:intro'] = 'In deze sectie leggen zowel de beoordelaar als de beoordeelde vast wat er de laatste twaalf maanden is bereikt en hoe dat is ingevoerd. Klik <a href="https://moodle.arup.com/appraisal/guide" target="_blank">hier</a> voor meer informatie over hoe dit vastgelegd kan worden.';
$string['form:lastyear:upload'] = 'appraisal uploaden';
$string['form:lastyear:appraiseereview'] = '1.1 Overzicht van de beoordeelde over de resultaten van afgelopen jaar.';
$string['form:lastyear:appraiseereviewhelp'] =
'<div class="well well-sm"><em>Wat heb je in het algemeen bereikt bij projecten, bij anderen en cliënten sinds je laatste beoordeling?</em>
    <ol class="m-b-0">
        <li><em>Hoe heb je samengewerkt en informatie en kennis gedeeld? Wat was het resultaat?</em></li>
        <li><em>Waren er resultaten beneden de verwachting?</em></li>
        <li><em>Wanneer je verantwoordelijk bent voor anderen, heb je die mensen op de juiste manier geleid zowel qua hun resultaten als qua hun gedrag, zowel in goede zin als slechte zin?</em></li>
        <li><em>Hoe heb je technologie gebruikt om jezelf effectiever te maken?</em></li>
    </ol>
</div>';
$string['form:lastyear:appraiserreview'] = '1.2 Overzicht van de beoordelaar over de resultaten van afgelopen jaar.';
$string['form:lastyear:appraiserreviewhelp'] =
'<div class="well well-sm"><em>Geef commentaar op het overzicht van de resultaten door de beoordelaar sinds de laatste beoordeling.</em>
    <ol class="m-b-0">
        <li><em>Welke vooruitgang is geboekt?</em></li>
        <li><em>Geef een samenvatting van alle feedback die de beoordelaar heeft ontvangen van de mensen die hebben bijgedragen in het proces.</em></li>
    </ol>
    <em>Als er iets bij de resultaten of het gedrag niet overeenkomt met de verwachtingen, dan <strong>moet</strong> dit besproken worden en vastgelegd worden in deze sectie. Dit kan gaan over projecten, het team, klanten of in het algemeen anderen.</em>
</div>';
$string['form:lastyear:appraiseedevelopment'] = '1.3 Overzicht door de beoordeelde over de eigen ontwikkeling in het afgelopen jaar';
$string['form:lastyear:appraiseedevelopmenthelp'] =
'<div class="well well-sm"><em>Bespreek de eigen ontwikkeling sinds de laatste beoordeling:</em>
    <ol class="m-b-0">
        <li><em>Hoe heb je je eigen kennis, vaardigheden en gedrag verbeterd?</em></li>
        <li><em>Wat zou er afgelopen jaar verbeterd zijn, wat nog niet gerealiseerd is?</em></li>
    </ol>
</div>';
$string['form:lastyear:appraiseefeedback'] = '1.4 Is er iets dat invloed heeft of zou kunnen hebben op de resultaten van jou of het team?';
$string['form:lastyear:appraiseefeedbackhelp'] = '<div class="well well-sm"><em>In te vullen door de beoordeelde.</em></div>';



// Career Direction
$string['form:careerdirection:intro'] = 'De doelstelling van deze sectie is de beoordeelde de mogelijkheid te bieden het eigen carrière perspectief te beschrijven zodat dit op een praktische manier met de beoordelaar kan worden besproken. Voor junior medewerkers is de tijdshorizon waarschijnlijk 1-3 jaar. Voor senior medewerkers is dit waarschijnlijk 3-5 jaar.';
$string['form:careerdirection:progress'] = '2.1 Hoe zou je willen dat je carriere verloopt?';
$string['form:careerdirection:progresshelp'] =
'<div class="well well-sm"><em>Neem in overweging:</em>
    <ol class="m-b-0">
        <li><em>Wat voor soort werk ben je in geïnteresseerd en welk niveau van verantwoordelijkheid zou je willen dragen?</em></li>
        <li><em>Wat is de komende jaren belangrijk bij je werk bijvoorbeeld ruimte, diepte, specialisatie, generalisatie, flexibiliteit, eigen invulling, verantwoordelijkheid voor mensen etc.?</em></li>
        <li><em>Waar zou je gestationeerd willen zijn?</em></li>
    </ol>
</div>';
$string['form:careerdirection:comments'] = '2.2 Commentaar van beoordelaar';
$string['form:careerdirection:commentshelp'] =
'<div class="well well-sm"><em>Neem in overweging:</em>
    <ol class="m-b-0">
        <li><em>Hoe realistisch, uitdagend en ambitieus zijn de verwachtingen van de beoordeelde?</em></li>
        <li><em>Wat zijn eventuele rollen, projecten en ander werk die de ervaring, vaardigheden en ontwikkeling van het juiste gedrag zouden ondersteunen?</em></li>
    </ol>
</div>';

// Agreed Impact Plan
$string['form:impactplan:intro'] = 'Het overeengekomen impact plan geeft aan hoe de beoordeelde het verschil wil maken in het komende jaar op het werk dat gedaan wordt en het resultaat voor het bedrijf. Dit plan moet aangeven hoe de beoordeelde zijn werk wil verbeteren of hoe het project/ team/ kantoor/ groep verbeterd kan worden. In de praktijk betekent dit dat er specifieke aanbevelingen worden gedaan over tijdslijnen, kwaliteit, budget, innovatie/ontwerp en effect op mensen, klanten en het werk in het algemeen.<br /><br /> De <a href="https://moodle.arup.com/appraisal/contribution" target="_blank">Contribution Guide</a> en de  <a href="https://moodle.arup.com/appraisal/guide" target="_blank">Guide To Appraisal</a> geven aanbevelingen hoe deze verbeteringen gerealiseerd kunnen worden.';
$string['form:impactplan:impact'] = '3.1 Beschrijf het effect dat je wil hebben op je projecten, je klanten en het bedrijf in het komende jaar.';
$string['form:impactplan:impacthelp'] =
'<div class="well well-sm"><em>In je beschrijving kan je aangeven:</em>
    <ol class="m-b-0">
        <li><em>Aandachtsgebieden</em></li>
        <li><em>Waarom deze belangrijk zijn</em></li>
        <li><em>Hoe je die wil bereiken</em></li>
        <li><em>Met wie je het gaat doen</em></li>
        <li><em>De ingeschatte tijdsspanne:  3/6/12/18 maanden of langer</em></li>
        <li><em>Hoe ondersteunt het impact plan je gewenste carrière</em></li>
    </ol>
</div>';
$string['form:impactplan:support'] = '3.2 Welke ondersteuning heb je hierbij nodig van Arup?';
$string['form:impactplan:supporthelp'] =
'<div class="well well-sm"><em>Neem in overweging:</em>
    <ol class="m-b-0">
        <li><em>Assistentie van anderen</em></li>
        <li><em>Supervisie</em></li>
        <li><em>Middelen (tijd, geld, materiaal)</em></li>
        <li><em>Persoonlijke ontwikkeling</em></li>
        <li><em>Hulpmiddelen (software, hardware)</em></li>
    </ol>
</div>';
$string['form:impactplan:comments'] = '3.3 Beoordelaar commentaar';
$string['form:impactplan:commentshelp'] = '<div class="well well-sm"><em>In te vullen door beoordelaar</em></div>';

//Development Plam
$string['form:development:intro'] = 'Het ontwikkelplan geeft aan welke veranderingen in persoonlijke vaardigheden, kennis en gedrag nodig zijn om de beoordeelde carrière voortgang en overeengekomen impact plan te ondersteunen.<br /><br />
Hoe moet je je in de komende 12-18 maanden ontwikkelen om het doel te bereiken. Welke ondersteuning heb je daarbij nodig en hoe ga je deze ontwikkeling aanpakken.<br /><br />
<div class="well well-sm">Bij Arup wordt het principe van “70-20-10” gebruikt. Dit betekent dat voor de meeste mensen 70% van de ontwikkeling tijdens het werk plaats vindt. 20% vindt plaats via anderen bijvoorbeeld via coaching. De resterende 10% komt via training zoals cursussen of elearning. De genoemde percentages zijn natuurlijk een indicatie, maar geven een indruk hoe geleerd wordt binnen Arup.</div>';
$string['form:development:seventy'] = 'Leren en ontwikkelen dat tijdens het werk plaats vindt (ongeveer 70%)';
$string['form:development:seventyhelp'] =
'<div class="well well-sm"> <em>Bijvoorbeeld:</em>
    <ol class="m-b-0">
        <li><em>Projecten waarbinnen gewerkt wordt</em></li>
        <li><em>Teams waarbinnen gewerkt wordt</em></li>
        <li><em>Mobiliteit</em></li>
        <li><em>Discussie over werk en bijbehorende feedback</em></li>
        <li><em>Reviews van projecten en ontwerp charette</em></li>
        <li><em>Lezen</em></li>
        <li><em>Onderzoek doen </em></li>
    </ol>
</div>';
$string['form:development:twenty'] = 'Leren van anderen (ongeveer 20%)';
$string['form:development:twentyhelp'] =
'<div class="well well-sm"> <em>Bijvoorbeeld:</em>
    <ol class="m-b-0">
        <li><em>Team leden</em></li>
        <li><em>Experts</em></li>
        <li><em>Klanten</em></li>
        <li><em>Mensen waarmee wordt samengewerkt</em></li>
        <li><em>Conferenties</em></li>
        <li><em>Coaching</em></li>
        <li><em>Mentoring</em></li>
    </ol>
</div>';

$string['form:development:ten'] = 'Leren via cursussen (face to face of online)';
$string['form:development:tenhelp'] =
'<div class="well well-sm"><em>Bijvoorbeeld:</em>
    <ul class="m-b-0">
        <li><em>Groep cursussen</em></li>
        <li><em>Elearning</em></li>
        <li><em>Virtuele groepen (Webinars etc.)</em></li>
    </ul>
</div>';

//Summaries
$string['form:summaries:intro'] = 'De doelstelling van deze sectie is om de beoordeling samen te vatten zodat er later naar gerefereerd kan worden bij salaris, promotie of ontwikkelingsgesprekken en beslissingen.';
$string['form:summaries:appraiser'] = '5.1 Samenvatting van de beoordelaar van de bereikte resultaten';
$string['form:summaries:appraiserhelp'] = '<div class="well well-sm">
    <em>De beoordelaar moet een duidelijke en samenhangende samenvatting geven die eenvoudig kan worden begrepen door diegenen die in de toekomst betrokken zijn bij salaris/ promotie/ ontwikkeling gesprekken en beslissingen. De beoordelaar moet specifiek aangeven waarin de resultaten minder dan wel meer waren dan de verwachtingen.</em>
</div>';
$string['form:summaries:recommendations'] = '5.2 Overeengekomen acties';
$string['form:summaries:recommendationshelp'] =
'<div class="well well-sm"><em>In te vullen door beoordelaar</em><br/><em>Wat moet er gebeuren. Bijvoorbeeld:</em>
    <ol>
        <li><em>Ontwikkeling</em></li>
        <li><em>Mobiliteit</em></li>
        <li><em>Opdrachten</em></li>
        <li><em>Ondersteuning bij de uitvoering</em></li>
    </ol>
</div>';
$string['form:summaries:appraisee'] = '5.3 Commentaar van de beoordeelde';
$string['form:summaries:appraiseehelp'] = '<div class="well well-sm"><em>In te vullen door de beoordeelde</em></div>';
$string['form:summaries:signoff'] = '5.4 Af te tekenen samenvatting';
$string['form:summaries:signoffhelp'] = '<div class="well well-sm"><em>In te vullen door de team leider of degene die moet af tekenen.</em></div>';

// Checkins
$string['checkins_intro'] = 'Het wordt verwacht dat beoordeelde en beoordelaar de voortgang ten opzichte van het impact plan, het ontwikkel plan en de bijbehorende acties en resultaten bespreken. Zowel de beoordeelde als de beoordelaar kunnen deze sectie gebruiken om de voortgang vast te leggen. De frequentie van de bespreking staat vrij, maar het is aanbevolen om het minimaal een keer per jaar te doen.';

// ADD FEEDBACK
$string['form:addfeedback:notfound']='Geen feedback verzoek gevonden';
$string['form:addfeedback:sendemailbtn']='Verstuur appraisal feedback';
$string['form:addfeedback:closed']='Het formulier om je feedback te geven is nu afgesloten';
$string['form:addfeedback:submitted']='Feedback verzonden';

// Feedback Contribution
$string['feedback_addfeedback'] = 'Beschrijf drie gebieden waarin de bijdrage van de beoordeelde in de laatste 12 positief wordt gewaardeerd. Geef dan ook drie gebieden waarin de bijdrage effectiever zou kunnen zijn. Wees eerlijk en geef constructieve feedback want dat maakt het eenvoudiger voor de beoordeelde om de naar voren gebrachte onderwerpen aan te pakken.';
$string['confidential_label_text'] = 'Hierin kan worden aangegeven of de informatie vertrouwelijk behandeld moet worden. Wanneer dit niet is aangegeven wordt de informatie gedeeld met de beoordeelde.';

// Feedback request email - sent by APPRAISEE
$string['email:subject:appraiseefeedback'] = 'Verzoek voor feedback voor mijn appraisal';

// Feedback request email - sent by APPRAISER
$string['email:subject:appraiserfeedback'] = 'Verzoek voor feedback voor {{appraisee_fullname}}';

// START OVERVIEW CONTENT

// APPRAISEE: Overview page Content
$string['overview:content:appraisee:2'] = 'Begin met het invullen van de beoordeling.<br /><br />
<strong>Volgende stappen:</strong>
    <ol class="m-b-20">
        <li>Voer de datum van het gesprek in</li>
        <li>Vraag om feedback</li>
        <li>Reflecteer op en geef commentaar over de resultaten en ontwikkeling tijdens het afgelopen jaar</li>
        <li>Vul het carrière pad, het impact en ontwikkel plan in om gedurende de beoordeling bijeenkomst te bespreken</li>
    <li>Deel de draft met {$a->styledappraisername}, je beoordelaar.</li>
    </ol>
Deel de draft met je beoordelaar tenminste een <strong><u>week</u></strong> voor de bijeenkomst. Je kan de draft nog steeds aanpassen nadat die is gedeeld.<br /><br />
<div class="alert alert-danger" role="alert">Je beoordelaar kan de draft beoordeling niet zien voordat je die met hem/haar gedeeld hebt.</div>';
// ERROR: translated word "note" is missing

$string['overview:content:appraisee:2:3'] = 'De beoordelaar heeft gevraagd om enkele veranderingen aan te brengen in de draft beoordeling.<br /><br />
<strong>Volgende stappen: </strong>
    <ol class="m-b-20">
        <li>Maak de door de beoordelaar gewenste aanpassingen (gebruik de activiteiten log voor meer informatie over wat er gewenst is).</li>
        <li>Deel je draft beoordeling met {$a->styledappraisername}.</li>
    </ol>';

// ERROR: translated word "note" is missing
$string['overview:content:appraisee:3:4'] = 'Je hebt de beoordeling terug gestuurd naar {$a->styledappraisername} om veranderingen aan te brengen.<br /><br /> Je ontvangt een bericht wanneer de beoordeling is aangepast en klaar is om gereviewed te worden.<br /><br /> <div class="alert alert-danger" role="alert">Je kan de beoordeling blijven aanpassen wanneer deze bij de beoordelaar ligt maar het is verstandig wijzigingen te markeren in de activiteiten log.</div>';
// ERROR: translated word "note" is missing
$string['overview:content:appraisee:4'] = '{$a->styledappraisername} heft zijn commentaar toegevoegd en teruggestuurd.<br /><br />
<strong>Volgende stappen:</strong>
    <ol class="m-b-20">
        <li>Review het commentaar en de samenvatting van je beoordelaar. Wanneer nodig stuur de beoordeling terug naar de beoordelaar wanneer je veranderingen aangebracht wil hebben</li>
        <li>Leg je commentaar vast in de sectie samenvatting </li>
        <li>Stuur je beoordeling naar je beoordelaar voor een laatste review voordat hij wordt afgetekend. Als de beoordeling is afgetekend kunnen er geen wijzigingen meer worden aangebracht.</li>
    </ol>
<div class="alert alert-danger" role="alert"> Je mag de secties aanpassen zolang de beoordeling nog niet is afgetekend maar het is verstandig de wijzigingen te markeren in de activiteiten log.</div>';
// ERROR: translated word "note" is missing
// ERROR: type "heft" --> "heeft" ?

// ERROR: translated word "note" is missing
$string['overview:content:appraisee:6'] = 'Je beoordeling is naar {$a->styledsignoffname} gestuurd om te reviewen en een samenvatting te schrijven.<br /><br />
<div class="alert alert-danger" role="alert">De beoordeling is nu afgesloten en er kunnen geen wijzigingen meer worden aangebracht.</div>';
// ERROR: translated word "note" is missing
$string['overview:content:appraisee:7'] = 'Je beoordeling is nu afgesloten. Je kan een PDF kopie op ieder moment downloaden door op “download appraisal” te klikken.';

// APPRAISER: Overview page Content.
$string['overview:content:appraiser:2'] = 'De beoordeling wordt opgesteld door {$a->styledappraiseename}. Je krijgt een bericht wanneer de beoordeling klaar is voor een review.<br /><br />
<div class="alert alert-danger" role="alert">Het is niet mogelijk om het appraisal te bekijken voordat het is gedeeld met je.</div>';
$string['overview:content:appraiser:2:3'] = 'Je hebt de beoordeling terug gestuurd naar {$a->styledappraiseename} om aan te passen. Je krijgt een bericht wanneer de draft beoordeling is aangepast en opnieuw gereviewed kan worden.<br /><br />
<div class="alert alert-danger" role="alert">Je kan nog steeds aanpassingen maken in de verschillende secties.</div>';

// ERROR: translated word "note" is missing
$string['overview:content:appraiser:5'] = '{$a->styledappraiseename} heeft zijn laatste commentaar toegevoegd.<br /><br />
<strong>Volgende stappen:</strong>
    <ol class="m-b-20">
        <li>Review de laatste versie van de beoordeling voordat hij wordt afgetekend</li>
        <li>Stuur de beoordeling naar (naam verantwoordelijke voor aftekenen) om te laten reviewen en eventueel commentaar toe te voegen</li>
        <li>Jij en de beoordeelde worden bericht wanneer de beoordeling is afgetekend</li>
    </ol>
<div class="alert alert-danger" role="alert"> Je kan geen wijzigingen in de beoordeling meer aanbrengen behalve wanneer je de beoordeling daarna terug stuurt naar de beoordeelde.</div>';
// ERROR: translated word "note" is missing
$string['overview:content:appraiser:6'] = 'Je hebt de beoordeling naar {$a->styledsignoffname} gestuurd om deze af te sluiten.<br /><br />
    <div class="alert alert-danger" role="alert">Het appraisal is afgesloten, er kunnen geen wijzigingen meer worden gedaan. </div>';
$string['overview:content:appraiser:7'] = 'Deze beoordeling is nu afgesloten.';

// GROUP LEADER: Overview page Content.
$string['overview:content:groupleader:2'] = 'Beoordeling wordt uitgevoerd.';
$string['overview:content:groupleader:3'] = 'Beoordeling wordt uitgevoerd.';
$string['overview:content:groupleader:4'] = 'Beoordeling wordt uitgevoerd.';
$string['overview:content:groupleader:5'] = 'Beoordeling wordt uitgevoerd.';
$string['overview:content:groupleader:6'] = 'Beoordeling wordt uitgevoerd.';
$string['overview:content:groupleader:7'] = 'This appraisal is complete and signed off.';
$string['overview:content:groupleader:7:groupleadersummary'] = 'De beoordeling is afgetekend en afgesloten.';

// SIGN OFF: Overview page Content.
$string['overview:content:signoff:2'] = 'De beoordeling wordt uitgevoerd.<br /><br /><div class="alert alert-danger" role="alert">Je krijgt een bericht wanneer de beoordeling klaar is en afgesloten.</div>';
// ERROR: translated word "note" is missing
$string['overview:content:signoff:3'] = 'Beoordeling wordt uitgevoerd<br /><br /><div class="alert alert-danger" role="alert">Je krijgt een bericht wanneer het beoordelingsformulier klaar is voor review en afgetekend kan worden.</div>';
// ERROR: translated word "note" is missing
// ERROR: Inconsistency - Beoordeling vs. De beoordeling
$string['overview:content:signoff:4'] = 'Beoordeling wordt uitgevoerd.<br /><br /><div class="alert alert-danger" role="alert">Je ontvangt een bericht wanneer het beoordelingsformulier klaar staat voor de review en het aftekenen.</div>';
// ERROR: translated word "note" is missing
$string['overview:content:signoff:5'] = 'Beoordeling wordt uitgevoerd.<br /><br /><div class="alert alert-danger" role="alert">Je ontvangt bericht wanneer het beoordelingsformulier klaar staat om te reviewen en af te tekenen.</div>';
// ERROR: translated word "note" is missing
// ERROR: typo? - Je ontvangt EEN bericht...
$string['overview:content:signoff:6'] = 'De beoordeling van {$a->styledappraiseename} is naar je gestuurd om te reviewen.<br /><br />
<strong>Volgende stappen:</strong>
    <ol class="m-b-20">
        <li>Review de beoordeling</li>
        <li>Maak je samenvatting in de Samenvatting sectie</li>
        <li>Klik op de Aftekenen knop om de beoordeling af te sluiten.</li>
    </ol>';

$string['overview:content:signoff:7'] = 'Deze beoordeling is nu afgetekend en afgesloten.';

//Overview page buttons
$string['overview:button:appraisee:2:extra'] = 'Start met het invullen van de beoordeling';
$string['overview:button:appraisee:2:submit'] = 'Deel dit met {$a->plainappraisername}';
$string['overview:button:appraisee:4:return'] = 'Stuur terug naar {$a->plainappraisername} om aanpassingen te doen';
$string['overview:button:appraisee:4:submit'] = 'Stuur je compleet ingevulde appraisal naar {$a->plainappraisername}';
$string['overview:button:appraiser:3:return'] = 'Vraag om extra informatie van de {$a->plainappraiseename}';
$string['overview:button:appraiser:3:submit'] = 'Stuur aangepast formulier naar {$a->plainappraiseename} voor laatste commentaar';
$string['overview:button:appraiser:5:return'] = 'Meer aanpassingen zijn gewenst voor aftekenen';
$string['overview:button:appraiser:5:submit'] = 'Stuur naar {$a->plainsignoffname} voor aftekening';
$string['overview:button:signoff:6:submit'] = 'Aftekenen';
$string['overview:button:returnit'] = 'Stuur terug ';
$string['overview:button:submitit'] = 'Verzend';


// END OVERVIEW CONTENT

// START NL string translations - spreadsheet

$string['startappraisal'] = 'Start Online Appraisal';
$string['continueappraisal'] = 'Ga verder met Online Appraisal';
$string['appraisee_feedback_edit_text'] = 'Wijzig';
$string['appraisee_feedback_resend_text'] = 'Zend verzoek opnieuw';
$string['appraisee_feedback_view_text'] = 'Bekijk';
$string['feedback_setface2face'] = 'Voordat feedback verzoeken verzonden kunnen worden moet een datum voor een face tot face gesprek zijn gepland. Dit is terug te vinden op de Appraisee Info Pagina';
$string['feedback_comments_none'] = '';
$string['actionrequired'] = 'Actie vereist';
$string['actions'] = 'Acties';
$string['appraisals:archived'] = 'Gearchiveerde Appraisals';
$string['appraisals:current'] = 'Huidige Appraisals';
$string['appraisals:noarchived'] = 'Je hebt geen gearchiveerde Appraisals';
$string['appraisals:nocurrent'] = 'Je hebt geen openstaande Appraisals';
$string['comment:adddots'] = 'Voeg een opmerking toe….';
$string['comment:addingdots'] = 'Toevoegen';
$string['comment:addnewdots'] = 'Voeg een nieuwe opmerking toe….';
$string['comment:showmore'] = '<i class="fa fa-plus-circle"></i> Laat meer opmerkingen zien';
$string['comment:status:0_to_1'] = '{$a->status} - Het appraisal is aangemaakt maar nog niet gestart';
$string['comment:status:1_to_2'] = '{$a->status} - Het appraisal is gestart door de appraisee';
$string['comment:status:2_to_3'] = '{$a->status} - Het appraisal is doorgezet naar de appraiser';
$string['comment:status:3_to_2'] = '{$a->status} - Het appraisal is teruggestuurd naar de appraisee';
$string['comment:status:3_to_4'] = '{$a->status} - Het appraisal wacht op opmerkingen van de appraisee';
$string['comment:status:4_to_3'] = '{$a->status} - Het appraisal is teruggestuurd naar de appraiser';
$string['comment:status:4_to_5'] = '{$a->status} - Wacht op aftekening van appraiser';
$string['comment:status:5_to_4'] = '{$a->status} - Het appraisal is teruggestuurd naar de appraisee';
$string['comment:status:5_to_6'] = '{$a->status} - Naar beoordelaar verstuurd voor definitieve goedkeuring';
$string['comment:status:6_to_7'] = '{$a->status} - Appraisal is compleet';
$string['comment:updated:appraiser'] = 'De appraiser is gewijzigd van {$a->oldappraiser} naar {$a->newappraiser}.';
$string['comment:updated:signoff'] = 'De sign off beoordelaar is gewijzigd van {$a->oldsignoff} naar {$a->newsignoff}.';
//$string['index:togglef2f:complete'] = '';
//$string['index:togglef2f:notcomplete'] = '';
$string['index:notstarted'] = 'Niet gestart';
$string['index:notstarted:tooltip'] = 'De appraisee is nog niet gestart met zijn/haar appraisal, zodra hij/zij is gestart is toegang mogelijk. ';
$string['index:printappraisal'] = 'Download Appraisal';
$string['index:printfeedback'] = 'Download Feedback';
$string['index:start'] = 'Start Appraisal';
$string['index:toptext:appraisee'] = 'Dit dashboard geeft je huidige en gearchiveerde appraisals weer. Je kunt je huidige appraisal bewerken via de link onder Acties in menu. Gearchiveerde appraisals kunnen worden gedownload door op de de Download Appraisal knop te drukken hieronder. ';
$string['index:toptext:appraiser'] = 'Dit dashboard geeft huidige en gearchiveerde appraisals weer waar jij appraiser voor bent. De huidige appraisals kun je bekijken via de link onder Acties in het menu. De feedback download bevat feedback wat niet beschikbaar zal worden gesteld tot na de face-to-face meeting. Vertrouwelijke feedback zal ten alle tijden afgeschermd blijven. Gearchiveerde appraisals kunnen worden gedownload door op de Download Appraisal knop te drukken hieronder. ';
$string['index:toptext:groupleader'] = 'Dit dashboard geeft huidige en gearchiveerde appraisals in jouw costcenter. De huidige appraisals kun je bekijken via de link onder Acties in het menu. Gearchiveerde appraisals kunnen worden gedownload door op de Download Appraisal knop te drukken hieronder. ';
$string['index:toptext:signoff'] = 'Dit dashboard geeft huidige en gearchiveerde appraisals weer die jij moet aftekenen. De huidige appraisals kun je bekijken via de link onder Acties in het menu. Gearchiveerde appraisals kunnen worden gedownload door op de Download Appraisal knop te drukken hieronder. ';
$string['index:view'] = 'Bekijk Appraisal';
$string['timediff:now'] = 'Nu';
$string['timediff:second'] = '{$a} seconde';
$string['timediff:seconds'] = '{$a} seconden';
$string['timediff:minute'] = '{$a} minuut';
$string['timediff:minutes'] = '{$a} minuten';
$string['timediff:hour'] = '{$a} uur';
$string['timediff:hours'] = '{$a} uren';
$string['timediff:day'] = '{$a} dag';
$string['timediff:days'] = '{$a} dagen';
$string['timediff:month'] = '{$a} maand';
$string['timediff:months'] = '{$a} maanden';
$string['timediff:year'] = '{$a} jaar';
$string['timediff:years'] = '{$a} jaren';
$string['error:togglef2f:complete'] = 'Niet mogelijk om F2F af te tekenen';
$string['error:togglef2f:notcomplete'] = 'Niet mogelijk om F2F af te tekenen als niet gehouden';
$string['appraisee_feedback_email_success'] = 'De email is succesvol verstuurd';
$string['appraisee_feedback_email_error'] = 'Niet gelukt om de email te verzenden';
$string['appraisee_feedback_invalid_edit_error'] = 'ongeldig email adres ingegeven';
$string['appraisee_feedback_inuse_edit_error'] = 'het email adres is al ingebruik';
$string['appraisee_feedback_inuse_email_error'] = 'het email adres is al ingebruik';
$string['appraisee_feedback_resend_success'] = 'de email is succesvol opnieuw verstuurd';
$string['appraisee_feedback_resend_error'] = 'foutmelding bij het versturen ';
$string['form:add'] = 'Toevoegen';
$string['form:language'] = 'Taal';

$string['form:addfeedback:alert:cancelled'] = 'Het sturen van de email is afgebroken, je appraisal feedback formulier is niet verzonden.';
$string['form:addfeedback:alert:error'] = 'Excuses, er is een foutmelding ontstaan bij het versturen van je appraisal feedback ';
$string['form:addfeedback:alert:saved'] = 'Dank je wel, je appraisal is succesvol verzonden';


$string['form:feedback:alert:cancelled'] = 'Verzending geannuleerd, je appraisal feedback verzoek is niet verzonden.';
$string['form:feedback:alert:error'] = 'Sorry, er is een fout opgetreden bij het verzenden van je appraisal feedback verzoek.';
$string['form:feedback:alert:saved'] = 'Je appraisal feedback verzoek is succesvol verstuurd.';


$string['form:lastyear:nolastyear'] = 'Opmerking: er is geen vorig appraisal document aanwezig in het systeem, upload je laatste appraisal document (pdf/word) hieronder.';

$string['form:lastyear:cardinfo:developmentlink'] = 'Ontwikkeling van vorig jaar';

// Feedback Requests
$string['feedbackrequests:description'] = 'Dit dashboard geeft openstaande feedback verzoeken weer en geeft je de mogelijkheid om feedback wat je in het verleden hebt gegeven te bekijken. ';
$string['feedbackrequests:outstanding'] = 'Openstaande verzoeken';
$string['feedbackrequests:norequests'] = 'Geen openstaande feedback verzoeken';
$string['feedbackrequests:completed'] = 'Afgeronde verzoeken';
$string['feedbackrequests:nocompleted'] = 'Geen afgeronde feedback verzoeken';
$string['feedbackrequests:th:actions'] = 'acties';
$string['feedbackrequests:emailcopy'] = 'mail mij een kopie';
$string['feedbackrequests:submitfeedback'] = 'stuur feedback in';
/*
$string['email:subject:myfeedback'] = 'Jouw appraisal feedback voor {{appraisee}}';
$string['email:body:myfeedback'] = 'Beste {{recipient}}, je hebt het volgende {{confidential}} feedback ingediend voor {{appraisee}}: {{feedback}} {{feedback_2}}';
*/
$string['feedbackrequests:confidential'] = 'Vertrouwelijk';
$string['feedbackrequests:nonconfidential'] = 'Niet vertrouwelijk';

$string['feedbackrequests:received:confidential'] ='Ontvangen (vertrouwelijk)';
$string['feedbackrequests:received:nonconfidential']='Ontvangen';
$string['feedbackrequests:paneltitle:confidential']	='Feedback (vertrouwelijk)';
$string['feedbackrequests:paneltitle:nonconfidential']='Feedback';

$string['success:checkin:add'] = 'Check-in succesvol toegevoegd';
$string['error:checkin:add'] = 'Mislukt om check-in toe te voegen';
$string['error:checkin:validation'] = 'Voeg tekst toe aub';
$string['checkin:deleted'] = 'Check-in verwijderd';
$string['checkin:delete:failed'] = 'Mislukt om check-in te verwijderen';
$string['checkin:update'] = 'verversen';
$string['checkin:addnewdots'] = 'check-in';

// PDF
$string['pdf:form:summaries:appraisee'] = 'Commentaar van de beoordeelde';
$string['pdf:form:summaries:appraiser'] = 'Samenvatting van de beoordelaar van de bereikte resultaten';
$string['pdf:form:summaries:signoff'] = 'Af te tekenen samenvatting';
$string['pdf:form:summaries:recommendations'] = 'Overeengekomen acties';

// END NL string translations - spreadsheet

// 2017 : Updates and additions.
$string['addreceivedfeedback'] = 'Voeg ontvangen feedback toe';
$string['appraisee_feedback_savedraft_error'] = 'Foutmelding bij het opslaan van je concept';
$string['appraisee_feedback_savedraft_success'] = 'Concept feedback opgeslagen';
$string['appraisee_feedback_viewrequest_text'] = 'Bekijk de e-mail voor het verzoeken van feedback';
$string['appraisee_welcome'] = 'Jouw appraisal is een kans voor jou en je beoordelaar om een waardevol gesprek te hebben over je performance, loopbaan en toekomstige bijdrage aan de organisatie. Wij willen dat dit een nuttig gesprek wordt, welke persoonlijk is en bruikbaar voor iedereen.<br />
<br /> Het doel van deze online tool is om jou te ondersteunen bij het gedurende het jaar terughalen van het gesprek.<br /><br />Meer informatie over het appraisal proces vind je<a href="https://moodle.arup.com/appraisal/essentials" target="_blank">hier.</a>';
$string['appraisee_welcome_info'] = 'De deadline van jouw appraisal dit jaar is {$a}.';
$string['email:body:appraiseefeedback'] = '{{emailmsg}}
<br>
<hr>
<p>Klik op deze link {{link}} om feedback te geven.</p>
<p>Naam appraisal {{appraisee_fullname}}<br>
   Mijn appraisal vindt plaats op <span class="placeholder">{{held_date}}</span></p>
<p>Dit is een automatisch gegenereerde e-mail, verstuurd door {{appraisee_fullname}} aan {{firstname}} {{lastname}}.</p>
<p>Als bovenstaande link niet werkt, kopieer de volgende link in je internet browser om toegang te krijgen tot het appraisal:<br />{{linkurl}}</p>';
$string['email:body:appraiseefeedbackmsg'] = 'Beste <span class="placeholder bind_firstname">{{firstname}}</span>,</p>
<p>Mijn appraisal gesprek vindt plaats op <span class="placeholder">{{held_date}}</span>. Mijn beoordelaar is <span class="placeholder">{{appraiser_fullname}}</span>. Omdat wij het afgelopen jaar nauw hebben samengewerkt, zou ik het op prijs stellen als je feedback geeft op welke onderdelen je mijn bijdrage als waardevol hebt ervaren en op welke onderdelen ik effectiever kan zijn. Als je feedback wilt geven, klik dan op onderstaande link om dit te doen.</p><p>Het zou fijn zijn als je voor mijn appraisal gesprek kunt reageren.</p>
<p class="ignoreoncopy">Onderstaand zijn een aantal opmerkingen van <span class="placeholder">{{appraisee_fullname}}</span>:<br /> <span>{{emailtext}}</span></p>
<p>Met vriendelijke groet,<br />
<span class="placeholder">{{appraisee_fullname}}</span></p>';
$string['email:body:appraiserfeedback'] = '{{emailmsg}}
<br>
<hr>
<p>Klik op deze link {{link}} om feedback te geven.</p>

<p>Naam appraisal {{appraisee_fullname}}<br>

<p>Het appraisal vindt plaats op <span class=""placeholder"">{{held_date}}</span></p>

<p>Dit is een automatisch gegenereerde e-mail, verstuurd door {{appraiser_fullname}} aan {{firstname}} {{lastname}}.</p>

<p>Als bovenstaande link niet werkt, kopieer de volgende link in je internet browser om toegang te krijgen tot het appraisal:<br />{{linkurl}}</p>';
$string['email:body:appraiserfeedbackmsg'] = '<p>Beste <span class="placeholder bind_firstname">{{firstname}}</span>,</p><p>Het appraisal van <span class="placeholder">{{appraisee_fullname}}</span> vindt plaats op <span class="placeholder">{{held_date}}</span>. Omdat jullie het afgelopen jaar nauw hebben samengewerkt, zou ik het op prijs stellen als je feedback geeft op welke onderdelen jij zijn/haar bijdrage als waardevol hebt ervaren en op welke onderdelen hij/zij effectiever kan zijn. Als je feedback wilt geven, klik dan op onderstaande link om dit te doen.</p><p>Het zou fijn zijn als je voor het appraisal gesprek kunt reageren.</p><p class="ignoreoncopy">Onderstaand zijn een aantal opmerkingen van <span class="placeholder">{{appraiser_fullname}}</span>:<br /> <span>{{emailtext}}</span></p><p>Met vriendelijke groet,<br /> <span class="placeholder">{{appraiser_fullname}}</span></p>';
$string['email:body:myfeedback'] = '<p>Beste {{recipient}},</p>
<p>Je hebt de volgende {{confidential}} feedback voor {{appraisee}}ingediend:</p> <div>{{feedback}}</div><div>{{feedback_2}}</div>';
$string['email:subject:myfeedback'] = 'Jouw appraisal feedback voor {{appraisee}}';
$string['error:noappraisal'] = 'Foutmelding - Je hebt geen appraisal in het systeem. Indien een appraisal opzet dient te worden, neem dan voor hulp contact op met een Appraisal Administrator in de lijst hieronder: {$a}';
$string['feedback_header'] = 'Geef je feedback over {$a->appraisee_fullname} (Appraiser: {$a->appraiser_fullname} - Appraisal datum: {$a->facetofacedate})';
$string['feedback_intro'] = 'Kies drie of meer collega\'s om feedback te geven op jouw appraisal. In de meeste regio\'s kan deze feedback zowel intern als extern zijn. Verwijs naar jouw regio voor specifieke informatie.<br/><br/> Met betrekking tot interne feedbackgevers: je dient 360 graden feedback op te halen bij diverse collega\'s; collega\'s die zowel meer junior als senior zijn.<br/><br/><div data-visible-regions="UKMEA, EUROPE, AUSTRALASIA">Een van de feedbackgevers kan een extere klant of medewerker zijn die jou goed kent.</div><div data-visible-regions="East Asia"><br /><div class="alert alert-warning">For East Asia region, we expect feedback to be from internal source only. Comments from external client or collaborator should be understood and fed back through internal people.</div></div> <div data-visible-regions="Americas"><br /><div class="alert alert-warning">For the Americas Region, comments from external clients or collaborators should be fed back through conversations gathered outside of this feedback tool.</div></div><br /><div class="alert alert-danger"> Aantekening: de feedback van jouw geselecteerde feedbackgevers wordt zo snel mogelijk hier gepubliceerd, tenzij de feedback is opgevraagd door jouw beoordelaar. In dit geval moet de beoordelaar de appraisal naar jou sturen voor jouw laatste opmerkingen (stadium 3) om de feedback zichtbaar te krijgen.</div>';
$string['feedbackrequests:paneltitle:requestmail'] = 'E-mail voor het verzoeken van feedback';
$string['form:addfeedback:addfeedback'] = 'Omschrijf maximaal drie onderdelen waarin je de bijdrage van de beoordeelde hebt gewaardeerd in de afgelopen 12 maanden.';
$string['form:addfeedback:addfeedback_2'] = 'Omschrijft maximaal drie onderdelen waarin jij denkt dat de beoordeelde effectiever kan zijn. Wees eerlijk en geef opbouwende feedback, zodat deze feedback jouw collega helpt effectiever om te gaan met problemen.';
$string['form:addfeedback:addfeedback_2help'] = '<div class="well well-sm">Het is voor iedereen belangrijk om waardevolle en evenwichtige feedback te ontvangen, inclusief positieve en kritische inbreng. <br>Voor meer informatie klik <a href="https://moodle.arup.com/scorm/_assets/ArupAppraisalGuidanceFeedback.pdf" target="_blank">hier</a></div>';
$string['form:addfeedback:addfeedback_help'] = 'Kopieer en plak je ontvangen feedback in het veld waardevolle bijdrage, tenzij dit gesplitst kan worden in waardevol en effectiever.';
$string['form:addfeedback:addfeedbackhelp'] = '<div class="well well-sm">Het is voor iedereen belangrijk om waardevolle en evenwichtige feedback te ontvangen, inclusief positieve en kritische inbreng. <br>Voor meer informatie klik <a href="https://moodle.arup.com/scorm/_assets/ArupAppraisalGuidanceFeedback.pdf" target="_blank">hier</a></div>';
$string['form:addfeedback:firstname'] = 'Voornaam feedbackgever';
$string['form:addfeedback:lastname'] = 'Achternaam feedbackgever';
$string['form:addfeedback:saveddraft'] = 'Je hebt een concept van je feedback opgeslagen. Totdat je de appraisal feedback verstuurt, kan de beoordelaar of de beoordeelde de feedback niet zien.';
$string['form:addfeedback:savedraftbtn'] = 'Als concept opslaan';
$string['form:addfeedback:savedraftbtntooltip'] = 'Sla op als concept om later te voltooien. Er wordt geen kopie van je feedback naar de beoordelaar/beoordeelde gestuurd.';
$string['form:addfeedback:savefeedback'] = 'Feedback opslaan';
$string['form:development:comments'] = 'Opmerkingen beoordelaar';
$string['form:development:commentshelp'] = '<div class="well well-sm"><em>Te voltooien door beoordelaar</em></div>';
$string['form:feedback:editemail'] = 'Bewerken';
$string['form:feedback:providefirstnamelastname'] = 'Vul de naam van de ontvanger in voordat je op de knop bewerken drukt.';
$string['form:lastyear:cardinfo:performancelink'] = 'Impact plan afgelopen jaar';
$string['form:lastyear:printappraisal'] = '<a href="{$a}" target="_blank">Het appraisal van afgelopen jaar</a> s beschikbaar om in te zien.
(PDF - opent in een nieuw scherm).';
$string['form:summaries:grpleaderhelp'] = '<div class="well well-sm"><em>In te vullen door de senior manager om te voltooien.</em></div>';
$string['helppage:intro'] = 'Klik op onderstaande knop voor toegang tot de Online Appraisal helppagina';
$string['leadersignoff'] = 'Afmelding manager';
$string['modal:printconfirm:cancel'] = 'Nee, het is goed';
$string['modal:printconfirm:content'] = 'Is het echt nodig om dit document te printen?';
$string['modal:printconfirm:continue'] = 'Ja, ga verder';
$string['modal:printconfirm:title'] = 'Denk na voordat je print';
$string['overview:content:appraisee:3'] = 'Je hebt een conceptversie van het appraisal ter beoordeling voorgelegd aan {$a->styledappraisername} <br/><br/> <strong>Volgende stappen:</strong> <ul class="m-b-20"> <li>Heb het appraisal gesprek - voor het gesprek kun je:</li> <ul class="m-b-0"> <li><a class="oa-print-confirm" href="{$a->printappraisalurl}">Het appraisal downloaden</a></li> <li><a href="https://moodle.arup.com/appraisal/reference" target="_blank">De Quick Reference Guide downloaden</a></li> </ul> <li>Na het gesprek zet de beoordelaar het appraisal terug naar jou. Je wordt dan gevraagd om op basis van het gesprek overeengekomen wijzigingen door te voeren of je laatste opmerkingen toe te voegen.</li> </ul> <div class="alert alert-danger" role="alert"><strong>Opmerking:</strong> je kunt wijzigingen aanbrengen als je appraisal bij jouw beoordelaar is, maar dient dan wel de activity log te gebruiken om de wijzigingen te markeren.</div>';
$string['overview:content:appraisee:5'] = 'Je hebt je compleet ingevulde beoordeling naar {$a->styledappraisername} gestuurd voor de laatste review.<br /><br /> <strong>Volgende stappen: </strong> <ol class="m-b-20"> <li> Je beoordelaar stuurt de beoordeling naar {$a->styledsignoffname} om af te laten tekenen.</li> </ol> <div class="alert alert-danger" role="alert">Je kan geen veranderingen meer aanbrengen in de beoordeling behalve wanneer de beoordelaar de beoordeling naar je terugstuurt om veranderingen aan te brengen.</div>';
$string['overview:content:appraiser:3'] = '{$a->styledappraiseename} heeft een conceptversie voorgelegd ter voorbereiding van het appraisal.<br /><br /><strong>Volgende stappen:</strong> <ul class="m-b-20"> <li>Beoordeel het appraisal ter voorbereiding van het gesprek. Indien nodig, stuur het appraisal terug naar de beoordeelde als je meer informatie nodig hebt.</li> <li>Voor het appraisel dien je:</li> <ul class="m-b-0"> <li><a class="oa-print-confirm" href="{$a->printappraisalurl}">Het appraisal te downloaden</a></li> <li><a class="oa-print-confirm" href="{$a->printfeedbackurl}">Ontvangen feedback te downloaden</a></li> <li>Je kunt ook de <a href="https://moodle.arup.com/appraisal/reference" target="_blank">Quick Reference Guide downloaden</a></li>
   </ul><li>Naar aanleiding van het appraisal:</li> <ul class="m-b-0"> <li>Noteer in het info gedeelte dat het gesprek heeft plaatsgevonden</li> <li>Voeg je opmerkingen toe aan elk onderdeel</li> <li>Schrijf een samenvatting inclusief gemaakte afspraken in het onderdeel Samenvatting</li>(indien noodzakelijk kun je het appraisal terugsturen naar de beoordeelde om het appraisal aan te passen voordat jij je opmerkingen toevoegt). </ul> <li>Stuur het appraisal naar de beoordeelde om jouw opmerkingen te beoordelen, feedback te bekijken en om zijn/haar laatste opmerkingen toe te voegen.</li> </ul>';
$string['overview:content:appraiser:3:4'] = '{$a->styledappraiseename} heft gevraagd veranderingen aan te brengen in de beoordeling.<br /><br /> <strong>Volgende stappen:</strong> <ol class="m-b-20"> <li>Pas de beoordeling aan conform het verzoek van de beoordeelde (zie activiteiten log voor meer) </li> <li>Deel de beoordeling met {$a->styledappraiseename} voor laatste commentaar</li> </ol>';
$string['overview:content:appraiser:4'] = 'Je hebt je commentaar en samenvatting toegevoegd en de beoordeling terug gestuurd naar {$a->styledappraiseename} om het laatste commentaar toe te voegen. Je krijgt een bericht wanneer de beoordeling klaar is voor een laatste review.<br /><br /> <div class="alert alert-danger" role="alert">Je mag de secties aanpassen zolang de beoordeling nog niet is afgetekend maar het is verstandig de wijzigingen te markeren in de activiteiten log voor de beoordeelde.</div>';
$string['overview:content:special:archived'] = '<div class="alert alert-danger" role="alert">Dit appraisal is gearchiveerd.<br />Het is alleen mogelijk om het appraisa <a class="oa-print-confirm" href="{$a->printappraisalurl}">te downloaden.</a>.</div>';
$string['overview:content:special:archived:appraisee'] = '<div class="alert alert-danger" role="alert">Dit appraisal is gearchiveerd.<br />Het is alleen mogelijk om je appraisal <a class="oa-print-confirm" href="{$a->printappraisalurl}">te downloaden.</a>.</div>';
$string['overview:lastsaved'] = 'Laatst opgeslagen: {$a}';
$string['overview:lastsaved:never'] = 'Nooit';
$string['pdf:feedback:confidentialhelp:appraisee'] = '# Bevat vertrouwelijke feedback welke niet zichtbaar is voor jou';
$string['pdf:feedback:notyetavailable'] = 'Nog niet zichtbaar.';
$string['pdf:feedback:requestedfrom'] = 'Beoordelaar: {$a->firstname} {$a->lastname}{$a->appraiserflag}{$a->confidentialflag}:';
$string['pdf:feedback:requestedhelp'] = '* Bevat feedback waar jouw beoordelaar om heeft verzocht en welke nog niet zichbaar is voor jou.';
$string['pdf:header:warning'] = 'Gedownload door: {$a->who} op {$a->when}
<br>
Geef niet af of laat het niet op een onveilige plaats achter.';
$string['status:7:leadersignoff'] = 'Afmelding manager';
