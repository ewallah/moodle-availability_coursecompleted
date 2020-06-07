@ewallah @availability @availability_coursecompleted @javascript
Feature: availability_coursecompleted
  In order to control student access to activities
  As a teacher
  I need to set course completion conditions which prevent student access

  Background:
    Given the following "courses" exist:
      | fullname | shortname | format | enablecompletion | numsections |
      | Course 1 | C1        | topics | 1                | 4           |
    And the following "activities" exist:
      | activity   | name   | intro                    | course | idnumber    | section | visible |
      | page       | Page A | page description         | C1     | page1       | 0       | 1       |
      | page       | Page B | page description         | C1     | page2       | 0       | 1       |
      | page       | Page C | page description         | C1     | page3       | 1       | 1       |
      | page       | Page D | page description         | C1     | page4       | 1       | 0       |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student   | First    | student1@example.com |
      | teacher1 | Teacher   | First    | teacher1@example.com |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |

  Scenario: Complete a course
    When I am on the "C1" "Course" page logged in as "teacher1"
    And I navigate to "Course completion" in current page administration
    And I expand all fieldsets
    And I set the field "Teacher" to "1"
    And I click on "Save changes" "button"
    And I turn editing mode on

    # Add a Page E for users who did not completed this course.
    When I add a "Page" to section "2"
    And I set the following fields to these values:
      | Name         | Page E |
      | Description  | x      |
      | Page content | x      |
    And I click on "Add restriction..." "button"
    And I click on "Course completed" "button" in the "Add restriction..." "dialogue"
    And I set the field "Course completed" to "No"
    And I click on ".availability-item .availability-eye img" "css_element"
    And I click on "Save and return to course" "button"

    # Add a Page F for users who did not completed the course.
    And I add a "Page" to section "2"
    And I set the following fields to these values:
      | Name         | Page F |
      | Description  | x      |
      | Page content | x      |
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    And I click on "Course completed" "button"
    Then I should see "Please set" in the "region-main" "region"
    And I set the field "Course completed" to "No"
    Then I should not see "Please set" in the "region-main" "region"
    And I click on "Save and return to course" "button"

    # Page G for users who completed the course.
    And I add a "Page" to section "2"
    And I set the following fields to these values:
      | Name         | Page G |
      | Description  | x      |
      | Page content | x      |
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    And I click on "Course completed" "button"
    And I set the field "Course completed" to "Yes"
    And I click on "Save and return to course" "button"

    # Page H for users who completed the course hidden.
    And I add a "Page" to section "2"
    And I set the following fields to these values:
      | Name         | Page H |
      | Description  | x      |
      | Page content | x      |
    And I expand all fieldsets
    And I click on "Add restriction..." "button"
    And I click on "Course completed" "button"
    And I set the field "Course completed" to "Yes"
    And I click on ".availability-item .availability-eye img" "css_element"
    And I click on "Save and return to course" "button"
    And I log out

    # Log in as student.
    When I am on the "C1" "Course" page logged in as "student1"
    Then I should see "Page A" in the "region-main" "region"
    And I should see "Page B" in the "region-main" "region"
    And I should see "Page C" in the "region-main" "region"
    And I should not see "Page D" in the "region-main" "region"
    And I should see "Page E" in the "region-main" "region"
    And I should see "Page F" in the "region-main" "region"
    And I should see "Page G" in the "region-main" "region"
    And I should not see "Page H" in the "region-main" "region"
    And I log out

    When I am on the "C1" "Course" page logged in as "teacher1"
    And I navigate to "Reports > Course completion" in current page administration
    Then I should see "Student First"
    And I follow "Click to mark user complete"
    # Running completion task just after clicking sometimes fail, as record
    # should be created before the task runs.
    And I wait "1" seconds
    And I run the scheduled task "core\task\completion_regular_task"
    And I run all adhoc tasks
    And I am on "Course 1" course homepage
    And I navigate to "Reports > Course completion" in current page administration
    And I log out

    When I am on the "C1" "Course" page logged in as "student1"
    Then I should see "Page A" in the "region-main" "region"
    And I should see "Page B" in the "region-main" "region"
    And I should see "Page C" in the "region-main" "region"
    And I should not see "Page D" in the "region-main" "region"
    And I should not see "Page E" in the "region-main" "region"
    And I should see "Page F" in the "region-main" "region"
    And I should see "Page G" in the "region-main" "region"
    And I should see "Page H" in the "region-main" "region"
