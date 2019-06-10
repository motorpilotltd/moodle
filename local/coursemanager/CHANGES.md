# Course Manager - CHANGES

Log of all changes within the plugin.

---

## 2017-03-15
- change SQL to allow case insensitive search
- show in class overview when maxattendants is unlimited (in DB -1)
- remove placeholder text in classname input
- add help string for classname input
- link to moodlcourse on class info page, or if no moodlecourse link to the add moodle course page
- swap class duration / units
- add dummy text above course info page. Configurable through language editing
- fix missing online description on course info page.
- separate tables on course info page
- if date 0 in class overview page show empty
- if a course has no classes show the add class tab instead of the class overview tab
- fix bug with duplicating class and switching class type
- add form validations for unique class names within a course
- link to mod/taps_enrolment when delete warning for a class shows.

## 2016-12-06 - Simon
All changes up to 2016-12-06:
- local/coursemanager
  - Added in course/class created/updated events and associated triggers for observing in other plugins (see below).


- auth/saml
  - Removed enrolment cache update trigger.
- blocks/arup_mylearning
  - Removed enrolment cache update links/functionality.
  - Updated CPD interactions to work without TAPS.
  - Updated class loading to account for updated namespacing and class name.
  - Migrated option loading to use local\taps.
- blocks/arup_recent_courses
  - Updated class loading to account for updated namespacing and class name.
- config
  - Removed TAPS configuration.
- local/delegatelist
  - Removed forcing of TAPS use (passthru) and subsequent data object retrieval.
- local/lunchandlearn
  - Non-TAPS compatibility (removed Oracle specific date formating).
  - Updated class loading to account for updated namespacing and class name.
  - Migrated option loading to use local\taps.
- local/onlineappraisal
  - Checked, no changes required.
- local/regions
  - Region checking/updating temporarily disabled [Region info will come from new data view]
- local/search
  - Checked, no changes required.
- local/taps
  - Removed all TAPS links...
    - Redundant intermediary classes removed (class/course/enrolment).
    - Redundant events removed.
    - Redundant tasks removed.
    - TAPS interface class rewritten for local_taps_* CRUD only.
    - Redundant tests removed.
    - Redundant library functions removed.
    - Redundant settings removed.
  - Options (for menus) brought in to main class.
  - Event observers added to observe course manager triggered event (To update enrolments when course/class updated).
- mod/arupadvert
  - Event observer updated to observe course manager triggered event (To update Moodle course summary from oneline description).
- mod/aruphonestybox
  - Non-TAPS compatibility (removed Oracle specific date formating).
  - Updated class loading to account for updated namespacing and class name.
- mod/tapscompletion
  - Added course_completed event observer.
  - Removed recalc_completion task.
  - Removed enrolment cache update calls.
  - Updated attendance marking/processing.
  - Refactored (namespacing/moving classes from locallib).
- mod/tapsenrol
  - Removed forcing of TAPS use (passthru), and subsequent data object retrieval, throughout plugin.
  - Removed local cache updates throughout plugin.
  - Removed local tracking of TAPS statuses.
  - Various updates to main class for enrolment, cancellation, workflow triggering, and enrolment checks (Best to diff to see!).
    - Updated approve, cancel, enrol, and view pages accordingly.
    - Updated enrolment management accordingly.
  - Updated tasks.
  - Updated class loading to account for updated namespacing and class name.
  - Added new seats remaining calculation based on maximum attendees and actual enrolments.
  - Removed enrolment cache update functionality.
  - Added links to edit linked course/classes via course manager.
- theme/arup
  - Updated class loading to account for updated namespacing and class name.

---

## 2016-12-06 - Bas
All changes up to 2016-12-06:
TBC

## 2016-12-15
- local/coursemanager
  - added link to coursemanager in Admin Settings
  - changed table layout to use standard layout
  - number of class per course now in separate row
  - Seach improvements including a date search option
  - Ordering options for "Course name", "Start date", "Region"
  - Backend fixes for keeping search options in place when navigating courses / classes
  - clear search option

## 2016-12-20 - Simon
- mod/tapsenrol
  - Added migration to internal 'Off' workflow for tapsenrol activities previously using Oracle workflow.

## 2017-01-12 - Bas
- updated forms
  - use taps defined duration units
  - make a number of fields required
  - add validation checks
  - remove some fields identified obsolete after call on Jan 10
  - reorder fields