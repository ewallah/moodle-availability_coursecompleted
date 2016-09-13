@availability @availability_coursecompleted
Feature: availability_coursecompleted
  In order to control student access to activities
  As a teacher
  I need to set course completion conditions which prevent student access

  Background:
    Given the following "courses" exist:
      | fullname | shortname | format | enablecompletion |
      | Course 1 | C1        | topics | 1                |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student   | First    | student1@example.com |
      | teacher1 | Teacher   | First    | teacher1@example.com |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |

      
  @javascript
  Scenario: Complete a course
    
    # Basic setup.
    Given I log in as "admin"
    And I am on site homepage
    And I navigate to "Turn editing on" node in "Front page settings"
    And I follow "Course 1"
    And completion tracking is "Enabled" in current course
    And I follow "Course completion"
    And I expand all fieldsets
    And I set the field "Teacher" to "1"
    And I press "Save changes"
    And I log out
    And I log in as "teacher1"
    And I am on site homepage
    And I follow "Course 1"
    And I turn editing mode on

    # Add a Page P1 for users who did not completed this course.
    And I add a "Page" to section "1"
    And I set the following fields to these values:
      | Name         | P1 |
      | Description  | x  |
      | Page content | x  |
    And I click on "Add restriction..." "button"
    And I click on "Course completed" "button"
    And I set the field "Course completed" to "No"
    And I click on ".availability-item .availability-eye img" "css_element"
    And I click on "Save and return to course" "button"

    # Add a Page P2 for users who did not completed the course.
    And I add a "Page" to section "1"
    And I set the following fields to these values:
      | Name         | P2 |
      | Description  | x  |
      | Page content | x  |
    And I click on "Add restriction..." "button"
    And I click on "Course completed" "button"
    And I set the field "Course completed" to "No"
    And I click on "Save and return to course" "button"
    
    # Page P3 for users who completed the course.
    And I add a "Page" to section "1"
    And I set the following fields to these values:
      | Name         | P3 |
      | Description  | x  |
      | Page content | x  |
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    And I click on "Course completed" "button"
    And I set the field "Course completed" to "Yes"
    And I click on "Save and return to course" "button"

    # Page P4 for users who completed the course hidden.
    And I add a "Page" to section "1"
    And I set the following fields to these values:
      | Name         | P4 |
      | Description  | x  |
      | Page content | x  |
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    And I click on "Course completed" "button"
    And I set the field "Course completed" to "Yes"
    And I click on ".availability-item .availability-eye img" "css_element"
    And I click on "Save and return to course" "button"
    
    # Log in as student.
    When I log out
    And I log in as "student1"
    And I am on site homepage
    And I follow "Course 1"

    Then I should see "P1" in the "region-main" "region"
    And I should see "P2" in the "region-main" "region"
    And I should see "P3" in the "region-main" "region"
    And I should not see "P4" in the "region-main" "region"
    And I log out
    
    When I log in as "teacher1"
    And I am on site homepage
    And I follow "Course 1"
    
    When I navigate to "Course completion" node in "Course administration > Reports"
    And I should see "Student First"
    And I follow "Click to mark user complete"
    # Running completion task just after clicking sometimes fail, as record
    # should be created before the task runs.
    And I wait "1" seconds
    And I run the scheduled task "core\task\completion_regular_task"
    And I log out

    When I log in as "student1"
    And I am on site homepage
    And I follow "Course 1"

    Then I should not see "P1" in the "region-main" "region"
    And I should see "P2" in the "region-main" "region"
    And I should see "P3" in the "region-main" "region"
    And I should see "P3" in the "region-main" "region"
    