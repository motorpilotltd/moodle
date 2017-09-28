/**
 * Add ruleset
 */
$('#id_addruleset').on('click', function () {
    var rulesetscount = $('#rulesetscontainer').find('.ruleset').length;
    ++rulesetscount;
    $.ajax({
        type: "GET",
        url: M.cfg.wwwroot + '/local/dynamic_cohorts/ajax/dynamic_cohorts.php?action=addruleset&rulesetscount=' + rulesetscount,
        dataType: "HTML",
        success: function (response) {
            $('#rulesetscontainer').append(response);
        }
    });
});

/**
 * Remove ruleset
 */
$(document).on('click', '.deleteruleset', function (e) {
    e.preventDefault();
    var deleterulesetconfirm = confirm(M.util.get_string('deleteruleset', 'local_dynamic_cohorts'));

    if (deleterulesetconfirm) {
        $(this).parent().remove();
        $(document).find('.counter').each(function (index) {
            $(this).html(++index);
        });
    }

});

/**
 * Remove rule
 */
$(document).on('click', '.deleterule', function (e) {
    e.preventDefault();
    var deleteruleconfirm = confirm(M.util.get_string('deleterule', 'local_dynamic_cohorts'));

    if (deleteruleconfirm) {
        $(this).parent().remove();
    }

});

/**
 * Edit rule
 */
$(document).on('click', '.editrule', function (e) {
    e.preventDefault();
    $(document).find('.edit_saverule').remove();
    var rulesetid = $(this).data('rulesetid');
    var ruleid = $(this).data('ruleid');
    var field = $("input[name='field[" + rulesetid + "][" + ruleid + "]']").val()
    var criteriatype = $("input[name='criteriatype[" + rulesetid + "][" + ruleid + "]']").val()
    var value = $("input[name='value[" + rulesetid + "][" + ruleid + "]']").val()

    var element = $(this);
    $.ajax({
        type: "GET",
        url: M.cfg.wwwroot + '/local/dynamic_cohorts/ajax/dynamic_cohorts.php?action=editrule&rulesetid=' + rulesetid + '&ruleid=' + ruleid + '&field=' + field + '&criteriatype=' + criteriatype + '&value=' + value,
        dataType: "HTML",
        success: function (response) {
            element.after(response);
        }
    });

});

/**
 * Save rule
 */
$(document).on('click', '.saverule', function () {
    var fieldset = $(this).parent();
    var rulesetid = $(this).data('rulesetid');
    var ruleid = $(this).data('ruleid');
    var field = $("select[name='field_" + rulesetid + "'] option:selected").val();
    var criteriatype = $("select[name='criteriatype_" + rulesetid + "'] option:selected").val();
    var value = $("input[name='value_" + rulesetid + "'].edit").val();

    if(value == undefined){
        var value = $("select[name='value_year_" + rulesetid + "']").val() + "-" +
            $("select[name='value_month_" + rulesetid + "']").val() + "-" +
            $("select[name='value_day_" + rulesetid + "']").val();

        var timevalue = $("select[name='value_hour_" + rulesetid + "']").val() + ":" +
            $("select[name='value_minute_" + rulesetid + "']").val();

        if( timevalue != 'undefined:undefined'){
            value = value + " " + timevalue;
        }
    }
    var editingelement = $(document).find("input[name='field[" + rulesetid + "][" + ruleid + "]']").parent();

    /**
     * when criteria type: is empty, is checked, is not checked value can be empty
     */
    if (value != '' || criteriatype == 5 || criteriatype == 7 || criteriatype == 8) {
        $.when(get_rule(rulesetid, ruleid, field, criteriatype, value)).done(function (response) {
            fieldset.remove();
            editingelement.after(response);
            editingelement.remove();
        });

    }
});

/**
 * Show / hide dynamic cohorts specific form part
 */
$("#id_type").change(function () {
    if (this.value == 1) { // check if cohort type is dynamic and show rules div
        $('.dynamic-cohort-rules').show();
    } else {
        $('.dynamic-cohort-rules').hide();
    }
});

/**
 * Change cirtiera types and value field type basing on selected field type
 */
$(document).on('change', '.field_select', function (e) {
    var rulesetid = $(this).data('rulesetid');
    var edit = $(this).data('edit');
    var field = $(this).val();
    var value = $('input[name="value_' + rulesetid + '"]').val();

    $.ajax({
        type: "GET",
        url: M.cfg.wwwroot + '/local/dynamic_cohorts/ajax/dynamic_cohorts.php',
        data: {
            action: 'getcriteriatypes',
            field: field
        },
        success: function (response) {
            json = jQuery.parseJSON(response);
            var $el = $("#menucriteriatype_" + rulesetid);
            $el.empty(); // remove old options
            $.each(json, function(key, value) {
                $el.append($("<option></option>")
                    .attr("value", key).text(value));
            });
        }
    });
    $.ajax({
        type: "GET",
        url: M.cfg.wwwroot + '/local/dynamic_cohorts/ajax/dynamic_cohorts.php',
        data: {
            action: 'getvaluefield',
            rulesetid: rulesetid,
            value: value,
            edit: edit,
            field: field
        },
        success: function (response) {
            $('#value_field_' + rulesetid).html(response);
        }
    });
});

$('#id_submitbutton').click(function(e){
    if($('#id_type').val() == 1){

        if($('input[name="rulesetid[]"]').length == 0){
            e.preventDefault();
            alert(M.util.get_string('norulesets', 'local_dynamic_cohorts'));
        }else{
            var showalert = false;
            $('input[name="rulesetid[]"]').each(function(){
                var rulesetid = $(this).val();
                showalert = true;
                $('input[name^="field[' + rulesetid + ']"]').each(function(){
                    showalert = false;
                });
            });
            if(showalert){
                e.preventDefault();
                alert(M.util.get_string('norulesinruleset', 'local_dynamic_cohorts'));
            }
        }
    }

});

/**
 * Add rule to ruleset
 */
$(document).on('click', '.addrule', function (e) {

    var rulesetid = $(this).data('rulesetid');
    var field = $("select[name='field_" + rulesetid + "'] option:selected").val();
    var criteriatype = $("select[name='criteriatype_" + rulesetid + "'] option:selected").val();
    var nextruleid = parseInt($("input[name='ruleid_" + rulesetid + "']").val()) + 1;

    var value = $("input[name='value_" + rulesetid + "'].add").val();
    if(value == undefined){
        var value = $("select[name='value_year_" + rulesetid + "']").val() + "-" +
            $("select[name='value_month_" + rulesetid + "']").val() + "-" +
            $("select[name='value_day_" + rulesetid + "']").val();

        var timevalue = $("select[name='value_hour_" + rulesetid + "']").val() + ":" +
            $("select[name='value_minute_" + rulesetid + "']").val();

        if( timevalue != 'undefined:undefined'){
            value = value + " " + timevalue;
        }
    }

    /**
     * when criteria type: is empty, is checked, is not checked value can be empty 
     */
    if (value != '' || criteriatype == 5 || criteriatype == 7 || criteriatype == 8) {
        $(document).find('.edit_saverule').remove();
        $.when(get_rule(rulesetid, nextruleid, field, criteriatype, value)).done(function (response) {
            $('.rules_' + rulesetid).append(response);
            $("input[name='value_" + rulesetid + "']").val('');
            $("input[name='ruleid_" + rulesetid + "']").val(nextruleid);
        });

    }
});

function get_rule(rulesetid, ruleid, field, criteriatype, value) {
    return $.ajax({
        type: "GET",
        url: M.cfg.wwwroot + '/local/dynamic_cohorts/ajax/dynamic_cohorts.php',
        data: {
            action: 'addrule',
            rulesetid: rulesetid,
            field: field,
            criteriatype: criteriatype,
            value: value,
            ruleid: ruleid
        },
        dataType: "HTML",
        success: function (response) {
            return response;
        }
    });
}
