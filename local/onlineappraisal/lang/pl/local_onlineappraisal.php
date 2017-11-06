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
$string['alert:language:notdefault'] = '<strong>Uwaga</strong>: Nie używasz domyślnego języka dla tej strony. Upewnij się, że udzielasz odpowiedzi na pytania w języku najbardziej odpowiednim dla wszystkich zainteresowanych.';
// INTRODUCTION PAGE
$string['appraisee_heading'] = 'Witaj w wersji online Rozmowy Oceniającej';

// ERROR: remove east asia specfic content?

// LAST YEAR REVIEW
$string['form:lastyear:title'] = 'Section 1: Review of last year';
$string['form:lastyear:nolastyear'] = 'Uwaga: W systemie nie ma Twoich wcześniejszych rozmów oceniających. Proszę prześlij/załaduj swoją ostatnią rozmowę oceniającą w formacie word bądź pdf.';

//ERROR: translations provided from this point onwards

$string['form:lastyear:intro'] = 'W tej sekcji osoba oceniana jak i oceniająca dyskutują na temat tego, co zostało osiągnięte w przeciągu ostatnich 12 miesięcy. <a href="https://moodle.arup.com/appraisal/guide" target="_blank">Przewodnik - Rozmowa Oceniająca</a> zawiera więcej informacji jak powinno wyglądać spotkanie oceniające.';
$string['form:lastyear:upload'] = 'Prześlij Ocenę Roczną';
// ERROR: missing translation
$string['form:lastyear:appraiseereview'] = '1.1 Podsumowanie wyników pracy prze Ocenianego';
$string['form:lastyear:appraiseereviewhelp'] =
'<div class="well well-sm"><em>OW jaki sposób oceniasz realizację swoich celów w kontekście projektów, ludzi, klientów w przeciągu ostatniego roku?</em>
    <ul class="m-b-0">
        <li><em>Jak oceniasz współpracę, dzielenie się widzą i informacjami?</em></li>
        <li><em>Czy Twoja efektywność pracy była poniżej oczekiwań?</em></li>
        <li><em>Jeśli jesteś odpowiedzialny za ludźmi, w jaki sposób udało Ci się zarządzić ich wynikami pracy, zachowaniem, zarówno dobrym i złym?</em></li>
        <li><em>Czy i w jaki sposób wykorzystałeś technologię, aby być bardziej wydajnym?</em></li>
    </ul>
</div>';
$string['form:lastyear:appraiserreview'] = '1.2 Podsumowanie wyników pracy przez Oceniającego';
$string['form:lastyear:appraiserreviewhelp'] =
'<div class="well well-sm"><em>Proszę odnieś się do komentarza osoby ocenianej odnośnie wyników pracy. Weź pod uwagę czas od ostatniej rozmowy oceniającej.</em>
<ul class="m-b-0">
        <li><em>Jaki postęp osiągnęli?</em></li>
        <li><em>Podsumuj informacje zwrotną, którą otryzmała osoba oceniana od wyznaczonych prze siebie osób.</em></li>
    </ul>
<em>Jeśli ich wyniki pracy bądź zachowanie w kotekście projektów, zespołu, klientów bądź innych osób zostało ocenione poniżej oczekiwań, trzeba to zaznaczyć i przedyskutować w tej części formularza.</em>
</div>';
// ERROR: word for "must" not marked as bold in translation
$string['form:lastyear:appraiseedevelopment'] = '1.3 Podsumowanie działań rozwojowych przez Ocenianego';
$string['form:lastyear:appraiseedevelopmenthelp'] =
'<div class="well well-sm"><em>Prosze zastanów się nad swoim rozwojem osobistym w przeciągu ostatniego roku:</em>
    <ul class="m-b-0">
        <li><em>W jaki sposób udało Ci się rozwinąć swoje umiejętności, wiedzę, kompetencje?</em></li>
        <li><em>Jakich działań rozwojowych, które były zaplanowane w ostatnim roku, nie udało Ci się zrealizować?</em></li>
    </ul>
</div>';
$string['form:lastyear:appraiseefeedback'] = '1.4. Czy jest coś co wpłynęło, mogło poprawić Twoje wyniki pracy bądź zespołu?';
$string['form:lastyear:appraiseefeedbackhelp'] = '<div class="well well-sm"><em>Do wypełnienia przez Osobę Ocenianą</em></div>';

// CAREER DIRECTION
$string['form:careerdirection:title'] = 'Section 2: Career Direction';

$string['form:careerdirection:intro'] = 'Celem tej części formularza oceny jest umożliwienie osobie ocenianej rozważenia i omówienia z osobą oceniającą swoich aspiracji zawodowych. Dla młodszych pracowników, horyzont czasowy w tej rozmowie będzie prawdopodobnie dotyczył około 1-3 lat. Dla bardziej doświadczonych pracowników moglibyśmy oczekiwać, że będzie on obejmował 3-5 lat.';
$string['form:careerdirection:progress'] = '2.1. W jaki sposób chciałbyś/chcialabyś rozwijać swoją karierę?';
$string['form:careerdirection:progresshelp'] =
'<div class="well well-sm"><em>Warto byś wziął pod uwagę:</em>
    <ul class="m-b-0">
        <li><em>Rodzaj pracy jaki chcesz wykonywać i poziom odpowiedzialności jaki chcesz wziąć na siebie?</em></li>
        <li><em>Co jest dla Ciebie ważne w pracy w nadchodzących latach np. jaki zakres pracy, jaka specjalizacja/generalizacja, możliwość wyjazdów, projektowanie, odpowiedzialność za innych ludzi itp.?</em></li>
        <li><em>W jakim miejscu, gdzie chciałbyś/chcialabyś pracować?</em></li>
    </ul>
</div>';
$string['form:careerdirection:comments'] = '2.2. Komentarze Osoby Oceniającej';
$string['form:careerdirection:commentshelp'] =
'<div class="well well-sm"><em>Powinieneś wziąć pod uwagę:</em>
    <ul class="m-b-0">
        <li><em>Czy aspiracje osoby ocenianej są realistyczne, wymagające i ambitne?</em></li>
        <li><em>Jakie projekty, stanowiska bądź inne możliwości pomogłyby w zdobyciu odpowiedniej wiedzy, umiejętności, kompetencji?</em></li>
    </ul>
</div>';

// AGREED IMPACT PLAN
$string['form:impactplan:title'] = 'Section 3: Agreed Impact Plan';
$string['form:impactplan:intro'] = 'Uzgodniony Plan Wpływu określa, w jaki sposób osoba oceniana chce coś zmienić w nadchodzącym roku, w zakresie wykonywanej przez siebie pracy i ogólnego wpływu na firmę. Plan powinien zawierać w jaki sposób osoba oceniana poprawi/udoskonali swoją pracę, albo projekt / zespół / biuro / grupę. W praktyce oznacza to dostarczenie informacji o terminach,  jakości, budżecie, projektowaniu/ innowacjach i o tym w jaki sposób wpłynie to na ludzi, klientów czy pracę samą w sobie.<br /><br /> <a href="https://moodle.arup.com/appraisal/contribution" target="_blank">Podręcznik</a> i <a href="https://moodle.arup.com/appraisal/guide" target="_blank">Przewodnik do Formularza Oceny</a> mogą Ci podpowiedzieć w jaki sposób wprowadzić te ulepszenia.';
$string['form:impactplan:impact'] = '3.1 Opisz jaki wpływ chiałbyś/chiałabyś mieć na swoje projekty, klientów, zespół czy firmę w przyszłym roku:';
$string['form:impactplan:impacthelp'] =
'<div class="well well-sm"><em>W swoim planie możesz ująć:</em>
    <ul class="m-b-0">
        <li><em>Twoje obszary zainteresowania</em></li>
        <li><em>Dlaczego są one istotne</em></li>
        <li><em>W jaki sposób chciałbyś/chcialabyś je osiągnąć</em></li>
        <li><em>Z kim będziesz współpracował?</em></li>
        <li><em>Przybliżone ramy czasow: 3/6/12/18 miesięcy bądź dłużej</em></li>
        <li><em>W jaki sposób uzgodniony Plan Wpływu wpisuje się w pożądaną przez Ciebie ścieżkę kariery </em></li>
    </ul>
</div>';
$string['form:impactplan:support'] = '3.2 Jakiego wsparcia potrzebujesz od Arup aby to osiągnąć?';
$string['form:impactplan:supporthelp'] = '<div class="well well-sm">
    <em>Możesz wziąć pod uwagę:</em>
    <ul class="m-b-0">
        <li><em>Pomoc innych</em></li>
        <li><em>Superwizję przełożonych</em></li>
        <li><em>Zasoby (czas, budżet, sprzęt)</em></li>
        <li><em>Rozwój osobisty</em></li>
        <li><em>Narzędzia (oprogramowanie, sprzęt)</em></li>
    </ul>
</div>';
$string['form:impactplan:comments'] = '3.3 Komentarze Oceniającego';
$string['form:impactplan:commentshelp'] = '<div class="well well-sm"><em>Do wypełnienia przez Oceniającego</em></div>';

// DEVELOPMENT PLAN
$string['form:development:title'] = 'Section 4: Development Plan';
$string['form:development:intro'] = 'Plan Rozwoju określa jakie umiejętności osobiste, wiedza, kompetencje są potzrebne aby wspeirać rozwój osoby Ocenianej i postęp uzgodnionego Planu Wpływu. <br /><br />
W jakis spoób chiałbyś się rozwinąć w przeciągu 12-18 miesiecy, żeby osiągnąć cel? Jakiego wsparcia potrzebujesz, kiedy planujesz podjąć działania rozwojowe?<br /><br />
<div class="well well-sm">W Arup wyznajemy zasadę „70-20-20” dotyczącą rozwoju osobistego. Oznacza ona, że 70% działań rozwojowych powinno się odbywać podczas wykonywania codziennej pracy, ucząć się poprzez dośiadczenie. 20% to rozwój dzięki innym osobom, być może poprzez coaching czy mentoring. Finalne 10% to działania, które powinny być realizowane poprzez formalne metody nauki takie jak szkolenia, e-learning. Wartości procentowe są oczywiście wskazówką.</div>';
$string['form:development:seventy'] = 'Działania rozowjowe, które są częśćią Twojej codziennej pracy – około 70%';
$string['form:development:seventyhelp'] =
'<div class="well well-sm"><em>Przykładowo:</em>
    <ul class="m-b-0">
        <li><em>Zadania projektowe</em></li>
        <li><em>Zadania zespołu</em></li>
        <li><em>Mobilność</em></li>
        <li><em>Omówienie pracy i informacje zwrotne</em></li>
        <li><em>Przegląd projektów</em></li>
        <li><em>Czytanie</em></li>
        <li><em>Badania</em></li>
    </ul>
</div>';
$string['form:development:twenty'] = 'Uczenie się od innych – około 20%';
$string['form:development:twentyhelp'] =
'<div class="well well-sm"><em>Przykładowo:</em>
    <ul class="m-b-0">
        <li><em>Od innych członków zespołu</em></li>
        <li><em>Od ekspertów</em></li>
        <li><em>Od klientów</em></li>
        <li><em>Od współpracowników</em></li>
        <li><em>Podczas konferencji</em></li>
        <li><em>Poprzez coaching</em></li>
        <li><em>Poprzez mentoring</em></li>
    </ul>
</div>';
$string['form:development:ten'] = 'Szkolenie na zorganizowanych kursach - osobistych bądź online - około 10%';
$string['form:development:tenhelp'] = '<div class="well well-sm">
    <em>Przykładowo:</em>
    <ul class="m-b-0">
        <li><em>Szkolenie zorganizowane</em></li>
        <li><em> formalny e-learning</em></li>
        <li><em>Virtual Clasroom learning</em></li>
    </ul>
</div>';
// ERROR: missing translation entirely!

// SUMMARIES
$string['form:summaries:title'] = 'Section 5: Summaries';
$string['form:summaries:intro'] = 'Celem tej części jest podsumowanie zawartości formularza oceny dla późniejszego wykorzystania przez osoby zaangażowane w podejmowanie decyzji płacowych, promocji czy rozwoju.';
$string['form:summaries:appraiser'] = '5.1 Podsumowanie oceny przez osobę oceniającą';
$string['form:summaries:appraiserhelp'] = '<div class="well well-sm"><em>Osoba oceniająca powinna podsumować ocenę w sposób jasny, zwięzły, tak by była ona również zrozumiała dla osób, które później będą zaangażowane np. w podejmowanie decyzji dotyczących płacy/ promocji/ rozwoju. Osoba oceniająca powinna w szczególności zaznaczyć obszary oceny, które są powyżej bądż poniżej oczekiwań. </em></div>';
$string['form:summaries:recommendations'] = '5.2 Uzgodnione działania';
$string['form:summaries:recommendationshelp'] =
'<div class="well well-sm"><em>Do wypełnienia przez osobę oceniającą</em><br/><em>Co powinno się teraz zdarzyć? Na przykład:</em>
    <ul>
        <li><em>Rozwój</em></li>
        <li><em>Mobility</em></li>
        <li><em>Zadania</em></li>
        <li><em>Wsparcie efektywności</em></li>
    </ul>
</div>';
$string['form:summaries:appraisee'] = '5.3 Komentarz osoby ocenianej';
$string['form:summaries:appraiseehelp'] = '<div class="well well-sm"><em>Do uzupełnienia przez osobę ocenianą</em></div>';
$string['form:summaries:signoff'] = '5.4 Podsumowanie przez osobę kończącą proces';
$string['form:summaries:signoffhelp'] = '<div class="well well-sm"><em>To be completed by leader / designated sign off.</em></div>';
//$string['form:summaries:groupleader'] = '5.5 Groupleader summary';
//$string['form:summaries:groupleaderhelp'] = '<div class="well well-sm"><em>To be completed by group leader.</em></div>';
// ERROR: missing translation

// CHECKINS
$string['appraisee_checkin_title'] = 'Section 6. Check-in';
$string['checkins_intro'] = 'Warto aby podczas roku dokonać ewaluacji uzgodnionego Planu Wpływu, Planu Rozwoju czy innych zaplanowanych działań oraz ich wyników. Do takiego podsumowania można użyć poniższej części formularza. Częstotliwość spotkań jest ustalana pomiędzy osobą oceniającą a ocenianą, rekomendujemy minimum jedno spotkanie w ciągu roku.';

// FEEDBACK CONTRIBUTION
$string['feedback_addfeedback'] = 'Proszę opisz trzy obszary, które uważasz za mocne strony osoby ocenianej w ostatnich 12 miesiącach. Późnej proszę podaj do trzech obszarów, nad którymi Twoim zdaniem osoba oceniająca powinna popracować. Bądź szczery, pamiętaj, żeby Twoja informacja zwrotna była konstruktywna i mogła się przyczynić do poprawy efektywności.';
// ERROR: word for "constructively" not marked as italic
$string['confidential_label_text'] = 'Zaznacz jeśli chesz by Twój komentarz był poufny. Jeśli nie odznaczysz, Twój komentarz zostanie przekazany osobie ocenianej. ';

// FEEDBACK EMAIL appraisee
$string['email:subject:appraiseefeedback'] = 'Prośba o informację zwrotną na potrzeby mojej oceny rocznej';

$string['email:body:appraiseefeedback_link_here'] = 'here';

$string['email:subject:appraiserfeedback'] = 'Prośba o informację zwrotną dla {{appraisee_fullname}} na potrzeby oceny rocznej';

// FEEDBACK EMAIL appraiser

// END FORM

// START OVERVIEW CONTENT

// APPRAISEE: Overview page content
$string['overview:content:appraisee:2'] = 'Proszę rozpocznij wypełnianie formularza oceny.<br /><br />
<strong>Następne kroki:</strong>
    <ul class="m-b-20">
        <li>Wpisz planowaną datę spotkania</li>
        <li>Poproś o informację zwrotną</li>
        <li>Zastanów się i skomentuj zeszłoroczny plan rozwoju i wyniki</li>
        <li>Uzupełnij kierunek rozwoju kariery, Plan Wpływu oraz Plan Rozwoju do dyskusji podczas spotkania.</li>
        <li>Udostępnij swój projekt oceny rocznej {$a->styledappraisername}, Twojej osobie oceniającej.</li>
    </ul>
Proszę udostępnij swój projekt oceny rocznej osobie oceniającej przynajmniej na tydzień przed spotkaniem. Będziesz w stanie kontynuować, gdy go udostępnisz.<br /><br />
<div class="alert alert-danger" role="alert"><strong>Uwaga:</strong> Osoba oceniająca nie będzie mogła zobaczyć twojego projektu, dopóki go nie udostępnisz.</div>';
// ERROR: word for "week" not marked in bold

$string['overview:content:appraisee:2:3'] = 'Osoba oceniająca zażądała zmian w projekcie formularza  oceny.<br /><br />
<strong>Następne kroki:</strong>
<ul class="m-b-20">
    <li>Dokonaj zmian zgodnie z wymaganiami Osoby oceniającej (proszę sprawdzić raport aktywności, celem uzyskania dodatkowych informacji na temat tego, co zostało zażądane).</li>
    <li>Udostępnij swój projekt {$a->styledappraisername}.</li>
</ul>';

$string['overview:content:appraisee:3:4'] = 'Wróciłeś do formularza oceny {$a->styledappraisername}, aby wprowadzić zmiany.<br /><br /> Otrzymasz powiadomienie, gdy uaktualni swój formularz oceny, gotowy do ponownego przeanalizowania.Uwaga:<br /><br /> <div class="alert alert-danger" role="alert"><strong>Uwaga:</strong> Możesz kontynuować edycję formularza oceny podczas spotkania z Osobą oceniającą ale sugeruję użycie  raportu aktywności aby podkreślić jakiekolwiek wprowadzone zmiany.</div>';

$string['overview:content:appraisee:4'] = '{$a->styledappraisername} dodaje teraz swoje komentarze i formularz oceny jest z powrotem u Ciebie.<br /><br />
<strong>Następne kroki:</strong>
<ul class="m-b-20">
    <li>Proszę dokonaj przeglądu komentarzy i podsumowania, które zostawiła Twoja Osoba Oceniająca. Jeśli to konieczne zwróć formularz oceny do Osoby oceniającej jeśli wymaga jakiś zmian.</li>
    <li>Napisz swoje komentarze w części Podsumowanie</li>
    <li>Wyślij do Osoby oceniającej do przeglądu przed ostateczną weyfikacją. Po złożeniu nie będziesz już w stanie edytować formularza oceny.</li>
</ul>
<div class="alert alert-danger" role="alert"><strong>Uwaga:</strong> Możesz kontynuować edycję twojej części formularza oceny, ale sugeruję użycie  raportu aktywności aby podkreślić jakiekolwiek zmiany twojej Osoby oceniającej.</div>';

$string['overview:content:appraisee:5'] = 'Przesłałeś wypełniony formularz oceny do {$a->styledappraisername} do ostatecznego przeglądu.<br /><br />
<strong>Następne kroki:</strong>
    <ul class="m-b-20">
        <li>•	Osoba oceniająca prześle formularz oceny {$a->styledsignoffname} do ostatecznego zatwierdzenia.</li>
    </ul>
<div class="alert alert-danger" role="alert"><strong>Uwaga:</strong> Nie możesz dokonywać zmian w formularzu oceny do momentu odesłania formularza przez Osobę Oceniającej z powrotem do Ciebie. </div>';

$string['overview:content:appraisee:6'] = 'Twój formularz oceny został wysłany do {$a->styledsignoffname} do przeglądu i podsumowania.<br /><br />
<div class="alert alert-danger" role="alert"><strong>Uwaga:</strong> Formularz oceny jest obecnie zablokowany do edycji, nie można dokonać zmian. </div>';

$string['overview:content:appraisee:7'] = 'Twój proces oceny jest zakończony. Możesz ściągnąć kopię w formacie PDF klikając na guzik ”Ściągnij formularz oceny”.';

// APPRAISER: Overview page content
$string['overview:content:appraiser:2'] = 'Formularz oceny jest obecnie opracowywana przez {$a->styledappraiseename}. Zostaniesz powiadomiony, gdy będzie gotowa do wglądu.<br /><br />
<div class="alert alert-danger" role="alert"><strong>Uwaga:</strong> Nie będziesz mógł zobaczyć formularza oceny, dopóki nie zostanie Ci udostępniony.</div>';

$string['overview:content:appraiser:2:3'] = 'Zwróciłeś formularza oceny do {$a->styledappraiseename} aby wprowadzić zmiany. Otrzymasz powiadomienie, gdy uaktualni ona swój formularz oceny, wtedy będzie on gotowy do ponownego przeanalizowania.<br /><br />
<div class="alert alert-danger" role="alert"><strong>Uwaga:</strong> Jesteś jeszcze w stanie dokonać zmian w swoich polach</div>';

$string['overview:content:appraiser:3:4'] = '{$a->styledappraiseename} poprosił o zmiany w ich formularzu oceny.<br /><br />
<strong>Następne kroki:</strong>
<ul class="m-b-20">
    <li>Dokonaj zmian zgodnie z wymaganiami Osoby ocenianej (proszę sprawdzić raport aktywności, celem uzyskania dodatkowych informacji na temat tego, co zostało zażądanie).</li>
    <li>Podziel się formularzem oceny z {$a->styledappraiseename} dla uwag końcowych.</li>
</ul>';

$string['overview:content:appraiser:4'] = 'Dodałeś swoje komentarze i podsumowanie. Formularz oceny wraca do {$a->styledappraiseename} aby dodała swoje uwagi końcowe. Zostaniesz powiadomiony, gdy będzie  gotowy do ostatecznego przeglądu.<br /><br />
<div class="alert alert-danger" role="alert"><strong>Uwaga:</strong> Możesz kontynuować edycję twojej części formularza oceny, ale sugeruję użycie raportu aktywności aby podkreślić jakiekolwiek zmiany wprowadzone przez Osobę ocenianą.</div>';

$string['overview:content:appraiser:5'] = '{$a->styledappraiseename} dodała swój finalny komentarz.<br /><br />
<strong>Kolejne kroki:</strong>
<ul class="m-b-20">
    <li>Proszę przejrzyj formularz oceny tak by był gotowy do ostatecznego zatwierdzenia.</li>
    <li>Prześlij go do {$a->styledsignoffname} aby mógł </li>
    <li>You and the appraisee will be notified once the appraisal is complete.</li>
</ul>
<div class="alert alert-danger" role="alert"><strong>Note:</strong> You can no longer make changes to the appraisal unless you return it to the appraisee.</div>';
// ERROR: missing translation for last bullet and Note: message

$string['overview:content:appraiser:6'] = 'Formularz oceny został wysłany do {$a->styledsignoffname} do zamknięcia oceny.<br /><br />
    <div class="alert alert-danger" role="alert"><strong>Uwaga:</strong> Formularz oceny jest obecnie zablokowany do edycji, nie można dokonać zmian.</div>';

$string['overview:content:appraiser:7'] = 'Ten formularz oceny jest ukończony i zatwierdzony.';

// SIGN OFF: Overview page content
$string['overview:content:signoff:2'] = 'Ocena w toku.<br /><br /><div class="alert alert-danger" role="alert"><strong>Uwaga:</strong> Zostaniesz powiadomiony, gdy formularz oceny będzie gotowy do sprawdzenia i zatwierdzenia.</div>';

$string['overview:content:signoff:3'] = 'Ocena w toku.<br /><br /><div class="alert alert-danger" role="alert"><strong>Uwaga:</strong> Zostaniesz powiadomiony, gdy formularz będzie gotowy do sprawdzenia i zatwierdzenia.</div>';

$string['overview:content:signoff:4'] = 'Ocena w toku.<br /><br /><div class="alert alert-danger" role="alert"><strong>Uwaga:</strong>Zostaniesz powiadomiony, gdy formularz będzie gotowy do sprawdzenia i zatwierdzenia.
</div>';

$string['overview:content:signoff:6'] = 'Formularz oceny dla {$a->styledappraiseename} został przesłany do Twojego sprawdzenia.<br /><br />
<strong>Następne kroki:</strong>
<ul class="m-b-20">
    <li>Proszę o sprawdzenie formularza</li>
    <li>Napisz swoje podsumowanie oceny w części Podsumowanie</li>
    <li>Kliknij guzik Zatwierdź by ukończyć ocenę</li>
</ul>';

$string['overview:content:signoff:7'] = 'Ten formularz oceny jest ukończony i zatwierdzony.';

// Overview page GROUP LEADER Content.
$string['overview:content:groupleader:1'] = ''; // Never seen...
$string['overview:content:groupleader:2'] = 'Ocena w toku.';
$string['overview:content:groupleader:3'] = 'Ocena w toku.';
$string['overview:content:groupleader:4'] = 'Ocena w toku.';
$string['overview:content:groupleader:5'] = 'Ocena w toku.';
$string['overview:content:groupleader:6'] = 'Ocena w toku.';
$string['overview:content:groupleader:7'] = 'Ten formularz oceny jest ukończony i zatwierdzony.';
$string['overview:content:groupleader:7:groupleadersummary'] = 'This appraisal is complete but the Groupleader can still add a Groupleader Summary on the Summaries page';
$string['overview:content:groupleader:8'] = $string['overview:content:groupleader:7']; // For legacy where there was a six month status.

// Buttons.
$string['overview:button:appraisee:2:extra'] = 'Rozpocznij wypełnianie formularza oceny';
$string['overview:button:appraisee:2:submit'] = 'Podziel się z {$a->plainappraisername}';

$string['overview:button:appraisee:4:return'] = 'Zwóć do {$a->plainappraisername} aby wprowadziła zmiany';
$string['overview:button:appraisee:4:submit'] = 'Wyślij kompletny formularz oceny {$a->plainappraisername}';

$string['overview:button:appraiser:3:return'] = 'Prośba o dalsze informację {$a->plainappraiseename}';
$string['overview:button:appraiser:3:submit'] = 'Wyślij do {$a->plainappraiseename} dla uwag końcowych';

$string['overview:button:appraiser:5:return'] = 'Wymaga edycji przed wysłaniem do ostatecznego zatwierdzenia';
$string['overview:button:appraiser:5:submit'] = 'Wysłany do {$a->plainsignoffname} do ostatecznego zatwierdzenia';

$string['overview:button:signoff:6:submit'] = 'Zatwierdź';

$string['overview:button:returnit'] = 'Return';
$string['overview:button:submitit'] = 'Send';

// END OVERVIEW CONTENT

// START PO STRING TRANSLATIONS - SPREADSHEET

$string['startappraisal'] = 'Rozpocznij Ocenę Roczną Online';
$string['continueappraisal'] = 'Kontynuuj Ocenę Roczną Online';
$string['appraisee_feedback_edit_text'] = 'Edytuj';
$string['appraisee_feedback_resend_text'] = 'Wyślij';
$string['appraisee_feedback_view_text'] = 'Zobacz';
$string['feedback_setface2face'] = 'Abyś był w stanie wysłać prośbę o ocenę zwrotną, musisz wyznaczyć wcześniej datę rozmowy oceniającej. Informację możesz znaleźć na stronie Info dla pracowników ocenianych.';
$string['feedback_comments_none'] = 'Brak dodatkowych komentarzy';
$string['actionrequired'] = 'Wymagane działanie';
$string['actions'] = 'Działania';
$string['appraisals:archived'] = 'Archiwalne Formularze Oceny';
$string['appraisals:current'] = 'Bieżące Formularze Oceny';
$string['appraisals:noarchived'] = 'Nie masz archiwalnych formularzy oceny';
$string['appraisals:nocurrent'] = 'Nie masz bieżących formularzy oceny';
$string['comment:adddots'] = 'Dodaj komentarz...';
$string['comment:addingdots'] = 'Dodaje...';
$string['comment:addnewdots'] = 'Dodaj nowy komentarz';
$string['comment:showmore'] = '<i class="fa fa-plus-circle"></i> Pokaż więcej';
$string['comment:status:0_to_1'] = '{$a->status} - Formularz oceny został stworzony, ale jeszcze nie rozpoczęty.';
$string['comment:status:1_to_2'] = '{$a->status} - Formularz oceny został rozpoczęty przez Ocenianego.';
$string['comment:status:2_to_3'] = '{$a->status} - Fomularz oceny został przesłany do Oceniającego.';
$string['comment:status:3_to_2'] = '{$a->status} - Formularz oceny został zwrócony do Ocenianego.';
$string['comment:status:3_to_4'] = '{$a->status} - Formularz oceny oczekuje na komentarze ze strony Ocenianego.';
$string['comment:status:4_to_3'] = '{$a->status} - Formularz oceny został zwrócony do Oceniającego.';
$string['comment:status:4_to_5'] = '{$a->status} - Oczekuje na osobę oceniającą by wysłała do ostatecznego zatwierdzenia.';
$string['comment:status:5_to_4'] = '{$a->status} - Formularz oceny został zwrócony Ocenianemu.';
$string['comment:status:5_to_6'] = '{$a->status} - Wysłany do użytkownika dokonującego ostatecznego zatwierdzenia.';
$string['comment:status:6_to_7'] = '{$a->status} Formularz oceny jest kompletny.';
$string['comment:updated:appraiser'] = '{$a->ba} zmienił osobę oceniającą z {$a->oldappraiser} na {$a->newappraiser}.';
$string['comment:updated:signoff'] = '{$a->ba} zmienił osobę zatwierdzającą z {$a->oldsignoff} na {$a->newsignoff}.';
$string['index:togglef2f:complete'] = 'Zaznacz spotkanie F2F jako odbyte.';
$string['index:togglef2f:notcomplete'] = 'Zaznacz spotkanie F2F jako nieodbyte.';
$string['index:notstarted'] = 'Nie rozpoczęty';
$string['index:notstarted:tooltip'] = 'Oceniany nie rozpoczął jeszcze procesu oceny, w momencie gdy rozpocznie bedziesz mógł mieć do niego dostęp.';
$string['index:printappraisal'] = 'Ściągnij Formularz Oceny';
$string['index:printfeedback'] = 'Ściągnij formularz oceny zwrotnej';
$string['index:start'] = 'Rozpocznij Proces Oceny';
$string['index:toptext:appraisee'] = 'Ten pulpit nawigacyjny pokazuje bieżące i archiwalne formularze oceny . Twój bieżący formularz oceny możesz uzyskać pod rozwijanym linkiem Działania. Archiwalne formularze oceny możesz ściągnąć używając guzika Ściągnij Formularz Oceny poniżej.';
$string['index:toptext:appraiser'] = 'Ten pulpit nawigacyjny pokazuje bieżące i archiwalne formularze oceny, gdzie jesteś osobą oceniającą.  Bieżące formularze oceny możesz uzyskać pod rozwijanym linkiem Działania. Informacja zwrotna będzie dostępna dla Osoby Ocenianej dopiero po osobistym spotkaniu. Wszelkie poufnr informacje zwrotne pozostaną ukryte na wszystkich etapach procesu. Archiwalne formularze oceny możesz ściągnąć używając guzika Ściągnij Formularz Oceny poniżej.';
$string['index:toptext:groupleader'] = 'Ten pupit nawigacyjny pokazuje bieżące i archiwalne formularze oceny w Twoim centrum kosztowym. Obecne formularze oceny możesz uzyskać pod rozwijanym linkiem Działania. Archiwalne formularze oceny możesz ściągnąć używając guzika Ściągnij Formularz Oceny poniżej.';
$string['index:toptext:signoff'] = 'Ten pulpit nawigacyjny pokazuje bieżące i archiwalne formularze oceny, dla których jesteś osobą dokonującą ostatecznej weryfikacji. Obecne formularze oceny możesz uzyskać pod rozwijanym linkiem Działania. Archiwalne formularze oceny możesz ściągnąć używając guzika Ściągnij Formularz Oceny poniżej.';
$string['index:view'] = 'Zobacz Formularze Oceny';
$string['timediff:now'] = 'Teraz';
$string['timediff:second'] = '{$a} sekunda';
$string['timediff:seconds'] = '{$a} sekundy';
$string['timediff:minute'] = '{$a} minuta';
$string['timediff:minutes'] = '{$a} minuty';
$string['timediff:hour'] = '{$a} godzina';
$string['timediff:hours'] = '{$a} godziny';
$string['timediff:day'] = '{$a} dzień';
$string['timediff:days'] = '{$a} dni';
$string['timediff:month'] = '{$a} miesiąc';
$string['timediff:months'] = '{$a} miesiące';
$string['timediff:year'] = '{$a} rok';
$string['timediff:years'] = '{$a} lata';
$string['error:togglef2f:complete'] = 'Brak możliwości zaznaczenia spotkania F2F jako odbyte.';
$string['error:togglef2f:notcomplete'] = 'Brak możliwości zaznaczenia spotkania F2F jako nieodbyte.';
$string['appraisee_feedback_email_success'] = 'Wiadomość email wysłana';
$string['appraisee_feedback_email_error'] = 'Nie można wysłać wiadomości email';
$string['appraisee_feedback_invalid_edit_error'] = 'Nieprawidłowy adres wiadomości email';
$string['appraisee_feedback_inuse_edit_error'] = 'Adres wiadomości email już w użyciu';
$string['appraisee_feedback_inuse_email_error'] = 'Adres wiadomości email już w użyciu';
$string['appraisee_feedback_resend_success'] = 'Wiadomość email pomyślnie wysłana';
$string['appraisee_feedback_resend_error'] = 'Błąd podczas próby ponownego wysłania wiadomości e-mail';
$string['form:add'] = 'Dodaj';
$string['form:language'] = 'Język';
$string['form:addfeedback:alert:cancelled'] = 'Wysyłanie anulowane, Twój formularz oceny zwrotnej nie został wysłany.';
$string['form:addfeedback:alert:error'] = 'Przepraszamy, wystąpił błąd podczas wysyłania Twojej oceny zwrotnej.';
$string['form:addfeedback:alert:saved'] = 'Dziękujemy, Twoja ocena zwrotna została pomyślnie wysłana.';
$string['form:feedback:alert:cancelled'] = 'Wysyłanie anulowane, Twoja ocena zwrotna nie została wysłana. ';
$string['form:feedback:alert:error'] = 'Przepraszamy, wystąpił błąd podczas wysyłania prośby o ocenę zwrotną.';
$string['form:feedback:alert:saved'] = 'Twoja prośba o ocenę zwrotną została pomyślnie wysłana.';
$string['form:lastyear:nolastyear'] = 'Uwaga: Zauważyliśmy, że nie masz poprzedniego formularza oceny w systemie. Proszę dodaj ostatni formularz ocenę jako dokument PDF / Word poniżej.';
$string['form:lastyear:file'] = '<strong>Plik z przeglądem został przesłany przez Osobę Ocenianą: <a href="{$a->path}" target="_blank">{$a->filename}</a></strong>';
$string['form:lastyear:cardinfo:developmentlink'] = 'Zeszłoroczne Działania Rozwojowe';
$string['feedbackrequests:description'] = 'Ten panel pokazuje wszystkie zaległe prośby o ocenę zwrotną i umożliwia dostęp do wszelkich informacji zwrotnych, których udzieliłeś w przeszłości.';
$string['feedbackrequests:outstanding'] = 'Zaległe Wnioski';
$string['feedbackrequests:norequests'] = 'Brak zaległych wniosków o ocenę zwrotną';
$string['feedbackrequests:completed'] = 'Wypełnione Wnioski';
$string['feedbackrequests:nocompleted'] = 'Niewypełnione Wnioski';
$string['feedbackrequests:th:actions'] = 'Działania';
$string['feedbackrequests:emailcopy'] = 'Wyślij mi kopię';
$string['feedbackrequests:submitfeedback'] = 'Zatwierdź ocenę zwrotną';
/*
$string['email:subject:myfeedback'] = 'Twoja ocena zwrotna dla {{appraisee}}';
$string['email:body:myfeedback'] = 'Drogi/a {{recipient}},
<br/>
Złożyłeś następującą {{confidential}} ocenę zwrotną dla {{appraisee}}: {{feedback}} {{feedback_2}}';
*/
$string['feedbackrequests:confidential'] = 'Poufny';
$string['feedbackrequests:nonconfidential'] = 'Jawny';
$string['success:checkin:add'] = 'Dodano spotkanie śródroczne';
$string['error:checkin:add'] = 'Brak możliwości dodania spotkania śródrocznego';
$string['error:checkin:validation'] = 'Proszę wprowadzić tekst';
$string['checkin:deleted'] = 'Usunięte spotkanie śródroczne';
$string['checkin:delete:failed'] = 'Próba usunięcia spotkania śródrocznego zakończona niepowodzeniem';
$string['checkin:update'] = 'Aktualizacja';
$string['checkin:addnewdots'] = 'Spotkanie śródroczne...';

//pdf
$string['pdf:form:summaries:appraisee'] = 'Komentarz osoby ocenianej';
$string['pdf:form:summaries:appraiser'] = 'Podsumowanie oceny przez osobę oceniającą';
$string['pdf:form:summaries:signoff'] = 'Podsumowanie przez osobę kończącą proces';
$string['pdf:form:summaries:recommendations'] = 'Uzgodnione działania';

//user info
$string['form:userinfo:intro'] = 'Proszę uzupełnij poniższe szczegóły. Niektóre z pół zostay uzupełnione danymi z TAPS, jeśli jakiekolwiek informacje są błędne proszę skontaktować się z lokalnym przedstawicielem HR.';
$string['form:userinfo:name'] = 'Imię osoby ocenianej';
$string['form:userinfo:staffid'] = 'Numer Pracownika';
$string['form:userinfo:grade'] = 'Grade';
$string['form:userinfo:jobtitle'] = 'Nazwa stanowiska';
$string['form:userinfo:operationaljobtitle'] = 'Operacyjna nazwa stanowiska';
$string['form:userinfo:facetoface'] = 'Proponowana data spotkania';
$string['form:userinfo:facetofaceheld'] = 'Spotkanie odbyło się';

//feedback
$string['feedbackrequests:received:confidential'] = 'Otrzymano (poufne)';
$string['feedbackrequests:received:nonconfidential'] = 'Otrzymano';
$string['feedbackrequests:paneltitle:confidential'] = 'Informacja zwrotna (poufna)';
$string['feedbackrequests:paneltitle:nonconfidential'] = 'Informacja zwrotna';
$string['feedbackrequests:legend'] = '* oznacza osobę/współpracownika dodaną przez osobę oceniającą';
$string['form:addfeedback:notfound'] = 'Nie znaleziono prośby o informację zwrotną';
$string['form:addfeedback:sendemailbtn'] = 'Wysłano informację zwrotną';
$string['form:addfeedback:closed'] = 'Okno do złożenia informacji zwrotnej jest zamknięte ';
$string['form:addfeedback:submitted'] = 'Informacja zwrotna złożona';

$string['form:feedback:alert:cancelled'] = 'Wysyłanie anulowane, informacja zwrotna nie została wysłana.';
$string['form:feedback:alert:error'] = 'Przepraszamy, wystąpił błąd podczas wysyłania informacji zwrotnej.';
$string['form:feedback:alert:saved'] = 'Wysyłanie informacji zwrotnej zakończone sukcesem.';
$string['form:feedback:email'] = 'Adres e-mail.';
$string['form:feedback:firstname'] = 'Imię';
$string['form:feedback:lastname'] = 'Nazwisko';
$string['form:feedback:language'] = 'Wybierz język informacji zwrotnej';


// END PO STRING TRANSLATIONS - SPREADSHEET

// 2017 : UPdates and additions.
$string['addreceivedfeedback'] = 'Dodaj otrzymaną opinię';
$string['appraisee_feedback_savedraft_error'] = 'Wystąpił błąd podczas zapisywania wersji roboczej';
$string['appraisee_feedback_savedraft_success'] = 'Zapisz wersję roboczą opinii';
$string['appraisee_feedback_viewrequest_text'] = 'Wyświetl wiadomość e-mail z prośbą.';
$string['appraisee_welcome'] = 'Twoje spotkanie oceniające jest dla Ciebie i Twojego Oceniającego okazją do rozmowy na temat wyników, rozwoju kariery, Twojego przyszłego wkładu dla rozwoju firmy. Chcemy, aby była to konstruktywna rozmowa, która jest osobista i przydatna dla wszsytkich.<br /><br />
Celem tego narzędzia online jest utwralenie przeprowadzonej rozmowy i odwoływanie się niej przez cały rok.<br /><br />więcej informacji na temat przeprowadzonego spotkania znajdziesz<a href="https://moodle.arup.com/appraisal/essentials" target="_blank"> tutaj</a>';
$string['appraisee_welcome_info'] = 'Termin tegorocznego spotkania został wyznaczony na {$a}.';
$string['email:body:appraiseefeedback'] = '{{emailmsg}} <br> <hr> <p>Kliknij {{link}} aby dodać opinię.</p>
<p>Appraisal Name {{appraisee_fullname}}<br>
 My appraisal is on <span class="placeholder">{{held_date}}</span></p>
<p>This is an auto generated email sent by {{appraisee_fullname}} to {{firstname}} {{lastname}}.</p> <p>If the link above does not work, please copy the following link into your browser to access the appraisal:<br />{{linkurl}}</p>';
$string['email:body:appraiseefeedbackmsg'] = 'Drogi <span class="placeholder bind_firstname">{{firstname}}</span>,</p>
<p>
Zbliża się termin mojego spotkania oceniającego<span class="placeholder">{{held_date}}</span>. Moim Oceniającym jest <span class="placeholder">{{appraiser_fullname}}</span>. Ponieważ pracowaliśmy razem w ciągu ubiegłego roku, bardzo ważne byłoby dla mnie gdybyś wyraził/a swoją opinię co bceniłeś/aś w moim  działaniu oraz co mógłbym/mogłabym zrobić inaczej. Jeśli wyrażasz zgodę kliknij poniższy link aby przesłać swoją opinię.</p> <p>
Byłbym wdzięczny za odpowiedź przed moim spotkaniem. </p>
<p class="ignoreoncopy">Poniżej znajdziesz dodatkowy formularz<span class="placeholder">{{appraisee_fullname}}</span>:<br /> <span>{{emailtext}}</span></p> <p>Z poważaniem,<br /> <span class="placeholder">{{appraisee_fullname}}</span></p>';
$string['email:body:appraiserfeedback'] = '{{emailmsg}} <br> <hr> <p>Kliknij {{link}} aby dodać opinię.</p>
<p>Nazwa spotkania {{appraisee_fullname}}<br>
Moje spotkanie jest wyznaczone na <span class="placeholder">{{held_date}}</span></p>
<p>To jest automatycznie wygenerowany mail przez {{appraiser_fullname}} do {{firstname}} {{lastname}}.</p> <p>Jeśli link powyżej nie działa, proszę spokój załączony link do przeglądarki aby uzyskać dostęp do formularza:<br />{{linkurl}}</p>';
$string['email:body:appraiserfeedbackmsg'] = '<p>Drogi<span class="placeholder bind_firstname">{{firstname}}</span>,</p>
<p>Spotkanie oceniające dla <span class="placeholder">{{appraisee_fullname}}</span> zostało wyznaczone na<span class="placeholder">{{held_date}}</span>.  Ponieważ pracowaliście razem w ciągu ostatniego roku, bardzo ważne byłoby gdybyś wyraził/a swoją opinię co według Ciebie było wartościowe w jego/jej działaniu oraz co mógłby/mogłaby poprawić. Jeśli wyrażasz zgodę kliknij link poniżej aby przesłać swoją opinię.</p> <p>Byłbym wdzięczny za odpowiedź przed moim spotkaniem. </p>
<p class="ignoreoncopy">Poniżej znajdziesz dodatkowy formularz<span class="placeholder">{{appraiser_fullname}}</span>:<br /> <span>{{emailtext}}</span></p>
<p>Z poważaniem<br /> <span class="placeholder">{{appraiser_fullname}}</span></p>';
$string['email:body:myfeedback'] = '<p>Drogi {{recipient}},</p> <p>Wysłałeś/aś poniższą {{confidential}}  informację zwrotną dla {{appraisee}}:</p> <div>{{feedback}}</div><div>{{feedback_2}}</div>';
$string['email:subject:myfeedback'] = 'Twoja informacja zwrotna dla {{appraisee}}';
$string['error:noappraisal'] = 'Błąd - nie masz formularza oceniającego w systemie. Aby uzyskać pomoc, skontaktuj  się z Administratorem systemu oceny, jeśli potrzebujesz wypełnić formularz:
{$a}';
$string['feedback_header'] = 'Przekaż opinię {$a->appraisee_fullname} (Oceniający: {$a->appraiser_fullname} - Data spotkania: {$a->facetofacedate})';
$string['feedback_intro'] = 'Wybierz proszę trzy lub więcej osób, które przekarzą swoją opinię o Tobie. W większości regionów informacja zwrotna może być wewnętrzna lub zewnętrzna. Więcej informacji znajdziesz w przewodniku danego regionu.<br/><br/> W przypadku wewnętrznych opiniodawców warto rozważyć zebranie informacji zwrotnej z perspektywy "360 stopni" czyli rówieśników, starszych oraz młodszych od Ciebie. Warto wybraż zróżnicowana grupę osób.<br/><br/><div data-visible-regions="UKMEA, EUROPE, AUSTRALASIA">Jednym z opiniodawców może być klient lub współpracownik, który zna cię bardzo dobrze.</div><div data-visible-regions="East Asia"><br /><div class="alert alert-warning">For East Asia region, we expect feedback to be from internal source only. Comments from external client or collaborator should be understood and fed back through internal people.</div></div> <div data-visible-regions="Americas"><br /><div class="alert alert-warning">For the Americas Region, comments from external clients or collaborators should be fed back through conversations gathered outside of this feedback tool.</div></div>
<br /><div class="alert alert-danger"> Uwaga: informacja zwrotna opiniodawców zostanie opublikowana tuż po jej otrzymaniu, chyba że Oceniający zażądał odpowiedzi zwrotnej. W tym przypadku Oceniający musi przesłać ci formularz do wprowadzenia ostatecznych poprawek (etap 3) aby informacja zwrotna była widoczna dla ciebie.</div>';
$string['feedbackrequests:paneltitle:requestmail'] = 'Wiadomość e-mail zwrotna.';
$string['form:addfeedback:addfeedback'] = 'Opisz proszę trzy obszary, w których osoba oceniana wniosła istotny wkład w ostatnich 12 miesiącach';
$string['form:addfeedback:addfeedback_2'] = 'Proszę podaj szczegóły dotyczące trzech obszarów, w których osoba oceniana może być bardziej efektywna. Bądź szczery, ale konstruktywnie krytyczny, tak aby Twoja opinia zwrotna pomogła koledze być bardziej efektywnym.';
$string['form:addfeedback:addfeedback_2help'] = '<div
class="well well-sm">Ważne jest aby wszyscy otrzymali wartościowe, zrównoważone informacje zwrotne, zarówno pozytywne jak i krytyczne. <br>Aby uzyskać więcej informacji, kliknij <a href="https://moodle.arup.com/scorm/_assets/ArupAppraisalGuidanceFeedback.pdf"
target="_blank">tutaj</a></div>';
$string['form:addfeedback:addfeedback_help'] = 'Proszę skopiuj i wklej opinię zwrotną w pole "cenny wkład" chyba że możesz podzielić opinię na "cenne" i "bardziej efektywne"';
$string['form:addfeedback:addfeedbackhelp'] = '<div
class="well well-sm">Ważne jest aby wszyscy otrzymali wartościowe, zrównoważone informacje zwrotne, zarówno pozytywne jak i krytyczne. <br>Aby uzyskać więcej informacji, kliknij <a href="https://moodle.arup.com/scorm/_assets/ArupAppraisalGuidanceFeedback.pdf"
target="_blank">tutaj</a></div>';
$string['form:addfeedback:firstname'] = 'Imię osoby wystawiającej opinię';
$string['form:addfeedback:saveddraft'] = 'Wysłałeś wersję roboczą Twojej opinii. Dopóki nie wyślesz swojej opinii zwrotej, będzie ona niewidoczna ani dla Oceniającego, ani dla Ocenianego';
$string['form:addfeedback:savedraftbtn'] = 'Zapisz jako kopię roboczą';
$string['form:addfeedback:savedraftbtntooltip'] = 'Zapisz kopię roboczą aby dokończyć później. Zapisanie kopii nie spowoduje wysłania Twojej opinii do osoby oceniającej / ocenianej';
$string['form:addfeedback:savefeedback'] = 'Zachowaj opinię zwrotną';
$string['form:development:comments'] = 'Komentarze Oceniającego';
$string['form:development:commentshelp'] = '<div class="well well-sm"><em>Do wypełnienia przez Oceniającego</em></div>';
$string['form:feedback:editemail'] = 'Edytuj';
$string['form:feedback:providefirstnamelastname'] = 'Proszę wpisać imię i nazwisko odbiorcy zanim klikniesz przycisk edycji.';
$string['form:lastyear:cardinfo:performancelink'] = 'Zeszłoroczny plan działania';
$string['form:lastyear:printappraisal'] = '<a href="{$a}" target="_blank">Zeszłoroczna ocena</a> jest dostępna do przejrzenia (PDF - opens in new window).';
$string['form:summaries:grpleaderhelp'] = '<div class="well well-sm"><em>Do uzupełniania przez lidera grupy.</em></div>';
$string['helppage:intro'] = 'Kliknij przycisk poniżej, aby uzyskać dostęp do strony wsparcia.';
$string['leadersignoff'] = 'Podpis Lidera Grupy';
$string['modal:printconfirm:cancel'] = 'Nie, jest ok';
$string['modal:printconfirm:content'] = 'Czy naprawdę potrzebujesz wydrukować ten dokument?';
$string['modal:printconfirm:continue'] = 'Tak, kontynuuj';
$string['modal:printconfirm:title'] = 'Zastanów się przed wydrukowaniem';
$string['overview:content:appraisee:3'] = 'Możesz przesłać forlumarz oceny do{$a->styledappraisername}  do sprawdzenia.<br /><br /> <strong>Następne kroki:</strong> <ul class="m-b-20">
<li>Spotkanie twarzą w twarz - przez spotkaniem możesz:</li> <ul class="m-b-0"> <li><a class="oa-print-confirm" href="{$a->printappraisalurl}">Pobrać wypełniony formularz oceny</a>
</li> <li><a
href="https://moodle.arup.com/appraisal/reference" target="_blank">Pobierz przewodnik</a></li> </ul> <li>Po spotakniu Oceniający prześle ci ponownie formularz oceny.  Zostaniesz poproszony o dokonanie zmian uzgodnionych w trakcie spotkania twarzą w twarz lub do wprowadzenia swoich ostatecznych komentarzy</li> </ul> <div class="alert alert-danger" role="alert"><strong>Note:</strong> Możesz nadal edytować formularz w czasie gdy Twój oceniający nanosi swoje uwagi. Pamiętaj, że warto do tego użyć panelu aktywności by podkreślić zmiany, które zostały naniesione.</div>';
$string['overview:content:appraiser:3'] = '{$a->styledappraiseename} wysłał wypełniony formularz oceny na wasze spotkanie twarzą w twarz.<br /><br /> <strong>
Następne kroki:</strong> <ul class="m-b-20"> <li>Proszę zapoznaj się z przygotowaną oceną przed spotkaniem. W razie konieczności zwróć formularz do Ocenianego, jeśli potrzebujesz dodatkowych informacji.</li> <li>Przed spotkaniem powinieneś:</li> <ul class="m-b-0"> <li><a class="oa-print-confirm" href="{$a->printappraisalurl}">Pobrać wypełniony formularz oceny</a></li> <li><a class="oa-print-confirm" href="{$a->printfeedbackurl}">Pobrać wszystkie otrzymane opinie</a></li> <li>Możesz również <a href="https://moodle.arup.com/appraisal/reference" target="_blank">pobrać przewodnik</a></li> </ul> <li>Po spotkaniu twarzą w twarz</li> <ul class="m-b-0"> <li>Zaznacz, że spotkanie twarzą w twarz się odbyło w sekcji informacji dla Ocenianego</li> <li>Dodaj komentarze do każdej sekcji</li> <li>Napisz podsumowanie i uzgodnione działania w sekcji podsumowania </li> (w razie potrzeby możesz zwrócić formularz do Ocenianego do modyfikacji przed wprowadzeniem swoich komentarzy.) </ul> <li>Wyślij formularz do Ocenianego aby zapoznała sie z twoimi komentarzami, przeczytaj opinie i dodaj końcowe komentarze</li> </ul>';
$string['overview:content:signoff:5'] = 'Ocena w toku.<br /><br /><div class="alert alert-danger" role="alert"><strong>Uwaga:</strong> Zostaniesz powiadomiony, gdy formularz będzie gotowy do sprawdzenia i ostatecznego zatwierdzenia.</div>';
$string['overview:content:special:archived'] = '<div class="alert alert-danger" role="alert">Formularz został zarchiwizowany.<br />Teraz można jedynie <a class="oa-print-confirm" href="{$a->printappraisalurl}">pobrać dokument.</a>.</div>';
$string['overview:content:special:archived:appraisee'] = '<div class="alert alert-danger" role="alert">Formularz został zarchiwizowany.<br />Teraz można jedynie<a class="oa-print-confirm" href="{$a->printappraisalurl}">pobrać dokument</a>.</div>';
$string['overview:lastsaved'] = 'Ostatni zapis: {$a}';
$string['overview:lastsaved:never'] = 'Nigdy';
$string['pdf:feedback:confidentialhelp:appraisee'] = '# Oznacza poufne opinie, które są niewidoczne dla ciebie.';
$string['pdf:feedback:notyetavailable'] = 'Jeszcze niewidoczne.';
$string['pdf:feedback:requestedfrom'] = 'Recenzent {$a->firstname}{$a->lastname}{$a->appraiserflag}{$a->confidentialflag}:';
$string['pdf:feedback:requestedhelp'] = '* Oznacza informacje zwrotne przekazane przez Oceniającego, które jeszcze są niewidoczne dla Ciebie.';
$string['pdf:header:warning'] = 'Pobrane przez: {$a->who} on {$a->when}<br>Proszę nie zachowywać i nie pozostawiać w niezabezpieczonym miejscu.';
