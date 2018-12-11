@javascript @block @block_dataformnotification @set_dataform @dataformrule @dataformnotificationtest
Feature: Dataform notifications
  In order to monitor events and receive Dataform notifications
  As a user
  I can create a new rule and receive notification

    Background:
        Given I start afresh with dataform "Test Dataform Notification Rules"

        And the following dataform "fields" exist:
            | name      | type      | dataform  |
            | Field 01  | text      | dataform1 |

        And the following dataform "views" exist:
            | name     | type      | dataform  | default   |
            | View 01  | aligned   | dataform1 | 1         |

        And view "View 01" in dataform "1" has the following entry template:
            """
            [[ENT:id]]
            [[Field 01]]
            [[EAC:edit]]
            [[EAC:delete]]
            """

        And the following dataform "views" exist:
            | name     | type      | dataform  | submission |
            | View 02  | grid   | dataform1 |            |

        And view "View 02" in dataform "1" has the following view template:
            """
            <div>Number of entries: ##numentriesfiltered##</div>
            ##entries##
            """

        And view "View 02" in dataform "1" has the following entry template:
            """
            <div class="entry">
                [[Field 01]] updated.
            </div>
            """
        # Make sure the popup notifications are enabled for assignments.
        And the following config values are set as admin:
          | popup_provider_mod_dataform_dataform_notification_permitted | permitted | message |
          | message_provider_mod_dataform_dataform_notification_loggedin | popup | message |
          | message_provider_mod_dataform_dataform_notification_loggedoff | popup | message |

    #Section: Notify recipients on entry created
    Scenario: Notify recipients on entry created
        Given the following dataform notification rule exists:
            | name          | New entry                                 |
            | dataform      | dataform1                                 |
            | enabled       | 1                                         |
            | from          |                                           |
            | to            |                                           |
            | views         |                                           |
            | events        | entry_created                             |
            | messagetype   | 0                                         |
            | subject       | Test notification - New entry added       |
            | contenttext   | New entry created.              |
            | contentview   |                                           |
            | messageformat |                                           |
            | sender        |                                           |
            | recipientadmin    |  1                                        |
            | recipientsupport  |                                           |
            | recipientauthor   |  1                                        |
            | recipientrole     |                                           |
            | recipientusername |  student1,assistant1                      |
            | recipientemail    |                                           |
            #| permission1   | student Allow mod/dataform:viewaccess     |

        And I am in dataform "Test Dataform Notification Rules" "Course 1" as "teacher1"

        When I follow "Add a new entry"
        And I press "Save"
        And I click on ".popover-region-notifications" "css_element"
        Then I see "Test notification - New entry added"
        And I follow "View full notification"
        And I see "New entry created"
        And I log out

        When I log in as "student1"
        And I click on ".popover-region-notifications" "css_element"
        Then I see "Test notification - New entry added"
        And I log out

        When I log in as "assistant1"
        And I click on ".popover-region-notifications" "css_element"
        Then I see "Test notification - New entry added"
        And I log out

        When I log in as "admin"
        And I click on ".popover-region-notifications" "css_element"
        Then I see "Test notification - New entry added"
        And I log out

    #:Section

    #Section: Notify recipients on entry updated via a designated view
    Scenario: Notify recipients on entry updated via a designated view
        Given the following dataform "entries" exist:
            | dataform  | user           |
            | dataform1 | student2       |


        And the following dataform notification rule exists:
            | name          | Updated entry                             |
            | dataform      | dataform1                                 |
            | enabled       | 1                                         |
            | from          |                                           |
            | to            |                                           |
            | views         |                                           |
            | events        | entry_updated                             |
            | messagetype   | 1                                         |
            | subject       | Test message - entry updated         |
            | contenttext   |                                           |
            | contentview   | View 02                                   |
            | messageformat | 1                                          |
            | sender        | author                                     |
            | recipientadmin    |                                           |
            | recipientsupport  |                                           |
            | recipientauthor   |  1                                        |
            | recipientrole     |                                           |
            | recipientusername |  student1,teacher1                        |
            | recipientemail    |                                           |
            #| permission1   | student Allow mod/dataform:viewaccess     |

        And I am in dataform "Test Dataform Notification Rules" "Course 1" as "teacher1"
        
        When I click on "Edit" "link" in the "1" "table_row"
        And I set the field "field_1_1" to "the big bang theory"
        And I press "Save"

        And I follow "Messages" in the user menu
        And I see "Student 2"
        And I see "the big bang theory updated"
        And I log out

        When I log in as "student1"
        And I click on ".popover-region-messages" "css_element"
        And I see "Student 2"
        And I see "the big bang theory updated"
        And I log out

    #:Section

    #Section: Converse recipients on entry created with specific content
    Scenario: Notify recipients on entry created
        Given the following dataform notification rule exists:
            | name          | New entry                                 |
            | dataform      | dataform1                                 |
            | enabled       | 1                                         |
            | from          |                                           |
            | to            |                                           |
            | views         |                                           |
            | events        | entry_created                             |
            | messagetype   | 1                                         |
            | subject       | Test notification - New entry added       |
            | contenttext   | Author says that this entry has been created.              |
            | contentview   |                                           |
            | messageformat |                                           |
            | sender        | author                                        |
            | recipientadmin    |                                           |
            | recipientsupport  |                                           |
            | recipientauthor   |  1                                        |
            | recipientrole     |                                           |
            | recipientusername |                                           |
            | recipientemail    |                                           |
            #| permission1   | student Allow mod/dataform:viewaccess     |
            | search1           | AND#1,content##=#Choose me   |

        And I am in dataform "Test Dataform Notification Rules" "Course 1" as "teacher1"

        When I follow "Add a new entry"
        And I press "Save"
        And I follow "Messages" in the user menu
        Then I do not see "Author says that this entry has been created."

        And I am on "Course 1" course homepage
        And I follow "Test Dataform Notification Rules"
        When I follow "Add a new entry"
        And I set the field "field_1_-1" to "Choose me"
        And I press "Save"
        And I follow "Messages" in the user menu
        Then I see "Author says that this entry has been created."

    #:Section
