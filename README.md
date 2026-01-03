# Availability coursecompleted

Restrict module and section access based on course completion.

## Idea

This availability condition makes it easy to show modules or sections only when a user
completed a course. A course certificate is a good sample, but it can also be used to close
discussion forums, hide quizes or exams when a user finished a course.

## New

Now you can also restrict access unless other courses are completed.

## Conditional availability conditions

Check the global documentation about conditional availability conditions: https://docs.moodle.org/en/Conditional_activities_settings

## Warning

This plugin is 100% open source and has NOT been tested in Moodle Workplace, Totara, or any other proprietary software system. As long as the latter do not reward plugin developers, you can use this plugin only in 100% open source environments.

## Installation:

 1. Unpack the zip file into the availability/condition/ directory. A new directory will be created called coursecompleted.
 2. Go to Site administration > Notifications to complete the plugin installation.

## Requirements

This plugin requires Moodle 4.02+

## Troubleshooting

 1. Goto "Administration" > "Advanced features", and ensure that "Enable completion tracking" is set to yes.
 2. Make sure "Enable completion tracking" is set to "yes" in the course settings.
 3. Goto "Administration" > "Course administration" > "Course completion", and configure the the conditions required for course completion. Note: you must set some conditions, you cannot just set the "completion requirements" option at the top. Save.
 4. Goto "Administration" > "Course administration". Make sure you can now "Course completion" listed under "reports". If you cannot see this report then course completion has not been set correctly.
 5. Start restricting

## Theme support

This plugin is developed and tested on Moodle Core's Boost theme and Boost child themes, including Moodle Core's Classic theme.

## Plugin repositories

This plugin will be published and regularly updated on Github: https://github.com/ewallah/moodle-availability_coursecompleted

## Bug and problem reports / Support requests

This plugin is carefully developed and thoroughly tested, but bugs and problems can always appear.
Please report bugs and problems on Github: https://github.com/ewallah/moodle-availability_coursecompleted/issues
We will do our best to solve your problems, but please note that due to limited resources we can't always provide per-case support.

## Feature proposals

Please issue feature proposals on Github: https://github.com/ewallah/moodle-availability_coursecompleted/issues
Please create pull requests on Github: https://github.com/ewallah/moodle-availability_coursecompleted/pulls
We are always interested to read about your feature proposals or even get a pull request from you, but please accept that we can handle your issues only as feature proposals and not as feature requests.

## Moodle release support

This plugin is maintained for the latest major releases of Moodle.

## Status

[![Build Status](https://github.com/ewallah/moodle-availability_coursecompleted/workflows/Tests/badge.svg)](https://github.com/ewallah/moodle-availability_coursecompleted/actions)
[![Coverage Status](https://coveralls.io/repos/github/ewallah/moodle-availability_coursecompleted/badge.svg?branch=main)](https://coveralls.io/github/ewallah/moodle-availability_coursecompleted?branch=main)
![Mutation score](https://badgen.net/badge/Mutation%20Score%20Indicator/100?color=orange)


## Copyright

eWallah

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program.  If not, see <https://www.gnu.org/licenses/>.
