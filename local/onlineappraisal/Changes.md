20160509 11:05 - Bas
Had to fix some SQL to make this work on my system. Search for "// Note Bas" in lib.php for more info.

20160509 12:55 - Bas 
Add in new files and structure.

20160510 16:00 - Bas
Add forms in /forms
Add view.php
Add main controller : class online_appraisal in /locallib.php
Add form controller : class appraisal_form in /locallib.php
Add new tables in /db/install.xml /db/upgrade.php
Bump version
Add renderer for navigation
Move CSS to LESS and add Gruntfile.js package.json. Please run `npm install` to initialize the node_modules folder

20160511 17:00 - SJL
Added appraisal loader and id is passed to form class and used for data loading/saving.
Need to consider user types (determine and load/set in appraisal object?).
    What if user has two types on same appraisal (as often used in testing, although not _strictly_ allowed)?
    I don't think the validation for this is that great and likely relies purely on what is presented in the <select> elements when initialising an appraisal.
Not sure about best way to print errors as core functionality always links back to moodle.org which is pointless for a plugin.
What is data_id field for in local_appraisal_forms table?
Need textarea_modal renderer from demo activity for various forms (I have temporarily replaced renderer loading call and commented out method calls to dodge errors).
Initial implementation of dashboard renderer and overview page renderer (needs work).

20160516 17:00 - SJL
Added injection of groupleader ID (via costcentre plugin) and an is_groupleader check/flag.
Added array of user types and flags to show whether user object must be loaded, e.g. groupleader may not be set and isn't required.
Added a viewingas variable (url param) to indicate the view for the user (in case of multiple roles on an appraisal for a single user).
Moved user picture injection out of controller to allow earlier loading of information.
    Can now be loaded in constructor again which makes more sense.
    Renderer can/should now set up user pictures as and when needed.
Added new dashboard page for introduction.
Added comment stream to overview dashboard page along with necessary templating and intital JS for adding new comments.
    This also includes a modals injection system.
Reworked a number of the overview dashboard renderers.
    Some still need work.

20160517 8:30 - Bas
Split up the less file in several files.
Create a new forms less file and use mailchimp form patterns as a example

20160518 18:25 - Bas
Move the appraisal logic to /classes folder.
Move forms to /classes folder.
Add the permissions system as a separate class.
Add DB table to store permissions and write logic to fill table.
Move output renderers for navigation block to /classes/output.
Add some debugging info in navigation block.
Defined Statics but not used them yet.

20160519 15:45 BST - SJL
Added back Gruntfile.js and included amd build task(s) (updated packages file accordingly).
Updated comment rendering to show user type and allow addition at any time by anyone (might need to think about email notifications?).
Moved output renders for dashboard pages to /classes/output and updated accordingly.
Stripped back lang file to new strings only and included old strings as a legacy file so we can work on deprecation.

20160520 13:00 BST - SJL
Adding in new admin controller and initial renderers/templates for pages.
Tweak to hide debug info in navlist if not set.

20160526 17:00 - Bas
Added AMD AJAX Webservices for the feedback sample mail
Moved lang strings to use {{variable}} type placeholders
2way databinding for Firstname / Lastname element in Add Contributer feedback element
Update the appraisal->add_page() function to take fewer parameters
Added a class loader to the add_page() and get it to execute a hook() function

20160527 17:00
Action functions for the appraisal. This tracks actions on the page and can be used to retreive status messages on those actions
Added a function hook for forms in case we need a different way of storing form data
Rewrote feedback system to replace adding and sending feedback requests

20160602 13:00 BST - SJL
Completed initial development of admin pages.

20160606 09:00 BST - SJL
Data grabbing (webservice), comment adding and email sending integrated with appraisal initialisation.
Data migration no longer needed as info will be presented on-the-fly.
Centralised email class added based on Bas's template variable replacements.

20160607 17:00 BST - SJL
Updating of progress bars.
Comments bugfix.
Dashboards created and display primarily working (no check-in display functionality).

20160707 20:00 - BAS
Add intro form as first form
Create function to change appraisal status.
Updated all forms
Add colours to form elements for different user types
Add new lang strings for all forms
Update CSS for forms
Add new permissions
Bumped version (will update permissions, see db/upgrade.php)
@TODO: Lock form fields for correct user types
@TODO: Form processing
@TODO: pre-populate User Inf

20160608 13:30 BST - SJL
Updated permissions class to not require instance of appraisal class.
Added updated permissions.
    NB. Made adjustment to treatment of 'all' in respect of statuses as nothing should be possible at this stage except for appraisee who will be bumped to the intro page.
Bumped version to update permissions.
Updated dashboard pages to use single template (as all intrinsically the same) and partial for repeated table and check permissions to present actions.
Removed numbers from progress bars.
Fixed bug with special dashboard view permissions check.

20160609 11:00 BST - SJL
Added filtering to tables in relevant dashboards and admin pages.
Added row highlighting to relevant dashboards for those appraisals requiring action.
    NB. Added a requires action property to index class - might need to move this somewhere else if needed elsewhere.
Added mark f2f complete functionality to dashboards.
Updated action links in dashboard (now point to what should be the right places, though print entry point doesn't exist yet).

20160610 15:45 BST - SJL
Updating overview page to new layout.
    New overall layout.
    New progress panel
    Previous callout has become summary column on left.
    Added activity log on right which includes comment stream and new user panel (highlighted icons).

20160613 14:00 BST - SJL
Updating overview page to new layout.
    Updated comment rendering/addition completed.

20160614 13:30 BST - SJL
Updated permissions class to match latest version of capabilities matrix.
Updated permissions class to use caching and added rebuild functionality (which can be called from plugin settings page).
Moved status definitions to locallib.php, stripped out old (commented) code, and add as a required file to entry pages (admin/index/view).
Added permissionsid field to local_appraisal_appraisal table to accommodate appraisals needing different permissions status to actual status (when returning).
    Upgrade automatically sets this to match the statusid.
Added legacy field to local_appraisal_appraisal table to mark appraisals from previous system (which will be print only via legacy print functionality).
    This is set to true if an associated row is found in local_appraisal_summary as this table is a) no longer used and b) a row was automatically added on initialisation.
Added initial work on stages controller. Currently only provides a method to pass a new status, check the change is valid and apply it (updating permissionsid and status_history).

20160615 16:00 BAS
Added a new dropdown menu to the theme. The menu is rendered by the online appraisal using output renderers, mustache teplates etc.
For this commit the theme has been altered too. Currently it renders both the new and the old menu (renamed to Old).
Where possible the new menu uses the nu capabilities system for checking permissions.]
Updated the Impact plan form to lock the appraiser field.
Bumped version

20160617 13:45 BST - SJL
Bugfix in bootstrap datepicker to fix issue when setting zIndexOffset.
    Used zIndexOffset to bring in front of fixe header.
Major updates to stages controller and implementation.
    @Bas: See update in appraisal class re error messages.
Revamp of overview renderer.
Various CSS updates.
Templates updates for overview and comments.
Couple of docblocks tidy ups spotted along the way.

20160620 22:00 BST - SJL
Completed integration of stages controller with overview page.
    Form processing initiated via hook.
Removed $owner property from stages class as checks now made using permissions class (purposefully based on status id _NOT_ permissions id).
Moved set_action() call in appraisal constructor above page set up, to avoid resetting action to blank after being set in a page hook.
Bumped version.

20160621 13:15 BST - SJL
Enabled ability to have alternate overview instruction language strings when status id and permissions id are different.
    Existence of alternate checked and falls back to status only string if not present.
Updated overview instruction language strings to be descriptive placeholders.
Tweaks to validation tooltip display on overview page (inc. increasing max width in CSS).
Bumped version to trigger cache clearance and JS/CSS reload.

20160622 15:00 BAS
On Forms a Submit and Continue and a Reset button have been added
From Buttons only show if the user is allowed to send some form data
More permission checks on form fields
Permission checks on adding feedback (needs extra checks on the add feedback page)
Add appraisee name on top of forms

20160623 13:00 BST - SJL
Updated dashboards to allow (given permissions) full toggling of F2F held status (on/off).
Updated dashboards to provide interface to update F2F date (given permission).
Removed modal injection code as not being used.
Updated comments to trigger sending of email when comment added (not for system comments or those added via stage changes).
    @Bas: This may be useful for you if we need to provide email notification when a check in is added?
Updated events and their triggers.
Updated stage changes to store success/error message (if DB update successful) to session and redirect before displaying to avoid possible form resubmission.
Various template and CSS tweaks.
Bumped version  to trigger cache clearance and JS/CSS/strings reload.

20160623 14:00 BST - SJL
Fix for missing tooltips in Chrome/IE when button is disabled.
Bumped version.

20160623 14:00 BAS
Added checkins page logic copied mostly from the comments class and logic
Bump version to trigger DB install
@TODO: Add edit / delete to checkins

20160624 10:00 BST - SJL
Bugfix for incorrect injected comment when initialising.
Removed held_date validation from status 2 submission and added to status 3 submission.
Added held date validation for marking as held on dashboard.
Updated styling for progess SVG.
Changed styled name replacements for overview page and added plain name replacement.
    e.g. {$a->plainappraiseename} and {$a->styledappraiseename}
Bumped version.

20160624 11:00 BAS
Force user to set face2face date before requesting feedback
Update Feedback to use email class
Tried to update Feedback for to use ajax.php (failed)
Moved Feedback
Lang file changes
Bugfixes

20160627 10:00 SJL
Added permissions building to install script.

20160627 12:00 SJL
Merged in updated language strings.
Minor formatting tweaks.
Version bump.

20160627 15:00 SJL
Removal of old code and associated fixes/tweaks.
Version bump.

20160628 10:48 BAS
Fix form storage engine. There was a wrong userid set in the forms table when data was stored.
Remove unused DB fields in forms table
unset submitandcontinue button.

20160628 19:18 BAS
Update the feedback sending. Appraisers now have their own form text
Moved from webservice JS to our own Ajax JS.

20160629 11:00 SJL
Updated permissions as per discussion with Chris (though left checkin permissions as were as modified redirection works ok for it).
    Definition of 'all' tweaked'.
    Added utility cache check function.
    Added permission exists function.
    All pages now have permissions checks, falls back to appraisal:view check if none exist.
    Only pages the current user has permissions on to view/add are added to the access/nav list.
Check for showing introduction page now carried out early and only for appraisee, other users have no access until statusid == 2.
Added new flag when adding pages to indicate if redirection to page is allowed (for save and continue).
    Save and continue will now only redirect to next page if a) available to current user and b) allows redirection to it
    On last page redirection will be back to overview.
    Forms class modified as submitcontinue data unset for saving but needed for redirect.
Admin page, appraiser/signoff update, updated to use deep cloning of objects to resolve issue of multiple entries being marked as selected.
Introduction page updated to show a continue button if appraisal already started and appraisee revisiting.
Dashboards updated to show start appraisal option to appraisee if not started and warning (with info tooltip) to other users.
Added overview language strings for appraisee and appraiser for statusid 2, permissions id 3.
Bumped version and permissions rebuild included in upgrade script.

20160629 12:00 BAS
Fix Feedback resend 3 emails bug
Add subtitle to checkins page

20160629 16:30 SJL
Admin: Fix for permissions id not being set on initialisation.
Navbarmenu: Fix for archived appraisals showing in count.
            Also moved some logic in the process as needed access to appraisal variables.
            Associated changes in template.

20160630 14:15 SJL
Merged in master branch (primarily to pull through earlier local_costcentre bugfix).
Modified feedback requests display to utilise permissions class.
Modified index to pick an available dashboard page if not set (previously defaulted to appraisee).
Navbarmenu: Only count feedback as active if not archived as well as being able to submit.
            Show link if any completed feedback exists (as will appear in completed requests table).
            Fixed bug where active feedback count wasn't displaying.
Lang string tweaks.
Version bump.

20160701 10:00 BAS
Fix submitting Feedback
Fix another navmenu issue
fix date select JS

20160701 10:30 SJL
Updated overview page/stages validation to provide begin button for appraisee when no data entered. (Previous commit)
Added breadcrumbs to all pages:
    add_feedback.php
    admin.php
    feedback_requests.php
    index.php
    view.php
Bumped version.

20160701 15:46 BAS
Hide debug if debugging off
Add status fa icons to feedback
Add next button on feedback page
Rename submit buttons to save
Update checkins. New styling, enable delete, variable template in comment.js
@todo: edit checkin

20160701 1600 SJL
Added language menu and print options to breadcrumb bar.
    @TODO: Neither work yet but provide example for creating learning.
Updated language strings to replace characters causing issues.
Version bump.

20160704 13:50 SJL
Updated language strings.
Version bump.

20160706 12:00 SJL
Form button colour updates.
Version bump.

20160706 16:20 SJL
PDF generation
    Feedback.
    Legacy appraisal.
    New appraisal.
Version bump.

20160707 08:45 SJL
Next button/string update.
Version bump.

20160707 16:15 SJL
Lang string updates.
Version bump.

20160708 12:00 BAS
Add last year info to Last Year Review Form
Add access checks for file URLS
Minor tweaks from meeting with Chris

20160708 13:45 SJL
Previous updates
    Added JS hijack of logo clicks to redirect to appraisal index.
    Improved error handling to display more user friendly messages.
Permissions update
    Permissions now built with added flags as to whether they are allowed for archived and/or legacy appraisals.
    Permissions check now require passing of archived and legacy states.
    Access to print, view (i.e. overview, inc. comments) allowed for both archived and legacy.
    Access to add comments allowed for legacy (if not archived).
    All other access is restricted.
    Upgrade script adds extra fields and rebuilds permissions cache.
Overview page update.
    Added top of page alerts if appraisal is archived and/or legacy.
    Updated overview contents to show archived/legacy messages rather than standard (one general one which can be customised for user type/permissions status by adding extra language strings).
Version bump.

20160711 16:00 SJL
Previous commits:
    Bugfixes (introduction link permissions, stages check).
    Lang string updates and typo fix.
Lang string update to fix image for introduction page.
Lang string updates to ensure no core strings used, for translation purposes.
Datepicker bugfix when editing F2F date on dashboard.
Removal of unused code in some forms and bugfix for attribute array being passed to lang string (styling also now applied via previous less update).
Added 'Update Staff List' button to admin all staff page (no functionality yet).
Version bump.

20160711 17:00 SJL
Bugfix for contributor menu appearance and active count to ignore deleted appraisals.

20160712 12:30 SJL
Fix for redirect destination when cancelling forms.
    Default is overview page, can be overridden by returning moodle_url from cancel_redirect_url method in form class.
    Add feedback (contributor) form returns to Request Feedback page.

20160714 12:15 SJL
Updated permissions to allow archived/legacy ONLY permissions.
Added sixmonth form for legacy appraisals.
Added form submit/cancel redirect and flash message.
    Avoids possible refresh resubmission and enables feedback to user.
    Changed all reset buttons to cancel type to take advantage of this (wording also changed to cancel following discussion).
Updated JS.
    Now uses SlideDown/SlideUp for better presentation/removal of alerts.
    Added automatic dismissal of alerts with close button after 10s.
    Added confirmation when cancelling a form.
Updated renderers.
    Moved main renderer to classes/output.
    Updated alert renderer to use template and updated usage across plugin accordingly.
    Ensured all renderers extend main renderer.
Updated overview content to have multiple lang string fallbacks when legacy/archived.
    Main generic, specific for user type and specific for user type _and_ permissions id.
Removed unused table (appraisal_userstatus) from install.xml and dropped in upgrade.php.
Version bump.

20160714 13:20 SJL
Added alert to all (view) pages if language has been changed from default.
Version bump.

20160714 15:30 SJL
Adding (JS) comment hiding (all but first 4) and then button to show (4) more.
Version bump.

20160715 12:30 SJL
Language string updates as per Google Doc.
Improved form submission redirection/flash message.
    Updated action processing in appraisal class.
        set_alert() moved from form class.
        Sets flash message alert on complete/failed.
        No longer sets status for template (and calls/usage in templates removed).
    Updated feedback class to add post-processing redirect.
    Updated forms class.
        set_alert() moved to appraisal class.
        Ability to add custom cancelled/error/saved strings for an individual form and override defaults.
    Updated add_feedback.php to avoid possible output before redirect.
Only show language button/warning if not guest (as they may not be logged in).
Version bump.

20160715 15:30 SJL
Fixed timestamping of appraisal record in stages classes (modified/completed).
Added display of last years appraisal data for new style appraisals.
    Modified display to be consistent across both types.
    Ensured deleted appraisals are ignored.
Updated held_date injected in feedback request emails to remove time and match other dates.
Updated feedback_requests page to remove confidential column from outstanding and to fix icons and centre in columns.
Updated alert usage to set role tag.
Version bump.

20160719 09:20 SJL
Lang string tweaks.
Overview template tweak (button orientation).
Version bump.

20160720 11:30 SJL
Added language string for type of alert used for language warning (for translation/regional purposes).
Updated permissions to allow signoff and groupleader to add check-ins.
    Rebuild permissions called in upgrade script.
Removed lastname from 'Dear' line in feedback request emails.
Added 'No additional comments.' string to feedback request email if no comments supplied.
Added new field to lastyear form.
Added 'action required' highlight to nav list.
    Includes updates to stages class.
Latest lang string updates.
Version bump.

20160720 14:30 BAS
Checkins now editable
Checking no longer using comment js
Allow Checkin Deletion
User can ony edit / delete their own checkins.

20160720 16:00 SJL
Progress SVG update.
Lang string updates.
Version bump.

20160721 09:30 SJL
Latest language string updates.
Version bump.

20160721 10:15 SJL
Upgrade script to purge removed language strings from customisation tool.
Version bump.

20160721 13:00 SJL
Language string updates.
Update to menu action items, including overall tweak in theme/arup.
Version bump (and in theme/arup).

20160721 13:15 SJL
Added new form field to PDF.

20160721 14:00 SJL
Tweaks to menu action items.
Version bump.

20160721 16:00 BAS
Added language selection option for sending feedback
updated the email class to allow getting emails in specific languages.
Version bump.

20160721 16:30 SJL
Added in integration with cost centre plugin updates.
Version bump.

20160721 22:15 SJL
Latest language string updates.
Minor table layout tweaks.
Version bump.

20160722 10:25 SJL
Fix for incomplete URL being used when changing language.
Latest language string updates.
Version bump.

20160722 16:00 SJL
Added required base language packs to main Moodle lang dir.
    New config.php settings:
        Required:
            $CFG->langotherroot = dirname(__FILE__) . '/lang';
        Optional (recommended):
            $CFG->skiplangupgrade = true; // Disables import/update of language packs.
Added (currently empty) language packs to plugin.
Language string updates.
Version bump (to ensure new languages are loaded after cache purge).

20160804 10:00 BAS
Added a Setting for activating a 4th role on the Appraisal for the Groupleader on a costcentre
A new field is added to the summaries page for the groupleader.
The groupleader can edit this field just once.
A Modal with a confirmation message is shown when (a fake) submit button is clicked. The Modal contains the real Moodle submit buttons.
Todo: add to PDF

20160804 12:45 SJL
Fixed bug showing multiple 'view' links in actions dropdown for feedback (only affected testing with same user for appraisee/appraiser).
Fixed bug with active appraisal count (navbarmenu).
Added ability for appraisees with legacy appraisal to download PDF of non-confidential feedback.
    Permissions update and rebuild in upgrade script.
Version bump.

20160804 17:15 SJL
Language string updates.

20160805 9:30 Bas
Added notifications to summaries
Changed the form data storage hook in all forms to be able to pass the $forms and $appraisal object
Added a check to prevent overwriting alerts
Groupleader summary notification in comments
Groupleader summary email notifications to appraisee, appraiser, signof.

20160805 11:19 Bas
Introducing a new stage: 9
Groupleader notification when moving from stage 6 to 7
Text strings for stage 9 and stage 7 (when groupleader active)

20160810 15:00 SJL
Various bugfixes and tweaks from feedback (See Appraisal Tasks document)

20160811 11:10 SJL
Access prevention when logged in as a user at course level (Also hides main nav menu).

20160811 16:45 SJL
Various bugfixes/tweaks as per Appraisal Tasks document.
    Adding language change menu to other pages.
    Added last check-in information to user dashboards and to admin 'complete' dashboard.
    Added extra data to admin all staff page.
    Added the ability to set whether an appraisal is required or not.
        This determines whether the user appears on the initialisation page (but does not affect already in progress appraisals).
Updating all date display to use userdate() and definitions from language pack.
    Ensured dates that are set as UTC are presented based on UTC (i.e. those set by the datepickers and some legacy dates).
Fix for dashboard filtering on status (complete >= 7).
Version bump (DB table install).

20160815 11:15 SJL
Dashboard menu tweaks to include contributor dashboard and use same nav menu for feedback_requests page.

20160815 12:30 SJL
Added extra field for Americas to summaries.
    Only visible if appraisee (geo) region is Americas.
    Appraiser edit only (at same time as other fields).
    Appraisee can't view.
    Not in PDF.
    Hardcoded question/anwsers (not lang strings) for ease of future (expected) removal.

20160815 13:00 SJL
Split introduction lnaguage string into multiple region specific to make easier for editing/translation.

20160815 14:10 SJL
Ensure date formatting in feedback request emails honour the selected language/locale.

20160815 15:00 SJL
Ensure date formatting in initialisation email respects locale of translation used.

20160815 16:00 SJL
Bugfixes to resetting language after forcing.
Adding translations for datepicker.

20160815 21:00
Added pre-selection of appraiser and sign off (if still valid options), from latest archived appraisal, when initialising.

20160817 15:00 SJL
Updated to handle multiple groupleaders.
    User loading in appraisal updated to handle array of userids/users.
    Group leader active check updated.
    Updated summaries form:
        Added storing of current user id when submitting groupleader summary (to distinguish which user added).
        Updated email sending (sends from current user as will be the group leader adding their summary).
    Updated email sending in stages class to handle array of groupleader users.
        Group leader replacements (groupleaderfirstname/lastname/email) only available when sending _to_ group leader.
Integrated new 'HR Leader' cost centre role.
    Permissions are as per groupleader but without adding/editing capabilities.
    User loading in appraisal as array of userids/users (as per groupleader update).
    Added dashboard.
    Added link to dashboard to site header nav menu.
    Added some not pretty nested variable checks required in mustache templates.
New language strings:
    hrleader
    index:hrleader
    index:toptext:hrleader
    form:summaries:grpleadercaption
    // Following overview:content:hrleader:* strings simply set to mirror groupleader equivalents.
    overview:content:hrleader:1
    overview:content:hrleader:2
    overview:content:hrleader:3
    overview:content:hrleader:4
    overview:content:hrleader:5
    overview:content:hrleader:6
    overview:content:hrleader:7
    overview:content:hrleader:7:groupleadersummary
    overview:content:hrleader:8
    overview:content:hrleader:9
    navbar:hrleaderdashboard
Updated event logging to add new user type (hrleader) and multiple printing events for clarity in logs.
Version bump (permissions rebuild).

20160818 15:00 SJL
Updated admin inprogress page.
    Added F2F date and held columns.
    Added ability to edit F2F date if not yet marked as held.
Updated admin complete page.
    Removed progress column (as redundant).
    Added ability to edit appraiser and sign off users.
Dashboard (index) bugfixes for F2F date editing.
    Ensuring UTC timezone used when setting datepicker data elements.
    Ensuring datepicker is updated correctly if no date selected.
New language strings:
    error:f2fdate:update:held

20160819 12:15 SJL
Updated local_appraisal_users (user preference table) to store setting/value rather than needing a column for each setting.
    appraisalnotrequired setting migrated on upgrade.
Added localised language control.
    Each page calls lang_setup() immediately after setting initial $PAGE info.
        Gets selected language OR language from appraisal session OR language set in DB.
        Shutdown function registered to reset language.
Part fixed bug with bootstrap datepicker whereby parts of Moodle string were being treated as replacement placeholders.
    Works for display in input field and saving OK.
    Doesn't work with setting start/end/disabled dates using format OR with highlighting selected date on calendar.
Version bump.

20160822 16:45 SJL
Updated comment email sending to handle groupleader adding commenting/sending notification (to handle possibility of multiple).
Updated localewin string for Vietnamese as was failing.
Fixing issue with datepicker and non-punctutation separators.
Updated datepicker languages to match PHP date formatting outputs.
Added original and modified third party JS code to /vendor to support updating.
Updated 'language' string in feedback request form to not use core.
    New string: form:feedback:language
Updated remaining 'cancel' strings to not use core.

20160824 11:00 SJL
Updated auto comments adding to work for status change from 7 to 9.
Add groupleader link replacements to stages email.
Updated stages email sending to allow extra info to be injected if groupleaderactive flagged.
Added groupleader name alongside summary (if present) in PDF.
Added validation check in stages for 7 to 9 status change (so flag appears for groupleader in nav).

20160825 13:00 SJL
Fix for session language issues on redirect (due to session being closed).
    Internal redirects will pick up the need to teardown prior to setting up again.
    NB. If redirecting outside of plugin you _must_ call \local_onlineappraisal\lang_teardown() prior to redirect.
Fix for incorrect $a being passed to standalone status comment additions.

20160914 10:00 SJL
Updated group leader sign off (minimum viable solution) functionality.
    Now a selectable user (but only if groupleacder active flag is enabled for costcentre).
    See TAPS-1113 for further info.
