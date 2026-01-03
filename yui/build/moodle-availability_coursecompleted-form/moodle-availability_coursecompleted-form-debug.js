YUI.add('moodle-availability_coursecompleted-form', function (Y, NAME) {

/**
 * JavaScript for form editing course completed condition.
 *
 * @module moodle-availability_coursecompleted-form
 */
M.availability_coursecompleted = M.availability_coursecompleted || {};

// Class M.availability_coursecompleted.form @extends M.core_availability.plugin.
M.availability_coursecompleted.form = Y.Object(M.core_availability.plugin);

// Options available for selection.
M.availability_coursecompleted.form.completed = null;

/**
 * Initialises this plugin.
 *
 * @method initInner
 * @param {Array} courses Array of objects containing courseid => shortname
 */
M.availability_coursecompleted.form.initInner = function(courses) {
    this.courses = courses;
};

M.availability_coursecompleted.form.getNode = function(json) {
    // Create HTML structure.
    var tit = M.util.get_string('title', 'availability_coursecompleted');
    var html = '<label class="mb-3"><span class="p-r-1">' + tit + '</span>';
    html += '<span class="availability-coursecompleted"><select class="form-select" name="id" title=' + tit + '>';
    html += '<option value="1">' + M.util.get_string('yes', 'moodle') + '</option>';
    html += '<option value="0">' + M.util.get_string('no', 'moodle') + '</option>';
    html += '</select></span></label><br/>';
    var tut = M.util.get_string('select', 'availability_coursecompleted');
    html += '<label class="mb-3"><span class="p-r-1">' + tut + '</span>';
    html += '<select class="form-select" name="courses" title="courses">';
    for (var i = 0; i < this.courses.length; i++) {
        var course = this.courses[i];
        // String has already been escaped using format_string.
        html += '<option value="' + course.id + '">' + course.name + '</option>';
    }
    html += '</select></span></label>';
    var node = Y.Node.create('<span class="d-flex flex-wrap align-items-center">' + html + '</span>');

    // Set initial values (leave default 'choose' if creating afresh).
    if (json.creating === undefined) {
        if (json.id !== undefined) {
            node.one('select[name=id]').set('value', '' + json.id);
        } else if (json.id === undefined) {
            node.one('select[name=id]').set('value', 0);
        }
        if (json.courseid !== undefined) {
            node.one('select[name=courses]').set('value', '' + json.courseid);
        } else {
            node.one('select[name=courses]').set('value', 0);
        }
    }

    // Add event handlers (first time only).
    if (!M.availability_coursecompleted.form.addedEvents) {
        M.availability_coursecompleted.form.addedEvents = true;
        var root = Y.one('.availability-field');
        root.delegate('change', function() {
            // Just update the form fields.
            M.core_availability.form.update();
        }, '.availability_coursecompleted select');
    }

    return node;
};

M.availability_coursecompleted.form.fillValue = function(value, node) {
    value.id = parseInt(node.one('select[name=id]').get('value'));
    value.courseid = parseInt(node.one('select[name=courses]').get('value'));
};


}, '@VERSION@', {"requires": ["base", "node", "event", "moodle-core_availability-form"]});
