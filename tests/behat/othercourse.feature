@ewallah @availability @availability_coursecompleted
Feature: availability coursecompleted other course completion
  I need to test other course completions when using availability course completion

  Background:
    Given the following "courses" exist:
      | fullname | shortname | format | enablecompletion |
      | Course 1 | C1        | topics | 1                |
      | Course 2 | C2        | topics | 1                |
      | Course 3 | C3        | topics | 1                |
    And the following "activities" exist:
      | activity   | name   | intro            | course | idnumber |
      | page       | Page A | page description | C1     | page1    |
      | page       | Page B | page description | C2     | page2    |
      | page       | Page C | page description | C3     | page3    |
    And the following "users" exist:
      | username |
      | student1 |
      | teacher1 |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | student1 | C1     | student        |
      | student1 | C2     | student        |
      | student1 | C3     | student        |
      | teacher1 | C1     | editingteacher |
      | teacher1 | C2     | editingteacher |
      | teacher1 | C3     | editingteacher |
    And I enable "selfcompletion" "block" plugin
    And the following "blocks" exist:
      | blockname        | contextlevel | reference | pagetypepattern | defaultregion |
      | selfcompletion   | Course       | C1        | course-view-*   | side-pre      |
      | selfcompletion   | Course       | C2        | course-view-*   | side-pre      |
      | selfcompletion   | Course       | C3        | course-view-*   | side-pre      |

  @javascript
  Scenario: A restricted module becomes available when completing another course
    Given I log in as "admin"
    And I am on "Course 1" course homepage with editing mode on
    And I navigate to "Course completion" in current page administration
    And I expand all fieldsets
    And I set the following fields to these values:
      | id_criteria_self | 1 |
    And I press "Save changes"
    And I am on "Course 2" course homepage with editing mode on
    And I navigate to "Course completion" in current page administration
    And I expand all fieldsets
    And I set the following fields to these values:
      | id_criteria_self | 1 |
    And I press "Save changes"
    And I am on "Course 3" course homepage with editing mode on
    And I navigate to "Course completion" in current page administration
    And I expand all fieldsets
    And I set the following fields to these values:
      | id_criteria_self | 1 |
    And I press "Save changes"
    And I log out

    # Page 1 is only available when course2 is completed.
    Given I am on the "C1" "Course" page logged in as "teacher1"
    And I am on the "page1" "page activity editing" page
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    And I click on "Course completed" "button" in the "Add restriction..." "dialogue"
    And I set the field "Course completed" to "Yes"
    And I set the field "Select course" to "C2"
    And I click on "Save and return to course" "button"
    And I should see "Not available unless: You completed course: C2" in the "region-main" "region"
    # Page 2 is only available when course3 is completed.
    And I am on the "page2" "page activity editing" page
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    And I click on "Course completed" "button" in the "Add restriction..." "dialogue"
    And I set the field "Course completed" to "Yes"
    And I set the field "Select course" to "C3"
    And I click on "Save and return to course" "button"
    And I should see "Not available unless: You completed course: C3" in the "region-main" "region"
    # Page 3 is only available when course1 is completed.
    And I am on the "page3" "page activity editing" page
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    And I click on "Course completed" "button" in the "Add restriction..." "dialogue"
    And I set the field "Course completed" to "Yes"
    And I set the field "Select course" to "C1"
    And I click on "Save and return to course" "button"
    And I should see "Not available unless: You completed course: C1" in the "region-main" "region"

    # Log in as student.
    When I am on the "C1" "Course" page logged in as "student1"
    And I should see "Page A" in the "region-main" "region"
    And I should see "Not available unless:" in the "region-main" "region"
    And I follow "Complete course"
    And I press "Yes"

    And I am on the "C2" "Course" page
    And I should see "Page B" in the "region-main" "region"
    And I should see "Not available unless:" in the "region-main" "region"
    And I follow "Complete course"
    And I press "Yes"

    And I am on the "C3" "Course" page
    And I should see "Page C" in the "region-main" "region"
    And I should see "Not available unless:" in the "region-main" "region"
    And I follow "Complete course"
    And I press "Yes"

    And I run the scheduled task "core\task\completion_regular_task"
    And I run the scheduled task "core\task\completion_regular_task"

    And I am on the "C1" "Course" page
    And I should not see "Not available unless:" in the "region-main" "region"

    And I am on the "C2" "Course" page
    And I should not see "Not available unless:" in the "region-main" "region"

    And I am on the "C3" "Course" page
    And I should not see "Not available unless:" in the "region-main" "region"
