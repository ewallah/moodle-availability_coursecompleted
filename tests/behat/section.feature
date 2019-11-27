@ewallah @availability @availability_coursecompleted @javascript
Feature: Section 0 availability_coursecompleted
  Section 0 cannot be restricted

  Background:
    Given the following "courses" exist:
      | fullname | shortname | format | enablecompletion | numsections | enablecompletion |
      | Course 1 | C1        | topics | 1                | 4           | 1                |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | First    | teacher1@example.com |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |

  Scenario: Restrict section0 hidden

    When I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    When I edit the section "0"
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    Then "Course completed" "button" should not exist in the "Add restriction..." "dialogue"

