$(document).ready(function () {
    var requestRunning = false;
    /**
     * @description Add message
     */
    $('#id_addmessage').on('click', function () {
        /**
         * @property String messagename Message name
         */
        var messagename = $('#id_messagetypes :selected').html();
        /**
         * @property integer messagetype Message type
         */
        var messagetype = $('#id_messagetypes :selected').val();
        /**
         * @description Print message box
         */
        $.ajax({
            type: "GET",
            url: M.cfg.wwwroot + '/local/custom_certification/ajax/messages.php?action=displaybox&messagename=' + messagename + '&messagetype=' + messagetype,
            dataType: "HTML",
            success: function (response) {
                $('.message-container').append(response);
            }
        });
    });

    /**
     * @description Save data
     */
    $('#savemessage').on('click', function () {


        /**
         * @property array messages All messages
         */
        var messages = [];
        /**
         * @property object message One message from html (foreach)
         */
        var message = {};
        /**
         * @property integer certifid Certification ID
         */
        var certifid = $(this).data('certifid');
        /**
         * @property integer triggertime
         * @description message trigger time available
         * only in 'CERTIFICATION WINDOW OPEN EMAIL'
         * and 'CERTIFICATION DUE EMAIL' cases
         */
        var triggertime = 0;
        /**
         * @property integer messagetype Message type
         */
        var messagetype = 0;
        /**
         * @property array emailaddresses all email addresses splitted by ';'
         */
        var emailaddresses = [];
        /**
         * @property boolean emailerrors specified error occurrence
         */
        var emailerrors = false;
        /**
         * @property boolean anyerror any error occurrence, prevent ajax run
         */
        var anyerror = false;
        $(document).find('.message-box').each(function () {
            /**
             * @property object messagebox message block from page
             */
            var messagebox = $(this);
            /**
             * @property String emailinputvalue value from email input
             */
            var emailinputvalue = $(this).find('.additionalinput').val();
            /**
             * @property boolean emailcheckboxconfirm additional recipent checkbox value
             */
            var emailcheckboxconfirm = messagebox.find('.additionalcheck').is(':checked');
            /**
             * @property String emailsubject Message subject
             */
            var emailsubject = messagebox.find('.messagesubject').val();

            emailerrors = false;

            if (emailinputvalue.length > 0 && emailcheckboxconfirm) {
                messagebox.find('.emptyemail').hide();
                emailaddresses = emailinputvalue.split(';');

                $.each(emailaddresses, function (key, value) {
                    /**
                     * @description email address validation
                     */
                    if (!isEmail(value)) {
                        emailerrors = true;
                        anyerror = true;
                    }
                });
            }
            /**
             * @description error alert for invalid email address
             */
            if (emailerrors) {
                messagebox.find('.invalidemail').show();
            } else {
                messagebox.find('.invalidemail').hide();
                /**
                 * @description check if triggertime input exist
                 */
                if (messagebox.find('.messagetriggertime').length > 0) {
                    triggertime = messagebox.find('.messagetriggertime').val();
                }
                message = {
                    messageid: messagebox.data('id'),
                    messagetype: messagebox.data('messagetype'),
                    subject: emailsubject,
                    body: messagebox.find('.messagetext').val(),
                    recipient: emailcheckboxconfirm,
                    recipientemail: emailinputvalue,
                    triggertime: triggertime
                }
                messages.push(message);
                triggertime = 0;
            }
            /**
             * @description If checkbox of additional email address is enabled
             * but there is empty input for email address show alert
             */
            if (emailinputvalue.length == 0 && emailcheckboxconfirm) {
                messagebox.find('.emptyemail').show();
                anyerror = true;
            }

            /**
             * @description if email subject is empty show alert
             */
            if (emailsubject.length == 0) {
                messagebox.find('.emptysubject').show();
                anyerror = true;
            } else {
                messagebox.find('.emptysubject').hide();
            }
        });
        /**
         * @description If any error occurred prevent ajax run
         */
        if (anyerror) {
            return false;
        }
        if (requestRunning) {
            return false;
        }
        requestRunning = true;
        /**
         * @description Save data
         */
        $.ajax({
            type: "POST",
            url: M.cfg.wwwroot + '/local/custom_certification/ajax/messages.php',
            dataType: "JSON",
            data: {
                action: 'save',
                certifid: certifid,
                messages: messages
            },
            success: function () {
                /**
                 * @description Prevent moodle leave page confirmation display
                 */
                M.core_formchangechecker.stateinformation.formchanged = false;
                window.location = window.location.href;
            }
        });
    });

   
    $(document).on('keyup keydown', '.messagetriggertime', function () {
        /**
         * @property String value value of trigger time field
         */
        var value = $(this).val();
        /**
         * @description Prevent type other characters than 0 - 9 numbers
         */
        if (!value.match(/^[0-9]+$/)) {
            $(this).val(value.substring(0, value.length - 1));
        }
    });

    $(document).on('click', '.additionalcheck', function () {
        /**
         * @property object messagebox block of message from page
         */
        var messagebox = $(this).parent();
        /**
         * @description Show additional input for email addresses
         */
        if ($(this).is(':checked')) {
            messagebox.find('.additionalrecipientbox').show();
        } else {
            /**
             * @description Hide additional input for email addresses
             * reset email addresses input value
             */
            messagebox.find('.additionalrecipientbox').hide();
            messagebox.find('.additionalinput').val('');
        }
    });
});

/**
 * @function deleteMessage
 * @param object element message delete button
 * @description Delete message
 */
function deleteMessage(element) {
    /**
     * @property integer messageid Message ID
     */
    var messageid = $(element).data('id');
    /**
     * @description If message not saved in database
     */
    if (messageid == 0) {
        $(element).parent().remove();
    } else {
        /**
         * @description Delete message from database
         */
        $.ajax({
            type: "POST",
            url: M.cfg.wwwroot + '/local/custom_certification/ajax/messages.php',
            dataType: "JSON",
            data: {
                action: 'delete',
                messageid: messageid
            },
            success: function () {
            }
        });
        /**
         * @description remove message block from page
         */
        $(element).parent().remove();
    }
}

/**
 * @function isEmail
 * @param String email email address
 * @description Validation email address
 * @return {boolean}
 */
function isEmail(email) {
    /**
     * @property RegExp regex email address validation
     */
    var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
    return regex.test(email);
}
