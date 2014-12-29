YUI.add('moodle-availability_coursecompleted-form', function (Y, NAME) {

/**
 * JavaScript for form editing course completed condition.
 *
 * @module moodle-availability_coursecompleted-form
 */
M.availability_coursecompleted = M.availability_coursecompleted || {};

/**
 * @class M.availability_coursecompleted.form
 * @extends M.core_availability.plugin
 */
M.availability_coursecompleted.form = Y.Object(M.core_availability.plugin);

/**
 * Course completed available for selection.
 *
 * @property completed
 * @type Array
 */
M.availability_coursecompleted.form.completed = null;

/**
 * Initialises this plugin.
 *
 * @method initInner
 * @param {Array} groups Array of objects
 */
M.availability_coursecompleted.form.initInner = function(completed) {
    this.completed = completed;
};

M.availability_coursecompleted.form.getNode = function(json) {
    // Create HTML structure.
    var strings = M.str.availability_coursecompleted;
    var yeslabel = M.util.get_string('yes', 'moodle');
    var nolabel = M.util.get_string('no', 'moodle')
    var html = '<label>' + strings.title + ' <span class="availability-coursecompleted">' +
            '<select name="id">' +
            '<option value="choose">' + M.str.moodle.choosedots + '</option>' +
            '<option value="1">' + yeslabel + '</option>' +
            '<option value="0">' + nolabel + '</option>';
    html += '</select></span></label>';
    var node = Y.Node.create('<span>' + html + '</span>');

    // Set initial values (leave default 'choose' if creating afresh).
    if (json.creating === undefined) {
        if (json.id !== undefined && node.one('select[name=id] > option[value=' + json.id + ']')) {
            node.one('select[name=id]').set('value', '' + json.id);
        } else if (json.id === undefined) {
            node.one('select[name=id]').set('value', 'choose');
        }
    }

    // Add event handlers (first time only).
    if (!M.availability_coursecompleted.form.addedEvents) {
        M.availability_coursecompleted.form.addedEvents = true;
        var root = Y.one('#fitem_id_availabilityconditionsjson');
        root.delegate('change', function() {
            // Just update the form fields.
            M.core_availability.form.update();
        }, '.availability_coursecompleted select');
    }

    return node;
};

M.availability_coursecompleted.form.fillValue = function(value, node) {
    var selected = node.one('select[name=id]').get('value');
    if (selected === 'choose') {
        value.id = '';
    } else {
        value.id = selected;
    }
};

M.availability_coursecompleted.form.fillErrors = function(errors, node) {
    var value = {};
    this.fillValue(value, node);

    if (value.id === '') {
        errors.push('availability_coursecompleted:missing');
    }
};


}, '@VERSION@', {"requires": ["base", "node", "event", "moodle-core_availability-form"]});
