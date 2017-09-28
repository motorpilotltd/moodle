$(document).ready(function () {
    $('#id_invdividualassignment').addClass('collapsed');
    /**
     * @description Print popup with search bar to assigning
     * users/cohorts to certification
     * (only non assigned users/cohorts will be shown)
     */
    $('.assignmentbtn').on('click', function () {
        /**
         * @property integer certifid Certification ID
         */
        var certifid = $(this).data('certifid');
        /**
         * @property String type 'individuals' or 'cohort' (assignment type)
         */
        var type = $(this).data('type');
        /**
         * @description Modal window contains not assigned users/cohorts to current certification
         */
        $.ajax({
            type: "GET",
            url: M.cfg.wwwroot + '/local/custom_certification/ajax/assignments.php?action=modal&certifid=' + certifid + '&param=' + type,
            dataType: "HTML",
            success: function (response) {
                $('#id_programdetails legend').after(response);
            }
        });
    });

    /**
     * @description Print popup with due date configuration
     * for assignment
     * Three due date types:
     * 1. Fixed
     * 2. From user first login date
     * 3. From user enrolment date
     */
    $('.setduedate').on('click', function () {
        /**
         * @property integer assignmentid Assignment ID
         */
        var assignmentid = $(this).data('assignmentid');
        /**
         * @property integer certifid Certification ID
         */
        var certifid = $(this).data('certifid');
        /**
         * @description Print due date popup window
         */
        $.ajax({
            type: "GET",
            url: M.cfg.wwwroot + '/local/custom_certification/ajax/assignments.php?action=duedate&assignmentid=' + assignmentid + '&certifid=' + certifid,
            dataType: "HTML",
            success: function (response) {
                /**
                 * @description Append modal window with due date configuration
                 */
                $('#id_programdetails legend').after(response);

                $(document).find("input[type='checkbox']").each(function () {
                    if (!$(this).is(":checked")) {
                        /**
                         * @property object group data type container - div
                         */
                        var group = $(this).parent().parent();
                        /**
                         * @description Disable select and text fields which are not checked
                         */
                        group.find('select').attr('disabled', 'disabled');
                        group.find('[type=text]').attr('disabled', 'disabled');
                    }
                });
            }
        });
    });

    /**
     * @description Checkboxes in popup from due dates configuration
     */
    $(document).on('click', "input[type='checkbox']", function () {
        $(document).find("input[type='checkbox']").each(function () {
            if ($(this).is(":checked")) {
                /**
                 * @description Trigger uncheck on checkbox
                 */
                $(this).trigger('click');
                /**
                 * @property object group Date type div
                 */
                var group = $(this).parent().parent();
                /**
                 * @description Disabled all select/input fields
                 */
                group.find('select').attr('disabled', 'disabled');
                group.find('[type=text]').attr('disabled', 'disabled');
            }
        });
        /**
         * @description Enable select/input fields of
         * clicked checkbox
         */
        $(this).parent().parent().find('[type=text]').removeAttr('disabled');
        $(this).parent().parent().find('select').removeAttr('disabled');
        /**
         * @description Set clicked checkbox as checked
         */
        $(this).trigger('click');
    });

    /**
     * @description remove due date modal window on close
     */
    $(document).on('click', '.cancelbtn', function () {
        $(document).find('.modal-wrapper').remove();
    });

});


/**
 * @function addAssignmentsToTable
 *
 * @param integer itemid Cohort ID / User ID (depends on assignment type)
 * @param String type 'individuals' or 'cohort' (assignment type)
 * @param integer certifid Certification ID
 */
function addAssignmentsToTable(itemid, type, certifid) {
    /**
     * @property object chosen_element Clicked row in popup
     */
    var chosenelement = $(".modal_body").find("[data-id='" + itemid + "']");
    /**
     * @description Set information about reload page
     * in data attribute of close button
     */
    $('.close').data('reload', 1);
    /**
     * @description
     */
    $.ajax({
        type: "POST",
        url: M.cfg.wwwroot + '/local/custom_certification/ajax/assignments.php',
        dataType: "JSON",
        data: {
            itemid: itemid,
            action: 'addtotable',
            certifid: certifid,
            type: type
        },
        success: function () {
            /**
             * @description Remove row counter,
             * increase counter div
             */
            chosenelement.find('#counter').width(40).html(' ');
            /**
             * @description Hide enrol user / add cohort button
             */
            chosenelement.find('input').hide();
        }

    });

}
/**
 * @function closeDueDateModal
 *
 * @description remove due date popup window
 */
function closeDueDateModal() {
    $(document).find('.modal-wrapper').remove();
}

/**
 * @function closeAssignmentModal
 */
function closeAssignmentModal() {
    /**
     * @property integer reload
     */
    var reload = $('.close').data('reload');
    /**
     * @description remove assignment popup window
     */
    $('.assignment-modal-container').remove();
    /**
     * @description
     * 0 - no changes
     * 1 - enrol user / add cohort action was made
     * and need to reload page
     */
    if (reload > 0) {

        location.reload();
    }
}

/**
 * @function deleteAssignment
 * @param object element Delete button of assignment row
 */
function deleteAssignment(element) {
    /**
     * @property String type 'individuals' or 'cohort' assignment type
     */
    var type = $(element).data('type');
    /**
     * @property integer id User ID / Cohort ID
     */
    var id = $(element).data('id');
    /**
     * @property integer certifid Certification ID
     */
    var certifid = $(element).data('certifid');
    /**
     * @description Delete assignment and reload page
     * to show changes
     */
    $.ajax({
        type: "POST",
        url: M.cfg.wwwroot + '/local/custom_certification/ajax/assignments.php',
        dataType: "JSON",
        data: {
            itemid: id,
            action: 'delete',
            certifid: certifid,
            type: type
        },
        success: function () {
            location.reload();
        }
    });
}

/**
 * @function search
 * @param String type 'individuals' or 'cohort' assignment type
 * @param integer certifid Certification ID
 * @param String action 'search' or 'reset'
 * @param integer coursesetid unused
 * @param String certificationtype unused
 */
function search(type, certifid, action, coursesetid, certificationtype) {
    /**
     * @property object modalbody popup window
     */
    var modalbody = $(document).find('.modal_body');
    /**
     * @property String word value of search input
     */
    var word = $('#search-input').val();
    /**
     * @description if action is search remove all white spaces
     * if action is reset remove text from search input
     */
    if (action == 'search') {
        word = word.replace(/\s+/g, '');
    } else {
        $('#search-input').val('');
        word = 'reset';
    }
    /**
     * Ajax run when search input is not empty
     */
    if (word.length > 0) {
        modalbody.html('');
        $.ajax({
            type: "GET",
            url: M.cfg.wwwroot + '/local/custom_certification/ajax/assignments.php?action=modal&certifid=' + certifid + '&param=' + type + '&word=' + word + '&coursesetid=' + coursesetid + '&certificationtype=' + certificationtype,
            dataType: "JSON",
            success: function (response) {
                /**
                 * @description change value of total rows in popup window
                 */
                $(document).find('#count-value').html(response.count);
                /**
                 * @description Append search result to popup window
                 * users/cohorts
                 */
                modalbody.append(response.content);
            }
        });
    }
}

/**
 * @function showCohortDueDates
 * @param integer assignmentid Assignment ID
 * @param integer certifid Certification ID
 * @description If user was assigned individually and next was added cohort
 * which contains that user, then due date settings for this user
 * can be set only from individual assignment of that user but user
 * will be listed in cohort with information about this operation
 */

function showCohortDueDates(assignmentid, certifid) {
    /**
     * @description Print all users from cohort with
     * calculated due date according to
     * due date type which was set
     */
    $.ajax({
        type: "GET",
        url: M.cfg.wwwroot + '/local/custom_certification/ajax/assignments.php?action=viewcohortuserduedates&certifid=' + certifid + '&assignmentid=' + assignmentid,
        dataType: "HTML",
        success: function (response) {
            $('#id_programdetails legend').after(response);
        }
    });
}
