@totara @local_reportbuilder @javascript
Feature: Verify functionality of user source report.

  # See admin/tests/behat/user_report.feature for more tests that are relevant
  # to the user source report but specific to the Browse List of Users report.

  Background:
    Given I am on a totara site
    And the following "users" exist:
      | username | firstname | lastname | email                             | maildisplay | deleted |
      | learner1 | Bob1      | Learner1 | bob1.learner1@example.com         | 1           | 0       |
      | learner2 | Bob2      | Learner2 | bob2.learner2@example.com         | 1           | 0       |
      | learner3 | Bob3      | Learner3 | bob3.learner3@example.com         | 0           | 0       |
      | learner4 | Bob4      | Learner4 | bob4.learner4@example.com         | 2           | 0       |
      | learner5 | Bob5      | Learner5 | a_32_char_email_test@example.com  | 1           | 0       |
      | learner6 | Bob6      | Learner6 | b_32_char_email_test@example.com  | 0           | 0       |
      | learner7 | Bob7      | Learner7 | c_32_char_email_test@example.com  | 1           | 1       |
      | learner8 | Bob8      | Learner8 | abcdefabcdefabcdefabcdefabcdef12  | 1           | 0       |

    When I log in as "admin"
    And I navigate to "Create report" node in "Site administration > Reports > Report builder"
    And I set the following fields to these values:
      | Report Name | User Report |
      | Source      | User        |
    And I press "Create report"
    Then I should see "Edit Report 'User Report'"

    When I switch to "Columns" tab
    And I set the field "newcolumns" to "User's Email"
    And I press "Add"
    And I set the field "newcolumns" to "User Status"
    And I press "Add"
    And I set the field "newcolumns" to "Actions"
    And I press "Add"
    And I press "Save changes"
    Then I should see "Columns updated"

    When I switch to "Access" tab
    And I click on "All users can view this report" "radio"
    And I press "Save changes"
    Then I should see "Report Updated"

    When I follow "View This Report"
    Then I should see "User Report: 9 records shown"

  Scenario: Verify the user report source contains the expected users.
    And I should see "Email is private" in the "user_email" report column for "Guest user"
    And I should see "moodle@example.com" in the "user_email" report column for "Admin User"
    And I should see "bob1.learner1@example.com" in the "user_email" report column for "Bob1 Learner1"
    And I should see "bob2.learner2@example.com" in the "user_email" report column for "Bob2 Learner2"
    And I should see "Email is private" in the "user_email" report column for "Bob3 Learner3"
    And I should see "Email is private" in the "user_email" report column for "Bob4 Learner4"
    And I should see "a_32_char_email_test@example.com" in the "user_email" report column for "Bob5 Learner5"
    And I should see "Email is private" in the "user_email" report column for "Bob6 Learner6"
    And I should see "abcdefabcdefabcdefabcdefabcdef12" in the "user_email" report column for "Bob8 Learner8"

  Scenario: Verify editing user record in user source report.

    Given I follow "Edit Bob1 Learner1"
    When I set the field "First name" to "Sir Bob1"
    And I press "Update profile"
    Then I should see "User Report: 9 records shown"
    And the "reportbuilder-table" table should contain the following:
      | User's Fullname   | Username | User's Email              | User Status |
      | Sir Bob1 Learner1 | learner1 | bob1.learner1@example.com | Active user |

  Scenario: Verify suspend and unsuspend of user in user source report.

    Given I follow "Suspend Bob1 Learner1"
    Then I should see "User Report: 9 records shown"
    And the "reportbuilder-table" table should contain the following:
      | User's Fullname | Username | User's Email              | User Status    |
      | Bob1 Learner1   | learner1 | bob1.learner1@example.com | Suspended user |

    When I follow "Unsuspend Bob1 Learner1"
    Then I should see "User Report: 9 records shown"
    And the "reportbuilder-table" table should contain the following:
      | User's Fullname | Username | User's Email              | User Status |
      | Bob1 Learner1   | learner1 | bob1.learner1@example.com | Active user |

  Scenario: Verify partial delete and undelete of user in user source report.

    Given the following config values are set as admin:
      | authdeleteusers | partial |
    When I follow "Delete Bob1 Learner1"
    Then I should see "Delete user"

    When I press "Delete"
    Then I should see "User Report: 9 records shown"
    And the "reportbuilder-table" table should contain the following:
      | User's Fullname | Username | User's Email              | User Status  |
      | Bob1 Learner1   | learner1 | bob1.learner1@example.com | Deleted user |

    When I follow "Undelete Bob1 Learner1"
    Then I should see "Undelete User"

    When I press "Undelete"
    Then I should see "User Report: 9 records shown"
    And the "reportbuilder-table" table should contain the following:
      | User's Fullname | Username | User's Email              | User Status |
      | Bob1 Learner1   | learner1 | bob1.learner1@example.com | Active user |

  Scenario: Verify full delete of user in user source report.

    Given the following config values are set as admin:
      | authdeleteusers | full |
    When I follow "Delete Bob1 Learner1"
    Then I should see "Delete user"

    When I press "Delete"
    # A fully deleted user is not shown in the report.
    Then I should see "User Report: 8 records shown"
    And I should not see "Bob1 Learner1"

  Scenario: Verify 'seedeletedusers' capability is supported in user source report.

    Given the following config values are set as admin:
      | authdeleteusers | partial |
    And the following "users" exist:
      | username | firstname | lastname | email                      |
      | manager1 | Dave1     | Manager1 | dave1.manager1@example.com |
    And the following "system role assigns" exist:
      | user     | role    |
      | manager1 | manager |
    And I set the following system permissions of "Site Manager" role:
      | capability                  | permission |
      | totara/core:seedeletedusers | Allow      |

    When I navigate to "Manage user reports" node in "Site administration > Reports > Report builder"
    And I click on "View" "link" in the "User Report" "table_row"
    Then I should see "User Report: 10 records shown"

    When I follow "Delete Bob1 Learner1"
    Then I should see "Delete user"

    When I press "Delete"
    Then I should see "User Report: 10 records shown"
    And the "reportbuilder-table" table should contain the following:
      | User's Fullname | Username | User's Email              | User Status  |
      | Bob1 Learner1   | learner1 | bob1.learner1@example.com | Deleted user |

    When I log out
    And I log in as "manager1"

    When I navigate to "Manage user reports" node in "Site administration > Reports > Report builder"
    And I click on "View" "link" in the "User Report" "table_row"
    Then I should see "User Report: 10 records shown"
    And the "reportbuilder-table" table should contain the following:
      | User's Fullname | Username | User's Email              | User Status  |
      | Bob1 Learner1   | learner1 | bob1.learner1@example.com | Deleted user |

    When I set the following system permissions of "Site Manager" role:
      | capability                  | permission |
      | totara/core:seedeletedusers | Prevent    |
    And I log out
    And I log in as "manager1"
    And I navigate to "Manage user reports" node in "Site administration > Reports > Report builder"
    And I click on "View" "link" in the "User Report" "table_row"
    Then I should see "User Report: 9 records shown"
    And the following should not exist in the "reportbuilder-table" table:
      | User's Fullname | Username | User's Email              | User Status  |
      | Bob1 Learner1   | learner1 | bob1.learner1@example.com | Deleted user |

  Scenario: Verify confirm new self-registration user in user source report.

    When I click on "Home" in the totara menu
    When I navigate to "Manage authentication" node in "Site administration > Plugins > Authentication"
    And I click on "Enable" "link" in the "Email-based self-registration" "table_row"
    And the following config values are set as admin:
      | registerauth | email |
    And I log out
    Then I should see "Is this your first time here?"

    When I press "Create new account"
    Then I should see "New account"

    When I set the following fields to these values:
      | Username      | learnerx                  |
      | Password      | P4ssword!                 |
      | First name    | Bobx                      |
      | Surname       | Learnerx                  |
      | Email address | bobx.learnerx@example.com |
      | Email (again) | bobx.learnerx@example.com |
    And I press "Create my new account"
    Then I should see "An email should have been sent to your address at bobx.learnerx@example.com"

    When I log in as "admin"
    And I navigate to "Manage user reports" node in "Site administration > Reports > Report builder"
    And I click on "View" "link" in the "User Report" "table_row"
    Then the "reportbuilder-table" table should contain the following:
      | User's Fullname | Username | User's Email     | User Status      |
      | Bobx Learnerx   | learnerx | Email is private | Unconfirmed user |

    When I follow "Confirm Bobx Learnerx"
    Then I should not see "Confirm Bobx Learnerx"
    And the "reportbuilder-table" table should contain the following:
      | User's Fullname | Username | User's Email     | User Status |
      | Bobx Learnerx   | learnerx | Email is private | Active user |

  Scenario: Verify unlock of user account in user source report.

    When the following config values are set as admin:
      | lockoutthreshold | 3 |
    And I log out
    # Attempt three failed logins so the account locks.
    And I set the following fields to these values:
      | Username | learner1 |
      | Password | 12345678 |
    And I press "Log in"
    Then I should see "Invalid login, please try again"

    # Second failed login attempt.
    When I set the following fields to these values:
      | Username | learner1 |
      | Password | abcdefgh |
    And I press "Log in"
    Then I should see "Invalid login, please try again"

    # Third failed login attempt.
    When I set the following fields to these values:
      | Username | learner1 |
      | Password | !"£$%^&* |
    And I press "Log in"
    Then I should see "Invalid login, please try again"

    When I log in as "admin"
    And I navigate to "Manage user reports" node in "Site administration > Reports > Report builder"
    And I click on "View" "link" in the "User Report" "table_row"
    Then the "reportbuilder-table" table should contain the following:
      | User's Fullname | Username | User's Email              | User Status |
      | Bob1 Learner1   | learner1 | bob1.learner1@example.com | Active user |

    When I follow "Unlock Bob1 Learner1"
    Then I should not see "Unlock Bob1 Learner1"
    And I log out

    # Login successfully after being locked out.
    When I log in as "learner1"
    Then I should see "Bob1 Learner1" in the "nav" "css_element"

  Scenario: Verify email address is displayed when correct permissions are used in user source report.

    Given the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    When the following "course enrolments" exist:
      | user     | course | role    |
      | learner2 | C1     | student |
      | learner3 | C1     | student |
      | learner4 | C1     | student |
    # As admin we can see all the learner's record.
    Then I should see "User Report: 9 records shown"
    And the "reportbuilder-table" table should contain the following:
      | User's Fullname | Username | User's Email              | User Status |
      | Bob1 Learner1   | learner1 | bob1.learner1@example.com | Active user |
      | Bob2 Learner2   | learner2 | bob2.learner2@example.com | Active user |
      | Bob3 Learner3   | learner3 | Email is private          | Active user |
      | Bob4 Learner4   | learner4 | Email is private          | Active user |
    And I log out

    When I log in as "learner1"
    And I click on "Reports" in the totara menu
    And I follow "User Report"
    # Email addresses is 'hidden from everyone' and only visible to course members.
    Then the "reportbuilder-table" table should contain the following:
      | User's Fullname | Username | User's Email              | User Status |
      | Bob2 Learner2   | learner2 | bob2.learner2@example.com | Active user |
      | Bob3 Learner3   | learner3 | Email is private          | Active user |
      | Bob4 Learner4   | learner4 | Email is private          | Active user |
    And I log out

    When I log in as "learner2"
    And I click on "Reports" in the totara menu
    And I follow "User Report"
    # Email addresses is 'hidden from everyone' and only visible to course members.
    Then the "reportbuilder-table" table should contain the following:
      | User's Fullname | Username | User's Email              | User Status |
      | Bob2 Learner2   | learner2 | bob2.learner2@example.com | Active user |
      | Bob3 Learner3   | learner3 | Email is private          | Active user |
      | Bob4 Learner4   | learner4 | Email is private          | Active user |

  Scenario: Verify Global Report Restrictions works on the report in user source report.

    Given the following "cohorts" exist:
      | name       | idnumber |
      | Audience 1 | A1       |
      | Audience 2 | A2       |
    And the following "cohort members" exist:
      | user     | cohort |
      | learner1 | A1     |
      | learner2 | A1     |
      | learner3 | A2     |
      | learner4 | A2     |

    When I click on "Home" in the totara menu
    And I navigate to "Global report restrictions" node in "Site administration > Reports > Report builder"
    And I press "New restriction"
    And I set the following fields to these values:
      | Name   | User Report Restriction |
      | Active | 1                       |
    And I press "Save changes"
    Then I should see "New restriction \"User Report Restriction\" has been created."

    When I set the field "menugroupselector" to "Audience"
    And I click on "Audience 1" "link" in the "Assign a group to restriction" "totaradialogue"
    And I click on "Save" "button" in the "Assign a group to restriction" "totaradialogue"
    Then the following should exist in the "datatable" table:
      | Learner       | Assigned Via        |
      | Bob1 Learner1 |	Audience Audience 1 |
      | Bob2 Learner2 |	Audience Audience 1 |

    When I switch to "Users allowed to select restriction" tab
    And I set the field "menugroupselector" to "Audience"
    And I click on "Audience 2" "link" in the "Assign a group to restriction" "totaradialogue"
    And I click on "Save" "button" in the "Assign a group to restriction" "totaradialogue"
    Then the following should exist in the "datatable" table:
      | Learner       | Assigned Via        |
      | Bob3 Learner3 |	Audience Audience 2 |
      | Bob4 Learner4 |	Audience Audience 2 |
    And I log out

    # Learner1 should not have any restrictions on what data it can see.
    When I log in as "learner1"
    And I click on "Reports" in the totara menu
    And I follow "User Report"
    Then I should see "User Report: 9 records shown"
    And the "reportbuilder-table" table should contain the following:
      | User's Fullname | Username | User's Email              | User Status |
      | Bob1 Learner1   | learner1 | bob1.learner1@example.com | Active user |
      | Bob2 Learner2   | learner2 | bob2.learner2@example.com | Active user |
      | Bob3 Learner3   | learner3 | Email is private          | Active user |
      | Bob4 Learner4   | learner4 | Email is private          | Active user |
    And I log out

    # Learner3 should be restricted to a report containing only learner1 and 2.
    When I log in as "learner3"
    And I click on "Reports" in the totara menu
    And I follow "User Report"
    Then I should see "User Report: 2 records shown"
    And the "reportbuilder-table" table should contain the following:
      | User's Fullname | Username | User's Email              | User Status |
      | Bob1 Learner1   | learner1 | bob1.learner1@example.com | Active user |
      | Bob2 Learner2   | learner2 | bob2.learner2@example.com | Active user |

  Scenario: Verify reports extending from the user source class do not support the action column in user source report.

    When I click on "Home" in the totara menu
    And I navigate to "Create report" node in "Site administration > Reports > Report builder"
    And I set the following fields to these values:
      | Report Name | Audiences Orphaned Users Report |
      | Source      | Audiences Orphaned Users        |
    And I press "Create report"
    Then I should see "Edit Report 'Audiences Orphaned Users Report'"

    When I switch to "Columns" tab
    Then I should not see "Actions" in the "newcolumns" "select"
