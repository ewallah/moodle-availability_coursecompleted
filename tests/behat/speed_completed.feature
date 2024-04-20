@ewallah @availability @availability_coursecompleted
Feature: availability coursecompleted fast completion
  I need to test how fast this a restricted module becomes available when using availability course completion

  Background:
    Given the following "courses" exist:
      | fullname | shortname | format | enablecompletion |
      | Course 1 | C1        | topics | 1                |
    And the following "activities" exist:
      | activity  | name   | course | idnumber | completion |
      | page      | Page 1 | C1     | page1    | 1          |
      | page      | Page 2 | C1     | page2    | 0          |
    And the following "users" exist:
      | username |
      | student1 |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | student1 | C1     | student        |

  @javascript
  Scenario: Bulk upload of users and restrict completion
    Given I am on the "C1" "Course" page logged in as "admin"
    And I navigate to "Course completion" in current page administration
    And I expand all fieldsets
    And I set the field "Page 1" to "1"
    And I click on "Save changes" "button"
    And I am on the "page2" "page activity editing" page
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    And I click on "Activity completion" "button" in the "Add restriction..." "dialogue"
    And I set the field "Activity or resource" to "Page 1"
    And I click on "Save and return to course" "button"
    And I log out
    # Log in as student.
    When I am on the "C1" "Course" page logged in as "student1"
    And I should see "Page 1" in the "region-main" "region"
    And I should see "Page 2" in the "region-main" "region"
    And I should see "Not available unless:" in the "region-main" "region"
    And I press "Mark as done"
    And I wait until "Done" "button" exists
    And I follow "Page 2"
    Then I should see "Test page content" in the "region-main" "region"

