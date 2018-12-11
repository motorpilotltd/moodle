@block @block_dataformaccessentry @set_dataform @dataformrule
Feature: Block dataform access entry

    @javascript
    Scenario: Manage access rule
        Given I run dataform scenario "access rule management" with:
            | ruletype | entry |
