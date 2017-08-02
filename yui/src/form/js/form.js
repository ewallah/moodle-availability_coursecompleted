/**
 * JavaScript for form editing course completed condition.
 *
 * @module moodle-availability_courseCompleted-form
 */

M.availability_courseCompleted = M.availability_courseCompleted || {};

// Class M.availability_courseCompleted.form @extends M.core_availability.plugin.
M.availability_courseCompleted.form = Y.Object(M.core_availability.plugin);

// Options available for selection.
M.availability_courseCompleted.form.completed = null;

/**
 * Initialises this plugin.
 *
 * @method initInner
 * @param {boolean} completed Is completed or not
 */
M.availability_courseCompleted.form.initInner = function(completed) {
    this.completed = completed;
};

M.availability_courseCompleted.form.getNode = function(json) {
    // Create HTML structure.
    var tit = M.util.get_string('title', 'availability_courseCompleted');
    var html = '<label class="form-group"><span class="p-r-1">' + tit + '</span>';
    html += '<span class="availability-courseCompleted"><select class="custom-select" name="id" title=' + tit + '>';
    html += '<option value="choose">' + M.util.get_string('choosedots', 'moodle') + '</option>';
    html += '<option value="1">' + M.util.get_string('yes', 'moodle') + '</option>';
    html += '<option value="0">' + M.util.get_string('no', 'moodle') + '</option>';
    html += '</select></span></label>';
    var node = Y.Node.create('<span class="form-inline">' + html + '</span>');

    // Set initial values (leave default 'choose' if creating afresh).
    if (json.creating === undefined) {
        if (json.id !== undefined && node.one('select[name=id] > option[value=' + json.id + ']')) {
            node.one('select[name=id]').set('value', '' + json.id);
        } else if (json.id === undefined) {
            node.one('select[name=id]').set('value', 'choose');
        }
    }

    // Add event handlers (first time only).
    if (!M.availability_courseCompleted.form.addedEvents) {
        M.availability_courseCompleted.form.addedEvents = true;
        var root = Y.one('.availability-field');
        root.delegate('change', function() {
            // Just update the form fields.
            M.core_availability.form.update();
        }, '.availability_courseCompleted select');
    }

    return node;
};

M.availability_courseCompleted.form.fillValue = function(value, node) {
    var selected = node.one('select[name=id]').get('value');
    if (selected === 'choose') {
        value.id = '';
    } else {
        value.id = selected;
    }
};

M.availability_courseCompleted.form.fillErrors = function(errors, node) {
    var selected = node.one('select[name=id]').get('value');
    if (selected === 'choose') {
        errors.push('availability_courseCompleted:missing');
    }
};