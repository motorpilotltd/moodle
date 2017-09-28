$(window).load(function () {
    /**
     * @description Check changes in form
     */
    checkIfContentChanged();
    /**
     * @description Check recertification path content
     */
    checkRecertificationAvailable();
});
$(document).ready(function () {

    $(".alert-success").delay(10000).fadeOut(300);
    /**
     * @description Check if checkbox 'Certification requires a re-certification path'
     * is checked, then add recertification path settings
     * If checkbox was clicked for uncheck, then recertification path
     * must be unset from Session
     */
    $(document).on('click', '#id_userecertif', function () {
        if ($(this).is(':checked')) {
            $(document).find('.recertification').removeClass('disable-recertification');
        } else {
            $('.loader-wrapper').show();
            /**
             * @property integer certifid certification ID
             */
            var certifid = $(document).find('.savedatabtn').data('id');

            $(document).find('#id_usecertif').attr('checked', false);
            /**
             * @description Unset recertification path from Session
             * page reload
             */
            $.when(setSessionDetails(certifid)).done(function (a1) {
                $.ajax({
                    type: "POST",
                    url: M.cfg.wwwroot + '/local/custom_certification/ajax/content.php',
                    dataType: "JSON",
                    data: {
                        action: 'removerecertificationpath',
                        certifid: certifid
                    },
                    success: function () {
                        try {

                            M.core_formchangechecker.stateinformation.formchanged = false;
                        } catch (err) {

                        }
                        location.reload();
                    }
                });
            });
        }
    });

    $('.coursesetbtn').on('click', function () {
        /**
         * @property integer certifid Certification ID
         */
        var certifid = $(this).data('id');
        /**
         * @property String certificationtype 'certification' / 'recertification'
         */
        var certificationtype = $(this).data('certiftype');
        /**
         * @description Print popup window with search bar
         * List of courses, called for certification
         * path and recertification path
         */
        $.ajax({
            type: "GET",
            url: M.cfg.wwwroot + '/local/custom_certification/ajax/content.php?action=modal&certifid=' + certifid + '&param=coursesets' + '&certificationtype=' + certificationtype,
            dataType: "HTML",
            success: function (response) {
                $('body').append(response);
            }
        });
    });

    /**
     * Save data to database
     */
    $('.savedatabtn').on('click', function () {
        /**
         * @property integer nextoperator type of operator between coursesets
         */
        var nextoperator = 0;
        /**
         * @property integer errors Ajax will not run
         * when any validation error occurred
         */
        var errors = 0;
        /**
         * @property integer certifid Certification ID
         */
        var certifid = $(this).data('id');
        /**
         * @property integer mincourses Amount of minimum courses to complete
         */
        var mincourses = 0;
        /**
         * @property integer certificationcoursesetscount count of coursesets from
         * certification path
         */
        var certificationcoursesetscount = $('.certification-coursesets').find('.coursesetbox').length;
        /**
         * @property integer recertificationcoursesetscount count of coursesets from
         * recertification path
         */
        var recertificationcoursesetscount = $('.recertification-coursesets').find('.coursesetbox').length;

        $(document).find('.coursesetbox').each(function () {
            if ($(this).next().hasClass('select')) {
                nextoperator = $(this).next().val();
            }
            /**
             * @description minimum courses completed
             * Only when is set that learner must
             * complete 'Some courses' (value = 3)
             */
            if ($(this).find('.inputs select').val() == 3) {
                mincourses = $(this).find('.completioncount').val();
                /**
                 * @description minimum courses completed
                 * Value must be a number and can't be greater
                 * than all chosen courses from set
                 */
                if (!mincourses.match(/^[0-9]+$/) || mincourses > $(this).find('.completioncount').data('count')) {
                    $(this).find('.coursesetmincourseserror').css("font-size", "20px");
                    $(this).find('.custom-error').css("display", "flex");
                    errors = 1;
                } else {
                    $(this).find('.coursesetmincourseserror').css("font-size", "0px");
                    $(this).find('.custom-error').css("display", "none");
                }
            }
        });
        /**
         * @description if any error
         * stop script run
         */
        if (errors != 0) {
            return false;
        }
        /**
         * @description If there are coursesets in recertification path
         * but certification path is not set
         */
        if ((recertificationcoursesetscount > 0) && (certificationcoursesetscount == 0)) {
            $(document).find('.certifpathcontenterror').css("display", "flex");
            return false;
        }
        /**
         * @description setSessionDetails gathering data and saving to session
         */
        $.when(setSessionDetails(certifid)).done(function (a1) {
            /**
             * @description save data to database
             */
            $.ajax({
                type: "POST",
                url: M.cfg.wwwroot + '/local/custom_certification/ajax/content.php',
                dataType: "JSON",
                data: {
                    action: 'savedata',
                    certifid: certifid
                },
                success: function () {
                    /**
                     * @description set no changes in form to avoid
                     * moodle leave page confirm
                     * Page reload
                     */
                    try {

                        M.core_formchangechecker.stateinformation.formchanged = false;
                    } catch (err) {

                    }
                    location.reload(true);
                }
            });
        });

    });

    /**
     * @description Use the existing certification content
     * Copy certification path to recertification
     */
    $('#id_usecertif').on('click', function () {
        $('.loader-wrapper').show();
        /**
         * @property integer certifid Certifcation ID
         */
        var certifid = $('#id_savedatabtn').data('id');
        /**
         * @description setSessionDetails called for copy certification
         * path to recertification from current page content
         */
        $.when(setSessionDetails(certifid, true)).done(function (a1) {
            /**
             * @description set no changes in form to avoid
             * moodle leave page confirm
             * Page reload
             */


            $.when(setSessionDetails(certifid)).done(function (a1) {
                try {
                    M.core_formchangechecker.stateinformation.formchanged = false;
                } catch (err) {

                }
                location.reload();
            });
        });

    });


    /**
     * @description Discard changes button
     */
    $('.cancelbtn').on('click', function () {
        /**
         * @description set no changes in form to avoid
         * moodle leave page confirm
         */
        try {
            M.core_formchangechecker.stateinformation.formchanged = false;
        } catch (err) {

        }
        /**
         * @property integer certifid Certification ID
         */
        var certifid = $(this).data('id');
        /**
         * @description Unset certification path, recertification path
         * and information about tab change from session
         */
        $.ajax({
            type: "POST",
            url: M.cfg.wwwroot + '/local/custom_certification/ajax/content.php',
            dataType: "JSON",
            data: {
                action: 'discardchanges',
                certifid: certifid
            },
            success: function () {
                location.reload();
            }
        });
    });
});


/**
 * @function checkIfContentChanged
 * @description Check changes in form
 * @return {boolean}
 */
function checkIfContentChanged() {
    /**
     * @property integer certifid Certification ID
     */
    var certifid = $('input[name=certifid]').val();
    $.ajax({
        type: "POST",
        url: M.cfg.wwwroot + '/local/custom_certification/ajax/content.php',
        data: {
            action: 'checkifchanged',
            certifid: certifid
        },
        dataType: "JSON",
        success: function (response) {
            if (response.resp == 'success') {
                /**
                 * @description trigger confirmation about moodle leave page
                 */
                M.core_formchangechecker.stateinformation.formchanged = true;
            }
        }
    });
    return true;
}
/**
 * @function closeAssignmentModal
 * @description Close popup window
 */
function closeAssignmentModal() {
    /**
     * @property integer reload Reload page confirmation
     */
    var reload = $('.close').data('reload');
    $('.assignment-modal-container').remove();
    /**
     * @description If any changes was made (add course/courseset)
     * page will reload
     */
    if (reload > 0) {
        /**
         * @description page reload without moodle
         * confirmation about leave page
         */
        try {
            M.core_formchangechecker.stateinformation.formchanged = false;
        } catch (err) {

        }
        location.reload();
    }
}

/**
 * @function createCourseset
 * @param integer certifid Certification ID
 * @param integer courseid Course ID
 * @param String certificationtype 'certification' or 'recertification'
 * @description After click on button to add course
 * from popup window
 */
function createCourseset(certifid, courseid, certificationtype) {
    /**
     * @property object modal body of popup window
     */
    var modal = $(".modal_body");
    /**
     * @property object chosen_element row from body of popup window
     */
    var chosen_element = modal.find("[data-id='" + courseid + "']");
    /**
     * @property integer coursesetid Courseset ID
     */
    var coursesetid = chosen_element.data('coursesetid');
    /**
     * @property String coursefullname fullname of course
     */
    var coursefullname = chosen_element.find('.user-personal p').html();
    /**
     * @description Save data to session
     */
    $.ajax({
        type: "POST",
        url: M.cfg.wwwroot + '/local/custom_certification/ajax/content.php',
        data: {
            action: 'addtotable',
            type: 'coursesets',
            certifid: certifid,
            coursesetid: coursesetid,
            courseid: courseid,
            certificationtype: certificationtype,
            coursefullname: coursefullname
        },
        dataType: "JSON",
        success: function (response) {
            /**
             * @description If no coursesetid set it will be create
             * new courseset, and update all courses html in popup window
             * with new generated coursesetid
             */
            if (coursesetid == 0) {
                /**
                 * @description update all courses rows with new Courseset ID
                 */
                modal.find('.user-row').each(function () {
                    $(this).attr('data-coursesetid', response.coursesetid);
                });
                $(document).find('#reset-btn').attr('data-coursesetid', response.coursesetid);
            }
            /**
             * @description Set information about need reload
             */
            $(document).find(".close").attr('data-reload', 1);
            /**
             * @description Remove row counter
             * and increase row counter div
             */
            chosen_element.find('#counter').width(40).html(' ');
            /**
             * @description remove add course button
             */
            chosen_element.find('input').remove();
        }
    });
}
/**
 * @function coursesetControl
 * @description Sort course/courseset move up/down
 * @param integer coursesetid Courseset ID
 * @param integer courseid Course ID
 * @param String param What should be sorted
 * 'coursesort' / 'coursesetsort'
 * @param String destination Where should be moved
 * 'moveup' / 'movedown'
 * @param integer certifid Certification ID
 * @param String certificationtype 'certification' / 'recertification'
 */
function coursesetControl(coursesetid, courseid, param, destination, certifid, certificationtype) {
    /**
     * @description setSessionDetails(certifid) for gather
     * and save to Session data from page
     */
    $.when(setSessionDetails(certifid)).done(function (a1) {
        $.ajax({
            type: "POST",
            url: M.cfg.wwwroot + '/local/custom_certification/ajax/content.php',
            data: {
                action: 'sort',
                param: param,
                coursesetid: coursesetid,
                courseid: courseid,
                destination: destination,
                certificationtype: certificationtype,
                certifid: certifid
            },
            dataType: "JSON",
            success: function () {
                /**
                 * @description page reload without moodle
                 * confirmation about leave page
                 */
                try {
                    M.core_formchangechecker.stateinformation.formchanged = false;
                } catch (err) {

                }
                location.reload();
            }
        });
    });
}

/**
 * @function setSessionDetails
 * @param integer certifid Certification ID
 * @param integer usecertif flag that certification should be copied
 */
function setSessionDetails(certifid, usecertif) {

    usecertif = typeof usecertif !== 'undefined' ? usecertif : false;

    /**
     * @property array coursesets coursesets from page
     */
    var coursesets = [];
    /**
     * @property object onecourseset current courseset from page (foreach)
     */
    var onecourseset = {};
    /**
     * @property object onecourse current course from page (foreach)
     */
    var onecourse = {};
    /**
     * @property array courses courses from courseset
     */
    var courses = [];
    /**
     *  @property integer nextoperator condition between two coursesets
     */
    var nextoperator = 0;
    /**
     * @property integer coursesetid Courseset ID
     */
    var coursesetid = 0;
    /**
     * @property boolean userecertif checkbox value
     * shows recertification path settings possibility
     */
    var userecertif = $(document).find('#id_userecertif').is(':checked');
    /**
     * @property boolean usecertificationcontent copy
     * certification path to recertification path
     */
    var usecertificationcontent = usecertif;
    /**
     * @property integer coursesetsortorder coursesets order iterator
     */
    var coursesetsortorder = 0;
    /**
     * @property integer certificationtype
     * 0 - certification path
     * 1 - recertification path
     */
    var certificationtype = 0;
    /**
     * @property integer previouscertificationtype
     * to know when to reset sort order
     * iteration of coursesets in certification path
     */
    var previouscertificationtype = 0;


    $(document).find('.coursesetbox').each(function () {
        coursesetid = $(this).data('coursesetid');
        certificationtype = $(this).data('certificationtype');
        if ($(this).next().hasClass('select')) {
            nextoperator = $(this).next().val();
        }else{
            nextoperator = 1;
        }

        $(this).find('.course').each(function () {
            var courseid = $(this).data('courseid');

            onecourse = {
                courseid: courseid,
                coursesetid: coursesetid,
                certifid: certifid,
                fullname: $(this).find('span').html()
            };
            courses.push(onecourse);
        });
        /**
         * @description first courseset from recertification path
         * will reset courseset sort order
         */
        if (certificationtype != previouscertificationtype) {
            coursesetsortorder = 0;
        }
        onecourseset = {
            id: coursesetid,
            label: $(this).find('.coursesetname').val(),
            certifid: certifid,
            sortorder: coursesetsortorder,
            certifpath: certificationtype,
            completiontype: $(this).find('.inputs select').val(),
            mincourses: $(this).find('.completioncount').val(),
            nextoperator: nextoperator,
            courses: courses
        };
        previouscertificationtype = certificationtype;
        coursesets.push(onecourseset);
        courses = [];
        coursesetsortorder++;
    });
    /**
     * @property String action
     */
    var action = 'session';
    /**
     * @description If set confirmation about use recertification
     * and about clone certification path
     * (checkboxes)
     */
    if (usecertificationcontent && userecertif) {
        action = 'clonecertificationcontent';
    }

    /**
     * @description If action is session data will be save in Session
     * If action is clonecertificationcontent certification path
     * will be copied to recertification path
     */
    return $.ajax({
        type: "POST",
        url: M.cfg.wwwroot + '/local/custom_certification/ajax/content.php',
        dataType: "JSON",
        data: {
            action: action,
            coursesets: JSON.stringify(coursesets),
            certifid: certifid
        }
    });

}
/**
 * @function search
 * @param String type always 'coursesets'
 * @param integer certifid Certification ID
 * @param String action 'search'/'reset'/'append'
 * @param integer coursesetid Courseset ID
 * @param String certificationtype 'certification' / 'recertification'
 */

function search(type, certifid, action, coursesetid, certificationtype) {
    /**
     * @property object modalbody body of popup window
     */
    var modalbody = $(document).find('.modal_body');
    /**
     * @property String word Value of search input
     */
    var word = '';
    /**
     * @property array existingcourses array with names
     * of courses in courseset
     */
    var existingcourses = [];
    /**
     * @property object coursesetbox courseset html block
     */
    var coursesetbox = $(document).find("[data-coursesetid='" + coursesetid + "']");

    /**
     * @description When search button was clicked
     */
    if (action == 'search') {
        word = $(document).find('#search-input').val();
        word = word.replace(/\s+/g, '');
    }
    /**
     * @description When adding courses to existing courseset
     */
    if (action == 'append') {
        coursesetbox.find('.course').each(function () {
            existingcourses.push($(this).find('span').html());
        });
        word = JSON.stringify(existingcourses);
    }
    /**
     * @description When reset button was clicked
     */
    if (action == 'reset') {
        $('#search-input').val('');
        word = 'reset';
    }
    /**
     * @description Ajax will run when search input is not empty
     */
    if (word.length > 0) {
        /**
         * @description if search or reset triggered
         * clean modal body for new search result
         */
        if (action != 'append') {
            modalbody.html('');
            coursesetid = $(document).find('#reset-btn').data('coursesetid');
        }
         $.ajax({
            type: "GET",
            url: M.cfg.wwwroot + '/local/custom_certification/ajax/content.php?action=modal&certifid=' + certifid + '&param=' + type + '&word=' + word + '&coursesetid=' + coursesetid + '&type=' + action + '&certificationtype=' + certificationtype,
            dataType: "JSON",
            success: function (response) {
                if (action == 'search' || action == 'reset') {
                    /**
                     * @description Print result after search / reset
                     */
                    modalbody.append(response.content);
                    /**
                     * @description Update counter value
                     */
                    $(document).find('#count-value').html(response.counter);
                } else {
                    /**
                     * If append action
                     */
                    $("body").append(response);
                }
            }
        });
    }
}
/**
 * @function deleteCourseset
 * @param integer coursesetid Courseset ID
 * @param integer certifpath
 * 0 - certification path
 * 1 - recertification path
 * @param integer certifid Certification ID
 */
function deleteCourseset(coursesetid, certifpath, certifid) {
    /**
     * @description setSessionDetails(certifid) for gather
     * current data and save to session
     */
    $.when(setSessionDetails(certifid)).done(function (a1) {
        /**
         * @description Delete courseset form Session
         */
        $.ajax({
            type: "POST",
            url: M.cfg.wwwroot + '/local/custom_certification/ajax/content.php',
            dataType: "JSON",
            data: {
                action: 'deletecourseset',
                coursesetid: coursesetid,
                certifpath: certifpath,
                certifid: certifid
            },
            success: function () {
                /**
                 * @description Prevent display moodle confirm about leave page
                 */
                try {
                    M.core_formchangechecker.stateinformation.formchanged = false;
                } catch (err) {

                }
                location.reload();
            }
        });
    });
}

/**
 * @function deleteCourse
 * @param integer coursesetid Courseset ID
 * @param integer courseid Course ID
 * @param integer certifpath
 * 0 - certification path
 * 1 - recertification path
 * @param integer certifid Certification ID
 */
function deleteCourse(coursesetid, courseid, certifpath, certifid) {
    /**
     * @description setSessionDetails(certifid) gather information
     * and save to Session
     */
    $.when(setSessionDetails(certifid)).done(function (a1) {
        /**
         * @description Delete course from Session
         */
        $.ajax({
            type: "POST",
            url: M.cfg.wwwroot + '/local/custom_certification/ajax/content.php',
            dataType: "JSON",
            data: {
                action: 'deletecourse',
                coursesetid: coursesetid,
                courseid: courseid,
                certifpath: certifpath,
                certifid: certifid
            }
        }).done(function(data){
            /**
             * @description Prevent display moodle leave page confirm
             */
            try {
                M.core_formchangechecker.stateinformation.formchanged = false;
            } catch (err) {

            }
            location.reload();
        });
    });
}

/**
 * @function checkRecertificationAvailable
 */
function checkRecertificationAvailable() {
    /**
     * @property object recertificationcheckbox checkbox to use recertification path
     */
    var recertificationcheckbox = $(document).find('#id_userecertif');
    /**
     * @property integer certificationcoursesetscount count of coursesets in certification path
     */
    var certificationcoursesetscount = $('.certification-coursesets').find('.coursesetbox').length;
    /**
     * @property integer recertificationcoursesetscount count of coursesets in recertification path
     */
    var recertificationcoursesetscount = $('.recertification-coursesets').find('.coursesetbox').length;
    /**
     * @description Disable use recertification possiblity when empty certification path and recertification path
     */
    if ((certificationcoursesetscount == 0) && (recertificationcoursesetscount == 0)) {
        recertificationcheckbox.attr('disabled', 'disabled');
    }
}

$(document).on('change', '.inputs select', function () {
    /**
     * @description If select 'Learner must complete'
     * has value 'some courses' enable 'minimum courses completed'
     * field
     */
    if ($(this).val() == 3) {
        $(this).next().removeAttr('disabled');
    } else {
        $(this).next().val('');
        $(this).next().attr('disabled', 'disabled');
    }
});

$(document).on('keyup', '.coursesetname', function () {
    /**
     * @property integer coursesetid Courseset ID
     */
    var coursesetid = $(this).data('coursesetid');
    /**
     * Copy name of courseset to left corner of box during
     * writing
     */
    $(document).find("[data-coursesetid='" + coursesetid + "']").find('.headerinfo').html($(this).val());
});